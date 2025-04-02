<script setup>
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { useSidebarStore } from "@/store/useSidebarStore";

// Import Components
import Navbar from "@/components/Layout/Navbar.vue";
import ProfileForm from "@/components/Profile/ProfileForm.vue";
import DashboardHome from "@/components/Layout/DashboardHome.vue";
import UserManagement from "@/components/Layout/UserManagement.vue";
import DashboardFooter from "@/components/Layout/DashboardFooter.vue";
import DashboardSidebar from "@/components/Layout/DashboardSidebar.vue";
import CourseManagement from "@/components/Layout/CourseManagement.vue";
import RevenueManagement from "@/components/Layout/RevenueManagement.vue";
import SettingsAndSecurity from "@/components/Layout/SettingsAndSecurity.vue";
import SystemAnalyticsReport from "@/components/Layout/SystemAnalyticsReport.vue";
import LiveSessionManagement from "@/components/Layout/LiveSessionManagement.vue";
import NotificationManagement from "@/components/Layout/NotificationManagement.vue";
import ExamManagement from "@/components/Layout/ExamManagement.vue";
import ScheduleManagement from "../../components/Live/ScheduleManagment.vue";


// Sidebar Store Setup
const sidebarStore = useSidebarStore();
const {
    sidebarCollapsed,
    sideBarOpen,
    selectedContent,
    profile,
    dashboard,
    users,
    courses,
    exams,
    schedule,
    payments,
    liveSssions, // Fixed typo
    messaging,
} = storeToRefs(sidebarStore);

// Vue Router Setup
const route = useRoute();
const router = useRouter();

// Mobile Sidebar Computation
const isMobileSidebarOpen = computed(() => sideBarOpen.value);

// Ensure selectedContent updates dynamically
watch(
    () => route.query.currentTab,
    (newTab) => {
        if (newTab) {
            selectedContent.value = newTab;
        }
    },
    { immediate: true } // Ensure it runs on component mount
);

// Function to Change Content
function setSelectedContent(contentId) {
    selectedContent.value = contentId;
}
</script>

<template>
    <div
        class="bg-gray-200 leading-normal tracking-normal flex min-h-screen transition-all duration-300"
    >
        <div class="relative md:flex transition-all duration-300">
            <DashboardSidebar />
        </div>

        <div
            class="flex flex-col bg-gray-100 min-h-screen border-4 mx-auto w-full overflow-hidden h-screen overflow-y-auto scrollbar transition-all duration-300"
        >
            <Navbar class="transition-all duration-300" />

            <div class="flex-grow flex md:p-6 bg-gray-100 transition-all duration-300" >
                <div class="w-full bg-gray-100">
                    <div v-if="selectedContent === profile">
                        <ProfileForm />
                    </div>
                    <div v-else-if="selectedContent === dashboard">
                        <DashboardHome />
                    </div>
                    <div v-else-if="selectedContent === users">
                        <UserManagement />
                    </div>
                    <div v-else-if="selectedContent === courses">
                        <CourseManagement />
                    </div>

                    <div v-else-if="selectedContent === exams">
                        <ExamManagement />
                    </div>
                    <div v-else-if="selectedContent === payments">
                        <RevenueManagement />
                    </div>
                    <div v-else-if="selectedContent === liveSssions">
                        <LiveSessionManagement />
                    </div>
                    <div v-else-if="selectedContent === schedule">
                        <ScheduleManagement />
                    </div>
                    <div v-else-if="selectedContent === messaging">
                        <NotificationManagement />
                    </div>
                    <div v-else-if="selectedContent === 'reports'">
                        <SystemAnalyticsReport />
                    </div>
                    <div v-else-if="selectedContent === 'settings'">
                        <SettingsAndSecurity />
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
