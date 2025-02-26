<template>
    <div class="max-w-5xl mx-auto p-6 md:p-8 bg-white rounded-lg shadow-md">
        <h2
            class="text-2xl md:text-3xl font-bold text-center text-lime-700 mb-6"
        >
            Manage Subscription Plans
        </h2>

        <!-- Plans Table -->
        <div v-if="plans.length" class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-lime-700 text-white">
                        <th class="p-3 text-left">Plan Name</th>
                        <th class="p-3 text-left">Price (Birr)</th>
                        <th class="p-3 text-left">Duration</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="plan in plans" :key="plan.id" class="border-b">
                        <td class="p-3">{{ plan.name }}</td>
                        <td class="p-3">{{ plan.price }}</td>
                        <td class="p-3">{{ plan.duration }}</td>
                        <td class="p-3 space-x-2">
                            <button
                                @click="editPlan(plan)"
                                class="text-lime-800 hover:text-lime-600"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button
                                @click="deletePlan(plan.id)"
                                class="text-red-800 hover:text-red-600"
                            >
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="text-center text-gray-600">No plans available.</p>

        <!-- Edit Plan Modal -->
        <div
            v-if="isEditing"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 p-4"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h3
                    class="text-xl font-semibold text-center text-lime-700 mb-4"
                >
                    Edit Plan
                </h3>

                <form @submit.prevent="updatePlan">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700"
                            >Plan Name</label
                        >
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                        <p v-if="errors.name" class="text-red-600 text-sm mt-1">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700"
                            >Price (Birr)</label
                        >
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

                    <div>
                        <label class="block text-sm font-semibold text-gray-700"
                            >Duration</label
                        >
                        <select
                            v-model="editForm.duration"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option value="1 Month">1 Month</option>
                            <option value="3 Months">3 Months</option>
                            <option value="6 Months">6 Months</option>
                        </select>
                        <p
                            v-if="errors.duration"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ errors.duration }}
                        </p>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button
                            type="button"
                            @click="isEditing = false"
                            class="px-4 py-2 bg-gray-400 text-white rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-lime-700 hover:bg-lime-800 text-white rounded-lg"
                        >
                            Update Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Sample data (Replace with API calls in real-world scenarios)
const plans = ref([
    { id: 1, name: "Monthly", price: 12000, duration: "1 Month" },
    { id: 2, name: "3 Months", price: 30000, duration: "3 Months" },
    { id: 3, name: "6 Months", price: 56000, duration: "6 Months" },
]);

const isEditing = ref(false);
const editForm = ref({ id: null, name: "", price: "", duration: "" });
const errors = ref({});

// Edit Plan
const editPlan = (plan) => {
    isEditing.value = true;
    editForm.value = { ...plan };
};

// Update Plan with Validation
const updatePlan = () => {
    errors.value = {};

    if (!editForm.value.name) {
        errors.value.name = "Plan name is required.";
    }
    if (!editForm.value.price || editForm.value.price <= 0) {
        errors.value.price = "Price must be greater than zero.";
    }
    if (!editForm.value.duration) {
        errors.value.duration = "Duration is required.";
    }

    if (Object.keys(errors.value).length === 0) {
        const index = plans.value.findIndex((p) => p.id === editForm.value.id);
        if (index !== -1) {
            plans.value[index] = { ...editForm.value };
        }
        isEditing.value = false;
    }
};

// Delete Plan with Confirmation
const deletePlan = (id) => {
    if (confirm("Are you sure you want to delete this plan?")) {
        plans.value = plans.value.filter((plan) => plan.id !== id);
    }
};
</script>

<style scoped>
/* No extra styles needed; Tailwind + FontAwesome icons handle UI */
</style>
