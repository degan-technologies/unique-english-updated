<script setup>
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";

import { useAppStore } from '@/store/useAppStore'
import { useSidebarStore } from "@/store/useSidebarStore";

// Import Components
import Navbar from "@/components/Layout/Navbar.vue";
import Attendance from "@/components/Layout/Attendance.vue";
import ManageTest from "@/components/Course/ManageTest.vue";
import ProfileForm from "@/components/Profile/ProfileForm.vue";
import DashboardHome from "@/components/Layout/DashboardHome.vue";
import UserManagement from "@/components/Layout/UserManagement.vue";
import DashboardFooter from "@/components/Layout/DashboardFooter.vue";
import DashboardSidebar from "@/components/Layout/DashboardSidebar.vue";
import CourseManagement from "@/components/Layout/CourseManagement.vue";
import RevenueManagement from "@/components/Layout/RevenueManagement.vue";
import ScheduleManagement from "@/components/Live/ScheduleManagement.vue";
import SettingsAndSecurity from "@/components/Layout/SettingsAndSecurity.vue"; 
import LiveSessionManagement from "@/components/Layout/LiveSessionManagement.vue";
import NotificationManagement from "@/components/Layout/NotificationManagement.vue";

const appStore = useAppStore();
const { authUser } = storeToRefs(appStore);
 
const sidebarStore = useSidebarStore();
const { 
    sideBarOpen,
    selectedContent,
    profile,
    dashboard,
    users,
    courses,
    exams,
    schedule,
    payments,
    liveSssions,
    attendance,
    messaging,
} = storeToRefs(sidebarStore);
 
const route = useRoute(); 

watch(
    () => route.query.currentTab,
    (newTab) => {
        if (newTab) {
            selectedContent.value = newTab;
        }
    },
    { immediate: true } 
);

</script>

<template>
    <div class="bg-gray-200 leading-normal tracking-normal flex min-h-screen transition-all duration-300">
        <div class="relative md:flex z-40 transition-all duration-300">
            <DashboardSidebar />
        </div>

        <div
            class="flex flex-col bg-gray-100 min-h-screen border-4 mx-auto w-full overflow-hidden h-screen overflow-y-auto scrollbar transition-all duration-300">
            <div class="transition-all duration-300 z-40">
                <Navbar />
            </div>

            <div class="flex-grow flex md:p-6 bg-gray-100 transition-all duration-300">
                <div class="w-full bg-gray-100">
                    <div v-if="authUser?.role !== 'instructor'">
                        <div v-if="selectedContent === dashboard">
                            <DashboardHome />
                        </div>
                        <div v-else-if="selectedContent === users">
                            <UserManagement />
                        </div> 
                        <div v-else-if="selectedContent === schedule">
                            <ScheduleManagement />
                        </div>
                        <div v-else-if="selectedContent === messaging">
                            <NotificationManagement />
                        </div>
                        <div v-else-if="selectedContent === 'settings'">
                            <SettingsAndSecurity />
                        </div>
                        <div v-else-if="selectedContent === attendance">
                            <Attendance />
                        </div>
                    </div>
                   <div>
                        <div v-if="selectedContent === profile">
                            <ProfileForm />
                        </div>
                        <div v-else-if="selectedContent === courses">
                            <CourseManagement />
                        </div>
                        <div v-else-if="selectedContent === payments">
                            <RevenueManagement />
                        </div>
                        <div v-else-if="selectedContent === liveSssions">
                            <LiveSessionManagement />
                        </div>
                        <div v-else-if="selectedContent === exams">
                            <ManageTest />
                        </div>
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
