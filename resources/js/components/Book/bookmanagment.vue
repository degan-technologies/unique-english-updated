<script setup>
import Axios from "axios";
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import Editor from "primevue/editor";
import { useToast } from "vue-toastification";
import { useRoute, useRouter } from "vue-router"
import Spinner from "@/components/Layout/Spinner";
import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { useInstructorStore } from "@/store/useInstructorStore";
import EditBookForm from "@/components/Book/EditBook.vue";
import BookDetail from "@/components/Book/AdminBookDetail.vue";
import Popper from "vue3-popper";

const InstructorStore = useInstructorStore();
const { analytics } = storeToRefs(InstructorStore);

const toast = useToast();

const props = defineProps({
    searchQuery: {
        type: String,
        default: "",
    },
    activeTab: {
        type: String,
        default: "Books",
    },
});

const router = useRouter();
const route = useRoute();

const cartStore = useCartStore();
const studentStore = UseStudentStore();
const { books, bookOverviewTab, selectedBook } = storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);


const booksAdmin = ref([]);
const loading = ref(true);
const error = ref("");
const selectedbook = ref(null);
const editingBookId = ref(null);
const activeMenuId = ref(null);
const showPopularBooks = ref(false);

showPopularBooks.value = route.query.selectedAction === "details" ? true : false;
const storedSlug = ref('');
const selectedFilter = ref("");
const currentPage = ref(1);
const itemsPerPage = 10;
const pagination = ref({});



const toggleMenu = (id) => {
    activeMenuId.value = activeMenuId.value === id ? null : id;
};

const closeMenu = () => {
    activeMenuId.value = null;
};

const handleClickOutside = (event) => {
    if (!event.target.closest(".relative")) {
        closeMenu();
    }
};

const fetchBooksAdmin = async (page = 1) => {
    loading.value = true;
    error.value = "";

    try {
        const response = await Axios.get("/api/books/books", {
            params: {
                page,
                rowsPerPageOptions: rowsPerPage.value // ✅ Add this line
            },
            headers: { "Content-Type": "application/json" },
        });
        console.log("Fetching page:", page, "with per page:", rowsPerPage.value);

        booksAdmin.value = response.data.data;
        pagination.value = response.data.pagination;
        analytics.value.total = response.data.total;
        analytics.value.newToday = response.data.newToday;
        currentPage.value = response.data.pagination.current_page;
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to fetch Books.";
        toast.error(error.value, { position: "top-right" });
    } finally {
        loading.value = false;
    }
};


const rowsPerPageOptions = [5, 10, 15, 20];
const rowsPerPage = ref(10);

function changeBooksPerPage(amount) {
    rowsPerPage.value = amount;
    fetchBooksAdmin(1);
}

const nextPage = () => {
    if (currentPage.value < pagination.value.last_page) {
        fetchBooksAdmin(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        fetchBooksAdmin(currentPage.value - 1);
    }
};

const deleteCourse = async (id) => {
    if (!window.confirm("Are you sure you want to delete this book?")) return;
    try {
        await Axios.delete(`/api/books/books/${id}`, {
            headers: { "Content-Type": "application/json" },
        });
        fetchBooksAdmin();
        toast.success("BOOK successfully deleted!", { position: "top-right" });
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to delete book.", { position: "top-right" });
    }
};

const selectBookforEdit = (book) => {
    editingBookId.value = book.id;
    selectedbook.value = { ...book };

};

const cancelEdit = () => {
    editingBookId.value = null;
    selectedbook.value = null;
    showPopularBooks.value = false;

};

function changeTab(slug) {
    router.push({
        name: "instructor",
        query: {
            ...route.query,
            selectedAction: "details",
            slug: slug
        },
    });
}

const filteredBooksAdmin = computed(() => {
    let filtered = booksAdmin.value;
    if (props.searchQuery.trim() !== "") {
        const query = props.searchQuery.trim().toLowerCase();
        filtered = filtered.filter((book) =>
            book.title.toLowerCase().includes(query)
        );
    }
    if (selectedFilter.value) {
        filtered = filtered.filter((book) => book.skill_level === selectedFilter.value);
    }
    return filtered;
});

const storedbookViewMode = localStorage.getItem("bookViewMode");
const bookViewMode = ref(storedbookViewMode ? storedbookViewMode : "auto");
const computedbookViewMode = computed(() => {
    if (bookViewMode.value === "auto") {
        return filteredBooksAdmin.value.length <= 4 ? "card" : "table";
    }
    return bookViewMode.value;
});
const toggleBookView = () => {
    if (bookViewMode.value === "auto") {
        bookViewMode.value = computedbookViewMode.value === "card" ? "table" : "card";
    } else {
        bookViewMode.value = bookViewMode.value === "card" ? "table" : "card";
    }
    localStorage.setItem("bookViewMode", bookViewMode.value);
};

watch(
    () => route.query.slug,
    (newSlug) => {
        if (newSlug && storedSlug.value !== newSlug) {
            storedSlug.value = newSlug;
        } else if (!newSlug) {
            storedSlug.value = '';
        }
    },
    { immediate: true }
);

watch(
    [() => storedSlug.value, () => booksAdmin.value],
    ([currentSlug, books]) => {
        if (currentSlug && books.length) {
            selectedbook.value = books.find(book => book.slug === currentSlug);
            showPopularBooks.value = route.query.selectedAction === "details";
        } else {
            selectedbook.value = null;
            showPopularBooks.value = false;
        }
    },
    { immediate: true }
);

function goBack() {
    router.back();
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    studentStore.fetchBooks();
    fetchBooksAdmin();
});
onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="max-w-full mx-auto">
        <div v-if="loading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>
        <div v-else>
            <div
                class="w-full bg-white p-4 py-2 rounded-lg space-y-4 md:space-y-0 md:flex md:flex-wrap md:items-center md:justify-between">
                <!-- analatics dashboard -->
                <div class="w-full bg-white py-2 rounded-lg  space-y-0 flex flex-wrap items-center justify-between">

                    <!-- Skill Level Dropdown -->
                    <div class="flex flex-row gap-8">
                        <div class="flex items-center px-3 py-2 rounded">
                            <p class="text-xs font-medium text-gray-600 mr-1">Total {{ props?.activeTab }}</p>
                            <p class="text-lg font-bold text-lime-700">{{ analytics?.total }}</p>
                        </div>
                        <div class="flex items-center px-3 py-2 rounded">
                            <p class="text-xs font-medium text-gray-600 mr-1">New Today</p>
                            <p class="text-lg font-bold">{{ analytics?.newToday }}</p>
                        </div>
                    </div>

                    <!-- View Toggle Button -->
                    <div>
                        <button @click="toggleBookView" class="text-black p-2 rounded-full transition duration-200">
                            <i :class="computedbookViewMode === 'card'
                                ? 'fa-solid fa-list'
                                : 'fa-solid fa-th-large'
                                " class="text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="booksAdmin.length === 0" class="text-gray-500">No books available.
            </div>
            <div v-if="!selectedbook && !showPopularBooks" class="pt-3">
                <!-- Card Layout -->
                <div v-if="computedbookViewMode === 'card'">
                    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 py-2">
                        <div v-for="book in filteredBooksAdmin" :key="book.id"
                            class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col max-w-sm w-full mx-auto">
                            <!-- Book Cover -->
                            <img :src="book.cover_page_url" alt="Cover Page"
                                class="w-full h-56 object-cover rounded-t-xl" />

                            <!-- Book Info -->
                            <div class="p-4 flex flex-col flex-grow">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">{{ book.title }}</h3>
                                <p class="text-sm text-gray-500 mb-3">{{ book.auther || 'Unknown Author' }}</p>

                                <!-- Details Grid -->
                                <div class="text-sm text-gray-700 space-y-1 mb-4">
                                    <div class="flex justify-between">
                                        <span class="font-medium">Price:</span>
                                        <span>${{ book.price || 4.6 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Published:</span>
                                        <span>{{ book.publish_date || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Edition:</span>
                                        <span>{{ book.eddition || 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="mt-auto pt-4 border-t border-gray-200 grid grid-cols-3 gap-2 text-sm font-medium">
                                    <button @click="selectBookforEdit(book)"
                                        class="flex items-center justify-center gap-1 px-3 py-2 rounded-md bg-slate-50 text-gray-500 hover:bg-slate-100 transition">
                                        ✏️ Edit
                                    </button>
                                    <button @click="changeTab(book.slug)"
                                        class="flex items-center justify-center gap-1 px-3 py-2 rounded-md bg-slate-50 text-gray-500 hover:bg-slate-100 transition">
                                        📄 Details
                                    </button>
                                    <button @click="deleteCourse(book.id)"
                                        class="flex items-center justify-center gap-1 px-3 py-2 rounded-md bg-slate-50 text-gray-500 hover:bg-slate-100 transition">
                                        🗑️ Delete
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <!-- Table Layout -->
                <div v-if="computedbookViewMode === 'table'">
                    <div v-if="filteredBooksAdmin.length > 0" class="w-full  overflow-x-auto scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white rounded-t-lg">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">TITLE
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">AUTHOR
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase"> Rating
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase"> Total
                                        Enroll </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase"> Revenue
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">
                                        PUBLISHED DATE</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">ACTIONS
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="book in filteredBooksAdmin" :key="book.id"
                                    class="border-b border-gray-200 hover:bg-gray-50 transition">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-3">
                                        <img :src="book.cover_page_url" alt="Cover Page"
                                            class="w-12 h-12 rounded-md object-cover" />
                                        <h3 class="text-sm font-semibold text-gray-800  truncate max-w-[30ch]">{{ book.title }}</h3>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ book.auther }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ book.averageRating }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ book.total_enroll }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ book.revenue }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ">
                                        {{ book.publish_date || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex h-full gap-2">
                                            <button @click="changeTab(book.slug)"
                                                class="bg-white border border-gray-200 px-3 py-1 rounded text-sm text-black hover:bg-gray-200 hover:text-gray-900 transition">
                                                Details
                                            </button>
                                            <div class="bg-white border border-gray-100 rounded-md p-1 relative">
                                                <button class="text-gray-600 hover:text-gray-900 focus:outline-none"
                                                    @click.stop="toggleMenu(book.id)">
                                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <circle cx="12" cy="6" r="1.5" fill="currentColor" />
                                                        <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                                                        <circle cx="12" cy="18" r="1.5" fill="currentColor" />
                                                    </svg>
                                                </button>
                                                <div v-if="activeMenuId === book.id"
                                                    class="absolute right-0 bg-white shadow-lg rounded-md border border-gray-300 z-50 w-32 text-xs flex flex-col">
                                                    <button @click="selectBookforEdit(book); closeMenu()"
                                                        class="w-full px-2 py-1.5 text-left text-blue-600 hover:bg-blue-100 transition">
                                                        Edit
                                                    </button>
                                                    <button @click="deleteCourse(book.id); closeMenu()"
                                                        class="w-full px-2 py-1.5 text-left text-red-600 hover:bg-red-100 transition">
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Footer -->
                    <div v-if="pagination.last_page >= 1"
                        class="p-4 bg-white flex flex-row items-center justify-between">
                        <Popper>
                            <div class="flex flex-row md:gap-2">
                                <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                                <span class="text-sm font-medium">{{ rowsPerPage }}</span>
                                <i class="fa-solid fa-chevron-down text-lg"></i>
                            </div>
                            <template #content>
                                <div v-for="option in rowsPerPageOptions" :key="option"
                                    @click="changeBooksPerPage(option)"
                                    class="border w-32 block border-gray-200 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                                    :class="{
                                        'bg-gray-300 text-white font-bold':
                                            rowsPerPage === option,
                                        'bg-white text-gray-700 hover:bg-gray-200':
                                            rowsPerPage !== option,
                                    }">
                                    {{ option }}
                                </div>
                            </template>
                        </Popper>

                        <!-- Pagination Controls -->
                        <div class="flex items-center space-x-3">
                            <button :disabled="pagination.current_page <= 1" @click="prevPage"
                                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                Previous
                            </button>

                            <span class="text-sm text-gray-700">
                                Page <span class="font-semibold">{{ pagination.current_page }}</span> of
                                <span class="font-semibold">{{ pagination.last_page }}</span>
                            </span>

                            <button :disabled="pagination.current_page >= pagination.last_page" @click="nextPage"
                                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                Next
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <EditBookForm v-if="editingBookId" :selectedbook="selectedbook" :booksAdmin="booksAdmin"
            :editingBookId="editingBookId" @cancel-edit="cancelEdit" />
        <BookDetail v-if="showPopularBooks && selectedbook" :selectedbook="selectedbook" :goBack="goBack" />
    </div>
</template>

<style scoped>
.custom-editor .p-editor-content {
    position: relative !important;
    z-index: 0 !important;
}

.editor-wrapper {
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 0;
}
</style>
