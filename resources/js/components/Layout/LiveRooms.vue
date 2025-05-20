<script setup>
import { ref, onMounted, watch } from "vue";
import Popper from "vue3-popper";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const rooms = ref([]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedRoom = ref(null);
const users = ref([]);

const newRoom = ref({
    class_name: "",
});
const props = defineProps({
    toggleAddButton: Boolean
});

const fetchUsers = async () => {
    try {
        const response = await Axios.get("/api/my-participants");
        users.value = response.data.data;
    } catch (error) {
        console.error("Error fetching users:", error);
        toast.error("Failed to fetch users");
    }
};

const fetchRooms = async () => {
    try {
        const response = await Axios.get("/api/get-rooms");
        rooms.value = response.data.data;
    } catch (error) {
        console.error("Error fetching rooms:", error);
        toast.error("Failed to fetch rooms");
    }
};

const addRoom = async () => {
    try {
        const response = await Axios.post("/api/rooms", newRoom.value);
        rooms.value.push(response.data.data);
        showAddModal.value = false;
        newRoom.value = { class_name: "" };
        toast.success("Room created successfully");
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to create room");
    }
};

const editRoom = async () => {
    try {
        const response = await Axios.put(
            `/api/rooms/${selectedRoom.value.id}`,
            {
                class_name: selectedRoom.value.class_name,
            }
        );
        const index = rooms.value.findIndex(
            (room) => room.id === selectedRoom.value.id
        );
        rooms.value[index] = response.data.data;
        showEditModal.value = false;
        selectedRoom.value = null;
        toast.success("Room updated successfully");
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to update room");
    }
};

const instructors = ref([]);

const fetchInstructors = async () => {
    try {
        const response = await Axios.get(`/api/my-instructors`);
        instructors.value = response.data.data;
    } catch (error) {
        console.error("Error fetching instructors:", error);
        toast.error("Failed to fetch instructors");
    }
};

const assignInstructor = async (roomId, instructorId) => {
    try {
        await Axios.post(`/api/assign-instructor/${roomId}`, {
            instructor_id: instructorId,
        });
        toast.success("Instructor assigned successfully");
        fetchRooms(); // Refresh rooms list
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Failed to assign instructor"
        );
    }
};

function assignClass(userId, classId) {
    Axios.post(`/api/assign-class/${userId}`, {
        classId: classId,
    }).then((res) => {
        toast.success("Class assigned successfully");
    });
}

const openEditModal = (room) => {
    selectedRoom.value = { ...room };
    showEditModal.value = true;
};

watch(() => props.toggleAddButton, (newValue) => {
    if (newValue) {
        showAddModal.value = true;
    }
});

onMounted(() => {
    fetchRooms();
    fetchUsers();
    fetchInstructors();
});
</script>

<template>
    <div>  
        <!-- Rooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="room in rooms" :key="room.id"
                class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-4">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-semibold text-gray-800">
                        {{ room.class_name }}
                    </h3>
                    <div class="flex gap-2">
                        <Popper>
                            <button class="text-gray-500 hover:text-lime-600 transition-colors">
                                <i class="fas fa-user-plus"></i>
                            </button>
                            <template #content>
                                <div class="bg-gray-50 text-black w-48 shadow-lg rounded p-2">
                                    <div class="py-2 border-b-2 border-gray-500">
                                        Assign Instructor
                                    </div>
                                    <div v-for="instructor in instructors" :key="instructor.id" @click="
                                        assignInstructor(
                                            room.id,
                                            instructor.id
                                        )
                                        " class="block hover:bg-gray-200 text-sm text-left gap-2 cursor-pointer">
                                        <span class="block px-4 py-2">
                                            {{ instructor.first_name }}
                                            {{ instructor.middle_name }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </Popper>
                        <button @click="openEditModal(room)"
                            class="text-gray-500 hover:text-lime-600 transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">
                    {{ room.instructor_name }} {{ room.middle_name }}
                </p>
                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                    <span><i class="fas fa-users mr-2"></i>{{ room.participants || 0 }}</span>
                    <span><i class="fas fa-clock mr-2"></i>{{ room.duration }} Unlimited</span>
                </div>
                <div class="flex flex-col gap-2">
                    <div v-if="room.instructor" class="text-sm text-gray-600">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        Instructor: {{ room.instructor.first_name }}
                        {{ room.instructor.last_name }}
                    </div> 
                </div>
            </div>
        </div>

        <!-- student register for live class -->
        <div class="w-full h-72 overflow-auto mt-12 bg-white scrollbar">
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
                            Room
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
                                <p>{{ user.class_name }}</p>
                                <Popper>
                                    <i
                                        class="fa-solid fa-chevron-down text-lg font-bold w-6 h-6 p-1 rounded-full hover:bg-slate-200"></i>
                                    <template #content>
                                        <div class="bg-gray-50 text-black w-48 shadow-lg rounded p-2">
                                            <div v-if="true">
                                                <div class="py-2 border-b-2 border-gray-500">
                                                    Assign Room
                                                </div>
                                                <div v-for="room in rooms" :key="room.id" @click.stop="
                                                    assignClass(
                                                        user.id,
                                                        room.id
                                                    )
                                                    " class="block hover:bg-gray-200 text-sm text-left gap-2">
                                                    <span class="block px-4 py-2 hover:bg-gray-200">{{
                                                        room.class_name
                                                    }}</span>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <div class="py-2 border-b-2 border-gray-500">
                                                    Assign Instructor
                                                </div>
                                                <div @click.stop="
                                                    openMessageModal(user)
                                                    " v-for="room in rooms" :key="room.id"
                                                    class="block hover:bg-gray-200 text-sm text-left gap-2">
                                                    <span class="block px-4 py-2 hover:bg-gray-200">{{
                                                        room.class_name
                                                    }}</span>
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
        </div>

        <!-- Add Room Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Add New Room</h2>
                <form @submit.prevent="addRoom">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Class Name</label>
                        <input v-model="newRoom.class_name" type="text" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700">
                            Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Room Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Edit Room</h2>
                <form @submit.prevent="editRoom">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Class Name</label>
                        <input v-model="selectedRoom.class_name" type="text" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700">
                            Update Room
                        </button>
                    </div>
                </form>
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
