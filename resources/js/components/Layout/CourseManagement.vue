<template>
  <div class="min-h-screen bg-gray-100 text-gray-800">
    <!-- Header -->
    <header class="bg-white shadow px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-lime-700">Product Management</h1>
        <p class="text-sm text-gray-500">Manage Courses &amp; Books</p>
      </div>
      <div class="mt-4 md:mt-0 flex items-center">
        <nav class="text-sm" aria-label="Breadcrumb">
          <ol class="list-reset flex text-gray-600">
            <li>
              <a href="#" class="hover:underline">Home</a>
            </li>
            <li>
              <span class="mx-2">/</span>
            </li>
            <li class="font-medium">{{ activeTab }}</li>
          </ol>
        </nav>
        <div class="ml-6 relative">
          <input
            type="text"
            placeholder="Search products..."
            class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-lime-700"
            v-model="searchQuery"
          />
          <span class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M12.9 14.32a8 8 0 111.414-1.414l4.243 4.243a1 1 0 01-1.414 1.414l-4.243-4.243zM8 14a6 6 0 100-12 6 6 0 000 12z"
                clip-rule="evenodd"
              />
            </svg>
          </span>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="container mx-auto px-6 py-6">
      <!-- If we are in list mode, show the Tab Navigation and Add Buttons -->
      <div v-if="currentPage === 'list'">
        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'Courses'" :class="tabClass('Courses')">
              Courses
            </button>
            <button @click="activeTab = 'Books'" :class="tabClass('Books')">
              Books
            </button>
          </nav>
        </div>

        <!-- Add Buttons -->
        <div class="mb-6 flex justify-end space-x-4">
          <button @click="goToAddCourse" class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
            Add Course
          </button>
          <button @click="goToAddBook" class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
            Add Book
          </button>
        </div>

        <!-- List / Grid View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left: Product List -->
          <section class="lg:col-span-2">
            <!-- Bulk Action Toolbar (visible when items are selected) -->
            <div v-if="selectedProducts.length" class="mb-4 p-4 bg-lime-50 rounded-md flex items-center justify-between">
              <span class="text-lime-700">
                {{ selectedProducts.length }} item(s) selected
              </span>
              <div class="space-x-2">
                <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition" @click="bulkApprove">
                  Approve
                </button>
                <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition" @click="bulkDelete">
                  Delete
                </button>
                <button class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition" @click="bulkEdit">
                  Edit
                </button>
              </div>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="bg-white shadow rounded-lg overflow-hidden flex flex-col"
              >
                <img :src="product.thumbnail" alt="Product Image" class="h-40 w-full object-cover" />
                <div class="p-4 flex-1 flex flex-col">
                  <h2 class="text-lg font-semibold mb-2">{{ product.title }}</h2>
                  <p class="text-sm flex-1">{{ product.description }}</p>
                  <div class="mt-4 flex items-center justify-between">
                    <span class="px-2 py-1 text-xs font-medium bg-gray-200 rounded">
                      {{ product.status }}
                    </span>
                    <div class="space-x-2">
                      <button class="px-2 py-1 text-sm text-lime-700 hover:underline" @click="editProduct(product)">
                        Edit
                      </button>
                      <button class="px-2 py-1 text-sm text-red-600 hover:underline" @click="deleteProduct(product.id)">
                        Delete
                      </button>
                    </div>
                  </div>
                  <div class="mt-2">
                    <label class="inline-flex items-center">
                      <input type="checkbox" class="form-checkbox h-4 w-4" v-model="selectedProducts" :value="product.id" />
                      <span class="ml-2 text-sm">Select</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Right: Analytics Dashboard -->
          <aside class="bg-white shadow rounded-lg p-4">
            <h3 class="text-xl font-semibold mb-4">Analytics Dashboard</h3>
            <div class="space-y-4">
              <!-- Quick Stats -->
              <div class="grid grid-cols-3 gap-4">
                <div class="p-4 bg-lime-50 rounded text-center">
                  <p class="text-sm">Total {{ activeTab }}</p>
                  <p class="text-2xl font-bold text-lime-700">{{ analytics.total }}</p>
                </div>
                <div class="p-4 bg-green-50 rounded text-center">
                  <p class="text-sm">New Today</p>
                  <p class="text-2xl font-bold">{{ analytics.newToday }}</p>
                </div>
                <div class="p-4 bg-yellow-50 rounded text-center">
                  <p class="text-sm">Pending</p>
                  <p class="text-2xl font-bold">{{ analytics.pending }}</p>
                </div>
              </div>
              <!-- Placeholder for Interactive Chart -->
              <div class="bg-gray-100 p-4 rounded">
                <canvas id="analyticsChart" class="w-full h-48"></canvas>
              </div>
            </div>
          </aside>
        </div>
      </div>

      <!-- If currentPage is not "list", then show the Add pages -->
      <div v-else-if="currentPage === 'addcourse'" class="space-y-4">
        <AddCourse />
        <button @click="currentPage = 'list'" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition">
          Back to List
        </button>
      </div>
      <div v-else-if="currentPage === 'addbook'" class="space-y-4">
        <AddBook />
        <button @click="currentPage = 'list'" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition">
          Back to List
        </button>
      </div>
    </div>

    <!-- Modal for Editing a Product -->
    <transition name="fade">
      <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-11/12 md:w-1/2 p-6">
          <h3 class="text-xl font-semibold mb-4">Edit {{ editProductData.title }}</h3>
          <!-- Sample form fields -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Title</label>
              <input
                type="text"
                v-model="editProductData.title"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium">Description</label>
              <textarea
                v-model="editProductData.description"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium">Status</label>
              <select
                v-model="editProductData.status"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2"
              >
                <option>Pending</option>
                <option>Approved</option>
                <option>Rejected</option>
              </select>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3">
            <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded" @click="closeEditModal">
              Cancel
            </button>
            <button class="px-4 py-2 bg-lime-700 text-white rounded" @click="saveEdit">
              Save
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
  name: 'CourseBookManagement',
  setup() {
    // --- Main List State ---
    const activeTab = ref('Courses');
    const searchQuery = ref('');
    const selectedProducts = ref([]);

    // Dummy product list (replace with API call as needed)
    const products = ref([
      {
        id: 1,
        type: 'Courses',
        title: 'Vue.js Mastery',
        description: 'Learn Vue.js from scratch with practical projects.',
        status: 'Pending',
        thumbnail: '/images/course-1.jpg',
      },
      {
        id: 2,
        type: 'Courses',
        title: 'Advanced JavaScript',
        description: 'Deep dive into advanced JavaScript concepts.',
        status: 'Approved',
        thumbnail: '/images/course-2.jpg',
      },
      {
        id: 3,
        type: 'Books',
        title: 'Tailwind CSS Guide',
        description: 'A complete guide to mastering Tailwind CSS.',
        status: 'Approved',
        thumbnail: '/images/book-4.jpg',
      },
      {
        id: 4,
        type: 'Books',
        title: 'Learning D3.js',
        description: 'Visualize data with interactive charts using D3.js.',
        status: 'Pending',
        thumbnail: '/images/book-2.jpeg',
      },
    ]);

    // Dummy analytics data
    const analytics = ref({
      total: 0,
      newToday: 0,
      pending: 0,
    });

    // Modal state for editing a product
    const showEditModal = ref(false);
    const editProductData = ref({});

    // Computed filtered list based on activeTab and searchQuery
    const filteredProducts = computed(() =>
      products.value.filter(
        (prod) =>
          prod.type === activeTab.value &&
          prod.title.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    );

    // Update analytics based on activeTab (dummy logic)
    const updateAnalytics = () => {
      const currentProducts = products.value.filter(
        (prod) => prod.type === activeTab.value
      );
      analytics.value.total = currentProducts.length;
      analytics.value.newToday = currentProducts.filter(
        (prod) => prod.status === 'Pending'
      ).length;
      analytics.value.pending = currentProducts.filter(
        (prod) => prod.status === 'Pending'
      ).length;
    };

    onMounted(() => {
      updateAnalytics();
      initChart();
    });

    // Dummy bulk action functions
    const bulkApprove = () => {
      alert(`Approved ${selectedProducts.value.length} products`);
      selectedProducts.value = [];
    };

    const bulkDelete = () => {
      alert(`Deleted ${selectedProducts.value.length} products`);
      products.value = products.value.filter(
        (prod) => !selectedProducts.value.includes(prod.id)
      );
      selectedProducts.value = [];
      updateAnalytics();
    };

    const bulkEdit = () => {
      alert(`Bulk editing ${selectedProducts.value.length} products`);
    };

    // Edit modal actions
    const editProduct = (product) => {
      editProductData.value = { ...product };
      showEditModal.value = true;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
    };

    const saveEdit = () => {
      const index = products.value.findIndex(
        (p) => p.id === editProductData.value.id
      );
      if (index !== -1) {
        products.value[index] = { ...editProductData.value };
      }
      showEditModal.value = false;
      updateAnalytics();
    };

    const deleteProduct = (id) => {
      if (confirm('Are you sure you want to delete this product?')) {
        products.value = products.value.filter((prod) => prod.id !== id);
        updateAnalytics();
      }
    };

    // Utility: dynamic classes for the tab buttons
    const tabClass = (tabName) => {
      return [
        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none',
        activeTab.value === tabName
          ? 'border-lime-700 text-lime-700'
          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
      ];
    };

    // --- View Switching (List vs. Add Pages) ---
    const currentPage = ref('list'); // 'list', 'addcourse', or 'addbook'
    const goToAddCourse = () => {
      currentPage.value = 'addcourse';
    };
    const goToAddBook = () => {
      currentPage.value = 'addbook';
    };

    // Dummy function to initialize chart (if using Chart.js, etc.)
    const initChart = () => {
      // Example: initialize your chart here
      // const ctx = document.getElementById('analyticsChart').getContext('2d');
      // new Chart(ctx, { type: 'line', data: { ... }, options: { ... } });
    };

    return {
      activeTab,
      searchQuery,
      selectedProducts,
      filteredProducts,
      analytics,
      bulkApprove,
      bulkDelete,
      bulkEdit,
      editProduct,
      closeEditModal,
      saveEdit,
      editProductData,
      showEditModal,
      deleteProduct,
      tabClass,
      currentPage,
      goToAddCourse,
      goToAddBook,
    };
  },
};
</script>

<style scoped>
/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
