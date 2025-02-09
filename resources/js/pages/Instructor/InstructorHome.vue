<template>
  <div class="bg-gray-200 leading-normal tracking-normal flex min-h-screen transition-all duration-300">
    <!-- Sidebar -->
    <div
      :class="{
        'hidden md:block xl:w-64': !sidebarCollapsed, /* Expanded on large screens */
        'hidden sm:block md:w-16': sidebarCollapsed, /* Always collapsed on medium screens */
        'block sm:hidden w-full absolute top-0 left-0 h-full z-50': isMobileSidebarOpen /* Mobile */
      }"
      class="relative transition-all duration-300"
    >
      <!-- Listen for the "selectContent" event from the sidebar -->
      <DashboardSidebar @selectContent="setSelectedContent" />
    </div>

    <!-- Main Content Wrapper -->
    <div
      :class="{
        'xl:w-[calc(100%-16rem)]': !sidebarCollapsed, /* Full size minus sidebar when expanded */
        'xl:w-[calc(100%-4rem)]': sidebarCollapsed,       /* Adjust width when collapsed */
        'w-full': sidebarCollapsed || isMobileSidebarOpen   /* Full width for mobile */
      }"
      class="flex flex-col bg-gray-100 min-h-screen transition-all duration-300"
    >
      <!-- Navbar (Header) -->
      <Navbar class="transition-all duration-300" />

      <!-- Main Content Area -->
      <div class="flex-grow flex p-6 bg-gray-100 transition-all duration-300">
        <div class="flex-grow bg-gray-100">
          <!-- Render content based on selectedContent -->
          <div v-if="selectedContent === 'dashboard'">
            <DashboardHome />
          </div>
          <div v-else-if="selectedContent === 'users'">
            <UserManagement />
          </div>
          <div v-else-if="selectedContent === 'courses'">
            <CourseManagement />
          </div>
          <div v-else-if="selectedContent === 'payments'">
            <RevenueManagement />
          </div>
          <div v-else-if="selectedContent === 'live-sessions'">
            <LiveSessionManagement />
          </div>
          <div v-else-if="selectedContent === 'messaging'">
            <NotificationManagement />
          </div>
          <div v-else-if="selectedContent === 'reports'">
            <SystemAnalyticsReport />
          </div>
          <div v-else-if="selectedContent === 'settings'">
            <SettingsAndSecurity />
          </div>
          <div v-else-if="selectedContent === 'addcourse'">
            <p>Add Course Content</p>
          </div>
          <div v-else-if="selectedContent === 'viewcourse'">
            <p>View Course Content</p>
          </div>
          <div v-else-if="selectedContent === 'notice'">
            <p>Notices Content</p>
          </div>
          <div v-else-if="selectedContent === 'controls'">
            <p>Controls Content</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DashboardFooter class="transition-all duration-300" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useSidebarStore } from '@/store/sidebar'

// Import your components
import DashboardSidebar from '@/components/Layout/DashboardSidebar.vue'
import Navbar from '@/components/Layout/Navbar.vue'
import DashboardHome from '@/components/Layout/DashboardHome.vue'
import UserManagement from '@/components/Layout/UserManagement.vue'
import CourseManagement from '@/components/Layout/CourseManagement.vue'
import RevenueManagement from '@/components/Layout/RevenueManagement.vue'
import LiveSessionManagement from '@/components/Layout/LiveSessionManagement.vue'
import NotificationManagement from '@/components/Layout/NotificationManagement.vue'
import SystemAnalyticsReport from '@/components/Layout/SystemAnalyticsReport.vue'
import SettingsAndSecurity from '@/components/Layout/SettingsAndSecurity.vue'
import DashboardFooter from '@/components/Layout/DashboardFooter.vue'

// Access sidebar store
const sidebarStore = useSidebarStore()
const { sidebarCollapsed, sideBarOpen } = storeToRefs(sidebarStore)

// Create a computed alias so the template can use `isMobileSidebarOpen`
const isMobileSidebarOpen = computed(() => sideBarOpen.value)

// Local state for the main content area.
const selectedContent = ref('dashboard')

// This function is called when the sidebar emits the "selectContent" event.
function setSelectedContent(contentId) {
  selectedContent.value = contentId
}
</script>

<style scoped>
.transition-all {
  transition: all 0.3s ease-in-out;
}
</style>
