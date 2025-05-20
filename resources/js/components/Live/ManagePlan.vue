<script setup>
import Axios from "axios";
import { ref, onMounted, watch } from "vue";
import { useToast } from "vue-toastification";

import AddPlan from "@/components/Live/AddPlan.vue";
import Spinner from "@/components/Layout/Spinner.vue";

const toast = useToast();

const plans = ref([]);
const loading = ref(true);
const error = ref(null);
const showEditModal = ref(false);
const showDeleteConfirmation = ref(false);
const showAddPlan = ref(false);
const editPlanData = ref({
    id: null,
    name: "",
    one_to_one_price: "",
    group_price: "",
});
const deletingPlanId = ref(null);
const isUpdating = ref(false);
const isDeleting = ref(false);

const props = defineProps({
    toggleAddButton: Boolean
});

const fetchPlans = async () => {
    loading.value = true;
    try {
        const response = await Axios.get("/api/get-my-plans");
        plans.value = response.data.data;
    } catch (err) {
        error.value = "Failed to fetch plans.";
        console.error("Error fetching plans:", err);
        toast.error("Failed to fetch plans.", {
            position: "top-right",
            timeout: 3000
        });
    } finally {
        loading.value = false;
    }
};

const editPlan = (plan) => {
    editPlanData.value = { ...plan };
    showEditModal.value = true;
};

const updatePlan = async () => {
    isUpdating.value = true;
    try {
        const response = await Axios.put(`/api/plans/${editPlanData.value.id}`, editPlanData.value);
        const data = response.data.data;
        plans.value = plans.value.map(item => item.id === data.id ? data : item);
        toast.success("Plan updated successfully!", {
            position: "top-right",
            timeout: 3000
        });
        showEditModal.value = false;
    } catch (error) {
        toast.error("Failed to update plan. Please try again.", {
            position: "top-right",
            timeout: 3000
        });
    } finally {
        isUpdating.value = false;
    }
};

const confirmDeletePlan = (id) => {
    deletingPlanId.value = id;
    showDeleteConfirmation.value = true;
};

const deletePlan = async (id) => {
    isDeleting.value = true;
    try {
        await Axios.delete(`/api/plans/${id}`);
        plans.value = plans.value.filter(item => item.id !== id);
        toast.success("Plan deleted successfully!", {
            position: "top-right",
            timeout: 3000
        });
    } catch (error) {
        toast.error("Failed to delete plan. Please try again.", {
            position: "top-right",
            timeout: 3000
        });
    } finally {
        isDeleting.value = false;
        showDeleteConfirmation.value = false;
    }
};

watch(() => props.toggleAddButton, (newValue) => {
    if (newValue) {
        showAddPlan.value = true;
    }
});

onMounted(() => {
    fetchPlans();
});
</script>

<template>
    <div class="mx-auto">
        <!-- Empty State -->
        <div v-if="!loading && plans.length === 0" class="text-center py-12">
            <div class="mx-auto w-24 h-24 text-gray-400 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No plans yet</h3>
            <p class="text-gray-500 mb-6">Get started by creating your first pricing plan</p>
            <button @click="showAddPlan = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Add Plan
            </button>
        </div>

        <!-- Plans Table -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="loading" class="flex justify-center items-center h-64">
                <Spinner />
            </div>
            <div v-else>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    One to One Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Group Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="plan in plans" :key="plan.id"
                                class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ plan.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ plan.one_to_one_price }} ETB</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ plan.group_price }} ETB</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-3">
                                        <button @click="editPlan(plan)"
                                            class="text-lime-600 hover:text-lime-900 transition-colors duration-200"
                                            title="Edit plan">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDeletePlan(plan.id)"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                            title="Delete plan">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Plan Modal -->
        <AddPlan v-if="showAddPlan" @close="showAddPlan = false" @plan-added="fetchPlans" class="fixed inset-0 z-50" />

        <!-- Edit Plan Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-lime-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-lime-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Plan</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="plan-name"
                                            class="block text-sm font-medium text-gray-700">Name</label>
                                        <input id="plan-name" v-model="editPlanData.name" type="text"
                                            class="mt-1 focus:ring-lime-500 focus:border-lime-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Prices (ETB)</label>
                                        <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                                            <div>
                                                <label for="one-to-one-price"
                                                    class="block text-sm font-medium text-gray-700">One to One</label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    </div>
                                                    <input id="one-to-one-price" v-model="editPlanData.one_to_one_price"
                                                        type="number"
                                                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                                                        placeholder="0.00" />
                                                </div>
                                            </div>
                                            <div>
                                                <label for="group-price"
                                                    class="block text-sm font-medium text-gray-700">Group</label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    </div>
                                                    <input id="group-price" v-model="editPlanData.group_price"
                                                        type="number"
                                                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                                                        placeholder="0.00" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="updatePlan" :disabled="isUpdating"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-lime-600 text-base font-medium text-white hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                            <span v-if="!isUpdating">Save Changes</span>
                            <span v-else class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                        <button type="button" @click="showEditModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteConfirmation" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete plan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this plan? This
                                        action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="deletePlan(deletingPlanId)" :disabled="isDeleting"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                            <span v-if="!isDeleting">Delete</span>
                            <span v-else class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                        <button type="button" @click="showDeleteConfirmation = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions for hover effects */
button,
input,
select,
textarea {
    transition: all 0.2s ease-in-out;
}

/* Better focus states */
input:focus,
select:focus,
textarea:focus {
    @apply ring-2 ring-lime-500 border-lime-500;
    outline: none;
}

/* Table row hover effect */
tr:hover {
    @apply bg-gray-50;
}

/* Modal enter/leave transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

/* Spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>