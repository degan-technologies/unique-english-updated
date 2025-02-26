<template>
    <div class="p-6 max-w-6xl mx-auto bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Manage Books
        </h2>

        <!-- Table of Books -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto items-center text-center">
                <thead>
                    <tr class="bg-lime-700 text-white">
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Title
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Price
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Discount
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Discounted Price
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Description
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            PDF
                        </th>
                        <th class="px-4 py-2 text-sm font-semibold text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="book in books" :key="book.id">
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{ book.title }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{ book.price }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{ book.discount }}%
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{
                                calculateDiscountedPrice(
                                    book.price,
                                    book.discount
                                )
                            }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{ book.description }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            <a
                                :href="book.pdfUrl"
                                target="_blank"
                                class="text-lime-700"
                                >Download PDF</a
                            >
                        </td>
                        <td
                            class="px-4 py-2 text-sm text-gray-700 flex items-center gap-4"
                        >
                            <button
                                @click="editBook(book)"
                                class="text-lime-700 hover:text-lime-500"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button
                                @click="deleteBook(book.id)"
                                class="text-red-600 hover:text-red-400"
                            >
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Update Book Modal -->
        <div
            v-if="isEditing"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-10 mt-3"
        >
            <div class="bg-white p-4 rounded-lg shadow-lg w-full max-w-lg">
                <h3 class="text-xl font-semibold text-lime-700 mb-4">
                    Update Book
                </h3>
                <form @submit.prevent="updateBook" class="space-y-3">
                    <div>
                        <label
                            for="title"
                            class="block text-sm font-semibold text-gray-700"
                            >Title</label
                        >
                        <input
                            v-model="editingBook.title"
                            id="title"
                            type="text"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100"
                        />
                    </div>
                    <div>
                        <label
                            for="price"
                            class="block text-sm font-semibold text-gray-700"
                            >Price</label
                        >
                        <input
                            v-model="editingBook.price"
                            id="price"
                            type="number"
                            step="0.01"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100"
                        />
                    </div>
                    <div>
                        <label
                            for="discount"
                            class="block text-sm font-semibold text-gray-700"
                            >Discount (%)</label
                        >
                        <input
                            v-model="editingBook.discount"
                            id="discount"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100"
                        />
                    </div>
                    <div>
                        <label
                            for="description"
                            class="block text-sm font-semibold text-gray-700"
                            >Description</label
                        >
                        <textarea
                            v-model="editingBook.description"
                            id="description"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100"
                        ></textarea>
                    </div>
                    <div>
                        <label
                            for="pdf"
                            class="block text-sm font-semibold text-gray-700"
                            >Upload PDF</label
                        >
                        <input
                            type="file"
                            @change="handleFileChange"
                            id="pdf"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100"
                            accept="application/pdf"
                        />
                    </div>
                    <div class="flex justify-between items-center">
                        <button
                            @click="isEditing = false"
                            type="button"
                            class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-600"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Static books data (hardcoded)
const books = ref([
    {
        id: 1,
        title: "Vue.js Complete Guide",
        price: 19.99,
        discount: 10,
        description: "A complete guide to mastering Vue.js.",
        pdfUrl: "/assets/vuejs-guide.pdf",
    },
    {
        id: 2,
        title: "Mastering JavaScript",
        price: 29.99,
        discount: 15,
        description: "Advanced JavaScript techniques and best practices.",
        pdfUrl: "/assets/js-mastering.pdf",
    },
    {
        id: 3,
        title: "Tailwind CSS Essentials",
        price: 14.99,
        discount: 5,
        description: "Learn the basics and advanced features of Tailwind CSS.",
        pdfUrl: "/assets/tailwind-essentials.pdf",
    },
]);

// Edit book modal state
const isEditing = ref(false);
const editingBook = ref({});

// Open edit modal with book data
const editBook = (book) => {
    editingBook.value = { ...book }; // Copy the book data to editingBook
    isEditing.value = true; // Show the edit modal
};

// Handle file upload change
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file && file.type === "application/pdf") {
        editingBook.value.pdfUrl = URL.createObjectURL(file); // Use the file URL temporarily
    } else {
        alert("Please upload a valid PDF.");
    }
};

// Update book information
const updateBook = () => {
    const index = books.value.findIndex(
        (book) => book.id === editingBook.value.id
    );
    if (index !== -1) {
        books.value[index] = { ...editingBook.value }; // Update the book in the books array
        isEditing.value = false; // Close the modal after update
    }
};

// Delete book
const deleteBook = (bookId) => {
    books.value = books.value.filter((book) => book.id !== bookId); // Remove the book from the array
};

// Calculate discounted price
const calculateDiscountedPrice = (price, discount) => {
    return (price - (price * discount) / 100).toFixed(2);
};
</script>

<style scoped>
/* Custom styles if needed */
</style>
