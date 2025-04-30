<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
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

const fetchPlans = async () => {
    loading.value = true;
    try {
        const response = await Axios.get("/api/plans");
        plans.value = response.data.data;
    } catch (err) {
        error.value = "Failed to fetch plans.";
        console.error("Error fetching plans:", err);
        toast.error("Failed to fetch plans.", { position: "top-right" });
    } finally {
        loading.value = false;
    }
};

const editPlan = (plan) => {
    editPlanData.value = { ...plan };
    showEditModal.value = true;
};

const updatePlan = async () => {
    await Axios
        .put(`/api/plans/${editPlanData.value.id}`, editPlanData.value)
        .then(res => {
            const data = res.data.data;
            plans.value = plans.value.map(item => item.id === data.id ? data : item);
            toast.success("Plan updated successfully!", { position: "top-right" });
        })
};

const confirmDeletePlan = (id) => {
    deletingPlanId.value = id;
    showDeleteConfirmation.value = true;
};

const deletePlan = async (id) => {

    await Axios
        .delete(`/api/plans/${id}`)
        .then(res => {
            plans.value = plans.value.filter(item => item.id !== id);
            toast.success("Plan deleted successfully!", { position: "top-right" });
            showDeleteConfirmation.value = false;
        }).catch(err => {
            toast.error("Failed to delete plan.", { position: "top-right" });
        })
};

onMounted(() => {
    fetchPlans();
});
</script>

<template>
    <div class="max-w-6xl mx-auto ">
        <div class="flex justify-end my-4 mr-2">
            <button @click="showAddPlan = true" class="bg-lime-700 text-white px-4 py-2 rounded-lg hover:bg-lime-600">
                Add Plan
            </button>
        </div>

        <div class="bg-white rounded-lg ">
            <div v-if="loading" class="flex justify-center items-center h-64">
                <Spinner />
            </div>
            <div v-else class="overflow-x-auto scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-white rounded-t-lg">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">One to One Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Group Price</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="plan in plans" :key="plan.id" class="border-b">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ plan.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ plan.one_to_one_price }} ETB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ plan.group_price }} ETB</td> 
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex justify-center gap-3">
                                <button @click="editPlan(plan)" class="text-lime-700 hover:text-lime-600">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button @click="confirmDeletePlan(plan.id)" class="text-red-600 hover:text-red-500">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="showAddPlan" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center z-50">
            <AddPlan @close="showAddPlan = false" />
        </div>
        <!-- Edit Plan Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl">
                <h2 class="text-xl font-semibold text-lime-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-pen"></i> Edit Plan
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium">Name</label>
                        <input v-model="editPlanData.name" type="text"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" />
                    </div>

                    <label class="block text-gray-700 font-medium">Price <span class="font-bold">ETB</span></label>
                    <div class="grid grid-cols-1  sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium">One to One</label>
                            <input v-model="editPlanData.one_to_one_price" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter one to one price" />
                            <p v-if="errors?.one_to_one_price" class="text-red-600 text-sm mt-1">{{ errors?.one_to_one_price }}</p>
                        </div> 
                        <div>
                            <label class="block text-gray-700 font-medium">Group</label>
                            <input v-model="editPlanData.group_price" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter group price" />
                            <p v-if="errors?.group_price" class="text-red-600 text-sm mt-1">{{ errors?.group_price }}</p>
                        </div> 
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button @click="updatePlan"
                            class="bg-lime-700 text-white px-4 py-2 rounded-lg hover:bg-lime-600 flex items-center gap-2">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button @click="showEditModal = false"
                            class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Overlay -->
        <div v-if="showDeleteConfirmation"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center  animate-fadeIn z-50">
            <div class="bg-white p-6 rounded-lg w-80 text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete this plan?
                </p>
                <div class="flex justify-center gap-4">
                    <button @click="showDeleteConfirmation = false"
                        class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">
                        Cancel
                    </button>
                    <button @click="deletePlan(deletingPlanId)"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2">
                        <i class="fas fa-trash"></i> Delete
                    </button>

                </div>
            </div>
        </div>

        <!-- Add Plan Overlay -->

    </div>
</template>

<style scoped>
input {
    transition: all 0.3s ease-in-out;
}

input:focus {
    border-color: #84cc16;
    box-shadow: 0 0 10px rgba(132, 204, 22, 0.2);
}
</style>
