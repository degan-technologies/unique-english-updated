<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

// --- Data & States ---

// Users will be fetched from the backend.
const users = ref([])

// Activity feed remains as dummy data (or you can fetch this from an endpoint)
const activityFeed = ref([
  { user: 'Alice Johnson', action: 'completed Quiz 1', timestamp: '2 hours ago', type: 'update' },
  { user: 'Bob Smith', action: 'requested instructor role', timestamp: '1 hour ago', type: 'update' },
  { user: 'Carol Danvers', action: 'logged in', timestamp: '30 minutes ago', type: 'login' }
])

// Search and filter states
const searchQuery = ref('')


// Bulk selection states
const selectedUsers = ref([])
const masterSelected = ref(false)

// Modal and messaging states
const showDeleteModal = ref(false)
const showMessageModal = ref(false)
const modalUser = ref(null)
const messageText = ref('')
// Activity Log Modal states
const showActivityLogModal = ref(false)
const activityLogDetails = ref([])  // Array to hold fetched log details
const activityLogUser = ref({})     // The user for whom we're showing the log

// Add User Modal state and new user form fields
const showAddUserModal = ref(false)
const newUser = ref({
  email: '',
  first_name: '',
  middle_name: '',
  role: 'INSTRUCTOR_ROLE', // Default role is now 'Instructor'
});


// Row expansion state
const expandedUser = ref(null)

// Activity feed filter
const activityFilter = ref('')

// Pagination states
const currentPage = ref(1)
const rowsPerPage = ref(10)

// --- Fetch Users from Backend ---
async function fetchUsers() {
  try {
    const response = await axios.get('/api/users', {
      params: {
        search: searchQuery.value,
        role: filters.value.role,
        status: filters.value.status,
        joinDateFrom: filters.value.joinDateFrom,
        joinDateTo: filters.value.joinDateTo,
        progress: filters.value.progress,
        
      }
    });
    users.value = response.data.data;
    console.log('Users fetched:', users.value);
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}


// Fetch users when the component mounts
onMounted(() => {
  fetchUsers()
})

// --- Computed Properties ---

// Filtered users based on search and filters
const filteredUsers = computed(() => {
  return users.value.filter((user) => {
    // Search query
    const matchesSearch =
  (`${user.first_name} ${user.middle_name || ''}`)
    .toLowerCase()
    .includes(searchQuery.value.toLowerCase()) ||
  user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
  user.role.toLowerCase().includes(searchQuery.value.toLowerCase());

    // Role filter
    const matchesRole = filters.value.role ? user.role === filters.value.role : true

    // Status filter
    const matchesStatus = filters.value.status ? user.status === filters.value.status : true

    // Join date range filter (using string comparison; for production use Date objects)
    const matchesJoinDate =
      (!filters.value.joinDateFrom || user.joinDate >= filters.value.joinDateFrom) &&
      (!filters.value.joinDateTo || user.joinDate <= filters.value.joinDateTo)

    // Progress filter (for students only)
    const matchesProgress =
      user.role !== 'Student' || user.progress >= filters.value.progress

    return matchesSearch && matchesRole && matchesStatus && matchesJoinDate && matchesProgress
  })
})

// Paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value
  return filteredUsers.value.slice(start, start + rowsPerPage.value)
})

// Total pages for pagination
const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / rowsPerPage.value) || 1
})

// Filtered activity feed
const filteredActivityFeed = computed(() => {
  if (!activityFilter.value) return activityFeed.value
  return activityFeed.value.filter(a => a.type === activityFilter.value)
})

// --- Methods ---

function onSearch() {
  currentPage.value = 1
}

// State
const showFilterModal = ref(false)
const filters = ref({
  role: '',
  status: '',
  joinDateFrom: '',
  joinDateTo: '',
  progress: 0
})

// Methods
const toggleFilterModal = () => {
  showFilterModal.value = !showFilterModal.value
}

const resetFilters = () => {
  filters.value = {
    role: '',
    status: '',
    joinDateFrom: '',
    joinDateTo: '',
    progress: 0
  }
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



function openDeleteModal(user) {
  modalUser.value = user
  showDeleteModal.value = true
}

// --- Delete Single User ---
async function confirmDelete() {
  try {
    // Call backend endpoint to delete the user (DELETE /api/delete-instructor/{id})
    const response = await axios.delete(`/api/delete-instructor/${modalUser.value.id}`)
    alert(response.data.message)
    console.log('Delete response:', response.data)
    fetchUsers() // Refresh list
    closeModal()
  } catch (error) {
    console.error('Error deleting user:', error)
    alert('Error deleting user')
  }
}

function openMessageModal(user) {
  modalUser.value = user
  showMessageModal.value = true
}

function openBulkMessageModal() {
  modalUser.value = null // Indicates bulk messaging
  showMessageModal.value = true
}

// Updated sendMessage method to integrate SMS endpoints
async function sendMessage() {
  if (messageText.value.trim() === '') {
    alert("Message cannot be empty.");
    return;
  }
  try {
    if (modalUser.value) {
      // Sending a single SMS using the /api/send-sms endpoint
      const payload = { 
        user_id: modalUser.value.id, 
        message: messageText.value 
      };
      const response = await axios.post('/api/send-sms', payload);
      alert(`Message sent to ${modalUser.value.first_name} ${modalUser.value.middle_name}: ${messageText.value}`);
      console.log('Single SMS response:', response.data);
    } else {
      // Sending bulk SMS using the /api/send-bulk-sms endpoint
      if (selectedUsers.value.length === 0) {
        alert("No users selected for bulk messaging.");
        return;
      }
      const payload = { 
        user_ids: selectedUsers.value, 
        message: messageText.value 
      };
      const response = await axios.post('/api/send-bulk-sms', payload);
      alert(`Bulk message sent to ${selectedUsers.value.length} users.`);
      console.log('Bulk SMS response:', response.data);
    }
  } catch (error) {
    console.error("Error sending SMS:", error);
    alert("Error sending SMS. Please try again later.");
  }
  messageText.value = '';
  showMessageModal.value = false;
}


function closeModal() {
  showDeleteModal.value = false
  showMessageModal.value = false
  modalUser.value = null
}

// Open/close Add User Modal
function openAddUserModal() {
  showAddUserModal.value = true
}

function closeAddUserModal() {
  showAddUserModal.value = false
  newUser.value = { email: '', first_name: '', middle_name: '', role: 'INSTRUCTOR_ROLE' }
}

async function submitAddUser() {
  try {
    // Send the new user data to the server
    const response = await axios.post('/api/add-instructor', newUser.value);

    // Display success message
    alert(response.data.message);

    // Refresh the user list
    fetchUsers();

    // Close the modal
    closeAddUserModal();
  } catch (error) {
    // Handle errors gracefully
    alert(
      (error.response && error.response.data.message) ||
        'Error adding user'
    );
  }
}

// --- Bulk Actions ---

async function bulkDelete() {
  if (selectedUsers.value.length === 0) return;

  const confirmAction = confirm(`Are you sure you want to delete ${selectedUsers.value.length} selected users?`);
  if (!confirmAction) return;

  try {
    // Call backend endpoint for bulk delete (POST /api/users/bulk/delete)
    const response = await axios.post('/api/users/bulk/delete', { ids: selectedUsers.value });
    alert(response.data.message);
    console.log('Bulk delete response:', response.data);
    fetchUsers(); 
    clearSelection(); 
  } catch (error) {
    console.error('Error in bulk delete:', error);
    alert('Error in bulk delete action');
  }
}



// temp_password column visibility
const showTempPasswordColumn = computed(() => {
  return users.value.some(user => user.role === 'INSTRUCTOR_ROLE');
});



// Toggle expandable row for additional user details
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

watch(selectedUsers, () => {
  masterSelected.value = filteredUsers.value.every(user => selectedUsers.value.includes(user.id))
})

watch(rowsPerPage, () => {
  currentPage.value = 1
})

// --- Activity Log Modal ---
async function openActivityLogModal(user) {
  activityLogUser.value = user
  try {
    // Optionally, fetch the activity log from your backend:
    const response = await axios.get(`/api/users/${user.id}/activity-log`)
    activityLogDetails.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching activity log:', error)
    // Fallback: use any activity details already present on the user object
    activityLogDetails.value = user.activity || []
  }
  showActivityLogModal.value = true
}

function closeActivityLogModal() {
  showActivityLogModal.value = false
  activityLogUser.value = {}
  activityLogDetails.value = []
}


</script>


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
          <li><span class="mx-2">/</span></li>
          <li>User Management</li>
        </ol>
      </nav>
    </div>

    <!-- Search, Filter, and Add User -->
    <div class="mt-4 md:mt-0 flex items-center space-x-2 w-full md:w-auto">
      <!-- Search Input -->
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

      <!-- Filter Icon Button -->
      <button
        @click="toggleFilterModal"
        class="bg-lime-700 text-white p-2 rounded-full hover:bg-lime-800 focus:outline-none"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M3 10h18M3 16h18" />
        </svg>
      </button>

      <!-- Add User Button -->
      <button
        @click="openAddUserModal"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none"
      >
        Add User
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
  <table v-if="users.length > 0" class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2">
          <input type="checkbox" v-model="masterSelected" @change="toggleSelectAll" />
        </th>
        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User</th>
        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Email &amp; Role</th>
        <th v-if="showTempPasswordColumn" class="px-4 py-2 text-left text-sm font-medium text-gray-600">
          Temp Password
        </th>
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
          <img :src="user.profile || './images/Avator.jpg'" alt="avatar" class="w-8 h-8 rounded-full mr-3" />
          <span class="font-medium">{{ user.first_name }} {{ user.middle_name }}</span>
        </td>
        <td class="px-4 py-3">
          <div class="text-sm text-gray-700">{{ user.email }}</div>
          <span
            class="inline-block text-xs px-2 py-1 rounded-full"
            :class="{
              'bg-lime-100 text-lime-800': user.role === 'INSTRUCTOR_ROLE',
              'bg-green-100 text-green-800': user.role === 'STUDENT_ROLE',
              'bg-purple-100 text-purple-800': user.role === 'SYSTEM_ADMIN_ROLE'
            }"
          >
            {{ user.role }}
          </span>
        </td>
        <td v-if="showTempPasswordColumn" class="px-4 py-3 text-sm text-gray-600">
          {{ user.role === 'INSTRUCTOR_ROLE' ? (user.temp_password ? user.temp_password : '-') : '-' }}
        </td>
        <td class="px-4 py-3 text-sm text-gray-600">{{ user.joinDate }}</td>
        <td class="px-4 py-3">
          <div v-if="user.role === 'INSTRUCTOR_ROLE'" class="w-full bg-gray-200 rounded-full h-2.5">
            <div
              class="h-2.5 rounded-full"
              :class="user.progress >= 100 ? 'bg-green-500' : 'bg-lime-700'"
              :style="{ width: user.progress + '%' }"
            ></div>
          </div>
          <span v-else class="text-sm text-gray-600">-</span>
        </td>
        <td class="px-4 py-3 text-center space-x-2">
          <!-- Only Delete, Message and Activity Log buttons remain -->
          <button
            @click.stop="openDeleteModal(user)"
            class="text-red-500 hover:text-red-600"
            title="Delete"
          >
            <span class="material-icons text-base">delete</span>
          </button>
          <button
            @click.stop="openMessageModal(user)"
            class="text-lime-700 hover:text-lime-800"
            title="Message"
          >
            <span class="material-icons text-base">chat</span>
          </button>
          <button
            @click.stop="openActivityLogModal(user)"
            class="text-blue-500 hover:text-blue-700"
            title="Activity Log"
          >
            <span class="material-icons text-base">history</span>
          </button>
        </td>
      </tr>
      <!-- Expandable Row for More Details (if needed) -->
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
  <div class="p-4 bg-gray-200 flex flex-col sm:flex-row items-center justify-between">
    <div class="flex items-center mb-2 sm:mb-0">
      <span class="text-sm text-gray-600 mr-2">Rows per page:</span>
      <select v-model.number="rowsPerPage" class="border-gray-300 text-gray-600 bg-gray-800 rounded focus:outline-none">
        <option :value="5">5</option>
        <option :value="10">10</option>
        <option :value="20">20</option>
        <option :value="50">50</option>
      </select>
    </div>
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

    <!-- Filter Modal -->
  <transition name="fade">
    <div v-if="showFilterModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white w-96 p-6 rounded-lg shadow-lg relative">
        <h2 class="text-xl font-bold mb-4">Advanced Filters</h2>

        <!-- Role Filter -->
        <div class="mb-4">
          <label class="block text-gray-700 mb-1">Role</label>
          <select v-model="filters.role" class="w-full border-gray-300 rounded focus:outline-none">
            <option value="">All</option>
            <option value="STUDENT_ROLE">Student</option>
            <option value="INSTRUCTOR_ROLE">Instructor</option>
            <option value="SYSTEM_ADMIN_ROLE">Admin</option>
          </select>
        </div>

        
        <!-- Join Date Range -->
        <div class="mb-4">
          <label class="block text-gray-700 mb-1">Join Date Range</label>
          <input type="date" v-model="filters.joinDateFrom" class="w-full border-gray-300 rounded focus:outline-none mb-2" />
          <input type="date" v-model="filters.joinDateTo" class="w-full border-gray-300 rounded focus:outline-none" />
        </div>

        <!-- Progress Filter -->
        <div class="mb-4">
          <label class="block text-gray-700 mb-1">Progress (for Students)</label>
          <input type="range" min="0" max="100" v-model="filters.progress" class="w-full" />
          <div class="text-sm text-gray-600 text-right">{{ filters.progress }}%</div>
        </div>

        <!-- Reset Filters Button -->
        <button @click="resetFilters" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">
          Reset Filters
        </button>

        <!-- Close Button -->
        <button
          @click="toggleFilterModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
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

    <!-- Add User Modal -->
<transition name="fade">
  <div
    v-if="showAddUserModal"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm z-50"
  >
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8 transform transition-all duration-300 ease-in-out scale-100">
      <div class="flex justify-between items-center border-b pb-3 mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Add New User</h3>
        <button @click="closeAddUserModal" class="text-gray-400 hover:text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <form @submit.prevent="submitAddUser">
        <div class="mb-4">
          <label class="block text-gray-700 font-semibold mb-2" for="email">Email</label>
          <input
            id="email"
            v-model="newUser.email"
            type="email"
            placeholder="example@degan.com"
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
            required
          />
        </div>
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-2" for="first_name">First Name</label>
            <input
              id="first_name"
              v-model="newUser.first_name"
              type="text"
              placeholder="John"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
              required
            />
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-2" for="middle_name">Middle Name</label>
            <input
              id="middle_name"
              v-model="newUser.middle_name"
              type="text"
              placeholder="Doe"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
            />
          </div>
        </div>
        <div class="mb-6">
          <p class="text-gray-600 text-sm">
            A random password will be generated for this account. This account will be created as an <strong>Instructor</strong> by default.
            The generated password will be displayed in the user table until the instructor updates it.
          </p>
        </div>
        <div class="flex justify-end space-x-4">
          <button
            type="button"
            @click="closeAddUserModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors duration-200"
          >
            Add User
          </button>
        </div>
      </form>
    </div>
  </div>
</transition>

<transition name="fade">
  <div
    v-if="showActivityLogModal"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
  >
    <div class="bg-white rounded shadow-lg w-96 p-6">
      <h3 class="text-xl font-bold mb-4">
        Activity Log for {{ activityLogUser.first_name }} {{ activityLogUser.middle_name }}
      </h3>
      <div v-if="activityLogDetails.length">
        <ul class="list-disc pl-5 mt-2 text-sm text-gray-700">
          <li v-for="(log, index) in activityLogDetails" :key="index">
            {{ log }}
          </li>
        </ul>
      </div>
      <div v-else class="text-sm text-gray-600">
        No activity found.
      </div>
      <div class="mt-4 flex justify-end">
        <button
          @click="closeActivityLogModal"
          class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</transition>



  </div>
</template>



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
