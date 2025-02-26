<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import Axios from "axios";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { useCartStore } from "@/store/useCartStore";
import { UseStudentStore } from "@/store/UseStudentStore";

// Accept the search query as a prop from the parent
const props = defineProps({
  searchQuery: {
    type: String,
    default: "",
  },
});

// ===================
// Admin Books Section
// ===================
const booksAdmin = ref([]); // Admin view: all books
const loading = ref(true);
const error = ref("");
const selectedCourse = ref(null); // Holds a single book's details for editing
const editingCourseId = ref(null);
const activeMenuId = ref(null);
const showPopularBooks = ref(false); // When true, display the detail page

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

const fetchBooksAdmin = async () => {
  loading.value = true;
  error.value = "";
  try {
    const response = await Axios.get("/api/books/books", {
      headers: { "Content-Type": "application/json" },
    });
    // Assuming the API returns an object like { data: [...] }
    booksAdmin.value = response.data.data;
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to fetch Books.";
  } finally {
    loading.value = false;
  }
};

// Update course using FormData (with _method override)
const updateCourse = async () => {
  if (!selectedCourse.value || !selectedCourse.value.id) {
    error.value = "No BOOK selected.";
    return;
  }
  
  // Create a FormData object and append all fields
  const formData = new FormData();
  formData.append("_method", "PUT");
  formData.append("title", selectedCourse.value.title);
  formData.append("auther", selectedCourse.value.auther);
  formData.append("price", selectedCourse.value.price);
  formData.append("eddition", selectedCourse.value.eddition);
  formData.append("discount", selectedCourse.value.discount);
  formData.append("publish_date", selectedCourse.value.publish_date);
  formData.append("description", selectedCourse.value.description);
  formData.append("language", selectedCourse.value.language);
  formData.append("file_format", selectedCourse.value.file_format);
  // Append new file inputs if a new file is chosen
  if (selectedCourse.value.cover_page_url instanceof File) {
    formData.append("cover_page_url", selectedCourse.value.cover_page_url);
  }
  if (selectedCourse.value.file_url instanceof File) {
    formData.append("file_url", selectedCourse.value.file_url);
  }
  // Append the new required fields
  formData.append("page_number", selectedCourse.value.page_number);
  formData.append("isDownloadable", selectedCourse.value.isDownloadable);
  formData.append("tag", selectedCourse.value.tag);
  
  try {
    // Using POST with _method override for file upload
    const response = await Axios.post(
      `/api/books/books/${selectedCourse.value.id}`,
      formData,
      { headers: { "Content-Type": "multipart/form-data" } }
    );
    booksAdmin.value = booksAdmin.value.map((book) =>
      book.id === selectedCourse.value.id ? response.data.data : book
    );
    // Clear selection and hide the detail page
    selectedCourse.value = null;
    editingCourseId.value = null;
    showPopularBooks.value = false;
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to update book.";
  }
};

const deleteCourse = async (id) => {
  if (!window.confirm("Are you sure you want to delete this book?")) return;
  try {
    await Axios.delete(`/api/books/books/${id}`, {
      headers: { "Content-Type": "application/json" },
    });
    booksAdmin.value = booksAdmin.value.filter((book) => book.id !== id);
    alert("BOOK successfully deleted!");
  } catch (err) {
    alert(err.response?.data?.message || "Failed to delete book.");
  }
};

const selectCourseForEdit = (book) => {
  editingCourseId.value = book.id;
  // Create a shallow copy so that file changes don't immediately mutate the original data.
  selectedCourse.value = { ...book };
};

const cancelEdit = () => {
  editingCourseId.value = null;
  selectedCourse.value = null;
  showPopularBooks.value = false;
};

// When "Details" is clicked, fetch the detailed info for that specific book by id
const showDetailModule = async (book) => {
  if (!book || !book.id) {
    error.value = "Invalid book selected.";
    return;
  }
  loading.value = true;
  error.value = "";
  try {
    const response = await Axios.get(`/api/books/books/${book.id}`, {
      headers: { "Content-Type": "application/json" },
    });
    console.log("Book detail fetched:", response.data);
    selectedCourse.value = response.data.data;
    showPopularBooks.value = true;
  } catch (err) {
    error.value =
      err.response?.data?.message || "Failed to fetch book details.";
  } finally {
    loading.value = false;
  }
};

// ===================
// Popular Books Section (from Pinia Store)
// ===================
const cartStore = useCartStore();
const studentStore = UseStudentStore();
const { books, bookOverviewTab, selectedBook } = storeToRefs(studentStore);
const { items, itemCount, image } = storeToRefs(cartStore);

function changeTab(slug) {
  router.push({
    name: "student",
    query: { tab: bookOverviewTab.value, slug: slug },
  });
}

onMounted(() => {
  studentStore.fetchBooks();
});

const selectedFilter = ref("");

// Updated computed property to filter booksAdmin based on the search query (by title)
// and the selectedFilter (if any)
const filteredBooksAdmin = computed(() => {
  let filtered = booksAdmin.value;
  if (props.searchQuery.trim() !== "") {
    const query = props.searchQuery.trim().toLowerCase();
    filtered = filtered.filter((book) =>
      book.title.toLowerCase().includes(query)
    );
  }
  if (selectedFilter.value) {
    filtered = filtered.filter(
      (book) => book.skill_level === selectedFilter.value
    );
  }
  return filtered;
});

// =======
// Pagination Logic
// =======
const currentPage = ref(1);
const itemsPerPage = ref(3);
const totalPages = computed(() =>
  Math.ceil(filteredBooksAdmin.value.length / itemsPerPage.value)
);
const paginatedBooksAdmin = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredBooksAdmin.value.slice(start, start + itemsPerPage.value);
});

// Reusable file change handler for updating files in edit mode
const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (file && editingCourseId.value) {
    selectedCourse.value[field] = file;
  }
};
</script>

<template>
  <div class="max-w-full mx-auto">
    <!-- Loading & Error -->
    <div v-if="loading" class="text-center text-gray-500">Loading books...</div>
    <div v-if="error" class="text-center text-red-500">{{ error }}</div>

    <!-- Admin Book List -->
    <div v-if="booksAdmin.length === 0" class="text-gray-500">No books available.</div>
    <div v-if="!selectedCourse && !showPopularBooks">
      <div class="overflow-x-auto border border-gray-300 rounded-lg">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 rounded-t-lg">
            <tr class="border-b border-gray-300 text-gray-600 text-sm">
              <th class="py-3 px-4 font-normal">TITLE</th>
              <th class="py-3 px-4 font-normal">PRICE</th>
              <th class="py-3 px-4 font-normal">AUTHOR</th>
              <th class="py-3 px-4 font-normal">PUBLISHED DATE</th>
              <th class="py-3 px-4 font-normal">EDDITION</th>
              <th class="py-3 px-4 font-normal">ACTIONS</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <!-- Use paginatedBooksAdmin instead of filteredBooksAdmin -->
            <tr
              v-for="book in paginatedBooksAdmin"
              :key="book.id"
              class="border-b border-gray-200 hover:bg-gray-50 transition"
            >
              <td class="py-2 px-2 flex items-center gap-3">
                <img
                  :src="book.cover_page_url"
                  alt="Cover Page"
                  class="w-12 h-12 rounded-md object-cover"
                />
                <h3 class="text-sm font-semibold text-gray-800">{{ book.title }}</h3>
              </td>
              <td class="py-2 px-2 text-gray-700 text-sm font-bold text-center">
                ${{ book.price || 4.6 }}
              </td>
              <td class="py-2 px-2 text-gray-700 text-sm font-bold text-center">
                {{ book.auther || 'Unknown' }}
              </td>
              <td class="py-2 px-2 text-gray-700 text-sm font-bold text-center">
                {{ book.publish_date || 'N/A' }}
              </td>
              <td class="py-2 px-2 text-gray-700 text-sm font-bold text-center">
                {{ book.eddition || '' }}
              </td>
              <td class="py-2 px-2 flex items-center gap-3">
                <button @click="changeTab(book.slug)" class="text-black text-sm font-semibold hover:underline">
                  Details
                </button>
                <div class="bg-white border border-gray-100 rounded-md p-1 relative">
                  <button
                    class="text-gray-600 hover:text-gray-900 focus:outline-none"
                    @click.stop="toggleMenu(book.id)"
                  >
                    <svg
                      class="w-5 h-5"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <circle cx="12" cy="6" r="1.5" fill="currentColor" />
                      <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                      <circle cx="12" cy="18" r="1.5" fill="currentColor" />
                    </svg>
                  </button>
                  <div
                    v-if="activeMenuId === book.id"
                    class="absolute right-0 bg-white shadow-lg rounded-md border border-gray-300 z-50 w-32 text-xs"
                  >
                    <button
                      @click="selectCourseForEdit(book); closeMenu()"
                      class="w-full px-2 py-1.5 text-left text-blue-600 hover:bg-blue-100 transition"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteCourse(book.id); closeMenu()"
                      class="w-full px-2 py-1.5 text-left text-red-600 hover:bg-red-100 transition"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="pagination flex justify-between items-center mt-4">
        <button
          :disabled="currentPage <= 1"
          @click="currentPage--"
          class="px-4 py-2 bg-green-500 hover:bg-blue-600 text-white rounded transition-colors duration-200 disabled:opacity-50"
        >
          Previous
        </button>
        <span class="text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
          class="px-4 py-2 bg-green-500 hover:bg-blue-600 text-white rounded transition-colors duration-200 disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Edit Book Form -->
    <div v-if="editingCourseId" class="mt-6 bg-white p-6 shadow-lg rounded-lg border border-gray-200">
      <h3 class="text-xl font-semibold mb-4">Edit Book</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Left Column -->
        <div class="space-y-3">
          <div>
            <label class="block text-gray-700 font-medium mb-1">Title</label>
            <input
              v-model="selectedCourse.title"
              type="text"
              class="w-full border border-gray-300 rounded p-2"
              required
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Author</label>
            <input
              v-model="selectedCourse.auther"
              type="text"
              class="w-full border border-gray-300 rounded p-2"
              required
            />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-gray-700 font-medium mb-1">Price</label>
              <input
                v-model.number="selectedCourse.price"
                type="number"
                class="w-full border border-gray-300 rounded p-2"
                required
              />
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-1">Edition</label>
              <input
                v-model.number="selectedCourse.eddition"
                type="number"
                class="w-full border border-gray-300 rounded p-2"
                required
              />
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Discount</label>
            <input
              v-model.number="selectedCourse.discount"
              type="number"
              class="w-full border border-gray-300 rounded p-2"
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Publish Date</label>
            <input
              v-model="selectedCourse.publish_date"
              type="text"
              class="w-full border border-gray-300 rounded p-2"
              required
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Description</label>
            <textarea
              v-model="selectedCourse.description"
              class="w-full border border-gray-300 rounded p-2"
              required
            ></textarea>
          </div>
        </div>
        <!-- Right Column -->
        <div class="space-y-3">
          <div>
            <label class="block text-gray-700 font-medium mb-1">Language</label>
            <input
              v-model="selectedCourse.language"
              type="text"
              class="w-full border border-gray-300 rounded p-2"
              required
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">File Format</label>
            <select
              v-model="selectedCourse.file_format"
              class="w-full border border-gray-300 rounded p-2"
              required
            >
              <option value="pdf">PDF</option>
              <option value="word">Word</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Upload Cover Page</label>
            <input
              type="file"
              @change="onFileChange('cover_page_url', $event)"
              class="w-full border border-gray-300 rounded p-2"
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Upload File URL</label>
            <input
              type="file"
              @change="onFileChange('file_url', $event)"
              class="w-full border border-gray-300 rounded p-2"
            />
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Tags (comma separated)</label>
            <input
              v-model="selectedCourse.tag"
              type="text"
              class="w-full border border-gray-300 rounded p-2"
            />
          </div>
          <!-- New Fields for Page Number and Downloadable -->
          <div>
            <label class="block text-gray-700 font-medium mb-1">Page Number</label>
            <input
              v-model.number="selectedCourse.page_number"
              type="number"
              class="w-full border border-gray-300 rounded p-2"
              required
            />
          </div>
          <div class="flex items-center space-x-2">
            <input
              v-model="selectedCourse.isDownloadable"
              type="checkbox"
              id="downloadable"
              class="h-5 w-5 text-lime-600 focus:ring-lime-500"
            />
            <label for="downloadable" class="text-gray-700 font-medium">Downloadable</label>
          </div>
        </div>
      </div>
      <div class="flex gap-4 mt-4">
        <button @click="updateCourse" class="bg-green-500 text-white px-6 py-2 rounded">
          Update Book
        </button>
        <button @click="cancelEdit" class="bg-gray-500 text-white px-6 py-2 rounded">
          Cancel
        </button>
      </div>
    </div>

    <!-- Book Detail Page -->
    <div v-if="showPopularBooks && selectedCourse" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
      <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
        <!-- Left Side: Book Info & Description -->
        <div>
          <div class="text-left">
            <h1 class="text-4xl text-gray-900 font-bold">{{ selectedCourse.title }}</h1>
            <div class="flex my-2 gap-4">
              <div>
                <img
                  src="/images/course-1.jpg"
                  :alt="selectedCourse?.user.first_name"
                  class="w-12 h-12 object-cover mt-4 rounded-full"
                />
              </div>
              <div class="text-lg text-gray-700 mt-2">
                <p>Book Offered by</p>
                <p class="font-bold">{{ selectedCourse?.user.first_name }}</p>
              </div>
            </div>
          </div>
          <div class="overflow-hidden max-w-full h-auto flex w-full justify-center items-center rounded-t-lg mt-6 relative cursor-pointer">
            <img
              :src="selectedCourse?.cover_page_url"
              alt="Book Image"
              class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl"
            />
          </div>
          <div class="bg-white p-4 py-8 rounded-b-lg">
            <div class="text-left mb-6">
              <div class="flex items-left justify-between">
                <h2 class="text-3xl text-slate-600 font-semibold">Book Description</h2>
              </div>
              <p class="py-4 text-lg leading-9">{{ selectedCourse?.description }}</p>
            </div>
          </div>
        </div>
        <!-- Right Side: Book Details -->
        <div class="w-full border border-e-gray-300 h-1/2 min-h-fit rounded-lg p-6">
          <button @click="cancelEdit" class="mt-6 bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 w-full">
            Back
          </button>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Auther:</span>{{ selectedCourse?.auther }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Language:</span>{{ selectedCourse?.language }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">File Format:</span>{{ selectedCourse?.file_format }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Publish Date:</span>{{ selectedCourse?.publish_date }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-red-600 text-lg"><span class="font-bold pr-4">Original Price:</span>{{ selectedCourse?.price }} Birr</p>
          </div>
          <div v-if="selectedCourse?.discount" class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-lime-600 text-lg"><span class="font-bold pr-4">Discount Price:</span>{{ selectedCourse?.discount }} Birr</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Page Numbers:</span>{{ selectedCourse?.page_number }}</p>
          </div>
          <div class="flex flex-row gap-4 my-2">
            <i class="fa-solid self-center fa-check text-lime-700 text-lg"></i>
            <p class="text-gray-600 text-lg"><span class="font-bold pr-4">Eddition:</span>{{ selectedCourse?.eddition }} Birr</p>
          </div>
          <div v-if="timeLeft && timeLeft.days >= 0" class="mt-4 p-4 bg-red-100 text-red-600 text-center rounded-lg">
            <p class="font-semibold">This offer ends in:</p>
            <p class="text-xl font-bold">
              {{ timeLeft.days }} days {{ timeLeft.hours }}h : {{ timeLeft.minutes }}m : {{ timeLeft.seconds }}s
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
