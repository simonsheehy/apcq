/**
 * Tracks the portions of a Vimeo video actually watched and reports them to the server.
 *
 * The employee cannot skip ahead: going past the furthest point watched without gaps sends the
 * video back there. Going back is always allowed.
 *
 * Segments are [start, end] ranges in seconds. Playback only extends a segment when the video
 * advanced no faster than real time at the current speed: any skip (seek, scrubbing, keyboard
 * shortcuts) closes it, so skipped portions are never counted. The full list is resent on
 * every report: the server merges it idempotently.
 */
const REPORT_INTERVAL_MS = 10000;
const STEP_TOLERANCE_SECONDS = 0.25;
const SEGMENT_TOLERANCE_SECONDS = 1;
const FORWARD_TOLERANCE_SECONDS = 1.5;
const SKIP_NOTICE_DURATION_MS = 4000;
const RETRY_DELAY_MS = 10000;
const SESSION_EXPIRED_STATUSES = [401, 419];

function mergeSegments(segments) {
    const sorted = segments.filter(([start, end]) => end > start).sort((a, b) => a[0] - b[0]);
    const merged = [];

    for (const [start, end] of sorted) {
        const last = merged[merged.length - 1];

        if (last && start <= last[1] + 1) {
            last[1] = Math.max(last[1], end);
        } else {
            merged.push([start, end]);
        }
    }

    return merged;
}

/**
 * End of the portion watched without gaps from the start of the video.
 */
function contiguousEnd(segments) {
    let end = 0;

    for (const [start, segmentEnd] of mergeSegments(segments)) {
        if (start > end + 1) {
            break;
        }

        end = Math.max(end, segmentEnd);
    }

    return end;
}

function initTrainingPlayer(container) {
    const iframe = container.querySelector('iframe');
    const progressUrl = container.dataset.progressUrl;
    const resumePosition = Number(container.dataset.resumePosition || 0);
    const progressBar = document.querySelector('[data-progress-bar]');
    const progressLabel = document.querySelector('[data-progress-label]');
    const skipNotice = document.querySelector('[data-skip-notice]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const player = new window.Vimeo.Player(iframe);

    let closedSegments = mergeSegments(JSON.parse(container.dataset.watchedSegments || '[]'));
    let currentSegment = null;
    let position = 0;
    let duration = 0;
    let isPlaying = false;
    let hasUnsentProgress = false;
    let playbackRate = 1;
    let lastUpdateAt = null;
    let segmentStartedAt = null;

    const closeSegment = () => {
        if (currentSegment && currentSegment[1] > currentSegment[0]) {
            closedSegments = mergeSegments([...closedSegments, currentSegment]);
        }

        currentSegment = null;
        segmentStartedAt = null;
    };

    const startSegment = (seconds, now) => {
        currentSegment = [seconds, seconds];
        segmentStartedAt = now;
    };

    // Both checks are needed: the first catches a single jump, the second a series of small
    // jumps (scrubbing) that would each look like normal playback.
    const isNormalPlayback = (seconds, now, elapsedSeconds) => {
        const advance = seconds - currentSegment[1];
        const segmentLength = seconds - currentSegment[0];
        const segmentElapsedSeconds = (now - segmentStartedAt) / 1000;

        return advance >= 0
            && advance <= elapsedSeconds * playbackRate + STEP_TOLERANCE_SECONDS
            && segmentLength <= segmentElapsedSeconds * playbackRate + SEGMENT_TOLERANCE_SECONDS;
    };

    const furthestAllowedPosition = () => contiguousEnd(currentSegment ? [...closedSegments, currentSegment] : closedSegments);

    let skipNoticeTimeout = null;

    const showSkipNotice = () => {
        if (!skipNotice) {
            return;
        }

        skipNotice.hidden = false;
        clearTimeout(skipNoticeTimeout);
        skipNoticeTimeout = setTimeout(() => {
            skipNotice.hidden = true;
        }, SKIP_NOTICE_DURATION_MS);
    };

    const sendBackIfSkippedAhead = (seconds) => {
        const limit = furthestAllowedPosition();

        if (seconds <= limit + FORWARD_TOLERANCE_SECONDS) {
            return false;
        }

        position = limit;
        player.setCurrentTime(limit);
        showSkipNotice();

        return true;
    };

    const updateProgressDisplay = ({ progress_percent: percent, completed }) => {
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
        }

        if (progressLabel) {
            progressLabel.textContent = completed ? 'Formation complétée' : `${percent} % visionné`;
        }
    };

    const showSessionExpired = () => {
        if (progressLabel) {
            progressLabel.textContent = 'Session expirée : rechargez la page pour enregistrer votre progression.';
        }
    };

    let retryTimeout = null;

    // Segments are resent in full, so a failed report is recovered by any later one.
    const retryLater = () => {
        hasUnsentProgress = true;

        if (!retryTimeout) {
            retryTimeout = setTimeout(() => {
                retryTimeout = null;
                report();
            }, RETRY_DELAY_MS);
        }
    };

    const report = ({ keepalive = false } = {}) => {
        if (!duration || !hasUnsentProgress) {
            return;
        }

        hasUnsentProgress = false;

        const segments = mergeSegments(currentSegment ? [...closedSegments, currentSegment] : closedSegments);

        fetch(progressUrl, {
            method: 'POST',
            keepalive,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ segments, duration, position }),
        })
            .then((response) => {
                if (response.ok) {
                    return response.json().then(updateProgressDisplay);
                }

                if (SESSION_EXPIRED_STATUSES.includes(response.status)) {
                    showSessionExpired();

                    return;
                }

                retryLater();
            })
            .catch(retryLater);
    };

    player.getDuration().then((value) => {
        duration = value;

        const startPosition = Math.min(resumePosition, furthestAllowedPosition());

        if (startPosition > 5 && startPosition < duration - 5) {
            player.setCurrentTime(startPosition);
        }
    });

    player.on('timeupdate', ({ seconds }) => {
        const now = performance.now();
        const elapsedSeconds = lastUpdateAt === null ? 0 : (now - lastUpdateAt) / 1000;

        position = seconds;
        lastUpdateAt = now;

        if (!isPlaying) {
            return;
        }

        if (currentSegment !== null && isNormalPlayback(seconds, now, elapsedSeconds)) {
            currentSegment[1] = seconds;
            hasUnsentProgress = true;

            return;
        }

        closeSegment();

        if (!sendBackIfSkippedAhead(seconds)) {
            startSegment(seconds, now);
        }
    });

    player.on('play', () => {
        isPlaying = true;
        lastUpdateAt = performance.now();
    });

    player.on('playbackratechange', ({ playbackRate: rate }) => {
        closeSegment();
        playbackRate = rate;
    });

    player.on('seeking', () => closeSegment());

    player.on('seeked', ({ seconds }) => {
        sendBackIfSkippedAhead(seconds);
    });

    player.on('pause', () => {
        isPlaying = false;
        closeSegment();
        report();
    });

    player.on('ended', () => {
        isPlaying = false;
        closeSegment();
        report();
    });

    setInterval(() => {
        if (isPlaying) {
            report();
        }
    }, REPORT_INTERVAL_MS);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            report({ keepalive: true });
        }
    });

    window.addEventListener('pagehide', () => report({ keepalive: true }));
}

document.querySelectorAll('[data-training-player]').forEach(initTrainingPlayer);
