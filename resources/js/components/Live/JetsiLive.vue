<script setup>
import Axios from 'axios';
import { storeToRefs } from 'pinia';
import { ref, onMounted, onBeforeUnmount, computed, onUnmounted } from 'vue';

import { useAppStore } from "@/store/useAppStore";
import Spinner from "@/components/Layout/Spinner.vue";

const appStore = useAppStore();
const { authUser, isLoggedIn } = storeToRefs(appStore);

const jitsiAPI = ref(null);
const loading = ref(true);
const error = ref(null);

const JITSI_DOMAIN = '8x8.vc';
const JITSI_APP_ID = process.env.MIX_JITSI_APP_ID;

const roomName = ref(`LiveClass_NewRoomToJoin`);
const fullRoomName = ref(`${JITSI_APP_ID}/${roomName.value}`);

const jitsiContainer = ref(null);
const participantCount = ref(0);
const isInitialized = ref(false);
const isSessionEnded = ref(false);

const props = defineProps({
    startSelectedSchedule: Object,
});

const emit = defineEmits(['closeStream']);

if (props.startSelectedSchedule.room_name) {
    roomName.value = props.startSelectedSchedule.room_name;
    fullRoomName.value = `${JITSI_APP_ID}/${roomName.value}`;
}

const userDisplayName = computed(() => {
    return `${authUser.value?.first_name}   ${authUser.value?.middle_name == null ? '' : authUser.value?.middle_name}` || 'Guest';
});

const userEmail = computed(() => {
    return authUser.value?.email || '';
});

const userAvatar = computed(() => {
    return authUser.value?.profile || '';
});

async function handleSessionEndStatus() {
    if (props.startSelectedSchedule.status !== 'live') {
        return;
    }

    let endpoint = `/api/update-instractor-attendance/${props.startSelectedSchedule.id}`;

    if (authUser.value?.role === 'student') {
        endpoint = `/api/update-student-attendance/${props.startSelectedSchedule.id}`;
    }

    Axios.post(endpoint).then(res => {
        return res.data.data;
    }).catch(err => {
        return;
    })
}

// JWT Token Generation with enhanced error handling
const getJitsiToken = async () => {
    try {
        const response = await Axios.post('/api/jitsi/token', {
            room: roomName.value,
            user: {
                name: userDisplayName.value,
                email: userEmail.value,
                avatar: userAvatar.value
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
    loading.value = true;
    try {
        const token = await getJitsiToken();

        const options = {
            roomName: fullRoomName.value,
            parentNode: jitsiContainer.value,
            width: '100%',
            height: 500,
            jwt: token,
            userInfo: {
                displayName: userDisplayName.value,
                email: userEmail.value,
                avatar: userAvatar.value
            },
            configOverwrite: {
                disableDeepLinking: true,
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                enableNoisyMicDetection: false,
                disableSimulcast: true,
                enableLayerSuspension: false,
                disableThirdPartyRequests: true,
                constraints: {
                    audio: true,
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
                HIDE_INVITE_MORE_HEADER: true,
                DISABLE_INVITE_FUNCTIONS: true,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions',
                    'desktop', 'fullscreen', 'fodeviceselection',
                    'hangup', 'profile', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'feedback', 'stats'
                ],
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
        loading.value = false;

    } catch (err) {
        handleInitializationError(err);
    }
};

// Enhanced Event Handlers
const handleConferenceJoined = () => {
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

};

const handleSessionEnd = () => {
    isSessionEnded.value = true;
    endSession();
    emit('closeStream');
    handleSessionEndStatus();
};

const handleInitializationError = (err) => {
    error.value = err.message;

    endSession();
};

const handleUnload = async () => {
    if (!isSessionEnded.value) {
        await handleSessionEndStatus();
    }
};

const setupBeforeUnload = () => {
    window.addEventListener('beforeunload', handleUnload);
    window.addEventListener('pagehide', handleUnload); // For mobile browsers
};

const cleanupBeforeUnload = () => {
    window.removeEventListener('beforeunload', handleUnload);
    window.removeEventListener('pagehide', handleUnload);
};

// Component Lifecycle with cleanup
onMounted(async () => {
    setupBeforeUnload();
    loading.value = true;
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
    cleanupBeforeUnload();
    endSession();
    const scripts = document.querySelectorAll('script[src*="8x8.vc"]');
    scripts.forEach(script => script.remove());
});

onUnmounted(() => {
    if (!isSessionEnded.value) {
        handleSessionEndStatus();
    }
});
</script>

<template>
    <div class="w-full h-full min-h-96 bg-white relative">
        <div v-if="loading" class="absolute bg-white w-full h-full flex justify-center items-center">
            <div class="w-full h-full flex  justify-center items-center">
                <Spinner />
            </div>
        </div>
        <div class="w-full h-full flex flex-col">
            <div v-show="isInitialized && !error" ref="jitsiContainer" class="video-container h-full w-full">
            </div>
            <button v-if="!loading && isInitialized && !error" @click="handleSessionEnd()"
                class="bg-red-700 my-8 self-center text-white px-4 py-1 rounded hover:bg-red-800 transition">
                Close Stream
            </button>
        </div>
    </div>
</template>
