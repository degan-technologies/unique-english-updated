<script setup>
import { storeToRefs } from "pinia";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

const cartStore = useCartStore();
const studentStore = UseStudentStore();

const { books, bookOverviewTab, selectedBook, selectedbookslug } =
    storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

const router = useRouter();

function addItems(item) {
    let selectedItem = {
        type: "book",
        slug: item.slug,
        price: item.price,
        name: item.course_name,
        image: item.cover_page_url,
    };
    cartStore.addToCart(selectedItem);
}

function changeTab(slug) {
    router.push({
        name: "student",
        query: {
            tab: bookOverviewTab.value,
            slug: slug,
        },
    });

    selectedbookslug.value = slug;
}

onMounted(() => {
    studentStore.fetchBooks();
});
</script>

<template>
    <div class="px-4 py-8 sm:px-6 lg:px-10 bg-gray-100 min-h-screen" id="books">
        <div class="max-w-7xl mx-auto"> 

            <div class="block text-center mb-12">
                <h1 class="mx-auto text-center text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black font-serif leading-[1.1] tracking-tight max-w-4xl">
                    <span class="text-gray-900"> Books From </span><br class="my-4" />
                    <span class="bg-gradient-to-r from-lime-500 to-lime-600 bg-clip-text text-transparent">unique English</span>
                </h1>

                <p class="mx-auto text-center text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                    Enroll Short, focused books written by our instructors and built around practical classroom breakthroughs.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                <article
                    v-for="book in books"
                    :key="book.id"
                    class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
                >
                    <button
                        class="relative h-64 w-full text-left"
                        @click="changeTab(book.slug)"
                    >
                        <img
                            :src="book.cover_page_url"
                            :alt="book.title"
                            class="h-full w-full object-cover"
                        />

                        <span
                            class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide bg-lime-100 text-lime-800"
                        >
                            {{ book.price === 0 ? "Free" : (book.averageRating >= 4.5 ? "Bestseller" : "New") }}
                        </span>
                    </button>

                    <div class="flex flex-col p-4 sm:p-5">
                        <h2 class="text-[31px] font-serif text-gray-700 leading-tight mb-2 line-clamp-1 min-h-[56px]">
                            {{ book.title }}
                        </h2>

                        <p class="mt-2 text-sm text-gray-600 line-clamp-1">
                            by {{ book.auther || "Mehari Mekonen" }} 
                        </p> 
                        <div class="text-[18px] text-gray-600 line-clamp-2 min-h-[44px]"
                            v-html="book?.description || 'No description available'">
                        </div>

                        <div class="mt-auto pt-5 flex items-center justify-between gap-3">
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ Number(book.price || 0) > 0 ? `${Number(book.price || 0).toFixed(2)} ETB` : "Free" }}
                            </p>

                            <button
                                v-if="!book.isMyBook"
                                @click="addItems(book)"
                                class="shrink-0 rounded-full px-2 py-2 text-sm font-semibold border border-lime-700 text-lime-800 hover:bg-lime-800 hover:text-white transition-colors"
                            >
                            <i
                                class="fa-solid fa-cart-plus  material-icons text-lime-700 rounded-full px-1 hover:text-lime-700"></i>
                                Add now
                            </button>

                            <button
                                v-else
                                class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold border border-emerald-600 text-emerald-700 bg-emerald-50"
                            >
                                Paid
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
