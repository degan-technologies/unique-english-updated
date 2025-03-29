<script setup>
    import { onMounted, onBeforeUnmount, ref } from 'vue';
    import Spinner from '@/components/Layout/Spinner.vue';

    const props = defineProps({
        selectedLesson: {
            type: Object,
            required: true,
        },
    });

    const imageRef = ref(null);
    const imageLoaded = ref(false);

    const preventCopy = (e) => {
        e.preventDefault();
    };

    const preventPrintScreen = (e) => {
        if (e.key === "PrintScreen") {
            e.preventDefault();
            alert("Screenshots are disabled.");
        }
    };

    const preventCtrlP = (e) => {
        if (e.ctrlKey && e.key.toLowerCase() === "p") {
            e.preventDefault();
            alert("Printing is disabled.");
        }
    };

    const preventWinShiftS = (e) => {
        if (e.shiftKey && e.key.toLowerCase() === "s" && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            alert("Screen snipping is disabled.");
        }
    };

    window.onbeforeprint = () => {
        if (imageRef.value) {
            imageRef.value.style.filter = "blur(8px)";
        }
    };

    window.onafterprint = () => {
        if (imageRef.value) {
            imageRef.value.style.filter = "";
        }
    };

    onMounted(() => {
        document.addEventListener("contextmenu", preventCopy);
        document.addEventListener("keydown", preventPrintScreen);
        document.addEventListener("keydown", preventCtrlP);
        document.addEventListener("keydown", preventWinShiftS);
    });

    onBeforeUnmount(() => {
        document.removeEventListener("contextmenu", preventCopy);
        document.removeEventListener("keydown", preventPrintScreen);
        document.removeEventListener("keydown", preventCtrlP);
        document.removeEventListener("keydown", preventWinShiftS);
        window.onbeforeprint = null;
        window.onafterprint = null;
    });
</script>

<template>
    <div>
        <Spinner v-if="!imageLoaded" />
    </div>
    <div class="w-full h-full flex justify-center items-center p-4 relative">
        <img ref="imageRef"
            :src="selectedLesson.course_content_url"
            alt="Lesson Content Image"
            class="w-full h-full object-contain rounded-md shadow-md border select-none"
            @load="imageLoaded = true"
            @contextmenu.prevent
            @dragstart.prevent
            v-show="imageLoaded" />
    </div>
</template>

<style scoped>
    @media print {
        img {
            filter: blur(8px) !important;
        }
    }
</style>
