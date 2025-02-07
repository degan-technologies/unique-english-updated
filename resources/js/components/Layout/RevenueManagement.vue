<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Header -->
      <header class="bg-white shadow p-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Payment & Revenue Management</h1>
          <p class="text-sm text-gray-500">Track and manage earnings from courses, books, and virtual classes</p>
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
        <!-- Sidebar (Optional) -->
        <aside class="w-64 bg-white shadow h-screen hidden md:block">
          <nav class="p-4">
            <ul>
              <li class="mb-2">
                <a href="#" class="text-gray-700 hover:text-blue-500">Overview</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-gray-700 hover:text-blue-500">Transactions</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-gray-700 hover:text-blue-500">Payouts</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-gray-700 hover:text-blue-500">Commission Settings</a>
              </li>
            </ul>
          </nav>
        </aside>
  
        <!-- Main Content Area -->
        <main class="flex-1 p-6">
          <!-- Breadcrumb Navigation -->
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
  
          <!-- Tab Navigation -->
          <div class="mb-6">
            <nav class="flex border-b">
              <button
                :class="tabClasses('overview')"
                @click="activeTab = 'overview'"
                class="px-4 py-2 font-medium focus:outline-none"
              >
                Overview
              </button>
              <button
                :class="tabClasses('transactions')"
                @click="activeTab = 'transactions'"
                class="px-4 py-2 font-medium focus:outline-none"
              >
                Transactions
              </button>
              <button
                :class="tabClasses('payouts')"
                @click="activeTab = 'payouts'"
                class="px-4 py-2 font-medium focus:outline-none"
              >
                Payouts
              </button>
            </nav>
          </div>
  
          <!-- Tab Content -->
          <div>
            <!-- Overview Tab -->
            <section v-if="activeTab === 'overview'">
              <!-- Summary Revenue Widgets -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Course Sales -->
                <div class="bg-white p-4 rounded shadow">
                  <div class="flex items-center">
                    <div class="bg-blue-500 text-white p-3 rounded-full mr-3">
                      <!-- Icon (Replace with your favorite icon library) -->
                      <i class="fas fa-book-reader"></i>
                    </div>
                    <div>
                      <h3 class="text-gray-700 text-lg font-medium">Course Sales</h3>
                      <p class="text-gray-500 text-sm">${{ revenue.courseSales }}</p>
                    </div>
                  </div>
                </div>
                <!-- Book Sales -->
                <div class="bg-white p-4 rounded shadow">
                  <div class="flex items-center">
                    <div class="bg-green-500 text-white p-3 rounded-full mr-3">
                      <i class="fas fa-book"></i>
                    </div>
                    <div>
                      <h3 class="text-gray-700 text-lg font-medium">Book Sales</h3>
                      <p class="text-gray-500 text-sm">${{ revenue.bookSales }}</p>
                    </div>
                  </div>
                </div>
                <!-- Virtual Classes -->
                <div class="bg-white p-4 rounded shadow md:col-span-2">
                  <div class="flex items-center">
                    <div class="bg-purple-500 text-white p-3 rounded-full mr-3">
                      <i class="fas fa-video"></i>
                    </div>
                    <div>
                      <h3 class="text-gray-700 text-lg font-medium">Virtual Classes</h3>
                      <p class="text-gray-500 text-sm">
                        1 Month: ${{ revenue.virtualClasses.oneMonth }} <br />
                        3 Months: ${{ revenue.virtualClasses.threeMonths }} <br />
                        6 Months: ${{ revenue.virtualClasses.sixMonths }}
                      </p>
                    </div>
                  </div>
                </div>
                <!-- Overall Revenue -->
                <div class="bg-white p-4 rounded shadow">
                  <div class="flex items-center">
                    <div class="bg-indigo-500 text-white p-3 rounded-full mr-3">
                      <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                      <h3 class="text-gray-700 text-lg font-medium">Overall Revenue</h3>
                      <p class="text-gray-500 text-sm">${{ overallRevenue }}</p>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Earnings Forecast Chart -->
              <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Earnings Forecast</h2>
                <canvas id="earningsChart"></canvas>
              </div>
            </section>
  
            <!-- Transactions Tab -->
            <section v-if="activeTab === 'transactions'">
              <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Transaction List</h2>
                <div class="flex justify-between items-center mb-4">
                  <div>
                    <select v-model="filters.status" class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="">All Status</option>
                      <option value="completed">Completed</option>
                      <option value="pending">Pending</option>
                      <option value="refund">Refunded</option>
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
                    <button class="bg-blue-500 text-white px-4 py-1 rounded ml-2" @click="applyFilters">
                      Filter
                    </button>
                  </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                      v-for="transaction in filteredTransactions"
                      :key="transaction.id"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.date }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.type }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ transaction.amount }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span
                          :class="{
                            'text-green-500': transaction.status === 'completed',
                            'text-yellow-500': transaction.status === 'pending',
                            'text-red-500': transaction.status === 'refund'
                          }"
                        >
                          {{ transaction.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button class="text-blue-500 hover:underline" @click="processRefund(transaction)">
                          Refund
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>
  
            <!-- Payouts Tab -->
            <section v-if="activeTab === 'payouts'">
              <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Instructor Payouts</h2>
                <div class="mb-4">
                  <label class="block text-gray-700 mb-2">Platform Commission (%)</label>
                  <input
                    type="number"
                    v-model="commission"
                    class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    min="0"
                    max="100"
                  />
                  <button class="bg-blue-500 text-white px-4 py-1 rounded ml-2" @click="updateCommission">
                    Update
                  </button>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Earnings</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Virtual Earnings</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Payout</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="payout in payouts" :key="payout.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ payout.instructor }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ payout.courseEarnings }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ payout.virtualEarnings }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${{ calculatePayout(payout) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button class="bg-green-500 text-white px-3 py-1 rounded" @click="initiateWithdrawal(payout)">
                          Withdraw
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
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
  
  <script>
  import { ref, onMounted, computed } from 'vue'
  import Chart from 'chart.js/auto'
  
  export default {
    name: 'RevenueManagement',
    setup() {
      const activeTab = ref('overview')
  
      // Example revenue data
      const revenue = ref({
        courseSales: 5000,
        bookSales: 3000,
        virtualClasses: {
          oneMonth: 2000,
          threeMonths: 3500,
          sixMonths: 4500,
        },
      })
  
      const overallRevenue = computed(() => {
        return (
          revenue.value.courseSales +
          revenue.value.bookSales +
          revenue.value.virtualClasses.oneMonth +
          revenue.value.virtualClasses.threeMonths +
          revenue.value.virtualClasses.sixMonths
        )
      })
  
      // Fake transactions data
      const transactions = ref([
        { id: 1, date: '2025-01-01', type: 'Course', amount: 100, status: 'completed' },
        { id: 2, date: '2025-01-02', type: 'Book', amount: 50, status: 'pending' },
        { id: 3, date: '2025-01-03', type: 'Virtual Class', amount: 200, status: 'refund' },
        { id: 4, date: '2025-01-04', type: 'Course', amount: 150, status: 'completed' },
      ])
  
      const filters = ref({
        status: '',
        startDate: '',
        endDate: '',
      })
  
      const filteredTransactions = computed(() => {
        return transactions.value.filter((tx) => {
          let match = true
          if (filters.value.status && tx.status !== filters.value.status) match = false
          if (filters.value.startDate && tx.date < filters.value.startDate) match = false
          if (filters.value.endDate && tx.date > filters.value.endDate) match = false
          return match
        })
      })
  
      // Fake payouts data
      const payouts = ref([
        { id: 1, instructor: 'John Doe', courseEarnings: 2000, virtualEarnings: 1500 },
        { id: 2, instructor: 'Jane Smith', courseEarnings: 2500, virtualEarnings: 1800 },
      ])
  
      const commission = ref(10) // Platform commission percentage
  
      const calculatePayout = (payout) => {
        const total = payout.courseEarnings + payout.virtualEarnings
        return (total * (1 - commission.value / 100)).toFixed(2)
      }
  
      // Toast notifications
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
  
      const updateCommission = () => {
        showToast('Commission updated successfully!')
      }
  
      const initiateWithdrawal = (payout) => {
        showToast(`Withdrawal initiated for ${payout.instructor}`)
      }
  
      const processRefund = (transaction) => {
        // In a real app, confirm and process refund
        showToast(`Refund processed for transaction ${transaction.id}`)
      }
  
      const applyFilters = () => {
        showToast('Filters applied')
      }
  
      // Helper for tab button classes
      const tabClasses = (tab) =>
        activeTab.value === tab
          ? 'border-b-2 border-blue-500 text-blue-500'
          : 'text-gray-600 hover:text-blue-500'
  
      // Initialize Chart.js once the component mounts
      onMounted(() => {
        const ctx = document.getElementById('earningsChart')
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
              {
                label: 'Revenue',
                data: [
                  overallRevenue.value * 0.8,
                  overallRevenue.value * 0.85,
                  overallRevenue.value,
                  overallRevenue.value * 1.1,
                  overallRevenue.value * 1.2,
                  overallRevenue.value * 1.3,
                ],
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                fill: true,
              },
            ],
          },
          options: {
            responsive: true,
            plugins: {
              tooltip: {
                mode: 'index',
                intersect: false,
              },
            },
            interaction: {
              mode: 'nearest',
              axis: 'x',
              intersect: false,
            },
            scales: {
              y: {
                beginAtZero: true,
              },
            },
          },
        })
      })
  
      return {
        activeTab,
        revenue,
        overallRevenue,
        transactions,
        filters,
        filteredTransactions,
        payouts,
        commission,
        calculatePayout,
        updateCommission,
        initiateWithdrawal,
        processRefund,
        toast,
        applyFilters,
        tabClasses,
      }
    },
  }
  </script>
  
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
  