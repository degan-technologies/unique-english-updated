<script setup>
import { ref, computed } from 'vue'
import AdminPdfReader from '@/components/Book/AdminPdfReader.vue'

const props = defineProps({
  selectedbook: {
    type: Object,
    required: true
  },
  goBack: {
    type: Function,
    required: true
  }
})

const isReadingMode = ref(false)
const selectedBookRead = props.selectedbook;

const bookDetails = computed(() => {
  return {
    auther: props.selectedbook.auther,
    language: props.selectedbook.language,
    file_format: props.selectedbook.file_format,
    publish_date: props.selectedbook.publish_date,
    price: props.selectedbook.price + ' Birr',
    ...(props.selectedbook.discount && { discount: props.selectedbook.discount + ' Birr' }),
    page_number: props.selectedbook.page_number,
    eddition: props.selectedbook.eddition
  }
})

const formatLabel = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function continueReading(bookId) {
  // You can use the bookId here if needed
  isReadingMode.value = true
}
function cancelBookRead() {
  isReadingMode.value = false
}
</script>

<template>
  <div v-if="!isReadingMode"
    class="p-6 mt-2 pb-16 rounded-lg bg-slate-50 w-[95%] mx-auto">
    <div class="flex items-start w-fit">
      <button @click="goBack"
        class="px-4 py-2 bg-gray-100 text-black rounded-md hover:bg-gray-200 transition mr-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 mr-2"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
    </div>
    <div class="grid w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <!-- Book Container -->
      <div class="rounded-lg p-4 sm:p-6 flex flex-col gap-6 w-full">

        <!-- Book Title and Author -->
        <div class="text-left">
          <h1 class="text-2xl sm:text-3xl md:text-4xl text-gray-900 font-bold">{{ selectedbook.title }}</h1>

          <div class="flex flex-row gap-4 mt-4">
            <img :src="selectedbook?.cover_page_url"
              :alt="selectedbook?.user?.first_name"
              class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-full" />
            <div class="text-base sm:text-lg text-gray-700">
              <p class="leading-5">Book Offered by</p>
              <p class="font-bold">{{ selectedbook?.user?.first_name }}</p>
            </div>
          </div>
        </div>

        <!-- Video & Book Info Section -->
        <div class="flex flex-col lg:flex-row w-full max-w-[1200px] mx-auto gap-6 px-4">

          <!-- Video Section -->
          <div class="w-full lg:w-2/3 aspect-video lg:aspect-auto lg:h-[300px] rounded-md overflow-hidden">
            <video controls
              class="w-full h-auto object-cover rounded-md">
              <source :src="selectedbook?.intro_video_url"
                type="video/mp4" />
              Your browser does not support the video tag.
            </video>
          </div>

          <!-- Book Info -->
          <div class="w-full lg:w-1/3 border border-gray-300 rounded-lg p-4 flex flex-col justify-between">
            <div class="mb-4">
              <button @click="continueReading(selectedbook.id)"
                class="w-full bg-lime-600 text-white font-semibold py-2 px-4 rounded hover:bg-lime-700 transition duration-200">
                Continue Reading
              </button>
            </div>
            <div class="flex flex-col gap-4">
              <div v-for="(value, key) in bookDetails"
                :key="key"
                class="flex items-start gap-2">
                <i class="fa-solid fa-check text-lime-700 text-lg mt-1"></i>
                <p :class="[
                  'text-sm sm:text-base',
                  key === 'price' ? 'text-red-600' :
                    key === 'discount' ? 'text-lime-600' : 'text-gray-600'
                ]">
                  <span class="font-bold pr-2">{{ formatLabel(key) }}:</span>{{ value }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Book Description -->
        <div class="p-0 sm:p-4 pt-8 rounded-b-lg">
          <h2 class="text-2xl sm:text-3xl text-slate-600 font-semibold mb-4 sm:mb-6">
            Book Description
          </h2>
          <div class="prose prose-sm sm:prose-base md:prose-lg lg:prose-xl ql-editor preview max-w-none"
            v-html="selectedbook?.description">
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else
    class="p-6 rounded-lg mx-auto">
    <div class="flex items-start w-fit">
      <button @click="cancelBookRead()"
        class="px-4 py-2 bg-gray-200 text-black rounded-md hover:bg-gray-300 transition mr-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 mr-2"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
    </div>
    <AdminPdfReader :selectedBook="selectedBookRead" />
  </div>
</template>
