<template>
  <div class="flex flex-col h-screen bg-gray-900 text-white">
    <!-- Header / Top Bar -->
    <header class="flex justify-between items-center p-4 border-b border-gray-700">
      <!-- Branding & Meeting Info -->
      <div class="flex items-center space-x-4">
        <img src="/logo.png" alt="Logo" class="w-10 h-10" />
        <div>
          <h1 class="text-xl font-bold">Live Stream</h1>
          <p class="text-sm">
            Meeting Code: <span class="font-mono">{{ meetingCode }}</span>
          </p>
        </div>
      </div>
      <!-- Connection Status & Invite Button -->
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-1">
          <!-- Green dot as connection indicator -->
          <span class="w-3 h-3 bg-green-500 rounded-full"></span>
          <span class="text-sm">Connected</span>
        </div>
        <button @click="toggleInviteModal" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded transition">
          Invite
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Video Canvas (Local Video and Overlays) -->
      <main class="flex-1 relative">
        <!-- Local Video Feed -->
        <div v-if="localStreamAvailable" class="w-full h-full">
          <video ref="localVideo" autoplay muted playsinline class="object-cover w-full h-full"></video>
        </div>
        <div v-else class="flex items-center justify-center w-full h-full bg-gray-800">
          <p class="text-xl">No Video Available</p>
        </div>
        <!-- Video Overlay: User info and current status -->
        <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 p-2 rounded">
          <p class="text-sm font-semibold">Your Name</p>
          <p class="text-xs">
            {{ isAudioMuted ? 'Mic Off' : 'Mic On' }} ·
            {{ isVideoOff ? 'Camera Off' : 'Camera On' }}
          </p>
        </div>
      </main>

      <!-- Sidebar: Participant List & Chat -->
      <aside class="w-80 border-l border-gray-700 flex flex-col">
        <!-- Participant Header -->
        <div class="p-4 border-b border-gray-700">
          <h2 class="text-lg font-bold">
            Participants ({{ meetingUsers.length }})
          </h2>
        </div>
        <!-- Participant List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
          <ul>
            <li
              v-for="user in meetingUsers"
              :key="user.id"
              class="flex items-center justify-between p-2 bg-gray-800 rounded hover:bg-gray-700 transition"
            >
              <div class="flex items-center space-x-2">
                <img
                  :src="user.avatar || '/default-avatar.png'"
                  alt="Avatar"
                  class="w-8 h-8 rounded-full"
                />
                <span>{{ user.name }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <!-- Material icon for mic status -->
                <span v-if="user.isAudioMuted" class="material-icons text-red-500 text-base">
                  mic_off
                </span>
                <!-- Material icon for video status -->
                <span v-if="user.isVideoOff" class="material-icons text-yellow-500 text-base">
                  videocam_off
                </span>
              </div>
            </li>
          </ul>
        </div>
        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-700">
          <input
            v-model="chatMessage"
            @keyup.enter="sendChat"
            type="text"
            placeholder="Type a message..."
            class="w-full bg-gray-800 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
          />
        </div>
      </aside>
    </div>

    <!-- Bottom Control Panel -->
    <footer class="flex items-center justify-center p-4 border-t border-gray-700 space-x-6">
      <!-- Toggle Mic Button -->
      <button @click="toggleMic" class="p-3 bg-gray-800 rounded-full hover:bg-gray-700 transition">
        <span class="material-icons" :class="isAudioMuted ? 'text-red-500' : 'text-green-500'">
          {{ isAudioMuted ? 'mic_off' : 'mic' }}
        </span>
      </button>
      <!-- Toggle Video Button -->
      <button @click="toggleVideo" class="p-3 bg-gray-800 rounded-full hover:bg-gray-700 transition">
        <span class="material-icons" :class="isVideoOff ? 'text-yellow-500' : 'text-green-500'">
          {{ isVideoOff ? 'videocam_off' : 'videocam' }}
        </span>
      </button>
      <!-- Screen Share Button -->
      <button @click="shareScreen" class="p-3 bg-gray-800 rounded-full hover:bg-gray-700 transition">
        <span class="material-icons text-blue-500">screen_share</span>
      </button>
    </footer>

    <!-- Invite Modal -->
    <div v-if="showInviteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-gray-800 p-6 rounded shadow-lg w-96">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-2xl font-bold">Invite Participants</h3>
          <button @click="toggleInviteModal" class="text-blue-400 hover:text-blue-600">
            <span class="material-icons">close</span>
          </button>
        </div>
        <p class="mb-2">Share this meeting code with others:</p>
        <div class="flex items-center space-x-2">
          <input
            v-model="meetingCode"
            type="text"
            readonly
            class="flex-1 bg-gray-700 p-2 rounded focus:outline-none"
          />
          <button @click="copyMeetingCode" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 transition">
            Copy
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watchEffect } from 'vue';
import { useWebRTCStore } from '@/store/useWebRTCStore';

const store = useWebRTCStore();

// Destructure reactive references from the store
const {
  meetingCode,
  isAudioMuted,
  isVideoOff,
  localStream,
  showInviteModal,
  meetingUsers
} = store;

const localVideo = ref(null);
const chatMessage = ref('');

// Computed property to check if a valid local stream exists
const localStreamAvailable = computed(() => {
  return localStream.value && localStream.value.getTracks().length > 0;
});

// Attach local media stream to video element
onMounted(() => {
  watchEffect(() => {
    if (localVideo.value && localStreamAvailable.value) {
      localVideo.value.srcObject = localStream.value;
    }
  });
});

// Methods

function toggleInviteModal() {
  store.setShowInviteModal(!showInviteModal.value);
}

function copyMeetingCode() {
  navigator.clipboard.writeText(meetingCode.value)
    .then(() => {
      toast.success('Meeting code copied to clipboard!');
    })
    .catch(() => {
      toast.error('Failed to copy meeting code.');
    });
}

async function toggleMic() {
  await store.toggleMic();
}

async function toggleVideo() {
  await store.toggleVideo();
}

async function shareScreen() {
  await store.shareScreen();
}

function sendChat() {
  // Implement your chat integration logic here
  console.log('Chat message sent:', chatMessage.value);
  chatMessage.value = '';
}
</script>

<!-- No additional CSS is needed since Tailwind CSS and Material Icons are handling styling -->
