<script setup>
import { ref, onMounted, watch } from "vue";
import Popper from "vue3-popper";
import Axios from "axios";
import { useToast } from "vue-toastification";
import { IndustrialScales } from "@icon-park/vue-next";

const toast = useToast();
const rooms = ref([]);
const showAddModal = ref(false); 
const users = ref([]);
const instructors = ref([]);
const selectedInstructor = ref(null);
const selectedStudentId = ref(null);

const currentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];
const pagination = ref("");
const totalPages = ref(0);
 
const props = defineProps({
    toggleAddButton: Boolean
});

const fetchUsers = async (page = 1) => {
    try {
        const res = await Axios.get(`/api/private-participants?page=${page}`,  {
                params:{ 
                    rowsPerPageOptions: rowsPerPage.value,
                }
            });
        users.value = res.data.data;
        pagination.value = res.data.pagination;
        totalPages.value = res.data.pagination.last_page;
        currentPage.value = res.data.pagination.current_page;
    } catch (error) {
        console.error("Error fetching users:", error);
        toast.error("Failed to fetch users");
    }
};  

const fetchInstructors = async () => {
    try {
        const response = await Axios.get(`/api/my-instructors`);
        instructors.value = response.data.data;
    } catch (error) {
        console.error("Error fetching instructors:", error);
        toast.error("Failed to fetch instructors");
    }
};

function assignInstructor(userId, instructor) {
    Axios.post(`/api/assign-private-instructor/${userId}`, {
        instructorId: instructor.id,
    }).then((res) => {
        toast.success("Class assigned successfully"); 

        selectedInstructor.value = instructor;
        selectedStudentId.value = userId;
    });
}

watch(() => props.toggleAddButton, (newValue) => {
    if (newValue) {
        showAddModal.value = true;
    }
});

function onNextPage() {
    if (currentPage.value == totalPages.value) return;

    fetchUsers(currentPage.value + 1);
}

function onPreviousPage() {
    if (currentPage.value <= 1) return;

    fetchUsers(currentPage.value - 1);
}

function studentPerPage(amount) {
    rowsPerPage.value = amount;
    fetchUsers(currentPage.value);
} 

onMounted(() => { 
    fetchUsers();
    fetchInstructors();
});
</script>

<template>
    <div>   
        <!-- student register for live class -->
        <div class="w-full overflow-auto mt-12 bg-white scrollbar">
            <table class="w-full" v-if="users.length">
                <thead>
                    <tr class="py-">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Instructor
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in users" :key="user.id" class="cursor-pointer">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2">
                            <img :src="user.profile" alt="avatar" class="w-8 h-8 rounded-full mr-3" />
                            <div class="flex flex-col">
                                <h1 class="font-medium capitalize">
                                    {{ user.first_name }} {{ user.middle_name }}
                                </h1>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="text-sm text-gray-700">
                                {{ user.email }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="text-sm text-lime-700 flex flex-1 justify-between">
                                <p>{{ selectedStudentId ? selectedInstructor?.first_name +' '+  selectedInstructor?.middle_name : user.instructor_name }}</p>
                                <Popper>
                                    <i
                                        class="fa-solid fa-chevron-down text-lg font-bold w-6 h-6 p-1 rounded-full hover:bg-slate-200"></i>
                                    <template #content>
                                        <div class="bg-gray-50 text-black w-48 shadow-lg rounded p-2">
                                            <div v-if="true">
                                                <div class="py-2 border-b-2 border-gray-500">
                                                    Assign Instructor
                                                </div>
                                                <div v-for="instructor in instructors" :key="instructor.id" @click="
                                                assignInstructor(user.id, instructor )
                                                " class="block hover:bg-gray-200 text-sm text-left gap-2 cursor-pointer">
                                                <span class="block px-4 py-2">
                                                    {{ instructor.first_name }}
                                                    {{ instructor.middle_name }}
                                                </span>
                                            </div>
                                            </div> 
                                        </div>
                                    </template>
                                </Popper>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No participants</h3>
                <p class="mt-1 text-sm text-gray-500">Participants will appear here once registered.
                </p>
            </div>
        </div>  

        <!-- Pagination Footer -->
        <div class="p-4 bg-white flex flex-row items-center justify-between">
            <!-- Rows Per Page Selector -->
            <Popper>
                <div class="flex flex-row md:gap-2">
                    <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                    <span class="text-sm font-medium">{{ rowsPerPage }}</span>
                    <i class="fa-solid fa-chevron-down text-lg"></i>
                </div>
                <template #content>
                    <div v-for="option in rowsPerPageOptions" :key="option" @click="studentPerPage(option)"
                        class="border w-32 block border-gray-200 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                        :class="{
                            'bg-gray-300 text-white font-bold':
                                rowsPerPage === option,
                            'bg-white text-gray-700 hover:bg-gray-200':
                                rowsPerPage !== option,
                        }">
                        {{ option }}
                    </div>
                </template>
            </Popper>

            <!-- Pagination Controls -->
            <div class="flex items-center space-x-3">
                <button @click="onPreviousPage()" :disabled="currentPage === 1"
                    class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    Prev
                </button>

                <span class="text-sm text-gray-600">
                    Page {{ currentPage }} of {{ totalPages }}
                </span>

                <button @click="onNextPage()" :disabled="currentPage === totalPages"
                    class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
