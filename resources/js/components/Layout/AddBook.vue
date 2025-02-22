<script setup>
import { ref } from "vue";
import Axios from "axios";

const newBook = ref({
  title: "",
  auther: "",
  price: null,
  eddition: null,
  discount: null,
  publish_date: "",
  description: "",
  language: "",
  file_format: "",
  cover_page_url: "",
  file_url: "",
  tag: "",
  isDownloadable: false,
});

const loading = ref(false);
const error = ref("");
const success = ref("");

const addBook = async () => {
  error.value = "";
  success.value = "";
  loading.value = true;
  
  // Convert comma-separated tags into a JSON string
  const tagsArray = newBook.value.tag
    .split(",")
    .map((t) => t.trim())
    .filter((t) => t !== "");
  
  const payload = { ...newBook.value, tag: JSON.stringify(tagsArray) };
  
  try {
    const response = await Axios.post("/api/books/books", payload, {
      headers: { "Content-Type": "application/json" },
    });
    success.value = "Book added successfully!";
    // Reset form fields
    newBook.value = {
      title: "",
      auther: "",
      price: null,
      eddition: null,
      discount: null,
      publish_date: "",
      description: "",
      language: "",
      file_format: "",
      cover_page_url: "",
      file_url: "",
      tag: "",
      isDownloadable: false,
    };
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to add book.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-3xl bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:scale-105">
      <div class="p-4 md:p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-center text-lime-700 mb-4">
          Add New Book
        </h2>
        
        <!-- Success & Error Messages -->
        <div v-if="error" class="mb-3 text-center text-red-600 animate-pulse">
          {{ error }}
        </div>
        <div v-if="success" class="mb-3 text-center text-green-600 animate-pulse">
          {{ success }}
        </div>
        
        <form @submit.prevent="addBook" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Left Column -->
            <div class="space-y-3">
              <div>
                <label class="block text-gray-700 font-medium mb-1">Title</label>
                <input
                  v-model="newBook.title"
                  type="text"
                  placeholder="Book Title"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Author</label>
                <input
                  v-model="newBook.auther"
                  type="text"
                  placeholder="Author Name"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-gray-700 font-medium mb-1">Price</label>
                  <input
                    v-model.number="newBook.price"
                    type="number"
                    placeholder="e.g. 20"
                    class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                    required
                  />
                </div>
                <div>
                  <label class="block text-gray-700 font-medium mb-1">Edition</label>
                  <input
                    v-model.number="newBook.eddition"
                    type="number"
                    placeholder="e.g. 1"
                    class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                    required
                  />
                </div>
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Discount</label>
                <input
                  v-model.number="newBook.discount"
                  type="number"
                  placeholder="Optional discount"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Publish Date</label>
                <input
                  v-model="newBook.publish_date"
                  type="text"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea
                  v-model="newBook.description"
                  placeholder="Book Description"
                  rows="3"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                ></textarea>
              </div>
            </div>
            <!-- Right Column -->
            <div class="space-y-3">
              <div>
                <label class="block text-gray-700 font-medium mb-1">Language</label>
                <input
                  v-model="newBook.language"
                  type="text"
                  placeholder="Language"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">File Format</label>
                <input
                  v-model="newBook.file_format"
                  type="text"
                  placeholder="e.g. PDF"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Cover Page URL</label>
                <input
                  v-model="newBook.cover_page_url"
                  type="url"
                  placeholder="https://example.com/cover.jpg"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">File URL</label>
                <input
                  v-model="newBook.file_url"
                  type="url"
                  placeholder="https://example.com/file.pdf"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                  required
                />
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-1">Tags (comma separated)</label>
                <input
                  v-model="newBook.tag"
                  type="text"
                  placeholder="e.g. Classic, Novel"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                />
              </div>
              <div class="flex items-center space-x-2">
                <input
                  v-model="newBook.isDownloadable"
                  type="checkbox"
                  id="downloadable"
                  class="h-5 w-5 text-lime-600 focus:ring-lime-500"
                />
                <label for="downloadable" class="text-gray-700 font-medium">Downloadable</label>
              </div>
            </div>
          </div>
          <div>
            <button type="submit" class="w-full bg-lime-700 text-white py-2 rounded-md shadow transition transform hover:scale-105 hover:bg-lime-800">
              <span v-if="loading">Adding...</span>
              <span v-else>Add Book</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
