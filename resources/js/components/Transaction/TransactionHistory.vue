<script setup>
import Axios from 'axios';
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/store/useAppStore';
import ChangeComission from '@/components/Transaction/ChangeComission.vue';

const appStore = useAppStore();
const { commission } = storeToRefs(appStore);
 
const CurrentBalance = ref(null);
const payouts = ref([]);
const isLoading = ref(false);
const isWithdrawing = ref(false);
 
const showWithdrawModal = ref(false);
const withdrawAmount = ref(null);
const withdrawError = ref(null);
 
const toast = ref({
    show: false,
    message: '',
    type: 'success'  
});
 
const fetchTransferTransactions = async () => {
    isLoading.value = true;
    try {
        const res = await Axios.get('/api/get-transfer-history');
        payouts.value = res.data.data;
    } catch (error) {
        showToast('Failed to fetch transactions', 'error');
        console.error('Fetch error:', error);
    } finally {
        isLoading.value = false;
    }
};

// Get current balance
const getCurrentBalance = async () => {
    try {
        const res = await Axios.get('/api/get-balance');
        CurrentBalance.value = res.data.data;
    } catch (error) {
        showToast('Failed to fetch balance', 'error');
        console.error('Balance error:', error);
    }
};
 
const validateWithdrawal = () => {
    withdrawError.value = null;

    if (!withdrawAmount.value) {
        withdrawError.value = 'Amount is required';
        return false;
    }

    const amount = Number(withdrawAmount.value);

    if (isNaN(amount)) {
        withdrawError.value = 'Please enter a valid number';
        return false;
    }

    if (amount <= 0) {
        withdrawError.value = 'Amount must be greater than 0';
        return false;
    }

    if (CurrentBalance.value !== null && amount > CurrentBalance.value) {
        withdrawError.value = 'Insufficient balance';
        return false;
    }

    return true;
};

// Handle withdrawal
const onWithdraw = async () => {
    if (!validateWithdrawal()) return;

    isWithdrawing.value = true;
    try {
        const res = await Axios.post('/api/withdrawals', {
            amount: withdrawAmount.value
        });
        showToast(res.data.message, 'success');
        closeWithdrawModal();
        // Refresh data
        await getCurrentBalance();
        await fetchTransferTransactions();
    } catch (error) {
        const message = error.response?.data?.message || 'Withdrawal failed';
        showToast(message, 'error');
        console.error('Withdrawal error:', error);
    } finally {
        isWithdrawing.value = false;
    }
};

// Show toast notification
const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Modal controls
const openWithdrawModal = () => {
    showWithdrawModal.value = true;
};

const closeWithdrawModal = () => {
    showWithdrawModal.value = false;
    withdrawAmount.value = null;
    withdrawError.value = null;
};

// Initial data load
onMounted(() => {
    fetchTransferTransactions();
    getCurrentBalance();
});
</script>

<template>
    <!-- Withdrawal Modal -->
    <transition name="fade">
        <div v-if="showWithdrawModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm z-50">
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-6">
                <div class="flex justify-between items-center pb-4 mb-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-800">Transfer to Bank</h3>
                    <button @click="closeWithdrawModal" class="text-gray-500 hover:text-gray-700">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="onWithdraw">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Amount (ETB)</label>
                        <input v-model="withdrawAmount" type="number" placeholder="Enter amount"
                            class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-lime-500 focus:border-transparent"
                            :class="{ 'border-red-500': withdrawError }" />
                        <p v-if="withdrawError" class="mt-1 text-sm text-red-500">
                            {{ withdrawError }}
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeWithdrawModal"
                            class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isWithdrawing"
                            class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 disabled:opacity-70 transition">
                            {{ isWithdrawing ? 'Processing...' : 'Withdraw' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>

    <!-- Balance & Commission Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow flex justify-between items-center">
            <div>
                <h3 class="text-sm text-gray-500">Current Balance</h3>
                <p class="text-2xl font-bold mt-1">ETB {{ CurrentBalance ?? '--' }}</p>
                <p class="text-sm text-gray-400 mt-2">Earnings update on product purchases</p>
            </div>
            <button @click="openWithdrawModal"
                class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition">
                Withdraw
            </button>
        </div>

        <ChangeComission />
    </div>

    <!-- Transaction History -->
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Transaction History</h2>
            <button @click="fetchTransferTransactions"
                class="flex items-center text-sm text-lime-600 hover:text-lime-700" :disabled="isLoading">
                <span v-if="isLoading">Refreshing...</span>
                <span v-else>Refresh ↻</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deposit (ETB)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Withdrawal (ETB)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="payout in payouts" :key="payout.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.reference }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.deposits || '--' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.withdrawals || '--' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="`px-2 py-1 rounded-full text-xs font-medium ${payout.color || 'bg-gray-100 text-gray-800'}`">
                                {{ payout.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.date }}</td>
                    </tr>
                    <tr v-if="payouts.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No transactions found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toast Notification -->
    <transition name="fade">
        <div v-if="toast.show" :class="`fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg text-white ${toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`">
            {{ toast.message }}
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>