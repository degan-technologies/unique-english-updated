<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Refs
const jitsiAPI = ref(null);
const isLoading = ref(true);
const error = ref(null);

const JITSI_DOMAIN = '8x8.vc';
const JITSI_APP_ID = process.env.MIX_JITSI_APP_ID;

const roomName = ref(`LiveClass_NewRoomToJoin`);
const fullRoomName = `${JITSI_APP_ID}/${roomName.value}`;

const jitsiContainer = ref(null);
const participantCount = ref(0);
const isInitialized = ref(false);

// Configuration

// JWT Token Generation with enhanced error handling
const getJitsiToken = async () => {
    try {
        const response = await axios.post('/api/jitsi/token', {
            room: roomName.value,
            user: {
                name: "Host User",
                email: "host@example.com",
                avatar: "" // Add avatar URL if needed
            }
        });

        if (!response.data?.token) {
            throw new Error('Invalid token response');
        }

        return response.data.token;
    } catch (err) {
        console.error('Token error:', err);
        throw new Error(err.response?.data?.error || 'Failed to get access token');
    }
};

// Jitsi Initialization with improved configuration
const initializeJitsi = async () => {
    try {
        const token = await getJitsiToken();

        const options = {
            roomName: fullRoomName,
            parentNode: jitsiContainer.value,
            width: '100%',
            height: 500,
            jwt: token,
            configOverwrite: {
                disableDeepLinking: true,
                startWithAudioMuted: true,
                startWithVideoMuted: false,
                enableNoisyMicDetection: false,
                disableSimulcast: true,
                enableLayerSuspension: false,
                disableThirdPartyRequests: true,
                constraints: {
                    video: {
                        aspectRatio: 16 / 9,
                        height: { ideal: 720, max: 720, min: 240 }
                    }
                },
                disabledSounds: [
                    'RECORDING_OFF_SOUND',
                    'RECORDING_ON_SOUND'
                ]
            },
            interfaceConfigOverwrite: {
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                SHOW_CHROME_EXTENSION_BANNER: false,
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                MOBILE_APP_PROMO: false,
                HIDE_INVITE_MORE_HEADER: true
            }
        };

        jitsiAPI.value = new window.JitsiMeetExternalAPI(JITSI_DOMAIN, options);

        // Event Listeners
        jitsiAPI.value.on('videoConferenceJoined', handleConferenceJoined);
        jitsiAPI.value.on('participantJoined', handleParticipantJoined);
        jitsiAPI.value.on('participantLeft', handleParticipantLeft);
        jitsiAPI.value.on('readyToClose', handleSessionEnd);
        jitsiAPI.value.on('error', handleError);
        jitsiAPI.value.on('passwordRequired', handlePasswordRequired);

        isInitialized.value = true;

    } catch (err) {
        handleInitializationError(err);
    }
};

// Enhanced Event Handlers
const handleConferenceJoined = () => {
    isLoading.value = false;
    participantCount.value = 1;
    jitsiAPI.value.executeCommand('displayName', 'Host');
};

const handleParticipantJoined = () => {
    participantCount.value++;
};

const handleParticipantLeft = () => {
    participantCount.value = Math.max(0, participantCount.value - 1);
};

const handlePasswordRequired = () => {
    error.value = 'This session requires a password. Please contact support.';
    endSession();
};

const handleError = (err) => {
    console.error('Jitsi error:', err);
    error.value = err.message || 'Connection error occurred';
    endSession();
};

// Session Management
const endSession = () => {
    if (jitsiAPI.value) {
        try {
            jitsiAPI.value.dispose();
            jitsiAPI.value = null;
            isInitialized.value = false;
        } catch (err) {
            console.error('Cleanup error:', err);
        }
    }
    isLoading.value = false;
};

const handleSessionEnd = () => {
    endSession();
    error.value = 'Session ended by remote participant';
};

const handleInitializationError = (err) => {
    error.value = err.message;
    isLoading.value = false;
    endSession();
};

// Component Lifecycle with cleanup
onMounted(async () => {
    try {
        if (!window.JitsiMeetExternalAPI) {
            const script = document.createElement('script');
            script.src = `https://${JITSI_DOMAIN}/external_api.js`;
            script.async = true;
            script.onload = () => initializeJitsi();
            script.onerror = () => {
                throw new Error('Failed to load Jitsi SDK');
            };
            document.body.appendChild(script);
        } else {
            await initializeJitsi();
        }
    } catch (err) {
        handleInitializationError(err);
    }
});

onBeforeUnmount(() => {
    endSession();
    const scripts = document.querySelectorAll('script[src*="8x8.vc"]');
    scripts.forEach(script => script.remove());
});
</script>

<template>
    <div class="jitsi-container">
        <div class="session-header">
            <h2 class="session-title">Live Session: {{ roomName }}</h2>
            <div class="participant-count">
                Participants: {{ participantCount }}
            </div>
        </div>

        <div v-if="isLoading" class="loading-state">
            <div class="spinner"></div>
            <p class="loading-text">Initializing session...</p>
            <p class="loading-subtext">This may take a few moments</p>
        </div>

        <div v-else-if="error" class="error-state">
            <p class="error-message">⚠️ {{ error }}</p>
            <button @click="initializeJitsi" class="retry-button" :disabled="isLoading">
                {{ isLoading ? 'Reconnecting...' : 'Retry Connection' }}
            </button>
        </div>

        <div v-show="isInitialized && !error" ref="jitsiContainer" class="video-container"></div>

        <div v-if="isInitialized" class="controls">
            <button @click="endSession" class="end-button" :disabled="isLoading">
                {{ isLoading ? 'Ending...' : 'End Session' }}
            </button>
        </div>
    </div>
</template>

<style>
.jitsi-container {
    @apply max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg;
    min-height: 600px;
}

.session-header {
    @apply flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2;
}

.session-title {
    @apply text-xl font-semibold text-gray-800 truncate;
}

.participant-count {
    @apply text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-md;
}

.video-container {
    @apply w-full h-[500px] bg-gray-800 rounded-lg overflow-hidden;
    min-height: 500px;
}

.loading-state {
    @apply h-[500px] flex flex-col items-center justify-center gap-4;
}

.spinner {
    @apply w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin;
}

.loading-text {
    @apply text-gray-700 font-medium;
}

.loading-subtext {
    @apply text-sm text-gray-500;
}

.error-state {
    @apply h-[500px] flex flex-col items-center justify-center gap-4;
}

.error-message {
    @apply text-red-600 text-lg font-medium text-center max-w-md;
}

.retry-button {
    @apply px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors;
}

.controls {
    @apply mt-4 text-center;
}

.end-button {
    @apply px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors;

    
}

@media (max-width: 640px) {
    .jitsi-container {
        @apply p-4;
    }

    .video-container {
        height: 400px;
    }
}
</style>