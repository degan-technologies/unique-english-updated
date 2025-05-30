<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const transactions = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchTopSellers = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get('/api/top-sellers');
        transactions.value = response.data.data;
    } catch (err) {
        console.error('Error fetching top sellers:', err);
        error.value = 'Failed to load top sellers. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchTopSellers();
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                <span class="text-amber-500 text-lg font-bold">🏆</span>
                Top Performing Sellers
            </h3>
            <button @click="fetchTopSellers"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
                :disabled="isLoading">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 5" :key="i" class="animate-pulse">
                <div class="h-20 bg-gray-100 rounded-lg"></div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
            <div class="text-red-500 mb-2">⚠️ {{ error }}</div>
            <button @click="fetchTopSellers"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                Retry
            </button>
        </div>

        <!-- Empty State -->
        <div v-else-if="!transactions.length" class="text-center py-8">
            <div class="text-gray-500 mb-2">No top sellers data available</div>
        </div>

        <!-- Success State -->
        <div v-else>
            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                <div v-for="(seller, index) in transactions" :key="seller.user"
                    class="p-4 rounded-lg border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/50 transition-colors cursor-pointer">
                    <!-- Rank and Profile -->
                    <div class="flex items-start gap-4 mb-3">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold"
                                :class="{
                                    'bg-amber-500': index === 0,
                                    'bg-gray-400': index === 1,
                                    'bg-amber-800': index === 2,
                                    'bg-gray-300': index > 2
                                }">
                                {{ index + 1 }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="text-md font-semibold text-gray-900 truncate">
                                {{ seller.first_name }} {{ seller.middle_name }}
                            </h4>
                            <p class="text-sm text-gray-500 truncate">{{ seller.email }}</p>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Sales</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ seller.transaction_count }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Revenue</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ formatCurrency(seller.amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
.scrollable-container {
    scrollbar-width: thin;
    scrollbar-color: #E0E7FF #F8FAFC;
}

.scrollable-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.scrollable-container::-webkit-scrollbar-thumb {
    background-color: #E0E7FF;
    border-radius: 20px;
}

.scrollable-container::-webkit-scrollbar-track {
    background-color: #F8FAFC;
}

/* Animation for rank badges */
@keyframes pulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }
}

.bg-amber-500 {
    animation: pulse 2s infinite;
}
</style>