<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import dayjs from 'dayjs';
import axios from 'axios';

// Import components (adjust paths as needed)
import PrimaryButton from './components/PrimaryButton.vue';
import TextInput from './components/TextInput.vue';
import SecondaryButton from './components/SecondaryButton.vue';
import SoundWaveCanvas from './components/SoundWaveCanvas.vue';
import RemoteStreamDisplay from './components/RemoteStreamDisplay.vue';

// ================== WebRTC & Meeting State ==================

// Media control flags
const isAudioMuted = ref(true);
const isVideoOff = ref(true);
const isScreenSharing = ref(false);
const isToggling = ref(null);

// Local and remote streams
const localStream = ref(new MediaStream());
const remoteStreams = reactive({});
const meetingUsers = ref([]); // Could be populated via signaling

// Peer connections and negotiation flag
const peersRef = reactive({});
const renegotiatingRef = ref(false);

// Meeting and user identifiers
const meetingId = ref('');
const meetingIdInput = ref('');
const userId = ref('123'); // Replace with dynamic user ID as needed
const meetingCode = computed(() => meetingId.value);

// ================== UI & Chat State ==================
const currentTime = ref(dayjs().format('h:mm A'));
const toastMessage = ref(null);
const showChat = ref(false);
const chatMessages = ref([]);
const chatInput = ref('');
const videoRef = ref(null);
const inviteModal = ref(false);
const invitedEmails = ref(''); // Comma separated emails for invitation

// Computed meeting link for sharing
const meetingLink = computed(() => window.location.href);

// Update current time every minute
setInterval(() => {
  currentTime.value = dayjs().format('h:mm A');
}, 60000);

// ------------------ Toast Notification ------------------
const showToast = (message) => {
  toastMessage.value = message;
  setTimeout(() => {
    toastMessage.value = null;
  }, 3000);
};

// ------------------ Media Functions ------------------

/**
 * Gets user media with both audio and video.
 */
const createStream = async () => {
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    console.error("Media devices not supported in this environment");
    return new MediaStream();
  }
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
    return stream;
  } catch (error) {
    console.error("Error accessing media devices:", error);
    return new MediaStream();
  }
};

/**
 * Creates and attaches the local video stream.
 */
const createMyVideoStream = async () => {
  try {
    const stream = await createStream();
    if (stream.getAudioTracks()[0]) {
      isAudioMuted.value = !stream.getAudioTracks()[0].enabled;
    }
    if (stream.getVideoTracks()[0]) {
      isVideoOff.value = !stream.getVideoTracks()[0].enabled;
    }
    localStream.value = stream;
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
    }
    return stream;
  } catch (error) {
    console.error("Error creating video stream:", error);
    localStream.value = new MediaStream();
    isAudioMuted.value = true;
    isVideoOff.value = true;
    return localStream.value;
  }
};

/**
 * Stop all media tracks and close peer connections.
 */
const destroyConnection = async () => {
  localStream.value.getTracks().forEach(track => track.stop());
  Object.values(remoteStreams).forEach(stream => {
    stream.getTracks().forEach(track => track.stop());
  });
  for (const targetId in peersRef) {
    const peer = peersRef[targetId];
    if (peer) {
      peer.ontrack = null;
      peer.onicecandidate = null;
      peer.onsignalingstatechange = null;
      peer.onconnectionstatechange = null;
      peer.close();
      delete peersRef[targetId];
      console.log("Closed connection with", targetId);
    }
  }
  showToast("Call ended.");
};

/**
 * Replace tracks in all active peer connections.
 */
const replaceRemoteTracks = (stream) => {
  Object.values(peersRef).forEach(peer => {
    peer.getSenders().forEach(sender => {
      if (sender.track) {
        if (sender.track.kind === 'video') {
          sender.replaceTrack(stream ? stream.getVideoTracks()[0] : null);
        } else if (sender.track.kind === 'audio') {
          sender.replaceTrack(stream ? stream.getAudioTracks()[0] : null);
        }
      }
    });
  });
};

// ------------------ WebRTC Peer Functions ------------------

// STUN server configuration
const servers = {
  iceServers: [
    { urls: ['stun:stun.l.google.com:19302'] }
  ]
};

const createPeer = async (targetId, stream) => {
  const peer = new RTCPeerConnection(servers);
  if (stream) {
    stream.getTracks().forEach(track => {
      peer.addTrack(track, stream);
    });
  }
  peer.onicecandidate = (event) => {
    if (event && event.candidate) {
      window.Echo.join(`handshake.${meetingCode.value}`)
        .whisper('negotiation', {
          data: JSON.stringify({
            type: 'candidate',
            data: event.candidate
          }),
          sender_id: userId.value,
          reciver_id: targetId
        });
    }
  };
  peer.ontrack = (event) => {
    const remoteStream = event.streams[0];
    remoteStreams[targetId] = remoteStream;
  };
  peer.onsignalingstatechange = () => {
    console.log(`Peer ${targetId} signaling state: ${peer.signalingState}`);
  };
  peer.onconnectionstatechange = () => {
    console.log(`Peer ${targetId} connection state: ${peer.iceConnectionState}`);
    if (['disconnected', 'failed', 'closed'].includes(peer.iceConnectionState)) {
      reNegotiation(targetId);
    }
  };
  peer.oniceconnectionstatechange = () => {
    if (peer.iceConnectionState === "failed") {
      peer.restartIce();
    }
  };
  peersRef[targetId] = peer;
  return peer;
};

const removePeer = (targetId) => {
  const peer = peersRef[targetId];
  if (!peer) {
    console.error(`No peer found for ${targetId}`);
    return;
  }
  peer.ontrack = null;
  peer.onicecandidate = null;
  peer.onsignalingstatechange = null;
  peer.onconnectionstatechange = null;
  peer.close();
  delete peersRef[targetId];
  remoteStreams[targetId] = new MediaStream();
  console.log("Removed peer", targetId);
};

const createOffer = async (targetId, stream) => {
  let peer = peersRef[targetId];
  if (!peer) {
    peer = await createPeer(targetId, stream);
  }
  const offer = await peer.createOffer();
  await peer.setLocalDescription(offer);
  window.Echo.join(`handshake.${meetingCode.value}`)
    .whisper('negotiation', {
      data: JSON.stringify(offer),
      sender_id: userId.value,
      reciver_id: targetId
    });
};

const handleIncomingOffer = async (sender_id, offer) => {
  const peer = peersRef[sender_id];
  if (!peer) {
    console.error(`No peer found for ${sender_id}`);
    return;
  }
  if (peer.signalingState !== 'stable') return;
  await peer.setRemoteDescription(offer);
  const answer = await peer.createAnswer();
  await peer.setLocalDescription(answer);
  window.Echo.join(`handshake.${meetingCode.value}`)
    .whisper('negotiation', {
      data: JSON.stringify(answer),
      sender_id: userId.value,
      reciver_id: sender_id
    });
};

const handleIncomingAnswer = async (sender_id, answer) => {
  const peer = peersRef[sender_id];
  if (!peer) {
    console.error(`No peer found for ${sender_id}`);
    return;
  }
  await peer.setRemoteDescription(answer);
};

const handleIncomingCandidate = async (sender_id, candidate) => {
  const peer = peersRef[sender_id];
  if (!peer) {
    console.error(`No peer found for ${sender_id}`);
    return;
  }
  if (!candidate) {
    console.log(`Candidate is null for ${sender_id}`);
    return;
  }
  if (peer.iceConnectionState === 'connected') return;
  await peer.addIceCandidate(candidate);
};

const reNegotiation = async (targetId) => {
  if (renegotiatingRef.value) return;
  renegotiatingRef.value = true;
  let peer = peersRef[targetId];
  const offer = await peer.createOffer({ iceRestart: true });
  await peer.setLocalDescription(offer);
  window.Echo.join(`handshake.${meetingCode.value}`)
    .whisper('negotiation', {
      data: JSON.stringify(offer),
      sender_id: userId.value,
      reciver_id: targetId
    });
  renegotiatingRef.value = false;
};

// ------------------ Meeting Control Functions ------------------

/**
 * Create a new meeting by calling the backend.
 */
const newMeetingHandle = async () => {
  try {
    const payload = {
      title: 'New Meeting',
      start_time: dayjs().add(1, 'minute').toISOString(),
      end_time: dayjs().add(1, 'hour').toISOString(),
      description: 'This is a new meeting created from the frontend.',
      max_participants: 50,
      stream_url: window.location.href + '/stream/' + Math.random().toString(36).substring(2, 10),
      status: 'scheduled'
    };
    const response = await axios.post('/api/live-sessions/create-meeting', payload);
    meetingId.value = response.data.data.slug;
    window.history.pushState(null, '', `/meeting/${meetingId.value}`);
    showToast(response.data.message);
    await createMyVideoStream();
  } catch (error) {
    console.error("Error creating meeting", error);
    showToast('Failed to create meeting');
  }
};

/**
 * Join an existing meeting by calling the join endpoint.
 */
const handleJoin = async () => {
  if (meetingIdInput.value.trim()) {
    try {
      const joinResponse = await axios.post(`/api/live-sessions/${meetingIdInput.value.trim()}/join`);
      meetingId.value = meetingIdInput.value.trim();
      window.history.pushState(null, '', `/meeting/${meetingId.value}`);
      showToast(joinResponse.data.message);
      await createMyVideoStream();
    } catch (error) {
      console.error("Error joining meeting", error);
      showToast('Failed to join meeting');
    }
  }
};

/**
 * Rejoin a meeting if already in session.
 */
onMounted(async () => {
  const pathSegments = window.location.pathname.split('/');
  const potentialId = pathSegments[pathSegments.length - 1];
  if (potentialId) {
    meetingId.value = potentialId;
    try {
      const rejoinResponse = await axios.post(`/api/live-sessions/${meetingId.value}/rejoin`);
      showToast(rejoinResponse.data.message);
    } catch (error) {
      console.error("Error rejoining meeting", error);
      showToast('Failed to rejoin meeting');
    }
    await createMyVideoStream();
    getChatMessages();
  }
});

// ------------------ Invite Function ------------------
const sendInvite = async () => {
  const emails = invitedEmails.value.split(',').map(email => email.trim()).filter(email => email);
  if (emails.length === 0) {
    showToast("Please enter valid email addresses.");
    return;
  }
  try {
    const response = await axios.post(`/api/live-sessions/${meetingId.value}/invite`, { emails });
    showToast(response.data.message);
    invitedEmails.value = '';
    inviteModal.value = false;
  } catch (error) {
    console.error("Error sending invites", error);
    showToast("Failed to send invites");
  }
};

// ------------------ Media Control Endpoints ------------------

/**
 * Toggle microphone state via the backend.
 */
const toggleMic = async () => {
  try {
    const response = await axios.post(`/api/live-sessions/${meetingId.value}/toggle-mic`, { mic_on: !isAudioMuted.value });
    // Controller returns new mic state; update our state (invert because local flag is “muted”)
    isAudioMuted.value = !response.data.mic_on;
    showToast(response.data.message);
  } catch (error) {
    console.error("Error toggling mic", error);
    showToast('Failed to toggle mic');
  }
};

/**
 * Toggle video camera state via the backend.
 */
const toggleVideo = async () => {
  try {
    const response = await axios.post(`/api/live-sessions/${meetingId.value}/toggle-video`, { video_on: !isVideoOff.value });
    isVideoOff.value = !response.data.video_on;
    showToast(response.data.message);
  } catch (error) {
    console.error("Error toggling video", error);
    showToast('Failed to toggle video');
  }
};

/**
 * Toggle screen sharing via the backend and update local stream.
 */
const toggleScreenShare = async () => {
  try {
    const response = await axios.post(`/api/live-sessions/${meetingId.value}/toggle-screen-share`, { screen_sharing: isScreenSharing.value });
    isScreenSharing.value = response.data.screen_sharing;
    showToast(response.data.message);
    if (isScreenSharing.value) {
      const videoToggleState = isVideoOff.value;
      const screenStream = await navigator.mediaDevices.getDisplayMedia();
      const screenVideoTrack = screenStream.getVideoTracks()[0];
      const existingVideoTrack = localStream.value.getVideoTracks()[0];
      if (existingVideoTrack) {
        localStream.value.removeTrack(existingVideoTrack);
        existingVideoTrack.stop();
      }
      localStream.value.addTrack(screenVideoTrack);
      isVideoOff.value = false;
      replaceRemoteTracks(screenStream);
      screenVideoTrack.onended = async () => {
        isScreenSharing.value = false;
        const newStream = await createStream();
        localStream.value = newStream;
        isVideoOff.value = videoToggleState;
        replaceRemoteTracks(newStream);
      };
    } else {
      const newStream = await createStream();
      localStream.value = newStream;
      isVideoOff.value = false;
      replaceRemoteTracks(newStream);
    }
  } catch (error) {
    console.error("Error toggling screen share", error);
    showToast('Failed to toggle screen share');
  }
};

/**
 * End the call and clean up connections.
 */
const endCall = async () => {
  await destroyConnection();
  meetingId.value = '';
  window.history.pushState(null, '', '/');
};

// ------------------ Chat Functions ------------------

const formatTimestamp = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString();
};

const getChatMessages = async () => {
  try {
    const response = await axios.get(`/api/chat/${meetingId.value}`);
    chatMessages.value = response.data;
  } catch (error) {
    console.error('Error retrieving chat messages:', error);
  }
};

const sendChat = async () => {
  if (chatInput.value.trim() !== '') {
    try {
      const payload = {
        meeting_id: meetingId.value,
        message: chatInput.value,
      };
      const response = await axios.post('/api/chat', payload);
      chatMessages.value.push(response.data);
      chatInput.value = '';
    } catch (error) {
      console.error('Error sending chat message:', error);
    }
  }
};

const toggleChat = () => {
  showChat.value = !showChat.value;
};

// ------------------ Watchers ------------------

watch(
  () => localStream.value,
  (newStream) => {
    if (videoRef.value && newStream) {
      videoRef.value.srcObject = newStream;
    }
  },
  { immediate: true }
);
</script>

<template>
  <div>
    <!-- Home View: Shown when no meeting has been started -->
    <div v-if="!meetingId">
      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="bg-white shadow-sm sm:rounded-lg grid gap-4 grid-cols-1 md:grid-cols-2 h-[34rem] overflow-y-auto">
            <!-- Left section: Meeting Info and Input -->
            <div class="flex flex-col justify-center p-10">
              <h1 class="text-4xl font-semibold">Premium video meetings.</h1>
              <h1 class="mb-5 text-4xl font-semibold">Now free for everyone.</h1>
              <p>
                We re-engineered the service we built for secure business meetings, Google Meet, to make it free and available for all.
              </p>
              <div class="grid grid-cols-6 gap-2 mt-10 lg:grid-cols-12">
                <PrimaryButton @click="newMeetingHandle" class="col-span-4 text-center">
                  New Meeting
                </PrimaryButton>
                <!-- Text input for Meeting ID -->
                <TextInput v-model="meetingIdInput" class="col-span-4" />
                <SecondaryButton @click="handleJoin" class="col-span-2">
                  Join
                </SecondaryButton>
              </div>
            </div>
            <!-- Right section: Info about Sharing Links -->
            <div class="flex flex-col items-center justify-center p-10 mr-10">
              <div>
                <img width="300" src="https://www.gstatic.com/meet/user_edu_get_a_link_light_90698cd7b4ca04d3005c962a3756c42d.svg" />
              </div>
              <p class="mb-2 text-2xl">Get a link you can share</p>
              <div class="text-sm">
                Click <strong>New meeting</strong> to get a link you can send to people you want to meet with.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meeting View: Shown when a meeting is active -->
    <div v-else class="relative w-screen h-screen bg-black bg-opacity-90">
      <!-- Video Grid -->
      <div class="video-grid grid h-full gap-2 px-10 pt-10 pb-32 sm:grid-cols-2 md:grid-cols-3">
        <div>
          <!-- Local Stream Display -->
          <div
            v-if="isVideoOff && !isScreenSharing"
            class="flex items-center justify-center bg-gray-400"
            style="width: 30rem; height: 18rem;"
          >
            <SoundWaveCanvas v-if="!isAudioMuted" :mediaStream="localStream" />
          </div>
          <video
            v-else
            autoPlay
            playsinline
            :id="userId.toString()"
            muted
            class="w-200 h-60 rounded"
            ref="videoRef"
          ></video>
        </div>
        <!-- Remote Streams -->
        <template v-if="meetingUsers.length">
          <RemoteStreamDisplay
            v-for="user in meetingUsers.filter(u => u.id !== userId)"
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
          <span class="mr-1">{{ currentTime }}</span> | <span class="ml-1">{{ meetingId }}</span>
        </div>
        <!-- Invite Button -->
        <div class="flex justify-center">
          <button @click="inviteModal = true" class="p-2 bg-gray-700 rounded hover:bg-gray-600">
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
            <span class="material-icons" v-else>mic_off</span>
          </button>
          <button
            @click="toggleVideo"
            :disabled="isToggling === 'video'"
            class="p-2 bg-gray-700 rounded hover:bg-gray-600"
          >
            <span class="material-icons" v-if="!isVideoOff">videocam</span>
            <span class="material-icons" v-else>videocam_off</span>
          </button>
          <button
            :disabled="isScreenSharing"
            @click="toggleScreenShare"
            class="p-2 bg-gray-700 rounded hover:bg-gray-600"
          >
            <span class="material-icons" v-if="!isScreenSharing">screen_share</span>
            <span class="material-icons" v-else>stop_screen_share</span>
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
      <div v-if="inviteModal" class="absolute p-5 bg-white rounded-lg w-80 bottom-20 left-10 shadow-lg">
        <div class="flex justify-between mb-5">
          <h1 class="text-lg font-bold">Your meeting's ready</h1>
          <button @click="inviteModal = false">
            <span class="material-icons">close</span>
          </button>
        </div>
        <p class="mb-5 text-sm">
          Share this meeting link with others you want in the meeting.
        </p>
        <div class="flex flex-col gap-3">
          <div class="flex justify-between p-3 bg-gray-200 rounded">
            <div class="text-lg break-all">{{ meetingLink }}</div>
            <button @click="navigator.clipboard.writeText(meetingLink).then(() => showToast('Meeting link copied!')).catch(() => showToast('Failed to copy meeting link'))">
              <span class="material-icons cursor-pointer">content_copy</span>
            </button>
          </div>
          <TextInput v-model="invitedEmails" placeholder="Enter emails, separated by commas" />
          <PrimaryButton @click="sendInvite">
            Send Invites
          </PrimaryButton>
        </div>
      </div>

      <!-- Chat Panel (Right Side) -->
      <div
        v-if="showChat"
        class="absolute top-0 right-0 h-full w-80 bg-gray-900 text-white p-4 overflow-y-auto shadow-lg"
      >
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Chat</h2>
          <button @click="toggleChat" class="p-1">
            <span class="material-icons">close</span>
          </button>
        </div>
        <div class="space-y-2">
          <div
            v-for="(message, index) in chatMessages"
            :key="message.id || index"
            class="p-2 bg-gray-700 rounded"
          >
            <div class="text-sm">{{ message.message }}</div>
            <div class="text-xs text-gray-400">
              {{ formatTimestamp(message.created_at) }}
            </div>
          </div>
        </div>
        <div class="mt-4 absolute bottom-4 left-4 right-4 flex">
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
      <div v-if="toastMessage" class="toast fixed bottom-5 right-5 p-3 text-white bg-green-600 rounded shadow-lg">
        {{ toastMessage }}
      </div>
    </div>
  </div>
</template>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
button {
  transition: background-color 0.3s ease;
}
button:hover {
  background-color: #555;
}
.video-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  padding: 1rem;
}
.toast {
  animation: fadeInOut 3s;
}
@keyframes fadeInOut {
  0%, 100% { opacity: 0; }
  10%, 90% { opacity: 1; }
}
.shadow-lg {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}
</style>
