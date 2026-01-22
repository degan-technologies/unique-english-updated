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
            (userId) => userId !== id,
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
    const isBanned = user.status === "Blocked";
    confirmationTitle.value = isBanned ? "Confirm Unban" : "Confirm Ban";
    confirmationMessage.value = isBanned
        ? `Are you sure you want to unban ${user.first_name} ${user.middle_name}? This will restore their access to the platform.`
        : `Are you sure you want to ban ${user.first_name} ${user.middle_name}? This will block their access to the platform.`;
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
            "success",
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

// Add new refs for bulk import
const showBulkImportModal = ref(false);
const bulkImportFile = ref(null);
const bulkImportFileInput = ref(null);
const bulkImportProgress = ref(false);
const bulkImportResults = ref(null);

// Add bulk import methods
function openBulkImportModal() {
    if (activeTab.value !== "students") {
        showToast("Bulk import is only available for students", "warning");
        return;
    }
    showBulkImportModal.value = true;
}

function closeBulkImportModal() {
    showBulkImportModal.value = false;
    bulkImportFile.value = null;
    bulkImportResults.value = null;
    if (bulkImportFileInput.value) {
        bulkImportFileInput.value.value = "";
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        const allowedTypes = [
            "text/csv",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ];
        if (!allowedTypes.includes(file.type) && !file.name.endsWith(".csv")) {
            showToast("Please select a valid CSV file", "error");
            event.target.value = "";
            return;
        }
        bulkImportFile.value = file;
        // Clear previous results when new file is selected
        bulkImportResults.value = null;
    }
}

async function submitBulkImport() {
    if (!bulkImportFile.value) {
        showToast("Please select a CSV file", "warning");
        return;
    }

    bulkImportProgress.value = true;

    try {
        const formData = new FormData();
        formData.append("excel_file", bulkImportFile.value);

        const response = await axios.post(
            "/api/students/bulk-import",
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            },
        );

        bulkImportResults.value = response.data;
        showToast(response.data.message, "success");

        // Refresh students list
        fetchStudents(1);
    } catch (error) {
        const errorMsg =
            error.response?.data?.message || "Failed to import students";
        showToast(errorMsg, "error");

        // Enhanced error handling with detailed errors
        if (error.response?.data) {
            const errorData = error.response.data;
            bulkImportResults.value = {
                success_count: errorData.success_count || 0,
                error_count: errorData.error_count || 0,
                total_processed: errorData.total_processed || 0,
                message: errorMsg,
                has_errors: true,
                error_summary: errorData.error_summary || {},
                detailed_errors: errorData.errors || [], // Add detailed errors
            };
        }
    } finally {
        bulkImportProgress.value = false;
    }
}

// Add new refs for export functionality
const isExporting = ref(false);

// Add export methods
async function exportUsers() {
    if (currentUsers.value.length === 0) {
        showToast(`No ${activeTab.value} to export`, "warning");
        return;
    }

    isExporting.value = true;

    try {
        const endpoint =
            activeTab.value === "instructors"
                ? "/api/instructors/export"
                : "/api/students/export";

        const response = await axios({
            method: "GET",
            url: endpoint,
            params: {
                search: searchQuery.value,
            },
            responseType: "blob",
        });

        // Validate response
        if (!response.data || response.data.size === 0) {
            throw new Error("Empty response received");
        }

        const blob = new Blob([response.data], {
            type: "text/csv;charset=utf-8",
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;

        const timestamp = new Date().toISOString().split("T")[0];
        link.download = `${activeTab.value}_export_${timestamp}.csv`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        showToast(`${activeTab.value} exported successfully`, "success");
    } catch (error) {
        console.error("Export error:", error);

        if (error.response && error.response.status === 404) {
            showToast("Export feature not available", "error");
        } else {
            showToast("Export failed. Please try again.", "error");
        }
    } finally {
        isExporting.value = false;
    }
}

function exportToCSV() {
    exportUsers();
}
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
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none',
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
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none',
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
                    <div class="relative flex-1 max-w-md w-full">
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

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 w-full md:w-auto">
                        <!-- Selection Info -->
                        <div
                            v-if="selectedUsers.length"
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <span>{{ selectedUsers.length }} selected</span>
                            <button
                                @click="clearSelection"
                                class="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Clear
                            </button>
                        </div>

                        <!-- Action Groups -->
                        <div
                            class="flex flex-wrap items-center gap-4 justify-start md:justify-end"
                        >
                            <!-- DATA ACTIONS GROUP -->
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- Export -->
                                    <div class="relative">
                                        <Popper>
                                            <button
                                                :disabled="
                                                    isExporting ||
                                                    currentUsers.length === 0
                                                "
                                                class="flex items-center gap-2 bg-green-600 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <i
                                                    v-if="isExporting"
                                                    class="fa-solid fa-spinner fa-spin"
                                                ></i>
                                                <i
                                                    v-else
                                                    class="fa-solid fa-download"
                                                ></i>

                                                <span>
                                                    {{
                                                        isExporting
                                                            ? "Exporting"
                                                            : "Export"
                                                    }}
                                                </span>

                                                <i
                                                    class="fa-solid fa-chevron-down text-xs"
                                                ></i>
                                            </button>

                                            <template #content>
                                                <div
                                                    class="bg-white shadow-lg rounded-lg py-2 w-44"
                                                >
                                                    <button
                                                        @click="exportToCSV"
                                                        :disabled="isExporting"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-50"
                                                    >
                                                        <i
                                                            class="fa-solid fa-file-csv mr-2"
                                                        ></i>
                                                        Export CSV
                                                    </button>
                                                </div>
                                            </template>
                                        </Popper>
                                    </div>

                                    <!-- Bulk Import -->
                                    <button
                                        v-if="activeTab === 'students'"
                                        @click="openBulkImportModal"
                                        class="flex items-center gap-2 bg-blue-600 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition"
                                    >
                                        <i class="fa-solid fa-upload"></i>
                                        <span>Import</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ADD USER -->
                            <div class="flex flex-col gap-1">
                                <button
                                    @click="openAddUserModal"
                                    class="bg-lime-600 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded-lg hover:bg-lime-700 transition"
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
                                    Engagement
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
                                        user.id,
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
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span
                                        v-if="user.engagement"
                                        class="font-medium"
                                    >
                                        {{ user.engagement || no }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                user.status === 'Active',
                                            'bg-red-100 text-red-800':
                                                user.status === 'Blocked' ||
                                                user.status === 'Banned',
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
                                                            user,
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
                                                        user.status ===
                                                        "Blocked"
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
                                                            user.id,
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
                    class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Info -->
                    <div
                        class="text-xs sm:text-sm text-gray-700 text-center sm:text-left"
                    >
                        Showing {{ currentPagination?.from || 0 }} to
                        {{ currentPagination?.to || 0 }} of
                        {{ currentPagination?.total || 0 }} results
                    </div>

                    <!-- Controls -->
                    <div
                        class="flex items-center justify-center sm:justify-end gap-2 text-sm"
                    >
                        <!-- Previous -->
                        <button
                            @click="onPreviousPage()"
                            :disabled="currentPage === 1"
                            class="px-2 py-1 sm:px-3 sm:py-1.5 border rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span class="hidden sm:inline">Previous</span>
                            <i class="fa-solid fa-chevron-left sm:hidden"></i>
                        </button>

                        <!-- Page Info -->
                        <span
                            class="px-2 sm:px-3 py-1 text-xs sm:text-sm whitespace-nowrap"
                        >
                            Page {{ currentPage }}
                            <span class="hidden sm:inline">
                                of {{ currentPagination?.last_page || 1 }}
                            </span>
                        </span>

                        <!-- Next -->
                        <button
                            @click="onNextPage()"
                            :disabled="
                                currentPage === currentPagination?.last_page
                            "
                            class="px-2 py-1 sm:px-3 sm:py-1.5 border rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span class="hidden sm:inline">Next</span>
                            <i class="fa-solid fa-chevron-right sm:hidden"></i>
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-3 sm:px-0"
            >
                <!-- Modal Card -->
                <div
                    class="bg-white rounded-lg shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6"
                >
                    <!-- Header -->
                    <h3 class="text-lg sm:text-xl font-bold mb-4">
                        Add New
                        {{
                            activeTab === "instructors"
                                ? "Instructor"
                                : "Student"
                        }}
                    </h3>

                    <form @submit.prevent="submitAddUser">
                        <!-- Email -->
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-medium mb-1"
                            >
                                Email
                            </label>
                            <input
                                v-model="newUser.email"
                                type="email"
                                required
                                class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                            />
                        </div>

                        <!-- Names -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-gray-700 text-sm font-medium mb-1"
                                >
                                    First Name
                                </label>
                                <input
                                    v-model="newUser.first_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-gray-700 text-sm font-medium mb-1"
                                >
                                    Middle Name
                                </label>
                                <input
                                    v-model="newUser.middle_name"
                                    type="text"
                                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                        </div>

                        <!-- Student Fields -->
                        <div v-if="activeTab === 'students'">
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-medium mb-1"
                                >
                                    Phone
                                </label>
                                <input
                                    v-model="newUser.phone"
                                    type="tel"
                                    required
                                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>

                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-medium mb-1"
                                >
                                    Password
                                </label>
                                <input
                                    v-model="newUser.password"
                                    type="password"
                                    required
                                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:border-lime-500"
                                />
                            </div>
                        </div>

                        <!-- Instructor Notice -->
                        <div v-else class="mb-4">
                            <p class="text-gray-600 text-sm">
                                A random password will be generated for this
                                instructor account.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-6"
                        >
                            <button
                                type="button"
                                @click="closeAddUserModal"
                                class="w-full sm:w-auto px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg hover:bg-gray-100 transition"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="isProcessing"
                                class="w-full sm:w-auto px-4 py-2 text-sm sm:text-base bg-lime-600 text-white rounded-lg hover:bg-lime-700 disabled:opacity-50 transition"
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
                    <p class="mb-6 text-gray-600">{{ confirmationMessage }}</p>
                    <div class="flex justify-end space-x-2">
                        <button
                            @click="showConfirmationDialog = false"
                            :disabled="isProcessing"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmationAction"
                            :disabled="isProcessing"
                            :class="[
                                'px-4 py-2 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed',
                                confirmationTitle.includes('Ban')
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-green-600 hover:bg-green-700',
                            ]"
                        >
                            <span v-if="isProcessing" class="flex items-center">
                                <svg
                                    class="animate-spin h-4 w-4 mr-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>
                                {{
                                    confirmationTitle.includes("Unban")
                                        ? "Unban User"
                                        : "Ban User"
                                }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Bulk Import Modal -->
        <transition name="fade">
            <div
                v-if="showBulkImportModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
            >
                <div
                    class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto max-h-[90vh] flex flex-col"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex-shrink-0 px-6 py-4 border-b border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-lg sm:text-xl font-bold text-gray-900"
                            >
                                Bulk Import Students
                            </h3>
                            <button
                                @click="closeBulkImportModal"
                                class="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <i class="fa-solid fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Content - Scrollable -->
                    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                        <!-- Import Instructions -->
                        <div
                            class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                        >
                            <h4 class="font-medium text-blue-800 mb-2 text-sm">
                                Instructions:
                            </h4>
                            <ul
                                class="text-xs sm:text-sm text-blue-700 space-y-1"
                            >
                                <li>
                                    • <strong>Required:</strong> First Name
                                    (column 1)
                                </li>
                                <li>
                                    • <strong>Optional:</strong> Middle Name,
                                    Last Name, Email, Phone, Gender
                                </li>
                                <li>
                                    • Email will be auto-generated if not
                                    provided or invalid
                                </li>
                                <li>
                                    • Default password "password123" will be
                                    assigned
                                </li>
                                <li>
                                    • Gender: "male" or "female"
                                    (case-insensitive)
                                </li>
                                <li>• Maximum file size: 5MB</li>
                                <li>
                                    • Supported formats: CSV, Excel (.xlsx,
                                    .xls)
                                </li>
                            </ul>
                        </div>

                        <!-- File Upload Section -->
                        <div>
                            <label
                                class="block text-gray-700 font-medium mb-2 text-sm"
                            >
                                Select File
                            </label>
                            <div class="space-y-3">
                                <input
                                    ref="bulkImportFileInput"
                                    type="file"
                                    accept=".csv,.xlsx,.xls"
                                    @change="handleFileSelect"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                                />
                                <p
                                    v-if="bulkImportFile"
                                    class="text-xs sm:text-sm text-green-600 bg-green-50 p-2 rounded flex items-center"
                                >
                                    <i class="fa-solid fa-file-check mr-2"></i>
                                    {{ bulkImportFile.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Import Results -->
                        <div v-if="bulkImportResults" class="space-y-4">
                            <div
                                class="bg-gray-50 border border-gray-200 rounded-lg p-4"
                            >
                                <h4
                                    class="font-medium text-gray-800 mb-3 text-sm"
                                >
                                    Import Results:
                                </h4>

                                <!-- Success/Error Summary -->
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3"
                                >
                                    <div
                                        class="text-center p-3 bg-green-50 border border-green-200 rounded-lg"
                                    >
                                        <div
                                            class="text-lg font-bold text-green-700"
                                        >
                                            {{
                                                bulkImportResults.success_count ||
                                                0
                                            }}
                                        </div>
                                        <div class="text-xs text-green-600">
                                            Successful
                                        </div>
                                    </div>
                                    <div
                                        class="text-center p-3 bg-red-50 border border-red-200 rounded-lg"
                                    >
                                        <div
                                            class="text-lg font-bold text-red-700"
                                        >
                                            {{
                                                bulkImportResults.error_count ||
                                                0
                                            }}
                                        </div>
                                        <div class="text-xs text-red-600">
                                            Failed
                                        </div>
                                    </div>
                                </div>

                                <!-- Enhanced Error Display -->
                                <div
                                    v-if="
                                        bulkImportResults.has_errors &&
                                        bulkImportResults.error_count > 0
                                    "
                                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"
                                >
                                    <div class="flex items-start space-x-2">
                                        <i
                                            class="fa-solid fa-exclamation-triangle text-yellow-600 mt-0.5 flex-shrink-0"
                                        ></i>
                                        <div
                                            class="text-xs sm:text-sm text-yellow-800"
                                        >
                                            <p class="font-medium mb-2">
                                                Import Issues Summary
                                            </p>
                                            <div class="space-y-1">
                                                <p
                                                    v-if="
                                                        bulkImportResults
                                                            .error_summary
                                                            ?.missing_required_data >
                                                        0
                                                    "
                                                >
                                                    •
                                                    {{
                                                        bulkImportResults
                                                            .error_summary
                                                            .missing_required_data
                                                    }}
                                                    rows missing required data
                                                    (First Name)
                                                </p>
                                                <p
                                                    v-if="
                                                        bulkImportResults
                                                            .error_summary
                                                            ?.duplicate_emails >
                                                        0
                                                    "
                                                >
                                                    •
                                                    {{
                                                        bulkImportResults
                                                            .error_summary
                                                            .duplicate_emails
                                                    }}
                                                    rows with duplicate emails
                                                </p>
                                                <p
                                                    v-if="
                                                        bulkImportResults
                                                            .error_summary
                                                            ?.validation_errors >
                                                        0
                                                    "
                                                >
                                                    •
                                                    {{
                                                        bulkImportResults
                                                            .error_summary
                                                            .validation_errors
                                                    }}
                                                    rows with validation errors
                                                </p>
                                                <p
                                                    v-if="
                                                        bulkImportResults
                                                            .error_summary
                                                            ?.format_errors > 0
                                                    "
                                                >
                                                    •
                                                    {{
                                                        bulkImportResults
                                                            .error_summary
                                                            .format_errors
                                                    }}
                                                    rows with format issues
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Error List -->
                                <div
                                    v-if="
                                        bulkImportResults.detailed_errors &&
                                        bulkImportResults.detailed_errors
                                            .length > 0
                                    "
                                    class="bg-red-50 border border-red-200 rounded-lg p-3 mt-3"
                                >
                                    <p
                                        class="font-medium text-red-800 mb-2 text-xs sm:text-sm"
                                    >
                                        Detailed Errors:
                                    </p>
                                    <div class="max-h-48 overflow-y-auto">
                                        <ul class="space-y-1">
                                            <li
                                                v-for="(
                                                    error, index
                                                ) in bulkImportResults.detailed_errors"
                                                :key="index"
                                                class="text-xs text-red-700 bg-red-100 p-2 rounded"
                                            >
                                                {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                    <p class="text-xs text-red-600 mt-2 italic">
                                        Fix these issues in your CSV file and
                                        try importing again.
                                    </p>
                                </div>

                                <!-- Success Message -->
                                <div
                                    v-if="bulkImportResults.success_count > 0"
                                    class="bg-green-50 border border-green-200 rounded-lg p-3"
                                >
                                    <div class="flex items-center space-x-2">
                                        <i
                                            class="fa-solid fa-check-circle text-green-600"
                                        ></i>
                                        <span
                                            class="text-xs sm:text-sm text-green-700 font-medium"
                                        >
                                            {{
                                                bulkImportResults.success_count
                                            }}
                                            students imported successfully
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="flex-shrink-0 px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3"
                    >
                        <button
                            @click="closeBulkImportModal"
                            :disabled="bulkImportProgress"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 transition-colors"
                        >
                            {{ bulkImportResults ? "Close" : "Cancel" }}
                        </button>
                        <button
                            @click="submitBulkImport"
                            :disabled="!bulkImportFile || bulkImportProgress"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors flex items-center space-x-2"
                        >
                            <span v-if="bulkImportProgress">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                                Importing...
                            </span>
                            <span v-else>
                                <i class="fa-solid fa-upload mr-2"></i>
                                Import Students
                            </span>
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
