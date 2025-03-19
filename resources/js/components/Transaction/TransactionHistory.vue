<script setup>
    import Axios from 'axios';
    import { onMounted, ref } from 'vue';
    import { storeToRefs } from 'pinia';

    import { useAppStore } from '@/store/useAppStore';

    import ChangeComission from '@/components/Transaction/ChangeComission.vue'

    const appStore = useAppStore();
    const { commission } = storeToRefs(appStore);

    const CurrentBalance = ref(null);
    const payouts = ref([]);

    const showAddUserModal = ref(false);
    const withdrawAmount = ref(null);

    function fetchTransferTransactions() {
        Axios
            .get('/api/get-transfer-history')
            .then(res => {
                payouts.value = res.data.data
            })
    }

    function getCurrentBalance() {
        Axios
            .get('/api/get-balance')
            .then(res => {
                CurrentBalance.value = res.data.data
            })
    }
    
    
    const calculatePayout = (amount) => {
        return (amount * (commission.value / 100)).toFixed(2)
    }
 
    const toast = ref({
        show: false,
        message: '',
    })

    const showToast = (message) => {
        toast.value.message = message
        toast.value.show = true
        setTimeout(() => {
            toast.value.show = false
        }, 3000)
    }

    function onWithdraw() {
         Axios
            .post('/api/transfer', {amount:withdrawAmount.value})
            .then(res => {
                showToast(res.data.message)
            })
    }

    function closeAddUserModal() {
        showAddUserModal.value = ! showAddUserModal.value;
        withdrawAmount.value = null;
    }

    onMounted(()=>{
        fetchTransferTransactions();
        getCurrentBalance();
    });
</script>

<template>
    <transition name="fade">
        <div
            v-if="showAddUserModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm z-50" >
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8 transform transition-all duration-300 ease-in-out scale-100">
                <div class="flex justify-between items-center  pb-3 mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Transfer to bank account</h3>
                </div>
                <form >
                    <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2" for="amount">Amount</label>
                    <input
                        id="amount"
                        v-model="withdrawAmount"
                        type="number"
                        placeholder="amount in ETB"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500"
                        required />
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button
                            type="button"
                            @click="closeAddUserModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors duration-200" >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="onWithdraw()"
                            class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors duration-200" >
                            Withdraw
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>

    <div class="grid grid-cols-2 gap-4 my-4">
        <div class=" bg-white p-6 rounded shadow  flex justify-between">
            <div class="text-sm text-gray-500">
                <h1>Current Balance</h1>
                <h1 class="font-bold text-xl">ETB {{ CurrentBalance }}</h1>
                <p class="pt-4">Earning updates when use purches producs</p>
            </div>
            <div class="flex h-full items-center justify-center mt-5">
                <button 
                    @click="closeAddUserModal()"
                class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors duration-200" >
                    Withdraw
                </button>
            </div>
        </div>
        <div>
            <ChangeComission/>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow ">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-4">Transaction History</h2>
        <div class="w-full  overflow-x-auto scrollbar">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">transaction Id</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform Fee </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="payout in payouts" :key="payout.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.reference }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.status }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.currency }} {{ payout.amount }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.currency }} {{ calculatePayout(payout.amount) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.date }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>