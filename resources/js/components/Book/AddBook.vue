<script setup>
import { ref } from "vue";
import Axios from "axios";
// import Editor from "primevue/editor";
import { useToast } from "vue-toastification";

const toast = useToast();

const newBook = ref({
  title: "",
  auther: "",
  price: null,
  eddition: null,
  discount: null,
  publish_date: "",
  description: "",
  page_number: "",
  language: "English",
  file_format: "pdf",
  cover_page_url: null,
  file_url: null,
  intro_video: null,
  tag: "",
});

// Reactive variables for file previews
const coverPreview = ref(null);
const videoPreview = ref(null);

const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (file) {
    newBook.value[field] = file;
    if (field === "cover_page_url") {
      coverPreview.value = URL.createObjectURL(file);
    }
    if (field === "intro_video") {
      videoPreview.value = URL.createObjectURL(file);
    }
  }
};

const loading = ref(false);
const error = ref("");
const success = ref("");

const addBook = async () => {
  error.value = "";
  success.value = "";
  loading.value = true;

  const tagsArray = newBook.value.tag
    .split(",")
    .map((t) => t.trim())
    .filter((t) => t !== "");

  const formData = new FormData();
  formData.append("title", newBook.value.title);
  formData.append("auther", newBook.value.auther);
  formData.append("price", newBook.value.price);
  formData.append("eddition", newBook.value.eddition);
  formData.append("discount", newBook.value.discount);
  formData.append("publish_date", newBook.value.publish_date);
  formData.append("description", newBook.value.description);
  formData.append("page_number", newBook.value.page_number);
  formData.append("language", newBook.value.language);
  formData.append("file_format", newBook.value.file_format);

  if (newBook.value.cover_page_url) {
    formData.append("cover_page_url", newBook.value.cover_page_url);
  }
  if (newBook.value.file_url) {
    formData.append("file_url", newBook.value.file_url);
  }
  if (newBook.value.intro_video) {
    formData.append("intro_video", newBook.value.intro_video);
  }
  formData.append("tag", JSON.stringify(tagsArray));

  try {
    await Axios.post("/api/books/books", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    success.value = "Book added successfully!";
    toast.success("Book added successfully!", { position: "top-right" });
    // Reset form fields
    newBook.value = {
      title: "",
      auther: "",
      price: null,
      eddition: null,
      discount: null,
      publish_date: "",
      description: "",
      page_number: "",
      language: "English",
      file_format: "pdf",
      cover_page_url: null,
      file_url: null,
      intro_video: null,
      tag: "",
    };
    coverPreview.value = null;
    videoPreview.value = null;
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to add book.";
    toast.error(error.value, { position: "top-right" });
  } finally {
    loading.value = false;
  }
};

function goBack() {
  window.history.back();
}
</script>

<template>
  <button
    @click="goBack()"
    class="m-2 bg-lime-700 text-white p-1 rounded hover:bg-lime-800 transition"
  >
    <i class="fas fa-arrow-left mr-2 text-xs"></i>
    <span class="text-xs">Back</span>
  </button>

  <div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold text-center mb-4">Add New Book</h2>

    <!-- Success & Error Messages -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 p-2 mb-3 rounded text-xs">
      {{ error }}
    </div>
    <div v-if="success" class="bg-green-100 border border-green-400 text-green-700 p-2 mb-3 rounded text-xs">
      {{ success }}
    </div>

    <form @submit.prevent="addBook" class="space-y-4">
      <!-- Section 1: File Uploads & Previews -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Cover Page Upload with Preview -->
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Upload Cover Page</label>
          <div class="relative border-dashed border-2 border-gray-300 rounded p-2 text-center cursor-pointer">
            <input
              type="file"
              @change="onFileChange('cover_page_url', $event)"
              class="absolute inset-0 opacity-0 cursor-pointer"
              required
            />
            <div v-if="coverPreview">
              <img :src="coverPreview" alt="Cover Preview" class="mx-auto max-h-32 object-contain" />
            </div>
            <div v-else>
              <p class="text-gray-500 text-xs">Click to select cover image</p>
            </div>
          </div>
        </div>

        <!-- Intro Video Upload with Preview -->
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Upload Intro Video</label>
          <div class="relative border-dashed border-2 border-gray-300 rounded p-2 text-center cursor-pointer">
            <input
              type="file"
              @change="onFileChange('intro_video', $event)"
              class="absolute inset-0 opacity-0 cursor-pointer"
            />
            <div v-if="videoPreview">
              <video :src="videoPreview" controls class="mx-auto max-h-32 object-contain"></video>
            </div>
            <div v-else>
              <p class="text-gray-500 text-xs">Click to select intro video</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 2: File URL Upload -->
      <div>
        <label class="block text-gray-700 font-medium text-xs mb-1">Upload File URL</label>
        <input
          type="file"
          @change="onFileChange('file_url', $event)"
          class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
          required
        />
      </div>

      <div class="mt-7">
        <label class="block text-gray-700 font-medium text-xs mb-1">Description</label>
        <textarea
          v-model="newBook.description"
          :style="{ height: '120px' }"
          class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
          placeholder="Enter description here..."
        ></textarea>
      </div>

      <!-- Section 3: Compact Other Inputs in 3-Column Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Title</label>
          <input
            v-model="newBook.title"
            type="text"
            placeholder="Book Title"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Author</label>
          <input
            v-model="newBook.auther"
            type="text"
            placeholder="Author Name"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Price</label>
          <input
            v-model.number="newBook.price"
            type="number"
            placeholder="e.g. 20"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Edition</label>
          <input
            v-model.number="newBook.eddition"
            type="number"
            placeholder="e.g. 1"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Discount</label>
          <input
            v-model.number="newBook.discount"
            type="number"
            placeholder="Optional"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Publish Date</label>
          <input
            v-model="newBook.publish_date"
            type="date"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Language</label>
          <input
            v-model="newBook.language"
            type="text"
            placeholder="Language"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">Page Number</label>
          <input
            v-model="newBook.page_number"
            type="number"
            placeholder="Page Number"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          />
        </div>
        <div>
          <label class="block text-gray-700 font-medium text-xs mb-1">File Format</label>
          <select
            v-model="newBook.file_format"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
            required
          >
            <option value="pdf">PDF</option>
            <option value="word">Word</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="block text-gray-700 font-medium text-xs mb-1">Tags (comma separated)</label>
          <input
            v-model="newBook.tag"
            type="text"
            placeholder="e.g. Classic, Novel"
            class="w-full border border-gray-300 rounded-sm p-1 text-xs focus:outline-none focus:ring-1 focus:ring-lime-500"
          />
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-4">
        <button type="submit" class="w-full bg-lime-700 text-white py-2 rounded-sm shadow transition hover:scale-105 text-xs">
          <span v-if="loading">Adding...</span>
          <span v-else>Add Book</span>
        </button>
      </div>
    </form>
  </div>
</template>
