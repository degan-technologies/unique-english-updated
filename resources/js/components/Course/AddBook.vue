<template>
    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Add New Book
        </h2>

        <!-- Book Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Book Title -->
            <div>
                <label
                    for="title"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Book Title
                </label>
                <input
                    v-model="book.title"
                    id="title"
                    type="text"
                    placeholder="Enter book title"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    :class="{ 'border-red-500': errors.title }"
                />
                <p v-if="errors.title" class="text-red-500 text-sm mt-1">
                    {{ errors.title }}
                </p>
            </div>

            <!-- Thumbnail Image -->
            <div>
                <label
                    for="thumbnail"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Thumbnail Image
                </label>
                <input
                    type="file"
                    id="thumbnail"
                    @change="handleThumbnailChange"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    accept="image/*"
                />
                <p v-if="errors.thumbnail" class="text-red-500 text-sm mt-1">
                    {{ errors.thumbnail }}
                </p>
            </div>

            <!-- Author -->
            <div>
                <label
                    for="author"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Author
                </label>
                <input
                    v-model="book.author"
                    id="author"
                    type="text"
                    placeholder="Enter author name"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    :class="{ 'border-red-500': errors.author }"
                />
                <p v-if="errors.author" class="text-red-500 text-sm mt-1">
                    {{ errors.author }}
                </p>
            </div>

            <!-- Book Description -->
            <div>
                <label
                    for="description"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Book Description
                </label>
                <textarea
                    v-model="book.description"
                    id="description"
                    placeholder="Enter book description"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="text-red-500 text-sm mt-1">
                    {{ errors.description }}
                </p>
            </div>

            <!-- Original Price -->
            <div>
                <label
                    for="price"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Original Price (Birr)
                </label>
                <input
                    v-model="book.price"
                    id="price"
                    type="number"
                    step="0.01"
                    placeholder="Enter original price"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    :class="{ 'border-red-500': errors.price }"
                />
                <p v-if="errors.price" class="text-red-500 text-sm mt-1">
                    {{ errors.price }}
                </p>
            </div>

            <!-- Discount Percentage -->
            <div>
                <label
                    for="discount"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Discount Percentage (%)
                </label>
                <input
                    v-model="book.discount"
                    id="discount"
                    type="number"
                    step="1"
                    placeholder="Enter discount percentage"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                />
            </div>

            <!-- Discounted Price -->
            <div>
                <p class="text-lg font-semibold text-red-600">
                    Discounted Price: {{ discountedPrice }} Birr
                </p>
            </div>

            <!-- PDF File Upload -->
            <div>
                <label
                    for="pdf"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Upload Book PDF
                </label>
                <input
                    type="file"
                    id="pdf"
                    @change="handleFileChange"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                    accept="application/pdf"
                />
                <p v-if="errors.pdf" class="text-red-500 text-sm mt-1">
                    {{ errors.pdf }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button
                    type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-600 focus:outline-none transition-colors"
                >
                    Add Book
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Book data
const book = ref({
    title: "",
    thumbnail: null,
    author: "",
    description: "",
    price: null,
    discount: 0,
    pdf: null, // PDF file
});

// Errors state
const errors = ref({
    title: "",
    thumbnail: "",
    author: "",
    description: "",
    price: "",
    pdf: "",
});

// Computed property to calculate discounted price
const discountedPrice = computed(() => {
    if (!book.value.price || !book.value.discount) {
        return book.value.price || 0;
    }
    return (book.value.price * (100 - book.value.discount)) / 100;
});

// Validate form inputs
const validateForm = () => {
    let isValid = true;
    errors.value = {};

    if (!book.value.title) {
        errors.value.title = "Book title is required";
        isValid = false;
    }
    if (!book.value.thumbnail) {
        errors.value.thumbnail = "Thumbnail image is required";
        isValid = false;
    }
    if (!book.value.author) {
        errors.value.author = "Author is required";
        isValid = false;
    }
    if (!book.value.description) {
        errors.value.description = "Book description is required";
        isValid = false;
    }
    if (!book.value.price || book.value.price <= 0) {
        errors.value.price = "Price must be a positive number";
        isValid = false;
    }
    if (!book.value.pdf) {
        errors.value.pdf = "PDF file is required";
        isValid = false;
    }

    return isValid;
};

// Handle form submission
const handleSubmit = () => {
    if (validateForm()) {
        console.log("Book Added:", book.value);
        book.value = {
            title: "",
            thumbnail: null,
            author: "",
            description: "",
            price: null,
            discount: 0,
            pdf: null,
        };
    }
};

// Handle file selection
const handleThumbnailChange = (event) => {
    book.value.thumbnail = event.target.files[0];
};

const handleFileChange = (event) => {
    book.value.pdf = event.target.files[0];
};
</script>
