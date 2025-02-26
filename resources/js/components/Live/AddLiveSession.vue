<template>
    <div class="max-w-3xl mx-auto p-6 md:p-8 bg-white rounded-lg shadow-md">
        <h2
            class="text-2xl md:text-3xl font-bold text-center text-lime-700 mb-6"
        >
            Add Live Session
        </h2>

        <form @submit.prevent="addSession" class="grid grid-cols-1 gap-4">
            <!-- Title -->
            <div>
                <label
                    class="block text-sm md:text-base font-semibold text-gray-700"
                    >Title</label
                >
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                    placeholder="Enter session title"
                />
                <p v-if="errors.title" class="text-red-600 text-sm mt-1">
                    {{ errors.title }}
                </p>
            </div>

            <!-- Price -->
            <div>
                <label
                    class="block text-sm md:text-base font-semibold text-gray-700"
                    >Price (Birr)</label
                >
                <input
                    v-model="form.price"
                    type="number"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                    placeholder="Enter session price"
                />
                <p v-if="errors.price" class="text-red-600 text-sm mt-1">
                    {{ errors.price }}
                </p>
            </div>

            <!-- Class Type -->
            <div>
                <label
                    class="block text-sm md:text-base font-semibold text-gray-700"
                    >Class Type</label
                >
                <select
                    v-model="form.classType"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
                    <option value="" disabled>Select Class Type</option>
                    <option value="Private">Private</option>
                    <option value="Group">Group</option>
                    <option value="All">All</option>
                </select>
                <p v-if="errors.classType" class="text-red-600 text-sm mt-1">
                    {{ errors.classType }}
                </p>
            </div>

            <!-- Image Upload -->
            <div>
                <label
                    class="block text-sm md:text-base font-semibold text-gray-700"
                    >Upload Image</label
                >
                <input
                    type="file"
                    @change="handleImageUpload"
                    class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                    accept="image/png, image/jpeg, image/webp"
                />
                <p v-if="errors.image" class="text-red-600 text-sm mt-1">
                    {{ errors.image }}
                </p>
                <div v-if="previewImage" class="mt-2 flex justify-center">
                    <img
                        :src="previewImage"
                        alt="Preview"
                        class="w-32 h-32 rounded-md shadow-md"
                    />
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-lime-700 hover:bg-lime-800 text-white font-medium px-4 py-3 rounded-lg shadow-lg transition-all duration-300 text-center text-lg md:text-xl"
            >
                Add Session
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";

const form = ref({
    title: "",
    price: "",
    classType: "",
    image: null,
});

const previewImage = ref(null);
const errors = ref({});

// Handle Image Upload
const handleImageUpload = (event) => {
    const file = event.target.files[0];

    if (!file) {
        errors.value.image = "Image is required.";
        return;
    }

    const validTypes = ["image/jpeg", "image/png", "image/webp"];
    if (!validTypes.includes(file.type)) {
        errors.value.image = "Only JPEG, PNG, and WebP formats are allowed.";
        form.value.image = null;
        previewImage.value = null;
        return;
    }

    errors.value.image = "";
    form.value.image = file;
    previewImage.value = URL.createObjectURL(file);
};

// Form Submission
const addSession = () => {
    errors.value = {}; // Reset errors

    if (!form.value.title) {
        errors.value.title = "Title is required.";
    }
    if (!form.value.price) {
        errors.value.price = "Price is required.";
    } else if (form.value.price <= 0) {
        errors.value.price = "Price must be greater than zero.";
    }
    if (!form.value.classType) {
        errors.value.classType = "Class type is required.";
    }
    if (!form.value.image) {
        errors.value.image = "Image is required.";
    }

    // If no errors, proceed with form submission
    if (Object.keys(errors.value).length === 0) {
        alert("Live session added successfully!");
        console.log("Session Data:", form.value);
    }
};
</script>
