<script setup>
    import Axios from 'axios'
    import { ref, onMounted, computed } from 'vue'

    import TransactionOverview from '@/components/Transaction/TransactionOverview.vue';
    import TransactionHistory from '@/components/Transaction/TransactionHistory.vue';

    const activeTab = ref('overview')
    const transactions = ref([]); 
    
    const transactionStatus = ref({
       success: 'success', 
       pending: 'pending', 
       failed: 'failed',
    })

    const filters = ref({
        status: '',
        startDate: '',
        endDate: '',
    })

    const revenue = ref({
        courseSales: 0.00,
        bookSales: 0.00,
        virtualClasses: 0.00,
        totalSell: 0,
        transactionSummary : null,
    })

    const summryLength = ref(false);

    const toast = ref({
        show: false,
        message: '',
    })

    function fetchTransactions() {
        Axios
            .get('/api/transaction', {
                params:{
                    summryLength: summryLength.value
                }
            })
            .then(res=>{
                transactions.value = res.data.data;
                revenue.value.courseSales = res.data.courseSell;
                revenue.value.bookSales = res.data.bookSell;
                revenue.value.virtualClasses = res.data.liveSell;
                revenue.value.totalSell = res.data.totalSell;
                revenue.value.transactionSummary = res.data.transactionSummary;
            })
    }

    const filteredTransactions = computed(() => {
        return transactions.value.filter(item => {
            let match = true
            if (filters.value.status && item.status !== filters.value.status) match = false
            if (filters.value.startDate && item.date < filters.value.startDate) match = false
            if (filters.value.endDate && item.date > filters.value.endDate) match = false
            return match
        })
    })
  
    onMounted(()=>{
        fetchTransactions();
    })
</script>
  

<template>
    <div class="min-h-screen overflow-hidden bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow p-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Payment & Revenue Management</h1> 
                 <nav class="text-gray-500 text-sm mb-4">
                    <ol class="list-reset flex">
                        <li>
                            <a href="#" class="hover:text-blue-500">Dashboard</a>
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li>Revenue Overview</li>
                    </ol>
                </nav>
            </div>
            <div>
                <input
                    type="text"
                    placeholder="Search..."
                    class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div> 
        </header>
    
        <div class="flex">
            <main class="flex-1 max-w-full">
                <div class="mb-6">
                    <nav class="flex border-b">
                    <button
                        :class="{
                                'border-lime-700 text-lime-700 border-b-2': activeTab === 'overview',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview',
                            }"
                        @click="activeTab = 'overview'"
                        class="px-4 py-2 font-medium focus:outline-none" >
                        Overview
                    </button>
                    <button
                        :class="{
                                'border-lime-700 text-lime-700  border-b-2': activeTab === 'transactions',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'transactions',
                            }" 
                        @click="activeTab = 'transactions'"
                        class="px-4 py-2 font-medium focus:outline-none" >
                        Transactions
                    </button>
                    <button
                        :class="{
                                'border-lime-700 text-lime-700  border-b-2': activeTab === 'payouts',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'payouts',
                            }" 
                        @click="activeTab = 'payouts'"
                        class="px-4 py-2 font-medium focus:outline-none" >
                        Payouts
                    </button>
                    </nav>
                </div>
        
                <!-- Tab Content -->
                <div>
                    <!-- Overview Tab -->
                    <section v-if="activeTab === 'overview'"> 
                        <div v-if="revenue?.transactionSummary">
                            <TransactionOverview 
                                :revenue="revenue" /> 
                        </div>             
                    </section>
        
                    <!-- Transactions Tab -->
                    <div>
                        <section  v-if="activeTab === 'transactions' ">
                            <div class="bg-white p-6 rounded shadow overflow-hidden">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">Transaction List</h2>
                                <div class="flex justify-between items-center mb-4">
                                <div>
                                    <select v-model="filters.status" class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Status</option>
                                        <option class="capitalize" :value="transactionStatus.success">{{ transactionStatus.success }}</option>
                                        <option class="capitalize" :value="transactionStatus.pending">{{ transactionStatus.pending }}</option>
                                        <option class="capitalize" :value="transactionStatus.failed">{{ transactionStatus.failed }}</option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        type="date"
                                        v-model="filters.startDate"
                                        class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    <input
                                        type="date"
                                        v-model="filters.endDate"
                                        class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2"
                                    />
                                </div>
                                </div>
                                <div class="w-full overflow-x-auto scrollbar">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trf_id</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount  ETB</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th> 
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr
                                            v-for="transaction in filteredTransactions"
                                            :key="transaction.id"
                                            class="hover:bg-gray-50" >
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction?.customer?.first_name }} {{ transaction?.customer?.middle_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.ref_key }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction?.customer?.phone }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction?.customer?.email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.date }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.type }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.amount }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span :class="transaction.color" >
                                                {{ transaction.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </section>
                    </div>
        
                    <!-- Payouts Tab -->
                    <section v-if="activeTab === 'payouts'">
                        <TransactionHistory/>
                    </section>
                </div>
            </main>
        </div>
    
        <!-- Toast Notification -->
        <transition name="fade">
            <div
            v-if="toast.show"
            class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow"
            >
            {{ toast.message }}
            </div>
        </transition>
    </div>
  </template>
  
  <style scoped>
  /* Fade transition for toast notifications */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.5s;
  }
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>
  