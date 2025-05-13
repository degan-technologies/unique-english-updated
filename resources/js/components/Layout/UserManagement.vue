<script setup>
import axios from "axios";
import Popper from "vue3-popper";
import { ref, computed, watch, onMounted } from "vue";

const users = ref([]);
const selectedUsers = ref([]);

const searchQuery = ref("");
const masterSelected = ref(false);
const showDeleteModal = ref(false);
const showMessageModal = ref(false);
const modalUser = ref(null);
const messageText = ref("");
const showAddUserModal = ref(false);
const newUser = ref({
    email: "",
    first_name: "",
    middle_name: "",
    role: "INSTRUCTOR_ROLE",
});
const currentPage = ref(1);
const rowsPerPage = ref(10);
const showActivityLogModal = ref(false);
const activityLogDetails = ref([]);
const activityLogUser = ref({});
const filters = ref({
    role: "",
    status: "",
    joinDateFrom: "",
    joinDateTo: "",
    progress: 0,
});

async function fetchUsers() {
    try {
        const response = await axios.get("/api/users", {
            params: {
                search: searchQuery.value,
                role: filters.value.role,
                status: filters.value.status,
                joinDateFrom: filters.value.joinDateFrom,
                joinDateTo: filters.value.joinDateTo,
                progress: filters.value.progress,
            },
        });
        users.value = response.data.data;
        console.log("Users fetched:", users.value);
    } catch (error) {
        console.error("Error fetching users:", error);
    }
}
onMounted(() => {
    fetchUsers();
});

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage.value;
    return users.value.slice(start, start + rowsPerPage.value);
});
const totalPages = computed(() => {
    return Math.ceil(users.value.length / rowsPerPage.value) || 1;
});
const showTempPasswordColumn = computed(() => {
    return users.value.some((user) => user.role === "INSTRUCTOR_ROLE");
});

function toggleMark(id) {
    if (!selectedUsers.value.includes(id)) {
        selectedUsers.value.push(id);
        return;
    }
    selectedUsers.value = selectedUsers.value.filter((userId) => userId !== id);
    return;
}

function toggleMarkAll() {
    users.value.map((user) => {
        selectedUsers.value.push(user.id);
    });
}

function toggleUnMarkAll() {
    selectedUsers.value = [];
}

function clearSelection() {
    selectedUsers.value = [];
    masterSelected.value = false;
}
function openDeleteModal(user) {
    modalUser.value = user;
    showDeleteModal.value = true;
}
async function confirmDelete() {
    try {
        const response = await axios.delete(
            `/api/delete-instructor/${modalUser.value.id}`
        );
        console.log("Delete response:", response.data);
        fetchUsers();
        closeModal();
    } catch (error) {}
}
function openMessageModal(user) {
    modalUser.value = user;
    showMessageModal.value = true;
}
function openBulkMessageModal() {
    modalUser.value = null;
    showMessageModal.value = true;
}
async function sendMessage() {
    if (messageText.value.trim() === "") {
        return;
    }
    try {
        if (modalUser.value) {
            const payload = {
                user_id: modalUser.value.id,
                message: messageText.value,
            };
            const response = await axios.post("/api/send-sms", payload);
        } else {
            if (selectedUsers.value.length === 0) {
                return;
            }
            const payload = {
                user_ids: selectedUsers.value,
                message: messageText.value,
            };
            const response = await axios.post("/api/send-bulk-sms", payload);
        }
    } catch (error) {}
    messageText.value = "";
    showMessageModal.value = false;
}
function closeModal() {
    showDeleteModal.value = false;
    showMessageModal.value = false;
    modalUser.value = null;
}
function openAddUserModal() {
    showAddUserModal.value = true;
}
function closeAddUserModal() {
    showAddUserModal.value = false;
    newUser.value = {
        email: "",
        first_name: "",
        middle_name: "",
        role: "INSTRUCTOR_ROLE",
    };
}
async function submitAddUser() {
    try {
        const response = await axios.post("/api/add-instructor", newUser.value);
        alert(response.data.message);
        fetchUsers();
        closeAddUserModal();
    } catch (error) {
        alert(
            (error.response && error.response.data.message) ||
                "Error adding user"
        );
    }
}
async function bulkDelete() {
    if (selectedUsers.value.length === 0) return;
    const confirmAction = confirm(
        `Are you sure you want to delete ${selectedUsers.value.length} selected users?`
    );
    if (!confirmAction) return;
    try {
        const response = await axios.post("/api/users/bulk/delete", {
            ids: selectedUsers.value,
        });
        fetchUsers();
        clearSelection();
    } catch (error) {}
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}
function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}
watch(selectedUsers, () => {
    masterSelected.value = users.value.every((user) =>
        selectedUsers.value.includes(user.id)
    );
});
watch(rowsPerPage, () => {
    currentPage.value = 1;
});

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
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <header
            class="bg-white shadow py-4 px-6 flex flex-col md:flex-row items-start md:items-center justify-between"
        >
            <div class="w-full md:w-auto">
                <h1 class="text-2xl font-bold text-gray-800">
                    User Management
                </h1>
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
            <div
                class="mt-4 md:mt-0 flex items-center space-x-2 w-full md:w-auto"
            >
                <div class="relative flex-1 md:flex-none">
                    <input
                        v-model="searchQuery"
                        @input="fetchUsers()"
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
            </div>
        </header>

        <div class="my-6">
            <div class="flex border-b">
                <button class="p-2">Manage User</button>
            </div>
        </div>
        <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
            <main class="flex-1 py-8 overflow-auto">
                <div class="bg-white p-6 rounded shadow overflow-hidden">
                    <div
                        class="w-full bg-white rounded border-b border-gray-200 p-2 flex flex-row justify-between gap-4"
                    >
                        <div>
                            <div
                                v-if="selectedUsers.length"
                                class="flex items-center space-x-2"
                            >
                                <span class="font-medium pr-4"
                                    >{{ selectedUsers.length }} Selected
                                </span>
                                <i
                                    @click="bulkDelete"
                                    class="fa-solid fa-trash text-md font-bold text-gray-400"
                                ></i>
                                <i
                                    @click="openBulkMessageModal"
                                    class="fa-solid fa-message text-md font-bold text-lime-400"
                                ></i>
                                <i
                                    @click="clearSelection"
                                    class="fa-solid fa-arrow-rotate-left text-md font-bold text-blue-400"
                                ></i>
                            </div>
                        </div>

                        <button
                            @click="openAddUserModal"
                            class="bg-lime-600 text-white px-4 py-2 h-fit rounded hover:bg-lime-700 focus:outline-none"
                        >
                            Add User
                        </button>
                    </div>
                    <div class="w-full overflow-x-auto scrollbar">
                        <table class="w-full" v-if="users.length > 0">
                            <thead>
                                <tr
                                    class="bg-gray-100 py-2 border-b border-gray-400"
                                >
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        User
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Email & Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Engagment
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
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="user in paginatedUsers"
                                    :key="user.id"
                                    :class="{
                                        'bg-blue-50': selectedUsers.includes(
                                            user.id
                                        ),
                                        'hover:bg-gray-50':
                                            selectedUsers.length == 0,
                                    }"
                                    class="cursor-pointer"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2"
                                    >
                                        <img
                                            :src="user.profile"
                                            alt="avatar"
                                            class="w-8 h-8 rounded-full mr-3"
                                        />
                                        <div class="flex flex-col">
                                            <h1 class="font-medium capitalize">
                                                {{ user.first_name }}
                                                {{ user.middle_name }}
                                            </h1>
                                            <p
                                                class="inline-block text-xs py-1 rounded-full w-fit px-2"
                                                :style="{
                                                    backgroundColor:
                                                        user?.role?.[
                                                            'bg-color'
                                                        ],
                                                    text: user?.role?.[
                                                        'text-color'
                                                    ],
                                                }"
                                            >
                                                {{ user.role?.name }}
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        <div class="text-sm text-gray-700">
                                            {{ user.email }}
                                        </div>
                                        <div class="text-xs text-lime-700 py-1">
                                            <span
                                                class="font-bold text-gray-700"
                                                >Generated Password:</span
                                            >
                                            {{
                                                user.temp_password
                                                    ? user.temp_password
                                                    : "Changed"
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        <div class="text-sm text-gray-700">
                                            {{ user.engagement }}
                                        </div>
                                    </td>
                                    <td
                                        v-if="showTempPasswordColumn"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                    >
                                        {{
                                            user.role === "INSTRUCTOR_ROLE"
                                                ? user.temp_password
                                                    ? user.temp_password
                                                    : "-"
                                                : "-"
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                    >
                                        {{ user.joinDate }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center space-x-2"
                                    >
                                        <Popper>
                                            <i
                                                class="fa-solid fa-ellipsis-vertical text-lg font-bold w-6 h-6 p-1 rounded-full hover:bg-slate-200"
                                            ></i>
                                            <template #content>
                                                <div
                                                    class="bg-gray-50 text-black w-48 shadow-lg rounded p-2"
                                                >
                                                    <div
                                                        @click.stop="
                                                            openMessageModal(
                                                                user
                                                            )
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Send message</span
                                                        >
                                                    </div>
                                                    <div
                                                        @click.stop="
                                                            openActivityLogModal(
                                                                user
                                                            )
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Activity logs</span
                                                        >
                                                    </div>
                                                    <div
                                                        @click.stop="
                                                            openDeleteModal(
                                                                user
                                                            )
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Ban User</span
                                                        >
                                                    </div>
                                                    <div
                                                        @click.stop="
                                                            toggleMark(user.id)
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Mark</span
                                                        >
                                                    </div>
                                                    <div
                                                        @click.stop="
                                                            toggleMarkAll()
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Mark all</span
                                                        >
                                                    </div>
                                                    <div
                                                        @click.stop="
                                                            toggleUnMarkAll()
                                                        "
                                                        class="block hover:bg-gray-200 text-sm text-left gap-2"
                                                    >
                                                        <span
                                                            class="block px-4 py-2 hover:bg-gray-200"
                                                            >Unmark all</span
                                                        >
                                                    </div>
                                                </div>
                                            </template>
                                        </Popper>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div
                        class="p-4 bg-white flex flex-col sm:flex-row items-center justify-between"
                    >
                        <div class="flex items-center mb-2 sm:mb-0">
                            <span class="text-sm text-gray-600 mr-2"
                                >Rows per page:</span
                            >
                            <select
                                v-model.number="rowsPerPage"
                                class="border-gray-300 text-gray-600 bg-gray-800 rounded focus:outline-none"
                            >
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
        </div>

        <!-- Modals -->
        <!-- Delete Confirmation Modal -->
        <transition name="fade">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
            >
                <div class="bg-white rounded shadow-lg w-96 p-6">
                    <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
                    <p class="mb-6">
                        Are you sure you want to delete
                        <strong
                            >{{ modalUser.first_name }}
                            {{ modalUser.middle_name }}</strong
                        >?
                    </p>
                    <div class="flex justify-end space-x-2">
                        <button
                            @click="closeModal"
                            class="px-4 py-3 border rounded hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmDelete"
                            class="px-4 py-3 bg-red-500 text-white rounded hover:bg-red-600"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Message Modal -->
        <transition name="fade">
            <div
                v-if="showMessageModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
            >
                <div class="bg-white rounded shadow-lg w-96 p-6">
                    <h3 class="text-xl font-bold mb-4">
                        {{
                            modalUser
                                ? "Message " +
                                  modalUser.first_name +
                                  " " +
                                  modalUser.middle_name
                                : "New Announcement"
                        }}
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
                            class="px-4 py-3 border rounded hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                        <button
                            @click="sendMessage"
                            class="px-4 py-3 bg-lime-700 text-white rounded hover:bg-lime-800"
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
                <div
                    class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8 transform transition-all duration-300 ease-in-out scale-100"
                >
                    <div
                        class="flex justify-between items-center border-b pb-3 mb-6"
                    >
                        <h3 class="text-2xl font-bold text-gray-800">
                            Add New User
                        </h3>
                        <button
                            @click="closeAddUserModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitAddUser">
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 font-semibold mb-2"
                                for="email"
                                >Email</label
                            >
                            <input
                                id="email"
                                v-model="newUser.email"
                                type="email"
                                placeholder="example@degan.com"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
                                required
                            />
                        </div>
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-gray-700 font-semibold mb-2"
                                    for="first_name"
                                    >First Name</label
                                >
                                <input
                                    id="first_name"
                                    v-model="newUser.first_name"
                                    type="text"
                                    placeholder="John"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 font-semibold mb-2"
                                    for="middle_name"
                                    >Middle Name</label
                                >
                                <input
                                    id="middle_name"
                                    v-model="newUser.middle_name"
                                    type="text"
                                    placeholder="Doe"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
                                />
                            </div>
                        </div>
                        <div class="mb-6">
                            <p class="text-gray-600 text-sm">
                                A random password will be generated for this
                                account. This account will be created as an
                                <strong>Instructor</strong> by default. The
                                generated password will be displayed in the user
                                table until the instructor updates it.
                            </p>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button
                                type="button"
                                @click="closeAddUserModal"
                                class="px-4 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-3 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors duration-200"
                            >
                                Add User
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
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
            >
                <div class="bg-white rounded shadow-lg md:w-2xl p-6">
                    <h3 class="text-xl font-bold mb-4">
                        Activity Log for {{ activityLogUser.first_name }}
                        {{ activityLogUser.middle_name }}
                    </h3>
                    <div
                        v-if="activityLogDetails.length"
                        class="h-[50] overflow-hidden overflow-y-auto scrollbar"
                    >
                        <table class="min-w-full text-sm">
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
                                    <td class="py-2 pr-6">
                                        {{ log.created_at }}
                                    </td>
                                    <td class="py-2">{{ log.activity }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-600">
                        No activity found.
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="closeActivityLogModal"
                            class="px-4 py-3 bg-gray-200 rounded hover:bg-gray-300"
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
