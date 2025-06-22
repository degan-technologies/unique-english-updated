<script setup>
import { ref, computed, onMounted } from 'vue'

import ReviewList from "@/components/Course/ReviewList.vue";
import AdminPdfReader from '@/components/Book/AdminPdfReader.vue'
import VueVideoPlayer from "@/components/Course/VueVideoPlayer.vue";

const props = defineProps({
    selectedbook: {
        type: Object,
        required: true,
        validator: (book) => {
            return book.id && book.description && book.intro_video_url
        }
    },
    goBack: {
        type: Function,
        required: true
    }
})

// State management
const isReadingMode = ref(false) 
const selectedBookRead = ref(props.selectedbook)

// Computed properties
const bookDetails = computed(() => {
    const details = {
        author: props.selectedbook.auther || 'Unknown Author',
        language: props.selectedbook.language || 'Not specified',
        file_format: props.selectedbook.file_format || 'Unknown format',
        publish_date: props.selectedbook.publish_date || 'Unknown date',
        price: `${props.selectedbook.price || 0} Birr`,
        page_number: props.selectedbook.page_number || 'N/A',
        edition: props.selectedbook.eddition || 'First Edition'
    }

    if (props.selectedbook.discount) {
        details.discount = `${props.selectedbook.discount} Birr`
        details.original_price = `${props.selectedbook.price} Birr`
    }

    return details
})

const formatLabel = (key) => {
    const labels = {
        author: 'Author',
        original_price: 'Original Price',
        publish_date: 'Publish Date',
        file_format: 'File Format',
        page_number: 'Pages',
        edition: 'Edition'
    }
    return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const continueReading = () => {
    isReadingMode.value = true
}

const cancelBookRead = () => {
    isReadingMode.value = false
}
 
</script>

<template>
    <div v-if="!isReadingMode" class="p-4 mt-2 sm:pb-16 rounded-lg bg-slate-50 mx-auto w-full"> 
        <button @click="goBack"
            class="flex items-center text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
            aria-label="Go back">
            <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
            <span class="ml-2 font-medium">Back to Books</span>
        </button>
 
        <div class="grid w-full mx-auto mt-4">
            <div class="rounded-lg sm:p-6 flex flex-col gap-6 w-full">
                <div class="flex flex-col lg:flex-row w-full mx-auto gap-6">
                    <div class="relative rounded-lg overflow-hidden bg-gray-100 aspect-video">
                        <template v-if="selectedbook?.intro_video_url">
                            <VueVideoPlayer 
                                :videoSource="selectedbook?.intro_video_url" 
                                :posterImage="selectedbook?.cover_page_url"/>
                        </template>  
                    </div> 
                    <div
                        class="w-full lg:w-1/3 border border-gray-200 rounded-lg p-4 flex flex-col justify-between bg-white shadow-sm">
                        <div class="mb-4">
                            <button @click="continueReading"
                                class="w-full bg-lime-600 text-white font-semibold py-3 rounded hover:bg-lime-700 transition duration-200 flex items-center justify-center"
                                aria-label="Continue reading">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Continue Reading
                            </button>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div v-for="(value, key) in bookDetails" :key="key" class="flex items-start gap-3">
                                <i class="fas fa-circle-check text-lime-600 text-sm mt-1.5"></i>
                                <p :class="[
                                    'text-sm sm:text-base',
                                    key === 'price' ? 'text-red-600 font-semibold' :
                                        key === 'discount' ? 'text-lime-600 font-semibold' :
                                            key === 'original_price' ? 'text-gray-400 line-through' : 'text-gray-700'
                                ]">
                                    <span class="font-semibold pr-2 text-gray-800">{{ formatLabel(key) }}:</span>{{
                                        value }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
 
                <div class="p-0 sm:p-4 pt-8 rounded-b-lg bg-white shadow-sm">
                    <h2 class="text-2xl sm:text-3xl text-slate-700 font-semibold mb-4 sm:mb-6">
                        {{ selectedbook?.title  }}
                    </h2>
                    <div class="prose prose-sm sm:prose-base max-w-none ql-editor preview"
                        v-html="selectedbook?.description">
                    </div>
                </div>
            </div>
        </div>

         <div v-if="selectedbook"  class="mt-4">
            <ReviewList 
                :courseSlug ="selectedbook.slug"
                :showOnly="false" 
                :addFeedbackType="'book'"/>
        </div>
    </div>  

    <div v-else class="p-4 mt-2 rounded-lg bg-white mx-auto w-full">        
        <button @click="cancelBookRead"
            class="flex items-center text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
            aria-label="Go back">
            <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
            <span class="ml-2 font-medium">Back to Details</span>
        </button>

        <AdminPdfReader :selectedBook="selectedBookRead" />
    </div>
</template> 