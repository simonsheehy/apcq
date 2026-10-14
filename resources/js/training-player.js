/**
 * Tracks the portions of a Vimeo video actually watched and reports them to the server.
 *
 * Segments are [start, end] ranges in seconds. A segment closes whenever playback jumps
 * (seek or skip), so skipped portions are never counted. The full list is resent on every
 * report: the server merges it idempotently.
 */
const REPORT_INTERVAL_MS = 10000;
const MAX_CONTINUOUS_GAP_SECONDS = 2;
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

    const closeSegment = () => {
        if (currentSegment && currentSegment[1] > currentSegment[0]) {
            closedSegments = mergeSegments([...closedSegments, currentSegment]);
        }

        currentSegment = null;
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
        position = seconds;

        if (!isPlaying) {
            return;
        }

        if (currentSegment === null) {
            currentSegment = [seconds, seconds];
        } else if (seconds >= currentSegment[1] && seconds - currentSegment[1] <= MAX_CONTINUOUS_GAP_SECONDS) {
            currentSegment[1] = seconds;
            hasUnsentProgress = true;
        } else {
            closeSegment();
            currentSegment = [seconds, seconds];
        }
    });

    player.on('play', () => {
        isPlaying = true;
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
