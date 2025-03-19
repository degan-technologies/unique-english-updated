<template>
    <div>
      <!-- Video Placeholder when video is disabled -->
      <div
        v-if="!videoEnabled"
        class="flex items-center justify-center bg-gray-400"
        :style="{ width: '30rem', height: '18rem' }"
      >
        <Avatar :name="name" size="2xl" class="z-10" />
        <SoundWaveCanvas v-if="audioEnabled && remoteStream" :mediaStream="remoteStream" />
      </div>
  
      <!-- Video Stream -->
      <video
        ref="videoRef"
        autoPlay
        playsInline
        class="w-full h-full rounded"
        v-show="videoEnabled && remoteStream"
      ></video>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue';
  import { defineProps } from 'vue';
  import SoundWaveCanvas from './SoundWaveCanvas.vue';
  import { Avatar } from '@chakra-ui/vue-next';  // Chakra UI Avatar


  
  const props = defineProps({
    remoteStream: {
      type: Object,
      required: false
    },
    name: {
      type: String,
      required: true
    }
  });
  
  const videoEnabled = ref(false);
  const audioEnabled = ref(false);
  const videoRef = ref(null);
  
  // Handle changes in remoteStream
  watch(() => props.remoteStream, (newStream) => {
    if (newStream) {
      videoEnabled.value = newStream.getVideoTracks().length > 0;
      audioEnabled.value = newStream.getAudioTracks().length > 0;
  
      newStream.onremovetrack = () => {
        videoEnabled.value = false;
        audioEnabled.value = false;
      };
    } else {
      videoEnabled.value = false;
      audioEnabled.value = false;
    }
  }, { immediate: true });
  
  onMounted(() => {
    if (videoRef.value && props.remoteStream) {
      videoRef.value.srcObject = props.remoteStream;
    }
  });
  </script>
  
  