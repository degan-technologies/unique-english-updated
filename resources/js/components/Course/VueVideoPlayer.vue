<script setup>
import videojs from "video.js";
import "video.js/dist/video-js.css";
import { onMounted, onBeforeUnmount, ref, watch } from "vue";

// Required for quality selection
import "videojs-contrib-quality-levels";
import "videojs-hls-quality-selector";

const props = defineProps({
    videoSource: { type: String, required: true },
    posterImage: { type: String, required: false },
    options: { type: Object, default: () => ({}) },
});

const videoPlayer = ref(null);
const player = ref(null);
const videoWatched = ref(false);
const totalDuration = ref(0);

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

        this.on("loadedmetadata", () => {
            totalDuration.value = this.duration();
        });
        this.on("ended", () => {
            videoWatched.value = true;
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

    player.value.src(getSourceObj(props.videoSource));
});

watch(
    () => props.videoSource,
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
        player.value.off("ended");
    }
    player.value?.dispose();
});
</script>

<template>
    <div
        class="w-full h-full aspect-video rounded-lg bg-black relative overflow-hidden"
    >
        <video
            ref="videoPlayer"
            class="video-js vjs-default-skin vjs-big-play-centered"
            controls
            playsinline
            crossorigin="anonymous"
            preload="auto"
            width="100%"
            height="auto"
            :poster="posterImage"
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
    background-color: rgba(249, 115, 22, 0.7) !important;
    border: none !important;
    border-radius: 50% !important;
    width: 2em !important;
    height: 2em !important;
    line-height: 2em !important;
    margin-top: -1em !important;
    margin-left: -1em !important;
    position: relative;
    z-index: 10;
    box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
    animation: pulse-ring 2s infinite;
}

:deep(.vjs-big-play-button:hover) {
    background-color: rgba(249, 115, 22, 0.9) !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

/* Dramatic pulse animation */
:deep(.vjs-big-play-button::before) {
    content: "";
    position: absolute;
    top: -15px;
    left: -15px;
    right: -15px;
    bottom: -15px;
    background: rgba(249, 115, 22, 0.4);
    border-radius: 50%;
    animation: pulse-dramatic 2s infinite;
    z-index: -1;
}

:deep(.vjs-big-play-button::after) {
    content: "";
    position: absolute;
    top: -15px;
    left: -15px;
    right: -15px;
    bottom: -15px;
    background: rgba(249, 115, 22, 0.4);
    border-radius: 50%;
    animation: pulse-dramatic 2s infinite 0.5s;
    z-index: -1;
}

@keyframes pulse-dramatic {
    0% {
        transform: scale(0.8);
        opacity: 0.9;
    }
    70% {
        transform: scale(1.5);
        opacity: 0.2;
    }
    100% {
        transform: scale(2);
        opacity: 0.1;
    }
}

@keyframes pulse-ring {
    0% {
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(249, 115, 22, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0);
    }
}

:deep(.vjs-play-progress) {
    background-color: #f97316 !important;
}
:deep(.vjs-poster) {
    position: absolute;
    inset: 0;
}

:deep(.vjs-poster img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}
</style>
