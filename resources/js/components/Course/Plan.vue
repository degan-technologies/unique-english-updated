<script setup>
import { ref, onMounted } from "vue";
import axios from "axios"; // Import Axios

// State to hold plans
const plans = ref([]);
const loading = ref(true);
const error = ref(null);

// Fetch plans from API using Axios
const fetchPlans = async () => {
    try {
        // Make GET request using Axios
        const response = await axios.get("/api/plans");

        // Log the raw response
        console.log("API Response:", response);

        // Store the data
        plans.value = response.data.data;
    } catch (err) {
        console.error("Error fetching plans:", err);
        error.value = "Failed to fetch plans.";
    } finally {
        loading.value = false;
    }
};

// Join plan function (dummy for now)
const joinPlan = (plan) => {
    alert(`You have joined the ${plan.name} plan for ${plan.price} Birr.`);
};

// Fetch data on component mount
onMounted(fetchPlans);
</script>

<template>
    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-lime-700 mb-6">Join a Plan</h1>

        <!-- Loading State -->
        <div v-if="loading" class="text-gray-500 text-center">
            Loading plans...
        </div>

        <!-- Error Message -->
        <div v-if="error" class="text-red-500 text-center">{{ error }}</div>

        <!-- Plan Buttons -->
        <div v-else-if="plans.length" class="flex flex-col gap-4">
            <button
                v-for="plan in plans"
                :key="plan.id"
                @click="joinPlan(plan)"
                class="w-full py-3 px-6 bg-lime-700 text-white font-semibold text-lg rounded-lg hover:bg-lime-800 transition duration-200"
            >
                Join {{ plan.name }} - {{ plan.price }} Birr
            </button>
        </div>

        <!-- No Plans Found -->
        <div v-else class="text-gray-500 text-center">No plans available.</div>
    </div>
</template>

<style scoped>
/* Additional styling if needed */
</style>
