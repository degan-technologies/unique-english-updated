<script setup>
  import { ref, computed } from 'vue';
  import { storeToRefs } from 'pinia';
  import { useSidebarStore } from '@/store/useSidebarStore';

  const sidebarStore = useSidebarStore();
  const { sidebarCollapsed, sideBarOpen } = storeToRefs(sidebarStore);

  import Navbar from '@/components/Layout/Navbar.vue';
  import DashboardHome from '@/components/Layout/DashboardHome.vue';
  import UserManagement from '@/components/Layout/UserManagement.vue';
  import DashboardFooter from '@/components/Layout/DashboardFooter.vue';
  import DashboardSidebar from '@/components/Layout/DashboardSidebar.vue';
  import CourseManagement from '@/components/Layout/CourseManagement.vue';
  import RevenueManagement from '@/components/Layout/RevenueManagement.vue';
  import SettingsAndSecurity from '@/components/Layout/SettingsAndSecurity.vue';
  import SystemAnalyticsReport from '@/components/Layout/SystemAnalyticsReport.vue';
  import LiveSessionManagement from '@/components/Layout/LiveSessionManagement.vue';
  import NotificationManagement from '@/components/Layout/NotificationManagement.vue';

  const isMobileSidebarOpen = computed(() => sideBarOpen.value)

  const selectedContent = ref('dashboard')

  function setSelectedContent(contentId) {
    selectedContent.value = contentId
  }
</script>

<template>
  <div class="bg-gray-200 leading-normal tracking-normal flex min-h-screen transition-all duration-300">
    <div class="relative md:flex transition-all duration-300" >
      <DashboardSidebar @selectContent="setSelectedContent" />
    </div>

    <div class="flex flex-col bg-gray-100 min-h-screen border-4 mx-auto w-full overflow-hidden h-screen overflow-y-auto scrollbar transition-all duration-300" >
      <Navbar class="transition-all duration-300" />

      <div class="flex-grow flex md:p-6 bg-gray-100 transition-all duration-300">
        <div class="flex-grow bg-gray-100">
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

      <DashboardFooter class="transition-all duration-300" />
    </div>
  </div>
</template>

<style scoped>
.transition-all {
  transition: all 0.3s ease-in-out;
}
</style>
