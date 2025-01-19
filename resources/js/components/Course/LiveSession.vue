<template>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">{{ className }}</h2>
            <span
                :class="classStatusClass"
                class="text-sm font-semibold px-4 py-1 rounded-full"
            >
                {{ classStatus }}
            </span>
        </div>

        <p class="text-gray-600 mb-4">
            <strong>Instructor:</strong> {{ instructorName }}
        </p>
        <p class="text-gray-600 mb-4">
            <strong>Schedule:</strong> {{ schedule }}
        </p>

        <!-- Join/Leave Button -->
        <div class="mt-6 text-center">
            <button
                @click="toggleJoin"
                :class="joinButtonClass"
                class="px-6 py-2 text-white rounded-lg transition duration-200"
            >
                {{ joinButtonText }}
            </button>
        </div>

        <!-- Jitsi Video Call Embed -->
        <div v-if="isJoined" class="mt-8">
            <div id="jitsi-video" class="w-full h-96"></div>
        </div>

        <!-- Controls for camera, screen share, and mute -->
        <div v-if="isJoined" class="mt-4 text-center flex justify-between">
            <button
                @click="toggleCamera"
                class="bg-blue-500 hover:bg-blue-600 px-4 py-2 text-white rounded-lg"
            >
                {{ cameraButtonText }}
            </button>
            <button
                @click="toggleMic"
                class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 text-white rounded-lg"
            >
                {{ micButtonText }}
            </button>
            <button
                @click="toggleScreenShare"
                class="bg-green-500 hover:bg-green-600 px-4 py-2 text-white rounded-lg"
            >
                Screen Share
            </button>
        </div>

        <!-- Copy Link Button -->
        <div v-if="isJoined" class="mt-4 text-center">
            <button
                @click="copyLink"
                class="bg-green-500 hover:bg-green-600 px-6 py-2 text-white rounded-lg"
            >
                Copy Class Link
            </button>
            <p v-if="linkCopied" class="text-sm text-green-600 mt-2">
                Link copied to clipboard!
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "LiveClassSession",
    data() {
        return {
            isJoined: false, // Whether the user has joined the class
            linkCopied: false, // To track if the link has been copied
            className: "Advanced Vue.js Development", // Class name
            instructorName: "John Doe", // Instructor's name
            schedule: "Every Monday at 3:00 PM", // Schedule time
            classStatus: "Upcoming", // Class status (can be "Live", "Upcoming", or "Completed")
            classLink: "https://example.com/class-link", // The live class URL link
            jitsiAPI: null, // Reference to Jitsi API
            cameraEnabled: true, // Whether the camera is enabled
            micEnabled: true, // Whether the mic is enabled
            screenSharing: false, // Whether the screen sharing is enabled
        };
    },
    computed: {
        classStatusClass() {
            switch (this.classStatus) {
                case "Live":
                    return "bg-green-500 text-white";
                case "Upcoming":
                    return "bg-blue-500 text-white";
                case "Completed":
                    return "bg-gray-500 text-white";
                default:
                    return "bg-gray-300 text-white";
            }
        },
        joinButtonClass() {
            return this.isJoined
                ? "bg-red-500 hover:bg-red-600"
                : "bg-blue-500 hover:bg-blue-600";
        },
        joinButtonText() {
            return this.isJoined ? "Leave Class" : "Join Class";
        },
        cameraButtonText() {
            return this.cameraEnabled ? "Turn off Camera" : "Turn on Camera";
        },
        micButtonText() {
            return this.micEnabled ? "Mute" : "Unmute";
        },
    },
    methods: {
        toggleJoin() {
            this.isJoined = !this.isJoined;
            this.classStatus = this.isJoined ? "Live" : "Upcoming";

            if (this.isJoined) {
                this.startJitsiSession();
            } else {
                this.endJitsiSession();
            }
        },
        startJitsiSession() {
            // Use Vue's nextTick to ensure the DOM is fully rendered
            this.$nextTick(() => {
                const domain = "meet.jit.si"; // Using the public Jitsi server
                const options = {
                    roomName: "LiveClassSessionRoom", // Unique room name
                    width: "100%",
                    height: "100%",
                    parentNode: document.querySelector("#jitsi-video"), // Ensure the element exists
                    configOverwrite: {
                        startWithAudioMuted: false,
                        startWithVideoMuted: false, // Start with video enabled
                    },
                    interfaceConfigOverwrite: {
                        filmStripOnly: false,
                        SHOW_JITSI_WATERMARK: false,
                        SHOW_WATERMARK_FOR_GUESTS: false,
                    },
                };

                // Ensure the element exists before creating the Jitsi iframe
                const jitsiElement = document.querySelector("#jitsi-video");
                if (jitsiElement) {
                    this.jitsiAPI = new JitsiMeetExternalAPI(domain, options);
                    // Enable the camera by default when joining
                    this.jitsiAPI.executeCommand("toggleVideo");
                    this.cameraEnabled = true;
                } else {
                    console.error("Jitsi video container element not found!");
                }
            });
        },
        endJitsiSession() {
            if (this.jitsiAPI) {
                this.jitsiAPI.dispose(); // Disposes of the Jitsi instance
                this.jitsiAPI = null;
            }
        },
        toggleCamera() {
            if (this.jitsiAPI) {
                if (this.cameraEnabled) {
                    this.jitsiAPI.executeCommand("toggleVideo"); // Turn off the camera
                } else {
                    this.jitsiAPI.executeCommand("toggleVideo"); // Turn on the camera
                }
                this.cameraEnabled = !this.cameraEnabled;
            }
        },
        toggleMic() {
            if (this.jitsiAPI) {
                if (this.micEnabled) {
                    this.jitsiAPI.executeCommand("toggleAudio"); // Mute the mic
                } else {
                    this.jitsiAPI.executeCommand("toggleAudio"); // Unmute the mic
                }
                this.micEnabled = !this.micEnabled;
            }
        },
        toggleScreenShare() {
            if (this.jitsiAPI) {
                if (this.screenSharing) {
                    this.jitsiAPI.executeCommand("stopSharing"); // Stop screen sharing
                } else {
                    this.jitsiAPI.executeCommand("startSharing"); // Start screen sharing
                }
                this.screenSharing = !this.screenSharing;
            }
        },
        copyLink() {
            navigator.clipboard
                .writeText(this.classLink)
                .then(() => {
                    this.linkCopied = true;
                    setTimeout(() => {
                        this.linkCopied = false;
                    }, 2000);
                })
                .catch((err) => {
                    console.error("Failed to copy: ", err);
                });
        },
    },
};
</script>

<style scoped>
/* You can add custom styles here if needed */
</style>
