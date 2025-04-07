<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import Axios from "axios";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import Spinner from "@/components/Layout/Spinner";
import Editor from "primevue/editor";
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    searchQuery: {
        type: String,
        default: "",
    },
});

// ===================
// Admin Books Section
// ===================
const booksAdmin = ref([]);
const loading = ref(true);
const error = ref("");
const selectedbook = ref(null);
const editingBookId = ref(null);
const activeMenuId = ref(null);
const showPopularBooks = ref(false);

const router = useRouter();

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

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    fetchBooksAdmin();
});
onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

// Backend pagination variables
const currentPage = ref(1);
const itemsPerPage = 10;
const pagination = ref({});

// Fetch books from backend API
const fetchBooksAdmin = async () => {
    loading.value = true;
    error.value = "";

    try {
        const response = await Axios.get("/api/books/books", {
            params: { page: currentPage.value },
            headers: { "Content-Type": "application/json" },
        });
        booksAdmin.value = response.data.data;
        pagination.value = response.data.pagination;
        // Optional: Uncomment below if you want a success toast on fetch
        // toast.success("Books fetched successfully!", { position: "top-right" });
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to fetch Books.";
        toast.error(error.value, { position: "top-right" });
    } finally {
        loading.value = false;
    }
};

const nextPage = () => {
    if (pagination.value.current_page < pagination.value.last_page) {
        currentPage.value++;
        fetchBooksAdmin();
        toast.info(`Page ${currentPage.value}`, { position: "top-right" });
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchBooksAdmin();
        toast.info(`Page ${currentPage.value}`, { position: "top-right" });
    }
};

const updateCourse = async () => {
    if (!selectedbook.value || !selectedbook.value.id) {
        error.value = "No BOOK selected.";
        toast.error("No BOOK selected.", { position: "top-right" });
        return;
    }

    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append("title", selectedbook.value.title);
    formData.append("auther", selectedbook.value.auther);
    formData.append("price", selectedbook.value.price);
    formData.append("eddition", selectedbook.value.eddition);
    formData.append("discount", selectedbook.value.discount);
    formData.append("publish_date", selectedbook.value.publish_date);
    formData.append("description", selectedbook.value.description);
    formData.append("language", selectedbook.value.language);
    formData.append("file_format", selectedbook.value.file_format);
    if (selectedbook.value.cover_page_url instanceof File) {
        formData.append("cover_page_url", selectedbook.value.cover_page_url);
    }
    if (selectedbook.value.file_url instanceof File) {
        formData.append("file_url", selectedbook.value.file_url);
    }
    if (selectedbook.value.intro_vedio instanceof File) {
        formData.append("intro_vedio", selectedbook.value.intro_vedio);
    }
    formData.append("page_number", selectedbook.value.page_number);
    formData.append("isDownloadable", selectedbook.value.isDownloadable);
    formData.append("tag", selectedbook.value.tag);

    try {
        const response = await Axios.post(
            `/api/books/books/${selectedbook.value.id}`,
            formData,
            { headers: { "Content-Type": "multipart/form-data" } }
        );
        booksAdmin.value = booksAdmin.value.map((book) =>
            book.id === selectedbook.value.id ? response.data.data : book
        );
        toast.success("Book updated successfully!", { position: "top-right" });
        selectedbook.value = null;
        editingBookId.value = null;
        showPopularBooks.value = false;
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to update book.";
        toast.error(error.value, { position: "top-right" });
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

const cartStore = useCartStore();
const studentStore = UseStudentStore();
const { books, bookOverviewTab, selectedBook } = storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

function changeTab(slug) {
    router.push({
        name: "student",
        query: { tab: bookOverviewTab.value, slug: slug },
    });
    //toast.info("Navigating to book details.", { position: "top-right" });
}

onMounted(() => {
    studentStore.fetchBooks();
});

const selectedFilter = ref("");
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

const storedViewMode = localStorage.getItem("viewMode");
const viewMode = ref(storedViewMode ? storedViewMode : "auto");
const computedViewMode = computed(() => {
    if (viewMode.value === "auto") {
        return filteredBooksAdmin.value.length <= 4 ? "card" : "table";
    }
    return viewMode.value;
});
const toggleView = () => {
    if (viewMode.value === "auto") {
        viewMode.value = computedViewMode.value === "card" ? "table" : "card";
    } else {
        viewMode.value = viewMode.value === "card" ? "table" : "card";
    }
    localStorage.setItem("viewMode", viewMode.value);
    //toast.info(`View mode changed to ${viewMode.value}`, { position: "top-right" });
};

const introVideoPreview = ref(null);

const onFileChange = (field, event) => {
    const file = event.target.files[0];

    if (file) {
        selectedbook.value[field] = file;

        if (field === 'intro_vedio') {
            introVideoPreview.value = URL.createObjectURL(file);
        }
    }
};
</script>

<style scoped>
/* Custom CSS for PrimeVue Editor */
.custom-editor .p-editor-content {
    position: relative !important;
    z-index: 0 !important;
}

.editor-wrapper {
    margin-bottom: 1.5rem;
    /* Increase bottom margin to ensure space for buttons */
    position: relative;
    z-index: 0;
}
</style>

<template>
    <div class="max-w-full mx-auto">
        <!-- Toggle Button -->
        <div class="flex justify-end mb-4">
            <button @click="toggleView"
                class="bg-lime-700 hover:bg-lime-900 text-white p-2 rounded-full">
                <i :class="computedViewMode === 'card' ? 'fa-solid fa-list' : 'fa-solid fa-th-large'"
                    class="text-xl"></i>
            </button>
        </div>
        <div v-if="loading"
            class="flex justify-center items-center h-64">
            <Spinner />
        </div>

        <!-- Loading & Error -->
        <div v-if="error"
            class="text-center text-red-500">{{ error }}</div>

        <!-- Admin Book List -->
        <div v-if="booksAdmin.length === 0"
            class="text-gray-500">No books available.</div>
        <div v-if="!selectedbook && !showPopularBooks">
            <!-- Card Layout -->
            <div v-if="computedViewMode === 'card'">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-2 p-3">
                    <div v-for="book in filteredBooksAdmin"
                        :key="book.id"
                        class="bg-white rounded-lg shadow p-2 flex flex-col max-w-sm mx-auto">
                        <img :src="book.cover_page_url"
                            alt="Cover Page"
                            class="w-full h-56 object-cover rounded mb-3" />
                        <h3 class="text-base font-semibold">{{ book.title }}</h3>
                        <p class="text-sm text-gray-600">{{ book.auther || 'Unknown' }}</p>
                        <p class="text-sm font-bold text-center">
                            Price: ${{ book.price || 4.6 }}
                        </p>
                        <p class="text-sm text-center">
                            Published: {{ book.publish_date || 'N/A' }}
                        </p>
                        <p class="text-sm text-center">
                            Edition: {{ book.eddition || '' }}
                        </p>
                        <div class="mt-3 flex justify-around">
                            <button @click="selectBookforEdit(book)"
                                class="text-green-600 hover:underline text-sm">
                                Edit
                            </button>
                            <button @click="changeTab(book.slug)"
                                class="text-blue-600 hover:underline text-sm">
                                Details
                            </button>
                            <button @click="deleteCourse(book.id)"
                                class="text-red-600 hover:underline text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table Layout -->
            <div v-if="computedViewMode === 'table'">
                <div class="w-full  overflow-x-auto scrollbar">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white rounded-t-lg">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">TITLE</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">PRICE</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">AUTHOR</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">PUBLISHED DATE</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">EDDITION</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="book in filteredBooksAdmin"
                                :key="book.id"
                                class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-3">
                                    <img :src="book.cover_page_url"
                                        alt="Cover Page"
                                        class="w-12 h-12 rounded-md object-cover" />
                                    <h3 class="text-sm font-semibold text-gray-800">{{ book.title }}</h3>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ book.price || 4.6 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ book.auther || 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ">
                                    {{ book.publish_date || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ">
                                    {{ book.eddition || '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex h-full gap-2">
                                    <button @click="changeTab(book.slug)"
                                        class="text-black text-sm font-semibold hover:underline">
                                        Details
                                    </button>
                                    <div class="bg-white border border-gray-100 rounded-md p-1 relative">
                                        <button class="text-gray-600 hover:text-gray-900 focus:outline-none"
                                            @click.stop="toggleMenu(book.id)">
                                            <svg class="w-5 h-5"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <circle cx="12"
                                                    cy="6"
                                                    r="1.5"
                                                    fill="currentColor" />
                                                <circle cx="12"
                                                    cy="12"
                                                    r="1.5"
                                                    fill="currentColor" />
                                                <circle cx="12"
                                                    cy="18"
                                                    r="1.5"
                                                    fill="currentColor" />
                                            </svg>
                                        </button>
                                        <div v-if="activeMenuId === book.id"
                                            class="absolute right-0 bg-white shadow-lg rounded-md border border-gray-300 z-50 w-32 text-xs">
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
                <!-- Pagination Controls -->
                <div v-if="pagination.last_page > 1"
                    class="pagination flex justify-between items-center mt-4">
                    <button :disabled="pagination.current_page <= 1"
                        @click="prevPage"
                        class="px-4 py-2 bg-green-500 hover:bg-blue-600 text-white rounded transition-colors duration-200 disabled:opacity-50">
                        Previous
                    </button>
                    <span class="text-gray-700">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <button :disabled="pagination.current_page >= pagination.last_page"
                        @click="nextPage"
                        class="px-4 py-2 bg-green-500 hover:bg-blue-600 text-white rounded transition-colors duration-200 disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Book Form -->
        <div v-if="editingBookId"
            class="max-w-4xl mx-auto p-4 mt-6 bg-white shadow-lg rounded-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-center mb-4">Edit Book</h2>
            <form @submit.prevent="updateCourse"
                class="space-y-4">
                <!-- Section 1: File Uploads -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Cover Page Upload with Preview -->
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Upload Cover Page</label>
                        <div
                            class="relative border-dashed border-2 border-gray-300 rounded p-2 text-center cursor-pointer">
                            <input type="file"
                                @change="onFileChange('cover_page_url', $event)"
                                class="absolute inset-0 opacity-0 cursor-pointer" />
                            <div v-if="selectedbook && selectedbook.cover_page_url">
                                <img :src="typeof selectedbook.cover_page_url === 'string' ? selectedbook.cover_page_url : URL.createObjectURL(selectedbook.cover_page_url)"
                                    alt="Cover Preview"
                                    class="mx-auto max-h-32 object-contain" />
                            </div>
                            <div v-else>
                                <p class="text-gray-500 text-xs">Click to select cover image</p>
                            </div>
                        </div>
                    </div>
                    <!-- File URL Upload with Display -->
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Upload Your Video</label>
                        <div
                            class="relative border-dashed border-2 border-gray-300 rounded p-2 text-center cursor-pointer">
                            <input type="file"
                                @change="onFileChange('intro_vedio', $event)"
                                accept="video/*"
                                class="absolute inset-0 opacity-0 cursor-pointer" />
                            <div v-if="introVideoPreview">
                                <video controls
                                    class="w-full max-h-40 mx-auto">
                                    <source :src="introVideoPreview"
                                        type="video/mp4" />
                                    Your browser does not support the video tag.
                                </video>
                                <p class="text-xs text-gray-700 mt-2">New Video Selected</p>
                            </div>
                            <div v-else-if="selectedbook.intro_vedio && typeof selectedbook.intro_vedio === 'string'">
                                <p class="text-xs text-gray-700">
                                    Current Video:
                                    <a :href="selectedbook.intro_vedio"
                                        target="_blank"
                                        class="underline">
                                        {{ selectedbook.intro_vedio.split('/').pop() }}
                                    </a>
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-gray-500 text-xs">Click to select video</p>
                            </div>
                        </div>
                    </div>

                    <!-- Intro Video Upload remains unchanged -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium text-xs mb-1">Upload book</label>
                        <input type="file"
                            @change="onFileChange('file_url', $event)"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs" />
                    </div>
                </div>

                <!-- Section 2: Description -->
                <div class="mt-7">
                    <label class="block text-gray-700 font-medium text-xs mb-1">Description</label>
                    <textarea v-model="selectedbook.description"
                        :key="selectedbook.id"
                        placeholder="Enter the book description..."
                        style="height:150px;"
                        class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-lime-500"></textarea>
                    <p v-if="!selectedbook.description"
                        class="text-red-500 text-xs mt-1">
                        Please fill out the description.
                    </p>
                </div>

                <!-- Section 3: Other Book Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Title</label>
                        <input v-model="selectedbook.title"
                            type="text"
                            placeholder="Book Title"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Author</label>
                        <input v-model="selectedbook.auther"
                            type="text"
                            placeholder="Author Name"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Price</label>
                        <input v-model.number="selectedbook.price"
                            type="number"
                            placeholder="e.g. 20"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Edition</label>
                        <input v-model.number="selectedbook.eddition"
                            type="number"
                            placeholder="e.g. 1"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Discount</label>
                        <input v-model.number="selectedbook.discount"
                            type="number"
                            placeholder="Optional"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Publish Date</label>
                        <input v-model="selectedbook.publish_date"
                            type="date"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Language</label>
                        <input v-model="selectedbook.language"
                            type="text"
                            placeholder="Language"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">Page Number</label>
                        <input v-model.number="selectedbook.page_number"
                            type="number"
                            placeholder="Page Number"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium text-xs mb-1">File Format</label>
                        <select v-model="selectedbook.file_format"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs"
                            required>
                            <option value="pdf">PDF</option>
                            <option value="word">Word</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-gray-700 font-medium text-xs mb-1">Tags (comma separated)</label>
                        <input v-model="selectedbook.tag"
                            type="text"
                            placeholder="e.g. Classic, Novel"
                            class="w-full border border-gray-300 rounded-sm p-1 text-xs" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-4">
                    <button type="submit"
                        class="w-full bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                        Update Book
                    </button>
                    <button type="button"
                        @click="cancelEdit"
                        class="w-full bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Book Detail Page -->
        <div v-if="showPopularBooks && selectedbook"
            class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
            <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
                <!-- Left Side: Book Info & Description -->
                <div>
                    <div class="text-left">
                        <h1 class="text-4xl text-gray-900 font-bold">{{ selectedbook.title }}</h1>
                        <div class="flex my-2 gap-4">
                            <div>
                                <img src="/images/course-1.jpg"
                                    :alt="selectedbook?.user.first_name"
                                    class="w-12 h-12 object-cover mt-4 rounded-full" />
                            </div>
                            <div class="text-lg text-gray-700 mt-2">
                                <p>Book Offered by</p>
                                <p class="font-bold">{{ selectedbook?.user.first_name }}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="overflow-hidden max-w-full h-auto flex w-full justify-center items-center rounded-t-lg mt-6 relative cursor-pointer">
                        <img :src="selectedbook?.cover_page_url"
                            alt="Book Image"
                            class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl" />
                    </div>
                    <div class="bg-white p-4 py-8 rounded-b-lg">
                        <div class="text-left mb-6">
                            <div class="flex items-left justify-between">
                                <h2 class="text-3xl text-slate-600 font-semibold">Book Description</h2>
                            </div>
                            <p class="py-4 text-lg leading-9">{{ selectedbook?.description }}</p>
                        </div>
                    </div>
                </div>
                <!-- Right Side: Book Details -->
                <div class="w-full border border-e-gray-300 h-1/2 min-h-fit rounded-lg p-6">
                    <button @click="cancelEdit"
                        class="mt-6 bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 w-full">
                        Back
                    </button>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Auther:</span>{{
                            selectedbook?.auther }}</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Language:</span>{{
                            selectedbook?.language }}</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">File Format:</span>{{
                            selectedbook?.file_format }}</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Publish Date:</span>{{
                            selectedbook?.publish_date }}</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-red-600 text-lg"><span class="font-bold pr-4">Original Price:</span>{{
                            selectedbook?.price }} Birr</p>
                    </div>
                    <div v-if="selectedbook?.discount"
                        class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-lime-600 text-lg"><span class="font-bold pr-4">Discount Price:</span>{{
                            selectedbook?.discount }} Birr</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Page Numbers:</span>{{
                            selectedbook?.page_number }}</p>
                    </div>
                    <div class="flex flex-row gap-4 my-2">
                        <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
                        <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Eddition:</span>{{
                            selectedbook?.eddition }} Birr</p>
                    </div>
                    <div v-if="timeLeft && timeLeft.days >= 0"
                        class="mt-4 p-4 bg-red-100 text-red-600 text-center rounded-lg">
                        <p class="font-semibold">This offer ends in:</p>
                        <p class="text-xl font-bold">
                            {{ timeLeft.days }} days {{ timeLeft.hours }}h : {{ timeLeft.minutes }}m : {{
                            timeLeft.seconds }}s
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
