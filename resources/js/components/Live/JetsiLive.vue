<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

import Spinner from "@/components/Layout/Spinner.vue";

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

 const props = defineProps({
   selectedRoom: Object,
}); 

const emit = defineEmits(['closeStream']);

if(props.selectedRoom){
    roomName.value = props.selectedRoom;
    fullRoomName.value = `${JITSI_APP_ID}/${roomName.value}`;
}


// JWT Token Generation with enhanced error handling
const getJitsiToken = async () => {
    try {
        const response = await axios.post('/api/jitsi/token', {
            room: roomName.value,
            user: {
                name: "Host User",
                email: "host@example.com",
                avatar: ""  
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
            roomName: fullRoomName.value,
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
        loading.value = false;

    } catch (err) {
        handleInitializationError(err);
    }
};

// Enhanced Event Handlers
const handleConferenceJoined = () => {
    loading.value = false;
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
    loading.value = false;
};

const handleSessionEnd = () => {
    endSession();
    error.value = 'Session ended by remote participant';
};

const handleInitializationError = (err) => {
    error.value = err.message;
    loading.value = false;
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

function closeStream() {
    emit('closeStream');

    if (jitsiAPI.value) {
        jitsiAPI.value.dispose();
        jitsiAPI.value = null;
        isInitialized.value = false;
    }
}

onBeforeUnmount(() => {
    endSession();
    const scripts = document.querySelectorAll('script[src*="8x8.vc"]');
    scripts.forEach(script => script.remove());
});
</script>

<template>
    <div class="w-full h-full min-h-96 bg-white relative">
        <div class="w-full h-full flex flex-col">
            <div   
                v-show="isInitialized && !error" 
                ref="jitsiContainer" 
                class="video-container h-full w-full">
            </div>
            <button 
                v-if="!loading"
                @click="closeStream()"
                class="bg-red-700 my-8 self-center text-white px-4 py-1 rounded hover:bg-red-800 transition">
                Close Stream
            </button>
        </div>
        <div  v-if="loading"
            class="absolute bg-white w-full h-full flex justify-center items-center">
            <div class="w-full h-full flex  justify-center items-center">
                <Spinner />
            </div>
        </div> 
    </div>
</template>
