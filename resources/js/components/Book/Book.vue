<script setup>
import { storeToRefs } from "pinia";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const cartStore = useCartStore();
const studentStore = UseStudentStore();

const { books, bookOverviewTab, selectedBook, } = storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

const route = useRoute();
const router = useRouter()

function addItems(item) {
    let selectedItem = {
        type: 'course',
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.thumbnail_url
    };
    cartStore.addToCart(selectedItem);
}

function changeTab(slug) {
    router.push({
        name: 'student',
        query: {
            tab: bookOverviewTab.value,
            slug: slug
        }
    });

    selectedCourseSlug.value = slug;
}

onMounted(() => {
    studentStore.fetchBooks();
});

</script>

<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen">
        <h1 class="text-2xl font-bold mb-6 text-center text-lime-700">
            Popular Books
        </h1>

        <!-- Responsive grid layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="book in books" :key="book.id"
                class="relative p-4 bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 hover:scale-105 cursor-pointer flex flex-col">
                <div class="relative group">
                    <img :src="book.cover_page_url" :alt="book.title" class="w-full h-40 object-cover rounded-t-lg" />
                </div>

                <div class="p-4 flex flex-col">
                    <div class="h-20">
                        <h2 class="text-lg font-bold mb-2">{{ book.title }}</h2>
                        <p class="text-sm text-gray-600">By: {{ book.auther }}</p>
                    </div>
                    <div class="flex items-center gap-2 font-bold h-grow mt-2">
                        <span class="text-yellow-500 text-lg">&#9733;</span>
                        <span class="ml-1 text-md text-gray-600">4</span>
                    </div>

                    <div class="h-fit bottem-0">
                        <div class="text-lg font-semibold flex flex-row items-center justify-between">
                            <p class="self-center">{{ book.price.toFixed(2) }} ETB</p>
                            <button @click="addItems(book)" class="ml-auto focus:outline-none">
                                <i
                                    class="fa-solid fa-cart-plus mb-2 material-icons right-8 text-lime-700 rounded-full  p-3 hover:text-lime-700"></i>
                            </button>
                        </div>

                        <button @click="changeTab(book.slug)"
                            class="mt-4 border border-lime-700 text-lime-800 w-full px-4 py-2 rounded hover:bg-lime-800 hover:text-white focus:outline-none transition-colors font-medium text-lg">
                            Overview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Typing text styles */
.typing-text {
    display: inline-flex;
    align-items: center;
}

.cursor {
    display: inline-block;
    width: 2px;
    background-color: limegreen;
    animation: blink 0.7s infinite;
}

/* Cursor blinking animation */
@keyframes blink {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0;
    }
}
</style>
