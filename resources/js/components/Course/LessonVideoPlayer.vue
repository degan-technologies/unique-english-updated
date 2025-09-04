<script setup>
import _ from "lodash";
import Axios from "axios";
import videojs from "video.js";
import "video.js/dist/video-js.css";
import { onMounted, onBeforeUnmount, ref, watch } from "vue";

// Required for quality selection
import "videojs-contrib-quality-levels";
import "videojs-hls-quality-selector";

const props = defineProps({
    selectedLesson: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
});

const videoPlayer = ref(null);
const player = ref(null);
const videoWatched = ref(false);
const totalDuration = ref(0);

const storeContentProgress = _.throttle(() => {
    if (!player.value || !props.selectedLesson?.id) return;

    const currentTime = player.value.currentTime();
    const videoDuration = player.value.duration();
    totalDuration.value = videoDuration;

    const formattedProgress = new Date(currentTime * 1000)
        .toISOString()
        .substr(11, 8);

    const completionPercentage =
        videoDuration > 0
            ? Math.min(100, (currentTime / videoDuration) * 100)
            : 0;

    const isCompleted = completionPercentage >= 95 || videoWatched.value;

    Axios.post("/api/coursecontent/progress", {
        course_content_id: props.selectedLesson?.id,
        progress: formattedProgress,
        duration_watched: currentTime,
        total_duration: videoDuration,
        is_completed: isCompleted,
        completion_percentage: completionPercentage.toFixed(2),
        last_watched_at: new Date().toISOString(),
    }).catch((error) => console.error("Progress save error:", error));
}, 30000);

function formatTime(seconds) {
    if (Number.isNaN(seconds)) return "0:00";
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60)
        .toString()
        .padStart(h ? 2 : 1, "0");
    const s = Math.floor(seconds % 60)
        .toString()
        .padStart(2, "0");
    return h ? `${h}:${m}:${s}` : `${m}:${s}`;
}

const getSourceObj = (url) => ({
    src: url,
    type: url?.includes(".m3u8") ? "application/x-mpegURL" : "video/mp4",
});

onMounted(() => {
    // Register custom time display component
    if (!videojs.getComponent("CombinedTimeDisplay")) {
        const Component = videojs.getComponent("Component");

        videojs.registerComponent(
            "CombinedTimeDisplay",
            class extends Component {
                constructor(player, options) {
                    super(player, options);
                    this.updateContent();
                    this.on(player, "timeupdate", this.updateContent);
                    this.on(player, "durationchange", this.updateContent);
                }

                createEl() {
                    return videojs.dom.createEl("div", {
                        className:
                            "vjs-time-control vjs-time-display vjs-combined-time-display",
                    });
                }

                updateContent() {
                    const currentTime = this.player().currentTime();
                    const duration = this.player().duration();
                    this.el().innerHTML = `
                    <span class="vjs-current-time">${formatTime(
                        currentTime
                    )}</span>
                    <span class="vjs-time-divider"> / </span>
                    <span class="vjs-duration">${formatTime(duration)}</span>
                `;
                }
            }
        );
    }

    const mergedOptions = {
        autoplay: false,
        controls: true,
        preload: "auto",
        html5: {
            vhs: {
                overrideNative: true,
                enableLowInitialPlaylist: true,
                smoothQualityChange: true,
            },
            nativeAudioTracks: false,
            nativeVideoTracks: false,
        },
        playbackRates: [0.5, 1, 1.25, 1.5, 2],
        controlBar: {
            children: [
                "playToggle",
                "volumePanel",
                "progressControl",
                "CombinedTimeDisplay",
                "playbackRateMenuButton",
                "qualitySelector",
                "fullscreenToggle",
            ],
        },
        ...props.options,
    };

    player.value = videojs(videoPlayer.value, mergedOptions, function () {
        this.hlsQualitySelector({
            displayCurrentQuality: true,
            vjsIconClass: "vjs-icon-hd",
        });

        this.on("timeupdate", storeContentProgress);
        this.on("loadedmetadata", () => {
            totalDuration.value = this.duration();
        });
        this.on("ended", () => {
            videoWatched.value = true;
            storeContentProgress();
            storeContentProgress.flush();
        });
    });

    player.value.ready(() => {
        if (!player.value.controlBar.getChild("CombinedTimeDisplay")) {
            const timeDisplay = player.value.controlBar.addChild(
                "CombinedTimeDisplay",
                {},
                1
            );
            timeDisplay.updateContent();
        }
    });

    player.value.src(getSourceObj(props.selectedLesson?.course_content_url));

    window.addEventListener("beforeunload", storeContentProgress);
});

watch(
    () => props.selectedLesson?.course_content_url,
    (newUrl) => {
        if (player.value && newUrl) {
            player.value.pause();
            player.value.src(getSourceObj(newUrl));
            player.value.load();
            player.value.play().catch(() => {});
        }
    }
);

onBeforeUnmount(() => {
    if (player.value) {
        player.value.off("timeupdate", storeContentProgress);
        player.value.off("ended");
    }
    window.removeEventListener("beforeunload", storeContentProgress);
    player.value?.dispose();
});
</script>

<template>
    <div
        class="w-full h-full aspect-video rounded-lg bg-black relative overflow-hidden"
    >
        <video
            ref="videoPlayer"
            class="video-js max-w-full vjs-default-skin vjs-big-play-centered"
            controls
            playsinline
            crossorigin="anonymous"
            preload="auto"
            width="100%"
            height="auto"
        />
    </div>
</template>

<style scoped>
.video-js {
    width: 100%;
    height: 100%;
    border-radius: 0.5rem;
}

/* Combined time display styles */
:deep(.vjs-combined-time-display) {
    display: flex !important;
    align-items: center;
    font-size: 1em;
    line-height: 3em;
    min-width: 8em;
    text-align: center;
    color: white;
}

:deep(.vjs-combined-time-display .vjs-current-time),
:deep(.vjs-combined-time-display .vjs-duration) {
    display: inline-block;
}

:deep(.vjs-combined-time-display .vjs-time-divider) {
    padding: 0 0.2em;
    display: inline-block;
}

:deep(.vjs-big-play-button) {
    background-color: rgba(173, 24, 63, 1) !important;
    border: none !important;
    border-radius: 50% !important;
    width: 2em !important;
    height: 2em !important;
    line-height: 2em !important;
    margin-top: -1em !important;
    margin-left: -1em !important;
}

:deep(.vjs-big-play-button:hover) {
    background-color: rgba(201, 21, 69, 0.9) !important;
}

:deep(.vjs-play-progress) {
    background-color: #ad183f !important;
}
</style>
