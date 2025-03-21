<script setup>
    import { storeToRefs } from "pinia";
    import { ref, onMounted } from "vue";
    import { useRoute, useRouter } from "vue-router";

    import { useCartStore } from "@/store/useCartStore";
    import { UseStudentStore } from "@/store/UseStudentStore";

    const cartStore = useCartStore();
    const studentStore = UseStudentStore();
    
    const { books, bookOverviewTab, selectedBook, } = storeToRefs(studentStore);
    const {items, itemCount, image} = storeToRefs(cartStore);

    const route = useRoute();
    const router = useRouter()

    function addItems(item){
      let  selectedItem = {
            type:'course',
            slug:item.slug,
            price: item.price,
            name:item.course_name,
            image:item.thumbnail_url
        };
        cartStore.addToCart(selectedItem);
    }

    function changeTab(slug) {
        router.push({
            name: 'student',
            query: {
                tab:bookOverviewTab.value,
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
        <!-- Popular Books Title -->
        <h1 class="text-2xl font-bold mb-6 text-center text-lime-700">
            Popular Books
        </h1>

        <!-- Responsive grid layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                v-for="book in books"
                :key="book.id"
                class="relative p-4 bg-white rounded-lg shadow hover:shadow-lg transform transition-transform duration-300 hover:scale-105 cursor-pointer flex flex-col" >
                <!-- Image with Play Button -->
                <div class="relative group">
                    <img
                        :src="book.cover_page_url"
                        :alt="book.title"
                        class="w-full h-40 object-cover rounded-t-lg"
                    />
                </div>

                <!-- Book Info -->
                <div class="flex-1 p-4">
                    <h2 class="text-lg font-bold mb-2 text-lime-700">
                        {{ book.title }}
                    </h2>
                    <p class="text-sm text-gray-600">By: {{ book.auther }}</p>
                   
                    <div
                        class="mt-4 text-lg font-semibold text-lime-700 text-right"
                    >
                        ${{ book.price.toFixed(2) }}
                    </div>
                </div>

                <!-- Buy Now Button -->
                <div class="p-4 pt-0 mt-auto flex justify-center">
                    <button 
                    @click="changeTab(book.slug)"
                        class="bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 focus:outline-none transition-colors w-full" >
                        Buy Now
                    </button>
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
