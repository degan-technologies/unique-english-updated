<template>
    <div
        class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-6"
    >
        <!-- Plan List Container -->
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-4xl">
            <h1
                class="text-3xl font-extrabold text-lime-700 mb-6 text-center flex items-center gap-2 justify-center"
            >
                Manage Plans
            </h1>

            <!-- Loading Spinner -->
            <Spinner v-if="loading" />

            <!-- Plans Table -->
            <div v-else>
                <table class="min-w-full table-auto border-collapse border">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Price</th>
                            <th class="px-4 py-3 text-left">Duration</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="plan in plans"
                            :key="plan.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">{{ plan.name }}</td>
                            <td class="px-4 py-3">{{ plan.price }} Birr</td>
                            <td class="px-4 py-3">
                                {{ plan.duration }}
                            </td>
                            <td class="px-4 py-3 flex justify-center gap-3">
                                <button
                                    @click="editPlan(plan)"
                                    class="text-lime-700 hover:text-lime-600"
                                >
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button
                                    @click="confirmDeletePlan(plan.id)"
                                    class="text-red-600 hover:text-red-500"
                                >
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Plan Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2
                    class="text-xl font-semibold text-lime-700 mb-4 flex items-center gap-2"
                >
                    <i class="fas fa-pen"></i> Edit Plan
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium"
                            >Name</label
                        >
                        <input
                            v-model="editPlanData.name"
                            type="text"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium"
                            >Price (in Birr)</label
                        >
                        <input
                            v-model="editPlanData.price"
                            type="number"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium"
                            >Duration (in months)</label
                        >
                        <input
                            v-model="editPlanData.duration"
                            type="number"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        />
                    </div>

                    <div class="flex justify-between mt-4">
                        <button
                            @click="updatePlan"
                            class="bg-lime-700 text-white px-4 py-2 rounded-lg hover:bg-lime-600 flex items-center gap-2"
                        >
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button
                            @click="showEditModal = false"
                            class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Overlay -->
        <div
            v-if="showDeleteConfirmation"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
                <h2
                    class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete this plan?
                </p>

                <div class="flex justify-between">
                    <button
                        @click="deletePlan(deletingPlanId)"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button
                        @click="showDeleteConfirmation = false"
                        class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Spinner from "../Layout/Spinner.vue";
import { useToast } from "vue-toastification"; // Import Vue Toastification

const toast = useToast(); // Initialize toast

const plans = ref([]);
const loading = ref(true);
const error = ref(null);
const showEditModal = ref(false);
const showDeleteConfirmation = ref(false);
const editPlanData = ref({
    id: null,
    name: "",
    price: null,
    duration: null,
});
const deletingPlanId = ref(null);

// Fetch all plans from API
const fetchPlans = async () => {
    loading.value = true;
    try {
        const response = await axios.get("/api/plans");
        plans.value = response.data.data;
    } catch (err) {
        error.value = "Failed to fetch plans.";
        console.error("Error fetching plans:", err);
        toast.error("Failed to fetch plans.", { position: "top-right" });
    } finally {
        loading.value = false;
    }
};

// Edit plan data
const editPlan = (plan) => {
    editPlanData.value = { ...plan };
    showEditModal.value = true;
};

// Update plan
const updatePlan = async () => {
    // Ensure duration is an integer
    editPlanData.value.duration = parseInt(editPlanData.value.duration);

    if (isNaN(editPlanData.value.duration)) {
        toast.error("Duration must be a valid number.", {
            position: "top-right",
        });
        return;
    }

    try {
        await axios.put(
            `/api/plans/${editPlanData.value.id}`,
            editPlanData.value
        );
        toast.success("Plan updated successfully!", { position: "top-right" });
        showEditModal.value = false;
        fetchPlans(); // Refresh the plan list after update
    } catch (err) {
        console.error("Error updating plan:", err);
        toast.error("Failed to update plan.", { position: "top-right" });
    }
};

// Confirm delete plan
const confirmDeletePlan = (id) => {
    deletingPlanId.value = id;
    showDeleteConfirmation.value = true;
};

// Delete plan
const deletePlan = async (id) => {
    try {
        await axios.delete(`/api/plans/${id}`);
        toast.success("Plan deleted successfully!", { position: "top-right" });
        showDeleteConfirmation.value = false;
        fetchPlans(); // Refresh the plan list after deletion
    } catch (err) {
        console.error("Error deleting plan:", err);
        toast.error("Failed to delete plan.", { position: "top-right" });
    }
};

// Fetch plans on component mount
onMounted(fetchPlans);
</script>

<style scoped>
/* Smooth transition effects */
input {
    transition: all 0.3s ease-in-out;
}

input:focus {
    border-color: #84cc16;
    box-shadow: 0 0 10px rgba(132, 204, 22, 0.2);
}
</style>
