<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import QrcodeVue from 'qrcode.vue';
import { storeToRefs } from 'pinia';
import html2canvas from 'html2canvas';
import { useAppStore } from '@/store/useAppStore';

const appStore = useAppStore();
const { authUser } = storeToRefs(appStore);
const isLoading = ref(false);

const props = defineProps({
    showDownload: {
        type: Boolean,
        default: true,
    },
    selectedCourse: {
        type: Object,
        default: () => ({})
    },
    overallProgress: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['backToHome']);

const qrCodeData = ref('');

const completionDate = computed(() => new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
}));

const fullName = computed(() =>
    `${authUser.value?.first_name || ''} ${authUser.value?.middle_name || ''}`.trim()
);

const downloadCertificate = async () => {
    isLoading.value = true;
    const hiddenContainer = document.createElement('div');
    hiddenContainer.style.position = 'fixed';
    hiddenContainer.style.left = '-9999px';
    hiddenContainer.style.top = '0';
    hiddenContainer.style.width = '800px';
    hiddenContainer.style.height = '600px';
    hiddenContainer.style.zIndex = '-1000';
    document.body.appendChild(hiddenContainer);

    try {
        // Get the correct image URL for the current environment
        const imageUrl = new URL('/images/certificate.png', window.location.origin).href;

        const { createApp } = await import('vue');
        const qrCodeApp = createApp({
            template: `
                <div class="certificate-template" style="
                    width: 100%;
                    height: 100%;
                    background-image: url('${imageUrl}');
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    position: relative;
                    font-family: 'Times New Roman', serif;
                ">
                    <!-- Recipient Name -->
                    <div style="
                        position: absolute;
                        top: 30%;
                        left: 0;
                        width: 100%;
                        text-align: center;
                    ">
                        <h2 style="
                            font-size: 2.5rem;
                            color: #000;
                            margin: 0;
                            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                        ">
                            ${fullName.value}
                        </h2>
                    </div>

                    <!-- Course Info -->
                    <div style="
                        position: absolute;
                        top: 51%;
                        left: 23%;
                        width: 54%;
                        text-align: left;
                    ">
                        <h3 style="
                            font-size: 1.25rem;
                            color: #000;
                            margin: 0 0 0.5rem 0;
                            font-weight: bold;
                            word-wrap: break-word;
                        ">
                            ${props.selectedCourse?.course_name || 'Course'}
                        </h3>
                    </div>

                    <!-- QR Code Section -->
                    <div style="
                        position: absolute;
                        bottom: 10%;
                        right: 20%;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    ">
                        <div style="
                            background: white;
                            padding: 8px;
                            display: inline-block;
                            box-shadow: 0 0 5px rgba(0,0,0,0.1);
                        ">
                            <qrcode-vue 
                                :value="qrData" 
                                :size="80" 
                                level="H"
                                style="display: block;"
                            ></qrcode-vue>
                        </div>
                        <p style="
                            font-size: 0.7rem;
                            color: #333;
                            margin: 0.2rem 0 0 0;
                            text-align: center;
                        ">
                            Scan to verify
                        </p>
                        <p style="
                            font-size: 0.9rem;
                            color: #333;
                            margin: 0;
                        ">
                            Date: <strong>${completionDate.value}</strong>
                        </p>
                    </div>
                </div>
            `,
            components: { QrcodeVue },
            data() {
                return {
                    qrData: qrCodeData.value
                };
            }
        });

        qrCodeApp.mount(hiddenContainer);

        await nextTick();

        const certificateElement = hiddenContainer.querySelector('.certificate-template');
        if (!certificateElement) return;

        // Add a temporary image to ensure it's loaded
        await new Promise((resolve) => {
            const img = new Image();
            img.src = imageUrl;
            img.onload = resolve;
            img.onerror = resolve;
        });

        // Add delay to ensure all elements are rendered
        await new Promise(resolve => setTimeout(resolve, 500));

        const canvas = await html2canvas(certificateElement, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: null,
            width: 800,
            height: 600,
            async: true
        });

        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = `Certificate_${props.selectedCourse?.course_name || 'Course'}.png`;
        link.click();
    } catch (error) {
        console.error('Error downloading certificate:', error);
    } finally {
        document.body.removeChild(hiddenContainer);
        isLoading.value = false;
    }
};

onMounted(() => {
    qrCodeData.value = JSON.stringify({
        userId: authUser.value?.id,
        courseId: props.selectedCourse?.id,
        date: new Date().toISOString()
    });
});
</script>

<template>
    <div class="certificate-view">
        <!-- Download Button - Visible only on mobile -->
        <div v-if="showDownload" class="download-btn-container">
            <button @click="downloadCertificate" class="download-btn">
                <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" viewBox="0 0 50 50"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle class="text-white mr-2" cx="25" cy="25" r="20" fill="none" stroke="currentColor"
                        stroke-width="5" stroke-linecap="round" stroke-dasharray="90,150" stroke-dashoffset="0" />
                </svg>
                <i v-else class="fas fa-download mr-2"></i>
                {{ isLoading ? 'Downloading...' : 'Download Certificate' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Your existing styles remain the same */
.certificate-view {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.download-btn-container {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
    max-width: 400px;
}

.download-btn,
.back-btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.download-btn {
    background-color: #4CAF50;
    color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.download-btn:hover {
    background-color: #45a049;
    transform: translateY(-1px);
}

.back-btn {
    background-color: #f0f0f0;
    color: #333;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.back-btn:hover {
    background-color: #e0e0e0;
    transform: translateY(-1px);
}

@media (min-width: 640px) {
    .download-btn-container {
        flex-direction: row;
    }

    .download-btn,
    .back-btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}

@media (min-width: 768px) {
    .certificate-view {
        padding: 2rem;
    }
}
</style>