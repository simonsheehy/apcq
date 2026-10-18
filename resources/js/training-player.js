/**
 * Tracks the portions of a Vimeo video actually watched and reports them to the server.
 *
 * Segments are [start, end] ranges in seconds. Playback only extends a segment when the video
 * advanced no faster than real time at the current speed: any skip (seek, scrubbing, keyboard
 * shortcuts) closes it, so skipped portions are never counted. The full list is resent on
 * every report: the server merges it idempotently.
 */
const REPORT_INTERVAL_MS = 10000;
const STEP_TOLERANCE_SECONDS = 0.25;
const SEGMENT_TOLERANCE_SECONDS = 1;
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

function initTrainingPlayer(container) {
    const iframe = container.querySelector('iframe');
    const progressUrl = container.dataset.progressUrl;
    const resumePosition = Number(container.dataset.resumePosition || 0);
    const progressBar = document.querySelector('[data-progress-bar]');
    const progressLabel = document.querySelector('[data-progress-label]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const player = new window.Vimeo.Player(iframe);

    let closedSegments = [];
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

        if (resumePosition > 5 && resumePosition < duration - 5) {
            player.setCurrentTime(resumePosition);
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
        } else {
            closeSegment();
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
