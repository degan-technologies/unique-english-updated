<script setup>
import { ref, onMounted, defineProps, computed, nextTick } from 'vue';
import Axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import { storeToRefs } from 'pinia';
import html2canvas from 'html2canvas';

import { useAppStore } from '@/store/useAppStore';
const appStore = useAppStore();

const { authUser } = storeToRefs(appStore);

const emit = defineEmits(['backToHome']);

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
const certificateWrapper = ref(null);
const completionDate = ref(new Date().toLocaleDateString());
const qrCodeData = ref('');

 
async function downloadCertificate() {
    if (!certificateRef.value || !certificateWrapper.value) return;
 
    certificateWrapper.value.classList.add("force-desktop");
 
    await nextTick();

    try {
        const canvas = await html2canvas(certificateRef.value, { scale: 2 });
        const imageData = canvas.toDataURL('image/png');
 
        certificateWrapper.value.classList.remove("force-desktop");
 
        const link = document.createElement('a');
        link.href = imageData;
        link.download = 'certificate.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error downloading certificate:', error);
        certificateWrapper.value.classList.remove("force-desktop");
    }
} 
</script>

<template>
    <div class="w-full"> 
        <!-- Buttons -->
        <div class="flex sticky">
            <div v-if="showDownload" class=" flex flex-col sm:flex-row items-start justify-start gap-4 mb-8 no-scroll">
                <button @click="$emit('backToHome')"
                    class="text-lime-500 px-4 py-2 text-base font-medium hover:text-lime-700 transition duration-300">
                    Close
                </button>
                <button @click="downloadCertificate"
                    class="text-blue-700 px-4 py-2 text-base font-medium hover:text-blue-800 transition duration-300">
                    Download
                </button>
            </div>
        </div>
        
        <div ref="certificateWrapper" class="certificate-wrapper ">
            <div ref="certificateRef" class="certificate shadow-xl relative">
                <!-- Top Decorative Border -->
                <div
                    class="absolute top-0 left-0 w-full h-4 bg-gradient-to-r from-yellow-500 via-gray-700 to-yellow-500">
                </div>

                <!-- Certificate Heading -->
                <h1 class="text-5xl font-serif font-extrabold text-center text-gray-900 uppercase mt-12 tracking-wide">
                    Certificate of Achievement
                </h1>

                <p class="text-lg text-center text-gray-600 italic mt-3">
                    This is proudly awarded to
                </p>

                <h2 class="text-2xl font-bold text-gray-900 text-center mt-3 underline">
                    {{ authUser?.first_name }} {{ authUser?.middle_name }}
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
                        <img src="/images/signature_image.png" alt="Signature" class="h-20 w-36 mx-auto" />
                        <p class="text-gray-900 font-semibold text-lg mt-2">{{ selectedCourse?.user?.first_name }} {{ selectedCourse?.user?.middle_name }}</p>
                        <p class="text-gray-600 text-md">Unique English Language Academy</p>
                    </div>

                    <!-- QR Code for Authenticity -->
                    <div class="text-center">
                        <qrcode-vue :value="qrCodeData" :size="90" level="H"
                            class="border border-gray-400 p-1 rounded-md shadow-md" />
                        <p class="text-gray-600 text-sm mt-1">Scan to verify authenticity</p>
                    </div>
                </div>

                <!-- Bottom Decorative Border -->
                <div
                    class="absolute bottom-0 left-0 w-full h-4 bg-gradient-to-r from-yellow-500 via-gray-700 to-yellow-500">
                </div>
            </div>
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
