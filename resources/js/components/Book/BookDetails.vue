<template>
    <div v-if="selectedbookslug" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
      <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
        <!-- Left Side: Book Info & Description -->
        <div>
          <div class="text-left">
            <h1 class="text-4xl text-gray-900 font-bold">{{ selectedbook?.title }}</h1>
            <div class="flex my-2 gap-4">
              <div>
                <img
                  src="/images/course-1.jpg"
                  :alt="selectedbook?.user.first_name"
                  class="w-12 h-12 object-cover mt-4 rounded-full"
                />
              </div>
              <div class="text-lg text-gray-700 mt-2">
                <p>Book Offered by</p>
                <p class="font-bold">{{ selectedbook?.user.first_name }}</p>
              </div>
            </div>
          </div>
          <!-- Video or Cover Image Display -->
          <div class="overflow-hidden max-w-full h-auto flex w-full justify-center items-center rounded-t-lg mt-6 relative cursor-pointer">
            <!-- If intro_video exists, display the video -->
            <video v-if="selectedbook?.intro_vedio" controls class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl">
              <source :src="selectedbook?.intro_vedio" type="video/mp4">
              Your browser does not support the video tag.
            </video>
            <!-- Fallback: display cover image -->
            <img v-else :src="selectedbook?.cover_page_url" alt="Book Image" class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl" />
          </div>
          <div class="bg-white p-4 py-8 rounded-b-lg">
            <div class="text-left mb-6">
              <div class="flex items-left justify-between">
                <h2 class="text-3xl text-slate-600 font-semibold">Book Description</h2>
              </div>
              <p class="py-4 text-lg leading-9">{{ selectedbook?.description }}</p>
            </div>
            <!-- Additional deal information can be added here -->
          </div>
        </div>
    
        <!-- Right Side: Book Details -->
        <div class="w-full border border-e-gray-300 h-1/2 min-h-fit rounded-lg p-6">
          <button @click="changeTabTemporaryFunction(selectedbook.slug)" class="mt-6 bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 w-full">
            Continue Reading
          </button>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Author:</span>{{ selectedbook?.auther }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Language:</span>{{ selectedbook?.language }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">File Format:</span>{{ selectedbook?.file_format }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Publish Date:</span>{{ selectedbook?.publish_date }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-red-600 text-lg"><span class="font-bold pr-4">Original Price:</span>{{ selectedbook?.price }} Birr</p>
          </div>
          <div v-if="selectedbook?.discount" class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-lime-600 text-lg"><span class="font-bold pr-4">Discount Price:</span>{{ selectedbook?.discount }} Birr</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Page Numbers:</span>{{ selectedbook?.page_number }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Edition:</span>{{ selectedbook?.eddition }} Birr</p>
          </div>
          <div v-if="timeLeft.days >= 0" class="mt-4 p-4 bg-red-100 text-red-600 text-center rounded-lg">
            <p class="font-semibold">This offer ends in:</p>
            <p class="text-xl font-bold">
              {{ timeLeft.days }} days {{ timeLeft.hours }}h : {{ timeLeft.minutes }}m : {{ timeLeft.seconds }}s
            </p>
          </div>
        </div>
      </div>
    </div>
  </template>
    
  <script setup>
  import Axios from "axios";
  import { storeToRefs } from "pinia";
  import { onMounted, ref, watch } from "vue";
  import { useRoute, useRouter } from "vue-router";
  import { UseStudentStore } from "@/store/UseStudentStore";
    
  const studentStore = UseStudentStore();
  const { bookReadingTab, books, selectedbookslug } = storeToRefs(studentStore);
    
  const route = useRoute();
  const router = useRouter();
    
  const checkoutUrl = ref(null);
  const selectedbook = ref(null);
  selectedbookslug.value = route.query.slug;
    
  const timeLeft = ref({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
  });
    
  function enrollCourse(item) {
    let selectedItem = [
      {
        type: "course",
        slug: item.slug,
      },
    ];
    Axios.post('/api/initiate-payment', { cartItems: selectedItem })
      .then(res => {
        checkoutUrl.value = res.data.checkout_url;
        window.open(checkoutUrl.value, "_blank");
      })
      .catch(error => {});
  }
    
  // When "Continue Reading" is clicked, navigate using router.push
 function changeTabTemporaryFunction(slug) {
  if (selectedbook.value?.file_url) {
    window.open(selectedbook.value.file_url, "_blank"); // Open the book file
  } else {
    router.push({
      name: 'student',
      query: {
        tab: bookReadingTab.value,
        slug: slug
      }
    });
    selectedbookslug.value = slug;
  }
}

  const discountEndDate = new Date();
  discountEndDate.setDate(discountEndDate.getDate() + 3);
    
  const updateCountdown = () => {
    const now = new Date().getTime();
    const distance = discountEndDate - now;
    if (distance > 0) {
      timeLeft.value.days = Math.floor(distance / (1000 * 60 * 60 * 24));
      timeLeft.value.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      timeLeft.value.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      timeLeft.value.seconds = Math.floor((distance % (1000 * 60)) / 1000);
    } else {
      timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }
  };
    
  onMounted(async () => {
    await studentStore.fetchBooks();
    updateCountdown();
    selectedbook.value = books.value.find(item => item?.slug == selectedbookslug.value);
  });
    
  watch(() => route.query.slug, () => {
    selectedbookslug.value = route.query.slug;
    selectedbook.value = books.value.find(item => item?.slug == selectedbookslug.value);
  });
  </script>
  