<template>
    <div
      ref="pluseRef"
      class="absolute w-32 h-32 bg-gray-500 rounded-full opacity-75"
    ></div>
  </template>
  
  <script setup>
  import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
  
  const props = defineProps({
    mediaStream: {
      type: Object,
      required: false
    }
  });
  
  const pluseRef = ref(null);
  
  onMounted(() => {
    if (!pluseRef.value || !props.mediaStream) return console.error('Sound Wave Canvas or MediaStream not found.');
    if (!props.mediaStream.getAudioTracks().length) return;
  
    const audioContext = new AudioContext();
    const analyser = audioContext.createAnalyser();
    const audioSource = audioContext.createMediaStreamSource(props.mediaStream);
    audioSource.connect(analyser);
  
    analyser.fftSize = 256;
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);
  
    const updatePulse = () => {
      analyser.getByteFrequencyData(dataArray);
      const average = dataArray.reduce((a, b) => a + b) / bufferLength;
      const scaleFactor = average / 100;
      if (!pluseRef.value) return;
      pluseRef.value.style.transform = `scale(${1 + scaleFactor})`;
      requestAnimationFrame(updatePulse);
    };
  
    audioContext.resume().then(() => {
      updatePulse();
    });
  
    onBeforeUnmount(() => {
      audioContext.close();
    });
  });
  </script>
  