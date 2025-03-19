<template>
  <div class="relative w-screen h-screen bg-black opacity-90">
    <Head title="Meeting" />

    <!-- Video Grid -->
    <div class="grid h-full grid-cols-1 gap-2 px-10 pt-10 pb-32 sm:grid-cols-2 md:grid-cols-3">
      <div>
        <!-- Local Stream Display -->
        <div v-if="isVideoOff && !isScreenSharing" class="flex items-center justify-center bg-gray-400" style="width: 30rem; height: 18rem;">
          <SoundWaveCanvas v-if="!isAudioMuted" :mediaStream="localStream" />
        </div>
        <video
          v-else
          autoPlay
          :id="auth.user.id.toString()"
          muted
          class="w-full h-full rounded"
          ref="videoRef"
        >
          <source :src="localStream" />
        </video>
      </div>

<!-- Remote Streams -->
<template v-if="meetingUsers && meetingUsers.length">
  <RemoteStreamDisplay
    v-for="user in meetingUsers.filter(u => u.id !== auth.user.id)"
    :key="user.id"
    :remoteStream="remoteStreams[user.id]"
    :name="user.name || 'N/A'"
  />
</template>


    </div>

    <!-- Footer with Controls -->
    <div class="absolute grid w-screen grid-cols-4 gap-2 px-10 text-white bottom-5">
      <!-- Meeting Info -->
      <div class="flex items-center">
        <span class="mr-1">{{ currentTime }}</span> | <span class="ml-1">{{ id }}</span>
      </div>

      <!-- Invite Button -->
      <div class="flex justify-center">
        <button @click="setShowInviteModal(true)" class="p-2 bg-gray-700 rounded hover:bg-gray-600">
          <span class="material-icons">person_add</span>
        </button>
      </div>

      <!-- Control Buttons -->
      <div class="flex justify-center space-x-2">
        <button
          @click="toggleMic"
          :disabled="isToggling === 'audio'"
          class="p-2 bg-gray-700 rounded hover:bg-gray-600"
        >
          <span class="material-icons" v-if="!isAudioMuted">mic</span>
          <span class="material-icons" v-if="isAudioMuted">mic_off</span>
        </button>

        <button
          @click="toggleVideo"
          :disabled="isToggling === 'video'"
          class="p-2 bg-gray-700 rounded hover:bg-gray-600"
        >
          <span class="material-icons" v-if="!isVideoOff">videocam</span>
          <span class="material-icons" v-if="isVideoOff">videocam_off</span>
        </button>

        <button
          :disabled="isScreenSharing"
          @click="shareScreen"
          class="p-2 bg-gray-700 rounded hover:bg-gray-600"
        >
          <span class="material-icons" v-if="!isScreenSharing">screen_share</span>
          <span class="material-icons" v-if="isScreenSharing">stop_screen_share</span>
        </button>

        <button @click="endCall" class="p-2 bg-red-600 rounded hover:bg-red-500">
          <span class="material-icons">call_end</span>
        </button>
      </div>

      <!-- Chat Button -->
      <div class="flex justify-end">
        <button @click="toggleChat" class="p-2 bg-gray-700 rounded hover:bg-gray-600">
          <span class="material-icons">chat</span>
        </button>
      </div>
    </div>

    <!-- Invite Modal -->
    <div v-if="showInviteModal" class="absolute p-5 bg-white rounded-lg w-80 bottom-20 left-10">
      <div class="flex justify-between mb-5">
        <h1 class="text-lg font-bold">Your meeting's ready</h1>
        <button @click="setShowInviteModal(false)">
          <span class="material-icons">close</span>
        </button>
      </div>
      <p class="mb-5 text-sm">
        Share this meeting link with others you want in the meeting
      </p>

      <div class="flex justify-between p-3 mb-5 bg-gray-200 rounded">
        <div class="text-lg">{{ window.location.pathname.substring(1) }}</div>
        <button @click="handleCopy">
          <span class="material-icons cursor-pointer">content_copy</span>
        </button>
      </div>
    </div>

    <!-- Chat Panel (Right Side) -->
    <div v-if="showChat" class="absolute top-0 right-0 h-full w-80 bg-gray-900 text-white p-4 overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Chat</h2>
        <button @click="toggleChat" class="p-1">
          <span class="material-icons">close</span>
        </button>
      </div>
      <div class="space-y-2">
        <div
          v-for="(message, index) in chatMessages"
          :key="index"
          class="p-2 bg-gray-700 rounded"
        >
          <div class="text-sm">{{ message.text }}</div>
          <div class="text-xs text-gray-400">
            {{ new Date(message.timestamp).toLocaleTimeString() }}
          </div>
        </div>
      </div>
      <div class="mt-4 flex">
        <input
          v-model="chatInput"
          type="text"
          placeholder="Type a message"
          class="flex-1 p-2 rounded-l bg-gray-800 border border-gray-600 text-white"
        />
        <button @click="sendChat" class="p-2 bg-blue-600 rounded-r hover:bg-blue-500">
          <span class="material-icons">send</span>
        </button>
      </div>
    </div>

    <!-- Custom Toast Notification -->
    <div v-if="toastMessage" class="fixed bottom-5 right-5 p-3 text-white bg-green-600 rounded">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useWebRTCStore } from '@/store/useWebRTCStore';
import { useRouter } from 'vue-router';
import dayjs from 'dayjs';

const props = defineProps({
  auth: Object,
  id: String,
});

const currentTime = ref(dayjs().format('h:mm A'));
const toastMessage = ref(null);

const {
  isAudioMuted,
  isVideoOff,
  isScreenSharing,
  toggleMic,
  toggleVideo,
  endCall,
  meetingUsers,
  remoteStreams,
  localStream,
  isToggling,
  showInviteModal,
  setShowInviteModal,
  handleCopy,
  shareScreen,
} = useWebRTCStore();

const router = useRouter();

const showToast = (message) => {
  toastMessage.value = message;
  setTimeout(() => {
    toastMessage.value = null;
  }, 3000);
};

onMounted(() => {
  showToast('Welcome to the meeting!');
});

// Chat functionality
const showChat = ref(false);
const chatMessages = ref([]);
const chatInput = ref('');

const toggleChat = () => {
  showChat.value = !showChat.value;
};

const sendChat = () => {
  if (chatInput.value.trim()) {
    chatMessages.value.push({
      text: chatInput.value,
      timestamp: new Date(),
    });
    chatInput.value = '';
  }
};
</script>

<style scoped>
/* Add your scoped styles here */
</style>
