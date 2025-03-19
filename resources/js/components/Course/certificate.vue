<template>
  <div :class="outerClass">
    <div
      ref="certificateRef"
      class="bg-cover bg-center shadow-lg rounded-lg relative"
      :style="{
        backgroundImage: 'url(' + backgroundImageUrl + ')',
        width: '1123px',
        height: '794px',
        backgroundSize: 'cover'
      }"
    >
      <div class="absolute inset-0 flex flex-col justify-center items-center px-36 py-8">
        <!-- Certificate Title -->
        <h1 class="text-4xl font-bold text-center text-gray-800 uppercase mb-4">
          Course Completion Certificate
        </h1>

        <!-- Logo -->
        <img src="/images/logo.jpg" alt="Logo" class="mx-auto my-6 w-28 h-28 rounded-full" />

        <!-- Student Info -->
        <h2 class="text-3xl font-semibold text-gray-800">{{ studentName }}</h2>
        <p class="text-xl text-gray-700 my-2">has successfully completed the</p>
        <!-- Here we use the courseName prop -->
        <h3 class="text-2xl font-medium text-gray-800">{{ courseName }}</h3>

        <!-- Date -->
        <div class="flex justify-between w-full mt-6 text-lg">
          <span class="text-gray-600">Date: {{ completionDate }}</span>
        </div>

        <!-- Bottom Section: Signature and QR Code -->
        <div class="flex justify-between items-center w-full px-18 mt-6">
          <div class="text-center">
            <img src="/images/signature.jpg" alt="Signature" class="h-28 w-36 mx-auto" />
            <p class="text-gray-700 font-semibold text-lg">{{ issuedBy }}</p>
            <p class="text-gray-600 text-base">{{ issuedRole }}</p>
          </div>
          <div class="text-center">
            <p class="text-gray-600 text-sm mb-1">Customer ID: {{ customerId }}</p>
            <qrcode-vue
              :value="qrCodeData"
              :size="120"
              level="H"
              class="border border-gray-300 p-2 rounded-md"
            />
            <p class="text-gray-600 text-sm mt-2">Scan to verify</p>
          </div>
        </div>

        <!-- Conditionally Render Download Button -->
        <div v-if="showDownload" class="mt-12">
          <button
            @click="downloadCertificate"
            class="download-btn bg-blue-600 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-700 transition"
          >
            Download Certificate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from 'vue';
import Axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import certificateBg from '/images/certificate.jpg';
import html2canvas from 'html2canvas';

// Define props; note that we now expect a courseName prop.
const props = defineProps({
  showDownload: {
    type: Boolean,
    default: true,
  },
  courseName: {
    type: String,
    default: 'English Language Proficiency'
  }
});

const outerClass = computed(() => {
  return props.showDownload
    ? "flex items-center justify-center min-h-screen bg-gray-100 p-4"
    : "";
});

const certificateRef = ref(null);
const customerId = ref('');
const studentName = ref('');
// We no longer declare courseName locally; we use the prop: props.courseName
const completionDate = ref(new Date().toLocaleDateString());
const issuedBy = ref('Mehari');
const issuedRole = ref('Course Instructor');
const backgroundImageUrl = ref(certificateBg);
const qrCodeData = ref('');

// Fetch user data and set up QR code.
async function fetchCurrentUser() {
  try {
    const response = await Axios.get('/api/current', {
      headers: { "Content-Type": "application/json" },
    });
    const data = response.data;
    customerId.value = data.phone;
    studentName.value = [data.first_name, data.middle_name, data.last_name]
      .filter(Boolean)
      .join(' ');
    const certificateURL = `https://uniquemahari.com/certificates/${data.id}`;
    qrCodeData.value = certificateURL;
  } catch (error) {
    console.error("Error fetching current user:", error);
    studentName.value = 'Student';
    customerId.value = 'Unknown';
    qrCodeData.value = 'https://uniquemahari.com/certificates/unknown';
  }
}

onMounted(() => {
  fetchCurrentUser();
});

// Download certificate using html2canvas
async function downloadCertificate() {
  if (!certificateRef.value) return;
  try {
    // Hide the download button temporarily so it's not captured
    const downloadButton = certificateRef.value.querySelector('.download-btn');
    if (downloadButton) {
      downloadButton.style.display = 'none';
    }
    const canvas = await html2canvas(certificateRef.value, { scale: 2 });
    const imageData = canvas.toDataURL('image/png');
    if (downloadButton) {
      downloadButton.style.display = '';
    }
    // Trigger the download
    const link = document.createElement('a');
    link.href = imageData;
    link.download = 'certificate.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error downloading certificate:', error);
  }
}

// Expose the downloadCertificate method for parent components
defineExpose({ downloadCertificate });
</script>
