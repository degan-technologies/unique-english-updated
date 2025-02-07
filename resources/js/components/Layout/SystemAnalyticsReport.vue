<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Header Section -->
      <header class="bg-white shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">System Analytics & Reports</h1>
            <p class="text-gray-600 mt-2">
              Comprehensive insights into student engagement, product performance, and system health.
            </p>
          </div>
          <div class="mt-4 md:mt-0 flex space-x-4">
            <!-- Export Buttons -->
            <button @click="exportReport('csv')"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
              Export CSV
            </button>
            <button @click="exportReport('pdf')"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
              Export PDF
            </button>
            <button @click="refreshData"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
              Refresh
            </button>
          </div>
        </div>
      </header>
  
      <!-- Navigation Tabs -->
      <nav class="bg-white mt-4 shadow">
        <ul class="flex">
          <li v-for="(tab, index) in tabs" :key="index"
              @click="activeTab = tab.value"
              :class="{'border-b-2 border-blue-600 text-blue-600': activeTab === tab.value}"
              class="cursor-pointer px-6 py-4 text-gray-700 hover:text-blue-600 transition">
            {{ tab.label }}
          </li>
        </ul>
      </nav>
  
      <!-- Content Area -->
      <main class="p-6">
        <!-- Engagement Reports Tab -->
        <section v-if="activeTab === 'engagement'">
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-4 shadow rounded">
              <p class="text-sm text-gray-500">Active Users</p>
              <p class="text-2xl font-semibold text-gray-800">{{ engagementData.activeUsers }}</p>
            </div>
            <div class="bg-white p-4 shadow rounded">
              <p class="text-sm text-gray-500">Avg. Session Duration</p>
              <p class="text-2xl font-semibold text-gray-800">{{ engagementData.avgSession }} min</p>
            </div>
            <div class="bg-white p-4 shadow rounded">
              <p class="text-sm text-gray-500">Courses Completed</p>
              <p class="text-2xl font-semibold text-gray-800">{{ engagementData.coursesCompleted }}</p>
            </div>
            <div class="bg-white p-4 shadow rounded">
              <p class="text-sm text-gray-500">Survey Response Rate</p>
              <p class="text-2xl font-semibold text-gray-800">{{ engagementData.responseRate }}%</p>
            </div>
          </div>
  
          <!-- Date Range & Course Filters -->
          <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div class="flex items-center space-x-2">
              <label class="text-gray-700">Date Range:</label>
              <select v-model="filters.dateRange" class="px-3 py-2 border rounded">
                <option value="30">Last 30 days</option>
                <option value="90">Last 3 months</option>
                <option value="ytd">Year-to-Date</option>
              </select>
            </div>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
              <label class="text-gray-700">Course:</label>
              <select v-model="filters.course" class="px-3 py-2 border rounded">
                <option value="">All Courses</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
              </select>
            </div>
          </div>
  
          <!-- Interactive Charts -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Engagement Trend</h2>
              <!-- Replace with your chart component -->
              <ChartPlaceholder chartType="line" :data="engagementChartData" />
            </div>
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Peak Activity Heatmap</h2>
              <!-- Replace with your heatmap chart component -->
              <ChartPlaceholder chartType="heatmap" :data="heatmapData" />
            </div>
          </div>
        </section>
  
        <!-- Sales & Ratings Tab -->
        <section v-if="activeTab === 'salesRatings'">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Selling Courses -->
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Top-Selling Courses</h2>
              <table class="min-w-full">
                <thead>
                  <tr>
                    <th class="text-left p-2 border-b">Course</th>
                    <th class="text-left p-2 border-b">Revenue</th>
                    <th class="text-left p-2 border-b">Enrollments</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="course in topCourses" :key="course.id" class="hover:bg-gray-50 cursor-pointer"
                      @click="viewCourseDetail(course)">
                    <td class="p-2 border-b">{{ course.name }}</td>
                    <td class="p-2 border-b">${{ course.revenue }}</td>
                    <td class="p-2 border-b">{{ course.enrollments }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <!-- Best-Rated Instructors -->
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Best-Rated Instructors</h2>
              <ul>
                <li v-for="instructor in topInstructors" :key="instructor.id"
                    class="p-2 border-b hover:bg-gray-50 cursor-pointer"
                    @click="viewInstructorDetail(instructor)">
                  <div class="flex justify-between">
                    <span>{{ instructor.name }}</span>
                    <span class="flex items-center">
                      <span class="mr-1">{{ instructor.rating }}</span>
                      <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.39 2.46a1 1 0 00-.364 1.118l1.286 3.97c.3.921-.755 1.688-1.54 1.118l-3.39-2.46a1 1 0 00-1.175 0l-3.39 2.46c-.784.57-1.838-.197-1.539-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.045 9.397c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.97z" />
                      </svg>
                    </span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
  
          <!-- Sales Trend Chart -->
          <div class="bg-white p-4 shadow rounded">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Sales Trend Over Time</h2>
            <!-- Replace with your chart component -->
            <ChartPlaceholder chartType="bar" :data="salesChartData" />
          </div>
        </section>
  
        <!-- System Health Tab -->
        <section v-if="activeTab === 'systemHealth'">
          <!-- Status Indicator -->
          <div class="mb-6">
            <span class="inline-block px-4 py-2 rounded text-white"
                  :class="systemStatus === 'healthy' ? 'bg-green-600' : 'bg-red-600'">
              {{ systemStatus === 'healthy' ? 'System Healthy' : 'Critical Issues Detected' }}
            </span>
          </div>
  
          <!-- Error Logs & Downtime Analysis -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Error Logs</h2>
              <table class="min-w-full">
                <thead>
                  <tr>
                    <th class="text-left p-2 border-b">Error Type</th>
                    <th class="text-left p-2 border-b">Timestamp</th>
                    <th class="text-left p-2 border-b">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="error in errorLogs" :key="error.id" class="hover:bg-gray-50">
                    <td class="p-2 border-b">{{ error.type }}</td>
                    <td class="p-2 border-b">{{ error.timestamp }}</td>
                    <td class="p-2 border-b">
                      <span :class="error.status === 'resolved' ? 'text-green-600' : 'text-red-600'">
                        {{ error.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <div class="bg-white p-4 shadow rounded">
              <h2 class="text-lg font-semibold text-gray-800 mb-2">Downtime Analysis</h2>
              <!-- Replace with your downtime chart -->
              <ChartPlaceholder chartType="line" :data="downtimeData" />
            </div>
          </div>
        </section>
      </main>
  
      <!-- Optional Sidebar for Additional Modules -->
      <aside class="fixed top-20 right-0 w-64 bg-white shadow p-4 hidden md:block">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Access</h2>
        <ul class="space-y-3">
          <li class="cursor-pointer text-gray-700 hover:text-blue-600" @click="activeTab = 'engagement'">Engagement Reports</li>
          <li class="cursor-pointer text-gray-700 hover:text-blue-600" @click="activeTab = 'salesRatings'">Sales & Ratings</li>
          <li class="cursor-pointer text-gray-700 hover:text-blue-600" @click="activeTab = 'systemHealth'">Error Logs</li>
          <li class="cursor-pointer text-gray-700 hover:text-blue-600" @click="openExportModal">Export Data</li>
        </ul>
      </aside>
  
      <!-- Export Modal (example of scheduling/configuring exports) -->
      <transition name="fade">
        <div v-if="showExportModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
          <div class="bg-white rounded shadow-lg p-6 w-96">
            <h2 class="text-xl font-semibold mb-4">Export Report</h2>
            <div class="mb-4">
              <label class="block text-gray-700 mb-1">Format:</label>
              <select v-model="exportFormat" class="w-full border px-3 py-2 rounded">
                <option value="csv">CSV</option>
                <option value="pdf">PDF</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 mb-1">Date Range:</label>
              <input type="text" v-model="exportDateRange" placeholder="e.g. 2025-01-01 to 2025-01-31" class="w-full border px-3 py-2 rounded" />
            </div>
            <div class="flex justify-end space-x-3">
              <button @click="showExportModal = false"
                      class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                Cancel
              </button>
              <button @click="confirmExport"
                      class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Export
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  // Placeholder component for charts – replace with your actual chart components.
  const ChartPlaceholder = {
    props: ['chartType', 'data'],
    template: `
      <div class="w-full h-64 flex items-center justify-center border-dashed border-2 border-gray-300">
        <p class="text-gray-500">[{{ chartType }} chart will render here]</p>
      </div>
    `
  };
  
  export default {
    name: 'SystemAnalyticsReports',
    components: { ChartPlaceholder },
    setup() {
      // Tab navigation state
      const tabs = [
        { label: 'Student Engagement', value: 'engagement' },
        { label: 'Sales & Ratings', value: 'salesRatings' },
        { label: 'System Health', value: 'systemHealth' },
      ];
      const activeTab = ref('engagement');
  
      // Dummy data for engagement reports
      const engagementData = ref({
        activeUsers: 1245,
        avgSession: 32,
        coursesCompleted: 560,
        responseRate: 78,
      });
      const engagementChartData = ref({ /* Dummy dataset for chart */ });
      const heatmapData = ref({ /* Dummy dataset for heatmap */ });
  
      // Dummy data for sales and ratings
      const topCourses = ref([
        { id: 1, name: 'Vue Mastery', revenue: 12000, enrollments: 340 },
        { id: 2, name: 'Tailwind CSS Basics', revenue: 8500, enrollments: 275 },
        { id: 3, name: 'Advanced JavaScript', revenue: 15000, enrollments: 410 },
      ]);
      const topInstructors = ref([
        { id: 1, name: 'Jane Doe', rating: 4.9 },
        { id: 2, name: 'John Smith', rating: 4.8 },
        { id: 3, name: 'Alice Johnson', rating: 4.7 },
      ]);
      const salesChartData = ref({ /* Dummy dataset for sales chart */ });
  
      // Dummy data for system health
      const errorLogs = ref([
        { id: 1, type: 'Bug', timestamp: '2025-01-15 10:45', status: 'resolved' },
        { id: 2, type: 'Performance Issue', timestamp: '2025-01-16 14:30', status: 'pending' },
        { id: 3, type: 'Bug', timestamp: '2025-01-17 09:20', status: 'resolved' },
      ]);
      const downtimeData = ref({ /* Dummy dataset for downtime analysis */ });
      const systemStatus = ref('healthy'); // or 'critical'
  
      // Filter options
      const filters = ref({
        dateRange: '30',
        course: '',
      });
      const courses = ref([
        { id: 1, name: 'Vue Mastery' },
        { id: 2, name: 'Tailwind CSS Basics' },
        { id: 3, name: 'Advanced JavaScript' },
      ]);
  
      // Export modal state
      const showExportModal = ref(false);
      const exportFormat = ref('csv');
      const exportDateRange = ref('');
  
      // Methods
      const exportReport = (format) => {
        // Add your export logic here
        alert(`Exporting report as ${format.toUpperCase()}`);
      };
  
      const refreshData = () => {
        // Add data refresh logic here (e.g., API calls)
        alert('Data refreshed!');
      };
  
      const viewCourseDetail = (course) => {
        // Navigate or open modal with detailed course data
        alert(`Viewing details for course: ${course.name}`);
      };
  
      const viewInstructorDetail = (instructor) => {
        // Navigate or open modal with detailed instructor data
        alert(`Viewing details for instructor: ${instructor.name}`);
      };
  
      const openExportModal = () => {
        showExportModal.value = true;
      };
  
      const confirmExport = () => {
        // Logic to confirm export settings and generate report
        alert(`Report scheduled for export as ${exportFormat.value} with date range ${exportDateRange.value}`);
        showExportModal.value = false;
      };
  
      return {
        tabs,
        activeTab,
        engagementData,
        engagementChartData,
        heatmapData,
        topCourses,
        topInstructors,
        salesChartData,
        errorLogs,
        downtimeData,
        systemStatus,
        filters,
        courses,
        exportReport,
        refreshData,
        viewCourseDetail,
        viewInstructorDetail,
        showExportModal,
        openExportModal,
        exportFormat,
        exportDateRange,
        confirmExport,
      };
    },
  };
  </script>
  
  <style scoped>
  /* Example fade transition for modal */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>
  