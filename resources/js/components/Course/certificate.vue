<script setup>
import { ref, onMounted, defineProps, computed, nextTick } from 'vue';
import Axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import html2canvas from 'html2canvas';

const props = defineProps({
  showDownload: {
    type: Boolean,
    default: true,
  },
  selectedCourse: {
    type: Object,
  },
  overallProgress: {
    type: Number,
    default: 0
  }
});

const certificateRef = ref(null);
const certificateWrapper = ref(null); // Reference for the wrapper (for scaling)
const customerId = ref('');
const studentName = ref('');
const completionDate = ref(new Date().toLocaleDateString());
const issuedBy = ref('Mehari');
const issuedRole = ref('Course Instructor');
const qrCodeData = ref('');

// Fetch user data
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
    console.error("Error fetching user:", error);
    studentName.value = 'Student';
    customerId.value = 'Unknown';
    qrCodeData.value = 'https://uniquemahari.com/certificates/unknown';
  }
}

onMounted(() => {
  fetchCurrentUser();
});

// Download certificate (always in desktop layout)
async function downloadCertificate() {
  if (!certificateRef.value || !certificateWrapper.value) return;
  
  // Add the 'force-desktop' class to remove mobile scaling
  certificateWrapper.value.classList.add("force-desktop");
  // Wait for DOM update
  await nextTick();
  
  try {
    const canvas = await html2canvas(certificateRef.value, { scale: 2 });
    const imageData = canvas.toDataURL('image/png');
    
    // Remove the override so the on-screen view returns to mobile scaling if applicable
    certificateWrapper.value.classList.remove("force-desktop");
    
    // Trigger download
    const link = document.createElement('a');
    link.href = imageData;
    link.download = 'certificate.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error downloading certificate:', error);
    // Ensure we remove the override in case of an error
    certificateWrapper.value.classList.remove("force-desktop");
  }
}
</script>

<template>
  <div class="certificate-container">
    <!-- Certificate Wrapper with responsive scaling on mobile -->
    <div ref="certificateWrapper" class="certificate-wrapper">
      <div 
        ref="certificateRef"
        class="certificate shadow-xl relative"
      >
        <!-- Top Decorative Border -->
        <div class="absolute top-0 left-0 w-full h-4 bg-gradient-to-r from-yellow-500 via-gray-700 to-yellow-500"></div>
  
        <!-- Certificate Heading -->
        <h1 class="text-5xl font-serif font-extrabold text-center text-gray-900 uppercase mt-12 tracking-wide">
          Certificate of Achievement
        </h1>
  
        <p class="text-lg text-center text-gray-600 italic mt-3">
          This is proudly awarded to
        </p>
  
        <h2 class="text-2xl font-bold text-gray-900 text-center mt-3 underline">
          {{ studentName }}
        </h2>
  
        <p class="text-xl text-gray-700 text-center mt-6">
          For successfully completing the course
        </p>
        <h3 class="text-2xl font-semibold text-gray-800 italic text-center">
          {{ selectedCourse?.course_name }}
        </h3>
  
        <p class="text-md text-center text-gray-700 mt-4">
          Date of Completion: <strong>{{ completionDate }}</strong>
        </p>
  
        <!-- Footer Section: Signature, Seal & QR Code -->
        <div class="flex justify-between items-center px-14 mt-10">
          <!-- Instructor Signature -->
          <div class="text-center">
            <img src="/images/signature_image.jpg" alt="Signature" class="h-20 w-36 mx-auto" />
            <p class="text-gray-900 font-semibold text-lg mt-2">{{ selectedCourse?.user.full_name }}</p>
            <p class="text-gray-600 text-md">{{ selectedCourse?.user.role }}</p>
          </div>
  
          <!-- QR Code for Authenticity -->
          <div class="text-center">
            <qrcode-vue 
              :value="qrCodeData" 
              :size="90" 
              level="H" 
              class="border border-gray-400 p-1 rounded-md shadow-md" 
            />
            <p class="text-gray-600 text-sm mt-1">Scan to verify authenticity</p>
          </div>
        </div>
  
        <!-- Bottom Decorative Border -->
        <div class="absolute bottom-0 left-0 w-full h-4 bg-gradient-to-r from-yellow-500 via-gray-700 to-yellow-500"></div>
      </div>
    </div>
  
    <!-- Buttons -->
    <div v-if="showDownload" class="flex flex-col sm:flex-row items-center justify-center gap-4">
      <button
        @click="$emit('cancelCertificate')"
        class="bg-red-500 text-white px-4 py-2 rounded-lg text-base font-medium shadow-lg hover:bg-red-600 transition duration-300"
      >
        🔙 Back to Course
      </button>
      <button 
        @click="downloadCertificate"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-base font-medium shadow-lg hover:bg-blue-800 transition duration-300"
      >
        📜 Download Certificate
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Certificate Container */
.certificate-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f8f9fa;
  padding: 15px;
}

/* Certificate Wrapper (for scaling on mobile) */
.certificate-wrapper {
  width: 900px;
  height: 600px;
  /* Desktop: no scaling; Mobile: scale applied via media query */
}

/* Force desktop style override for downloads */
.force-desktop {
  transform: none !important;
}

/* Certificate Styling */
.certificate {
  width: 100%;
  height: 100%;
  padding: 30px;
  background: url('/images/certificate bg.jpg') no-repeat center center;
  background-size: cover;
  position: relative;
  border: 5px solid #ccc;
  box-shadow: 6px 6px 14px rgba(0, 0, 0, 0.2);
}

/* Responsive scaling for mobile devices */
@media (max-width: 768px) {
  .certificate-wrapper {
    transform: scale(0.5);
    transform-origin: top center;
    /* The dimensions of the wrapper remain unchanged internally */
    width: 900px;
    height: 600px;
  }
  /* Optionally adjust spacing and fonts visually on mobile */
  .certificate {
    padding: 20px;
  }


}
</style>
