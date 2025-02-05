<template>
    <div class="max-w-5xl mx-auto p-6 md:p-8 bg-white rounded-lg shadow-md">
        <h2
            class="text-2xl md:text-3xl font-bold text-center text-lime-700 mb-6"
        >
            Manage Live Sessions
        </h2>

        <!-- Live Sessions List -->
        <div v-if="sessions.length" class="space-y-6">
            <div
                v-for="(session, index) in sessions"
                :key="session.id"
                class="p-4 md:p-6 border rounded-lg shadow-sm hover:shadow-md transition duration-300"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h3
                            class="text-lg md:text-xl font-semibold text-lime-700"
                        >
                            {{ session.title }}
                        </h3>
                        <p class="text-gray-600 text-sm md:text-base">
                            Class Type: {{ session.classType }} | Price:
                            {{ session.price }} Birr
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="openEditModal(index)"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button
                            @click="deleteSession(session.id)"
                            class="text-red-600 hover:text-red-800"
                        >
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500">
            No live sessions available.
        </p>

        <!-- Edit Modal -->
        <div
            v-if="isEditing"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-lime-700 mb-4">
                    Edit Live Session
                </h3>
                <form @submit.prevent="updateSession" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Title
                        </label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                        <p
                            v-if="errors.title"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ errors.title }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Price (Birr)
                        </label>
                        <input
                            v-model="editForm.price"
                            type="number"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                        <p
                            v-if="errors.price"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ errors.price }}
                        </p>
                    </div>

                    <!-- Class Type -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Class Type
                        </label>
                        <select
                            v-model="editForm.classType"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option value="Private">Private</option>
                            <option value="Group">Group</option>
                            <option value="All">All</option>
                        </select>
                        <p
                            v-if="errors.classType"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ errors.classType }}
                        </p>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Upload Image
                        </label>
                        <input
                            type="file"
                            @change="handleImageUpdate"
                            class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                            accept="image/png, image/jpeg, image/webp"
                        />
                        <p
                            v-if="errors.image"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ errors.image }}
                        </p>
                        <div
                            v-if="editPreviewImage"
                            class="mt-2 flex justify-center"
                        >
                            <img
                                :src="editPreviewImage"
                                alt="Preview"
                                class="w-32 h-32 rounded-md shadow-md"
                            />
                        </div>
                    </div>

                    <!-- Update Button -->
                    <button
                        type="submit"
                        class="w-full bg-lime-700 hover:bg-lime-800 text-white font-medium px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
                    >
                        Update Session
                    </button>

                    <!-- Close Modal Button -->
                    <button
                        @click="closeEditModal"
                        type="button"
                        class="mt-2 w-full text-red-600 hover:text-red-800 text-sm"
                    >
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faEdit, faTrashAlt } from "@fortawesome/free-solid-svg-icons";

library.add(faEdit, faTrashAlt);

// Sample Sessions Data (Replace with API call)
const sessions = ref([
    {
        id: 1,
        title: "Basic English Class",
        price: "12,000",
        classType: "Private",
        image: "/images/English-3.jpeg",
    },
    {
        id: 2,
        title: "Intermediate Group Class",
        price: "8,000",
        classType: "Group",
        image: "/images/English-4.webp",
    },
]);

const isEditing = ref(false);
const editIndex = ref(null);
const editPreviewImage = ref(null);

const editForm = ref({
    id: null,
    title: "",
    price: "",
    classType: "",
    image: null,
});

const errors = ref({});

// Open Edit Modal
const openEditModal = (index) => {
    const session = sessions.value[index];
    editForm.value = { ...session };
    editIndex.value = index;
    editPreviewImage.value = session.image;
    isEditing.value = true;
};

// Close Edit Modal
const closeEditModal = () => {
    isEditing.value = false;
};

// Handle Image Update
const handleImageUpdate = (event) => {
    const file = event.target.files[0];

    if (!file) {
        errors.value.image = "Image is required.";
        return;
    }

    const validTypes = ["image/jpeg", "image/png", "image/webp"];
    if (!validTypes.includes(file.type)) {
        errors.value.image = "Only JPEG, PNG, and WebP formats are allowed.";
        editForm.value.image = null;
        editPreviewImage.value = null;
        return;
    }

    errors.value.image = "";
    editForm.value.image = file;
    editPreviewImage.value = URL.createObjectURL(file);
};

// Update Session
const updateSession = () => {
    errors.value = {};

    if (!editForm.value.title) {
        errors.value.title = "Title is required.";
    }
    if (!editForm.value.price) {
        errors.value.price = "Price is required.";
    } else if (editForm.value.price <= 0) {
        errors.value.price = "Price must be greater than zero.";
    }
    if (!editForm.value.classType) {
        errors.value.classType = "Class type is required.";
    }

    if (Object.keys(errors.value).length === 0) {
        sessions.value[editIndex.value] = { ...editForm.value };
        isEditing.value = false;
    }
};

// Delete Session
const deleteSession = (id) => {
    if (confirm("Are you sure you want to delete this session?")) {
        sessions.value = sessions.value.filter((session) => session.id !== id);
    }
};
</script>

<style scoped>
/* Make the modal and content responsive */
@media (max-width: 640px) {
    .max-w-5xl {
        max-width: 100%;
        padding: 16px;
    }
}
</style>
