<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow py-4 px-6 flex flex-col md:flex-row items-start md:items-center justify-between">
      <div class="w-full md:w-auto">
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
        <p class="text-sm text-gray-600">
          Manage students and instructors with advanced search, filtering, and bulk actions.
        </p>
        <!-- Breadcrumb Navigation -->
        <nav class="text-sm mt-2">
          <ol class="list-reset flex text-gray-600">
            <li>
              <a href="#" class="hover:underline">Dashboard</a>
            </li>
            <li>
              <span class="mx-2">/</span>
            </li>
            <li>User Management</li>
          </ol>
        </nav>
      </div>
      <!-- Global Search Bar & Filter Toggle -->
      <div class="mt-4 md:mt-0 flex items-center space-x-2 w-full md:w-auto">
        <div class="relative flex-1 md:flex-none">
          <input
            v-model="searchQuery"
            @input="onSearch"
            type="text"
            placeholder="Search by name, email, or role…"
            class="w-full border border-gray-300 rounded-full py-2 px-4 pl-10 focus:outline-none focus:border-lime-700"
          />
          <svg
            class="w-5 h-5 absolute left-3 top-3 text-gray-400"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM10 16a6 6 0 100-12 6 6 0 000 12z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <button
          @click="toggleFilterPanel"
          class="bg-lime-700 text-white px-4 py-2 rounded-full hover:bg-lime-800 focus:outline-none"
        >
          Filters
        </button>
      </div>
    </header>

    <!-- Main & Sidebar Container -->
    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
      <!-- Main Content Area -->
      <main class="flex-1 p-6 overflow-auto">
        <!-- Bulk Actions Toolbar -->
        <div
          v-if="selectedUsers.length"
          class="mb-4 bg-white p-4 rounded shadow flex flex-col sm:flex-row items-start sm:items-center justify-between"
        >
          <div class="flex items-center space-x-2 mb-2 sm:mb-0">
            <span class="font-medium">{{ selectedUsers.length }} selected</span>
            <!-- Bulk Actions with Material Icons -->
            <button
              @click="bulkSuspend"
              class="bg-lime-600 text-white p-2 rounded hover:bg-lime-700"
              title="Suspend/Activate"
            >
              <span class="material-icons text-base">block</span>
            </button>
            <button
              @click="bulkDelete"
              class="bg-red-500 text-white p-2 rounded hover:bg-red-600"
              title="Delete"
            >
              <span class="material-icons text-base">delete</span>
            </button>
            <button
              @click="openBulkMessageModal"
              class="bg-lime-600 text-white p-2 rounded hover:bg-lime-700"
              title="Message"
            >
              <span class="material-icons text-base">chat</span>
            </button>
          </div>
          <button @click="clearSelection" class="text-gray-600 hover:underline">
            Clear selection
          </button>
        </div>

        <!-- Data Table Card -->
        <div class="bg-white rounded shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2">
                  <input type="checkbox" v-model="masterSelected" @change="toggleSelectAll" />
                </th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Email & Role</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Join Date</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Progress</th>
                <th class="px-4 py-2 text-center text-sm font-medium text-gray-600">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="user in paginatedUsers"
                :key="user.id"
                class="hover:bg-gray-50 cursor-pointer"
                @click="toggleRowExpansion(user)"
              >
                <td class="px-4 py-3">
                  <input type="checkbox" :value="user.id" v-model="selectedUsers" @click.stop />
                </td>
                <td class="px-4 py-3 flex items-center">
                  <img :src="user.avatar" alt="avatar" class="w-8 h-8 rounded-full mr-3" />
                  <span class="font-medium">{{ user.name }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm text-gray-700">{{ user.email }}</div>
                  <span
                    class="inline-block text-xs px-2 py-1 rounded-full"
                    :class="{
                      'bg-lime-100 text-lime-800': user.role === 'Instructor',
                      'bg-green-100 text-green-800': user.role === 'Student',
                      'bg-purple-100 text-purple-800': user.role === 'Admin'
                    }"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="text-xs font-semibold px-2 py-1 rounded"
                    :class="{
                      'bg-green-100 text-green-800': user.status === 'Active',
                      'bg-red-100 text-red-800': user.status === 'Suspended',
                      'bg-yellow-100 text-yellow-800': user.status === 'Pending'
                    }"
                  >
                    {{ user.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ user.joinDate }}</td>
                <td class="px-4 py-3">
                  <div v-if="user.role === 'Student'" class="w-full bg-gray-200 rounded-full h-2.5">
                    <div
                      class="h-2.5 rounded-full"
                      :class="user.progress >= 100 ? 'bg-green-500' : 'bg-lime-700'"
                      :style="{ width: user.progress + '%' }"
                    ></div>
                  </div>
                  <span v-else class="text-sm text-gray-600">-</span>
                </td>
                <td class="px-4 py-3 text-center space-x-2">
                  <!-- Edit Action -->
                  <button
                    @click.stop="openEditModal(user)"
                    class="text-lime-700 hover:text-lime-800"
                    title="Edit"
                  >
                    <span class="material-icons text-base">edit</span>
                  </button>
                  <!-- Suspend/Activate Action -->
                  <button
                    @click.stop="openSuspendModal(user)"
                    class="text-lime-700 hover:text-lime-800"
                    :title="user.status === 'Active' ? 'Suspend' : 'Activate'"
                  >
                    <span class="material-icons text-base">block</span>
                  </button>
                  <!-- Delete Action -->
                  <button
                    @click.stop="openDeleteModal(user)"
                    class="text-red-500 hover:text-red-600"
                    title="Delete"
                  >
                    <span class="material-icons text-base">delete</span>
                  </button>
                  <!-- Message Action -->
                  <button
                    @click.stop="openMessageModal(user)"
                    class="text-lime-700 hover:text-lime-800"
                    title="Message"
                  >
                    <span class="material-icons text-base">chat</span>
                  </button>
                </td>
              </tr>
              <!-- Expandable Row for More Details -->
              <tr v-if="expandedUser && expandedUser.id === user.id">
                <td colspan="7" class="bg-gray-50 p-4">
                  <div>
                    <strong>Recent Activity:</strong>
                    <ul class="list-disc pl-5 mt-2 text-sm text-gray-700">
                      <li v-for="(activity, index) in user.activity" :key="index">
                        {{ activity }}
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Controls -->
          <div class="p-4 bg-gray-50 flex flex-col sm:flex-row items-center justify-between">
            <!-- Rows per page selector -->
            <div class="flex items-center mb-2 sm:mb-0">
              <span class="text-sm text-gray-600 mr-2">Rows per page:</span>
              <select v-model.number="rowsPerPage" class="border-gray-300 rounded focus:outline-none">
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
              </select>
            </div>
            <!-- Page Navigation -->
            <div class="flex items-center space-x-2">
              <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-3 py-1 border rounded disabled:opacity-50"
              >
                Prev
              </button>
              <span class="text-sm text-gray-600">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 border rounded disabled:opacity-50"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </main>

      <!-- Sidebar / Activity Feed -->
      <aside class="w-full md:w-80 bg-white border-t md:border-l md:border-t-0 overflow-auto">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4">Activity Feed</h2>
          <!-- Activity Filter -->
          <div class="mb-4">
            <select v-model="activityFilter" class="w-full border-gray-300 rounded focus:outline-none">
              <option value="">All Activities</option>
              <option value="login">Logins</option>
              <option value="update">Profile Updates</option>
              <option value="enroll">Course Enrollments</option>
            </select>
          </div>
          <ul class="space-y-4">
            <li
              v-for="(activity, index) in filteredActivityFeed"
              :key="index"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0">
                <span class="material-icons text-lime-700">info</span>
              </div>
              <div>
                <p class="text-sm text-gray-800">
                  <strong>{{ activity.user }}</strong> {{ activity.action }}
                </p>
                <p class="text-xs text-gray-500">{{ activity.timestamp }}</p>
              </div>
            </li>
          </ul>
        </div>
      </aside>
    </div>

    <!-- Filter Panel (Collapsible) -->
    <transition name="slide">
      <div
        v-if="showFilterPanel"
        class="fixed inset-0 bg-black bg-opacity-30 flex justify-end z-50"
        @click.self="toggleFilterPanel"
      >
        <div class="bg-white w-80 h-full p-6 overflow-auto">
          <h2 class="text-xl font-bold mb-4">Advanced Filters</h2>
          <!-- Role Filter -->
          <div class="mb-4">
            <label class="block text-gray-700 mb-1">Role</label>
            <select v-model="filters.role" class="w-full border-gray-300 rounded focus:outline-none">
              <option value="">All</option>
              <option value="Student">Student</option>
              <option value="Instructor">Instructor</option>
              <option value="Admin">Admin</option>
            </select>
          </div>
          <!-- Status Filter -->
          <div class="mb-4">
            <label class="block text-gray-700 mb-1">Status</label>
            <select v-model="filters.status" class="w-full border-gray-300 rounded focus:outline-none">
              <option value="">All</option>
              <option value="Active">Active</option>
              <option value="Suspended">Suspended</option>
              <option value="Pending">Pending</option>
            </select>
          </div>
          <!-- Join Date Range Picker (dummy) -->
          <div class="mb-4">
            <label class="block text-gray-700 mb-1">Join Date Range</label>
            <input
              type="date"
              v-model="filters.joinDateFrom"
              class="w-full border-gray-300 rounded focus:outline-none mb-2"
            />
            <input
              type="date"
              v-model="filters.joinDateTo"
              class="w-full border-gray-300 rounded focus:outline-none"
            />
          </div>
          <!-- Progress Filter -->
          <div class="mb-4">
            <label class="block text-gray-700 mb-1">Progress (for Students)</label>
            <input
              type="range"
              min="0"
              max="100"
              v-model="filters.progress"
              class="w-full"
            />
            <div class="text-sm text-gray-600 text-right">{{ filters.progress }}%</div>
          </div>
          <!-- Reset Filters Button -->
          <button @click="resetFilters" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">
            Reset Filters
          </button>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <transition name="fade">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
      >
        <div class="bg-white rounded shadow-lg w-96 p-6">
          <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
          <p class="mb-6">
            Are you sure you want to delete <strong>{{ modalUser.name }}</strong>?
          </p>
          <div class="flex justify-end space-x-2">
            <button
              @click="closeModal"
              class="px-4 py-2 border rounded hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Message Modal (for single user or bulk messaging) -->
    <transition name="fade">
      <div
        v-if="showMessageModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
      >
        <div class="bg-white rounded shadow-lg w-96 p-6">
          <h3 class="text-xl font-bold mb-4">
            {{ modalUser ? 'Message ' + modalUser.name : 'New Announcement' }}
          </h3>
          <textarea
            v-model="messageText"
            placeholder="Type your message here..."
            class="w-full border border-gray-300 rounded p-2 mb-4 focus:outline-none"
            rows="4"
          ></textarea>
          <div class="flex justify-end space-x-2">
            <button
              @click="closeModal"
              class="px-4 py-2 border rounded hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              @click="sendMessage"
              class="px-4 py-2 bg-lime-700 text-white rounded hover:bg-lime-800"
            >
              Send
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Dummy user data
const users = ref([
  {
    id: 1,
    name: 'Alice Johnson',
    email: 'alice@example.com',
    role: 'Student',
    status: 'Active',
    joinDate: '2023-01-15',
    progress: 75,
    avatar: 'https://i.pravatar.cc/40?img=1',
    activity: ['Logged in 2 hours ago', 'Completed Quiz 1']
  },
  {
    id: 2,
    name: 'Bob Smith',
    email: 'bob@example.com',
    role: 'Instructor',
    status: 'Pending',
    joinDate: '2023-02-10',
    progress: 0,
    avatar: 'https://i.pravatar.cc/40?img=2',
    activity: ['Requested instructor role', 'Updated profile']
  },
  {
    id: 3,
    name: 'Carol Danvers',
    email: 'carol@example.com',
    role: 'Admin',
    status: 'Active',
    joinDate: '2022-12-05',
    progress: 0,
    avatar: 'https://i.pravatar.cc/40?img=3',
    activity: ['Logged in 30 minutes ago', 'Approved a user']
  }
  // ... more users
])

// Dummy activity feed data
const activityFeed = ref([
  { user: 'Alice Johnson', action: 'completed Quiz 1', timestamp: '2 hours ago', type: 'update' },
  { user: 'Bob Smith', action: 'requested instructor role', timestamp: '1 hour ago', type: 'update' },
  { user: 'Carol Danvers', action: 'logged in', timestamp: '30 minutes ago', type: 'login' }
  // ... more activities
])

// Search and filter states
const searchQuery = ref('')
const filters = ref({
  role: '',
  status: '',
  joinDateFrom: '',
  joinDateTo: '',
  progress: 0
})
const showFilterPanel = ref(false)

// Bulk selection states
const selectedUsers = ref([])
const masterSelected = ref(false)

// Modal and messaging states
const showDeleteModal = ref(false)
const showMessageModal = ref(false)
const modalUser = ref(null)
const messageText = ref('')

// Row expansion state
const expandedUser = ref(null)

// Activity feed filter
const activityFilter = ref('')

// Pagination states
const currentPage = ref(1)
const rowsPerPage = ref(10)

// Computed: filtered users based on search and filters
const filteredUsers = computed(() => {
  return users.value.filter((user) => {
    // Search query
    const matchesSearch =
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.role.toLowerCase().includes(searchQuery.value.toLowerCase())

    // Role filter
    const matchesRole = filters.value.role ? user.role === filters.value.role : true

    // Status filter
    const matchesStatus = filters.value.status ? user.status === filters.value.status : true

    // Join date range filter (simple string comparison; in production, use Date objects)
    const matchesJoinDate =
      (!filters.value.joinDateFrom || user.joinDate >= filters.value.joinDateFrom) &&
      (!filters.value.joinDateTo || user.joinDate <= filters.value.joinDateTo)

    // Progress filter (for students only)
    const matchesProgress =
      user.role !== 'Student' || user.progress >= filters.value.progress

    return matchesSearch && matchesRole && matchesStatus && matchesJoinDate && matchesProgress
  })
})

// Computed: paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value
  const end = start + rowsPerPage.value
  return filteredUsers.value.slice(start, end)
})

// Computed: total pages based on filtered users
const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / rowsPerPage.value) || 1
})

// Computed: filtered activity feed
const filteredActivityFeed = computed(() => {
  if (!activityFilter.value) return activityFeed.value
  return activityFeed.value.filter(a => a.type === activityFilter.value)
})

// Methods
function onSearch() {
  currentPage.value = 1
}

function toggleFilterPanel() {
  showFilterPanel.value = !showFilterPanel.value
}

function resetFilters() {
  filters.value = {
    role: '',
    status: '',
    joinDateFrom: '',
    joinDateTo: '',
    progress: 0
  }
  currentPage.value = 1
}

function toggleSelectAll() {
  if (masterSelected.value) {
    selectedUsers.value = filteredUsers.value.map(user => user.id)
  } else {
    selectedUsers.value = []
  }
}

function clearSelection() {
  selectedUsers.value = []
  masterSelected.value = false
}

function openEditModal(user) {
  alert(`Edit ${user.name}`)
}

function openSuspendModal(user) {
  alert(`Toggle suspend for ${user.name}`)
}

function openDeleteModal(user) {
  modalUser.value = user
  showDeleteModal.value = true
}

function confirmDelete() {
  users.value = users.value.filter(u => u.id !== modalUser.value.id)
  showDeleteModal.value = false
  modalUser.value = null
}

function openMessageModal(user) {
  modalUser.value = user
  showMessageModal.value = true
}

function openBulkMessageModal() {
  modalUser.value = null // Indicates bulk messaging
  showMessageModal.value = true
}

function sendMessage() {
  if (modalUser.value) {
    alert(`Message sent to ${modalUser.value.name}: ${messageText.value}`)
  } else {
    alert(`Bulk message sent to ${selectedUsers.value.length} users: ${messageText.value}`)
  }
  messageText.value = ''
  showMessageModal.value = false
}

function closeModal() {
  showDeleteModal.value = false
  showMessageModal.value = false
  modalUser.value = null
}

watch(selectedUsers, () => {
  masterSelected.value = filteredUsers.value.every(user => selectedUsers.value.includes(user.id))
})

function toggleRowExpansion(user) {
  expandedUser.value = expandedUser.value && expandedUser.value.id === user.id ? null : user
}

// Pagination navigation
function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

watch(rowsPerPage, () => {
  currentPage.value = 1
})
</script>

<style scoped>
/* Transitions for modal and filter panel */
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
