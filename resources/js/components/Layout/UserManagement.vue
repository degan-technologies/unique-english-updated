<script setup>
import axios from "axios";
import { computed, onMounted, ref, watch } from "vue";
import Popper from "vue3-popper";

// Tab management
const activeTab = ref("students");

// Separate data for instructors and students
const instructors = ref([]);
const students = ref([]);
const selectedInstructors = ref([]);
const selectedStudents = ref([]);

// Pagination for both tabs
const instructorPagination = ref({});
const studentPagination = ref({});
const currentInstructorPage = ref(1);
const currentStudentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];

// Search and filters
const instructorSearchQuery = ref("");
const studentSearchQuery = ref("");

// Modal states
const showMessageModal = ref(false);
const modalUser = ref(null);
const messageText = ref("");
const showAddUserModal = ref(false);
const showActivityLogModal = ref(false);
const activityLogDetails = ref([]);
const activityLogUser = ref({});

// Confirmation dialog state
const showConfirmationDialog = ref(false);
const confirmationAction = ref(null);
const confirmationMessage = ref("");
const confirmationTitle = ref("");
const isProcessing = ref(false);

// Forms
const newUser = ref({
    email: "",
    first_name: "",
    middle_name: "",
    role: "INSTRUCTOR_ROLE",
});

// Computed properties
const currentUsers = computed(() => {
    return activeTab.value === "instructors"
        ? instructors.value
        : students.value;
});

const selectedUsers = computed(() => {
    return activeTab.value === "instructors"
        ? selectedInstructors.value
        : selectedStudents.value;
});

const currentPagination = computed(() => {
    return activeTab.value === "instructors"
        ? instructorPagination.value
        : studentPagination.value;
});

const currentPage = computed(() => {
    return activeTab.value === "instructors"
        ? currentInstructorPage.value
        : currentStudentPage.value;
});

const searchQuery = computed({
    get() {
        return activeTab.value === "instructors"
            ? instructorSearchQuery.value
            : studentSearchQuery.value;
    },
    set(value) {
        if (activeTab.value === "instructors") {
            instructorSearchQuery.value = value;
        } else {
            studentSearchQuery.value = value;
        }
    },
});

const showTempPasswordColumn = computed(() => {
    return activeTab.value === "instructors";
});

// Methods
async function fetchInstructors(page = 1) {
    try {
        const res = await axios.get(`/api/instructors?page=${page}`, {
            params: {
                search: instructorSearchQuery.value,
                rowsPerPageOptions: rowsPerPage.value,
            },
        });
        instructors.value = res.data.data;
        instructorPagination.value = res.data.pagination;
        currentInstructorPage.value = res.data.pagination.current_page;
    } catch (error) {
        console.error("Error fetching instructors:", error);
        showToast("Failed to fetch instructors", "error");
    }
}

async function fetchStudents(page = 1) {
    try {
        const res = await axios.get(`/api/students?page=${page}`, {
            params: {
                search: studentSearchQuery.value,
                rowsPerPageOptions: rowsPerPage.value,
            },
        });
        students.value = res.data.data;
        studentPagination.value = res.data.pagination;
        currentStudentPage.value = res.data.pagination.current_page;
    } catch (error) {
        console.error("Error fetching students:", error);
        showToast("Failed to fetch students", "error");
    }
}

function switchTab(tab) {
    activeTab.value = tab;
    if (tab === "instructors" && instructors.value.length === 0) {
        fetchInstructors();
    } else if (tab === "students" && students.value.length === 0) {
        fetchStudents();
    }
}

onMounted(() => {
    fetchStudents(); // Load students by default
});

function toggleMark(id) {
    const selectedArray =
        activeTab.value === "instructors"
            ? selectedInstructors
            : selectedStudents;

    if (!selectedArray.value.includes(id)) {
        selectedArray.value.push(id);
    } else {
        selectedArray.value = selectedArray.value.filter(
            (userId) => userId !== id
        );
    }
}

function toggleMarkAll() {
    const currentUsersList =
        activeTab.value === "instructors" ? instructors.value : students.value;
    const selectedArray =
        activeTab.value === "instructors"
            ? selectedInstructors
            : selectedStudents;

    // Add all current page user IDs to selected array if not already present
    currentUsersList.forEach((user) => {
        if (!selectedArray.value.includes(user.id)) {
            selectedArray.value.push(user.id);
        }
    });
}

function toggleUnMarkAll() {
    if (activeTab.value === "instructors") {
        selectedInstructors.value = [];
    } else {
        selectedStudents.value = [];
    }
}

function clearSelection() {
    if (activeTab.value === "instructors") {
        selectedInstructors.value = [];
    } else {
        selectedStudents.value = [];
    }
}

function openAddUserModal() {
    if (activeTab.value === "instructors") {
        newUser.value.role = "INSTRUCTOR_ROLE";
    } else {
        newUser.value.role = "STUDENT_ROLE";
    }
    showAddUserModal.value = true;
}

async function submitAddUser() {
    isProcessing.value = true;
    try {
        const endpoint =
            activeTab.value === "instructors"
                ? "/api/add-instructor"
                : "/api/add-student";
        const response = await axios.post(endpoint, newUser.value);
        showToast("User added successfully", "success");

        if (activeTab.value === "instructors") {
            fetchInstructors();
        } else {
            fetchStudents();
        }
        closeAddUserModal();
    } catch (error) {
        const errorMsg =
            (error.response && error.response.data.message) ||
            "Error adding user";
        showToast(errorMsg, "error");
    } finally {
        isProcessing.value = false;
    }
}

function closeAddUserModal() {
    showAddUserModal.value = false;
    newUser.value = {
        email: "",
        first_name: "",
        middle_name: "",
        role:
            activeTab.value === "instructors"
                ? "INSTRUCTOR_ROLE"
                : "STUDENT_ROLE",
    };
}

// Message modal functions
function openMessageModal(user) {
    modalUser.value = user;
    showMessageModal.value = true;
}

function openBulkMessageModal() {
    if (selectedUsers.value.length === 0) {
        showToast("Please select at least one user", "warning");
        return;
    }
    modalUser.value = null;
    showMessageModal.value = true;
}

function closeModal() {
    showMessageModal.value = false;
    modalUser.value = null;
    messageText.value = "";
}

async function sendMessage() {
    if (messageText.value.trim() === "") {
        showToast("Message cannot be empty", "warning");
        return;
    }

    isProcessing.value = true;
    try {
        if (modalUser.value) {
            const payload = {
                user_id: modalUser.value.id,
                message: messageText.value,
            };
            await axios.post("/api/send-sms", payload);
            showToast("Message sent successfully", "success");
        } else {
            const payload = {
                user_ids: selectedUsers.value,
                message: messageText.value,
            };
            await axios.post("/api/send-bulk-sms", payload);
            showToast("Bulk message sent successfully", "success");
        }
        messageText.value = "";
        showMessageModal.value = false;
    } catch (error) {
        showToast("Failed to send message", "error");
    } finally {
        isProcessing.value = false;
    }
}

// Ban/Unban functions
function openBanModal(user) {
    const isBanned = user.status === "banned";
    confirmationTitle.value = isBanned ? "Confirm Unban" : "Confirm Ban";
    confirmationMessage.value = isBanned
        ? `Are you sure you want to unban ${user.first_name} ${user.middle_name}?`
        : `Are you sure you want to ban ${user.first_name} ${user.middle_name}? This will restrict their access.`;
    confirmationAction.value = () => toggleBanStatus(user.id);
    showConfirmationDialog.value = true;
}

async function toggleBanStatus(userId) {
    isProcessing.value = true;
    try {
        await axios.delete(`/api/delete-instructor/${userId}`);
        showToast("User status updated successfully", "success");

        // Refresh current list
        if (activeTab.value === "instructors") {
            fetchInstructors(currentInstructorPage.value);
        } else {
            fetchStudents(currentStudentPage.value);
        }
    } catch (error) {
        showToast("Failed to update user status", "error");
    } finally {
        isProcessing.value = false;
        showConfirmationDialog.value = false;
    }
}

// Bulk delete functions
function showBulkDeleteConfirmation() {
    if (selectedUsers.value.length === 0) {
        showToast("Please select at least one user", "warning");
        return;
    }

    confirmationTitle.value = "Confirm Bulk Delete";
    confirmationMessage.value = `Are you sure you want to delete ${selectedUsers.value.length} selected users? This action cannot be undone.`;
    confirmationAction.value = bulkDelete;
    showConfirmationDialog.value = true;
}

async function bulkDelete() {
    isProcessing.value = true;
    try {
        const response = await axios.post("/api/users/bulk/delete", {
            ids: selectedUsers.value,
        });
        showToast(
            `${selectedUsers.value.length} users deleted successfully`,
            "success"
        );

        if (activeTab.value === "instructors") {
            fetchInstructors();
        } else {
            fetchStudents();
        }
        clearSelection();
    } catch (error) {
        showToast("Failed to delete users", "error");
    } finally {
        isProcessing.value = false;
        showConfirmationDialog.value = false;
    }
}

// Activity log functions
async function openActivityLogModal(user) {
    activityLogUser.value = user;
    try {
        const response = await axios.get(`/api/activity-logs/`, {
            params: {
                id: user.id,
            },
        });
        activityLogDetails.value = response.data.data || [];
    } catch (error) {
        activityLogDetails.value = user.activity || [];
    }
    showActivityLogModal.value = true;
}

function closeActivityLogModal() {
    showActivityLogModal.value = false;
    activityLogUser.value = {};
    activityLogDetails.value = [];
}

// Pagination methods
function onNextPage() {
    if (activeTab.value === "instructors") {
        if (
            currentInstructorPage.value < instructorPagination.value.last_page
        ) {
            fetchInstructors(currentInstructorPage.value + 1);
        }
    } else {
        if (currentStudentPage.value < studentPagination.value.last_page) {
            fetchStudents(currentStudentPage.value + 1);
        }
    }
}

function onPreviousPage() {
    if (activeTab.value === "instructors") {
        if (currentInstructorPage.value > 1) {
            fetchInstructors(currentInstructorPage.value - 1);
        }
    } else {
        if (currentStudentPage.value > 1) {
            fetchStudents(currentStudentPage.value - 1);
        }
    }
}

function userPerPage(amount) {
    rowsPerPage.value = amount;
    if (activeTab.value === "instructors") {
        fetchInstructors(1);
    } else {
        fetchStudents(1);
    }
}

// Toast notification function
function showToast(message, type = "info") {
    console.log(`${type.toUpperCase()}: ${message}`);
}

// Watch for search changes with debounce
let searchTimeout = null;
watch(instructorSearchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (activeTab.value === "instructors") {
            fetchInstructors(1);
        }
    }, 500);
});

watch(studentSearchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (activeTab.value === "students") {
            fetchStudents(1);
        }
    }, 500);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow py-4 px-6">
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <nav class="text-sm mt-2">
                <ol class="list-reset flex text-gray-600">
                    <li><a href="#" class="hover:underline">Dashboard</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>User Management</li>
                </ol>
            </nav>
        </header>

        <!-- Tabs -->
        <div class="bg-white border-b">
            <div class="px-6">
                <div class="flex space-x-8">
                    <button
                        @click="switchTab('students')"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                            activeTab === 'students'
                                ? 'border-lime-500 text-lime-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        Students ({{ studentPagination?.total || 0 }})
                    </button>
                    <button
                        @click="switchTab('instructors')"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                            activeTab === 'instructors'
                                ? 'border-lime-500 text-lime-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        Instructors ({{ instructorPagination?.total || 0 }})
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-1 py-6 px-6">
            <div class="bg-white rounded-lg shadow">
                <!-- Search and Actions Bar -->
                <div
                    class="p-4 border-b border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
                >
                    <!-- Search -->
                    <div class="relative flex-1 max-w-md">
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="`Search ${activeTab}...`"
                            class="w-full border border-gray-300 rounded-lg py-2 px-4 pl-10 focus:outline-none focus:border-lime-500"
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

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <div
                            v-if="selectedUsers.length"
                            class="flex items-center space-x-2 mr-4"
                        >
                            <span class="text-sm text-gray-600"
                                >{{ selectedUsers.length }} selected</span
                            >
                            <button
                                @click="clearSelection"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                Clear
                            </button>
                        </div>

                        <button
                            @click="openAddUserModal"
                            class="bg-lime-600 text-white px-4 py-2 rounded-lg hover:bg-lime-700 transition-colors duration-200"
                        >
                            Add
                            {{
                                activeTab === "instructors"
                                    ? "Instructor"
                                    : "Student"
                            }}
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div
                    v-if="selectedUsers.length"
                    class="px-4 py-2 bg-blue-50 border-b border-blue-200"
                >
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-blue-800">
                            {{ selectedUsers.length }} {{ activeTab }} selected
                        </span>
                        <button
                            @click="showBulkDeleteConfirmation"
                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                        >
                            <i class="fa-solid fa-trash mr-1"></i> Delete
                            Selected
                        </button>
                        <button
                            @click="openBulkMessageModal"
                            class="text-lime-600 hover:text-lime-800 text-sm font-medium"
                        >
                            <i class="fa-solid fa-message mr-1"></i> Message
                            Selected
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full" v-if="currentUsers.length > 0">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Contact Info
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    v-if="showTempPasswordColumn"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Temp Password
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Join Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="user in currentUsers"
                                :key="user.id"
                                :class="{
                                    'bg-blue-50': selectedUsers.includes(
                                        user.id
                                    ),
                                }"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 flex items-center">
                                    <input
                                        type="checkbox"
                                        @change="toggleMark(user.id)"
                                        :checked="
                                            selectedUsers.includes(user.id)
                                        "
                                        class="mr-3 rounded border-gray-300 text-lime-600 focus:ring-lime-500"
                                    />
                                    <img
                                        :src="
                                            user.profile ||
                                            '/default-avatar.png'
                                        "
                                        alt="avatar"
                                        class="w-10 h-10 rounded-full mr-3"
                                    />
                                    <div>
                                        <div
                                            class="font-medium text-gray-900 capitalize"
                                        >
                                            {{ user.first_name }}
                                            {{ user.middle_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ user.role?.name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ user.email }}
                                    </div>
                                    <div
                                        v-if="user.phone"
                                        class="text-sm text-gray-500"
                                    >
                                        {{ user.phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                user.status === 'active',
                                            'bg-red-100 text-red-800':
                                                user.status === 'banned',
                                        }"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td
                                    v-if="showTempPasswordColumn"
                                    class="px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ user.temp_password || "Changed" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ user.joinDate }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <Popper>
                                        <button
                                            class="text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                                                />
                                            </svg>
                                        </button>
                                        <template #content>
                                            <div
                                                class="bg-white shadow-lg rounded-lg py-2 w-48"
                                            >
                                                <button
                                                    @click="
                                                        openMessageModal(user)
                                                    "
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Send Message
                                                </button>
                                                <button
                                                    @click="
                                                        openActivityLogModal(
                                                            user
                                                        )
                                                    "
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Activity Logs
                                                </button>
                                                <button
                                                    @click="openBanModal(user)"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    {{
                                                        user.status === "banned"
                                                            ? "Unban User"
                                                            : "Ban User"
                                                    }}
                                                </button>
                                                <hr class="my-1" />
                                                <button
                                                    @click="toggleMark(user.id)"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    {{
                                                        selectedUsers.includes(
                                                            user.id
                                                        )
                                                            ? "Unmark"
                                                            : "Mark"
                                                    }}
                                                </button>
                                                <button
                                                    @click="toggleMarkAll()"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Mark All
                                                </button>
                                                <button
                                                    @click="toggleUnMarkAll()"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Unmark All
                                                </button>
                                            </div>
                                        </template>
                                    </Popper>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="text-center py-12 text-gray-500">
                        No {{ activeTab }} found
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    class="px-6 py-4 border-t border-gray-200 flex items-center justify-between"
                >
                    <div class="text-sm text-gray-700">
                        Showing {{ currentPagination?.from || 0 }} to
                        {{ currentPagination?.to || 0 }} of
                        {{ currentPagination?.total || 0 }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="onPreviousPage()"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <span class="px-3 py-1">
                            Page {{ currentPage }} of
                            {{ currentPagination?.last_page || 1 }}
                        </span>
                        <button
                            @click="onNextPage()"
                            :disabled="
                                currentPage === currentPagination?.last_page
                            "
                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Message Modal -->
        <transition name="fade">
            <div
                v-if="showMessageModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            >
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                    <h3 class="text-xl font-bold mb-4">
                        {{
                            modalUser
                                ? `Send Message to ${modalUser.first_name} ${modalUser.middle_name}`
                                : "New Announcement"
                        }}
                    </h3>
                    <textarea
                        v-model="messageText"
                        placeholder="Type your message here..."
                        class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-lime-500"
                        rows="4"
                    ></textarea>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                        <button
                            @click="sendMessage"
                            :disabled="isProcessing"
                            class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700"
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
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            >
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                    <h3 class="text-xl font-bold mb-4">
                        Add New
                        {{
                            activeTab === "instructors"
                                ? "Instructor"
                                : "Student"
                        }}
                    </h3>
                    <form @submit.prevent="submitAddUser">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2"
                                >Email</label
                            >
                            <input
                                v-model="newUser.email"
                                type="email"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-2"
                                    >First Name</label
                                >
                                <input
                                    v-model="newUser.first_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-2"
                                    >Middle Name</label
                                >
                                <input
                                    v-model="newUser.middle_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                        </div>
                        <div v-if="activeTab === 'students'">
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 font-medium mb-2"
                                    >Phone</label
                                >
                                <input
                                    v-model="newUser.phone"
                                    type="tel"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                            <div class="mb-6">
                                <label
                                    class="block text-gray-700 font-medium mb-2"
                                    >Password</label
                                >
                                <input
                                    v-model="newUser.password"
                                    type="password"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                        </div>
                        <div v-else class="mb-6">
                            <p class="text-gray-600 text-sm">
                                A random password will be generated for this
                                instructor account.
                            </p>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button
                                type="button"
                                @click="closeAddUserModal"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isProcessing"
                                class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700"
                            >
                                Add
                                {{
                                    activeTab === "instructors"
                                        ? "Instructor"
                                        : "Student"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Activity Log Modal -->
        <transition name="fade">
            <div
                v-if="showActivityLogModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            >
                <div
                    class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 p-6"
                >
                    <h3 class="text-xl font-bold mb-4">
                        Activity Log for {{ activityLogUser.first_name }}
                        {{ activityLogUser.middle_name }}
                    </h3>
                    <div
                        v-if="activityLogDetails.length"
                        class="max-h-96 overflow-y-auto"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2 text-left">Timestamp</th>
                                    <th class="py-2 text-left">Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(log, index) in activityLogDetails"
                                    :key="index"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="py-2 pr-6 whitespace-nowrap">
                                        {{ log.created_at }}
                                    </td>
                                    <td class="py-2">{{ log.activity }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-600 py-4 text-center">
                        No activity found for this user.
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="closeActivityLogModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Confirmation Dialog -->
        <transition name="fade">
            <div
                v-if="showConfirmationDialog"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            >
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                    <h3 class="text-xl font-bold mb-4">
                        {{ confirmationTitle }}
                    </h3>
                    <p class="mb-6">{{ confirmationMessage }}</p>
                    <div class="flex justify-end space-x-2">
                        <button
                            @click="showConfirmationDialog = false"
                            :disabled="isProcessing"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmationAction"
                            :disabled="isProcessing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        >
                            <span v-if="isProcessing">Processing...</span>
                            <span v-else>Confirm</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
