<script setup>
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import Axios from "axios";
import { ref, onMounted, watch } from "vue";

import { useSidebarStore } from "@/store/useSidebarStore";
import { useAppStore } from "@/store/useAppStore";
const appStore = useAppStore();
const { profileUpdated, authUser } = storeToRefs(appStore);

const router = useRouter();
const emit = defineEmits(["selectContent"]);
const logoUrl = ref(null);

const sidebarStore = useSidebarStore();
const {
    sideBarOpen,
    sidebarCollapsed,
    selectedContent,
    profile,
    dashboard,
    users,
    courses,
    exams,
    payments,
    liveSssions,
    schedule,
    attendance,
    messaging,
    blogs,
} = storeToRefs(sidebarStore);

const mainItems = [
    {
        label: "Dashboard",
        route: dashboard.value,
        description: "Overview of revenue, users, courses, and system health.",
        icon: "home",
        role: "systemAdmin",
    },
    {
        label: "Users Management",
        route: users.value,
        description: "Manage students, instructors, and admins.",
        icon: "user-group",
        role: "systemAdmin",
    },
    {
        label: "Course Management",
        route: courses.value,
        description: "Manage courses and books.",
        icon: "book-open",
        role: true,
    },

    {
        label: "Test Management",
        route: "exams",
        description: "Manage tests, quizzes, and questions.",
        icon: "clipboard-list",
        role: "systemAdmin",
    },

    {
        label: "Payments & Revenue",
        route: payments.value,
        description: "Transactions, earnings, payouts.",
        icon: "dollar-sign",
        role: true,
    },
    {
        label: "Live Management",
        route: liveSssions.value,
        description: "Track, join, schedule live classes.",
        icon: "video",
        role: true,
    },
    {
        label: "Plan and Rooms",
        route: schedule.value,
        description: " schedule live classes.",
        icon: "calendar",
        role: "systemAdmin",
    },
    {
        label: "Attendances",
        route: attendance.value,
        description: "Attendance Tracking.",
        icon: "signature",
        role: "systemAdmin",
    },
    {
        label: "Announcements",
        route: messaging.value,
        description: "Send & manage messages.",
        icon: "envelope",
        role: "systemAdmin",
    },
    {
        label: "Blogs",
        route: blogs.value,
        description: "Post & manage Posts",
        icon: "newspaper",
        role: "systemAdmin",

    },
    {
        label: "Settings ",
        route: "settings",
        description: "Configure platform branding & security.",
        icon: "gear",
        role: "systemAdmin",
    },
];

function selectContent(changeTab) {
    router.push({
        name: "instructor",
        query: {
            currentTab: changeTab,
        },
    });

    selectedContent.value = changeTab;

    // Ensure sidebar is closed on small screens
    if (window.innerWidth < 768) {
        sideBarOpen.value = false;
    }
}

// Function to fetch logo from backend
const fetchLogo = async () => {
    try {
        const res = await Axios.get("/api/logos");
        if (res.data.data.length) {
            logoUrl.value = res.data.data[0].file_url;
        }
    } catch (error) {
        console.error("Error fetching logo:", error);
    }
};

// Call the function when component mounts
onMounted(() => {
    fetchLogo();
});

watch(profileUpdated, (updated) => {
    if (updated) {
        fetchLogo(); // your logo fetching function
        profileUpdated.value = false;
        // reset it
    }
});
</script>

<template>
    <!-- Sidebar container: slides in/out on mobile -->
    <aside
        :class="[
            'fixed top-0 left-0 h-screen z-50 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 xl:w-full',
            sideBarOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Sidebar panel: width changes when sidebarCollapsed -->
        <div
            :class="[
                sidebarCollapsed ? 'w-16' : 'w-64',
                'bg-gray-50 border-r border-lime-300 h-full relative flex flex-col transition-all duration-300',
            ]"
        >
            <!-- Header with logo and mobile close button (desktop: no close icon) -->
            <div
                class="flex items-center justify-between h-20 px-4 border-b border-lime-300"
            >
                <div
                    v-if="!sidebarCollapsed"
                    class="flex items-center animate-fadeIn"
                >
                    <span
                        class="ml-2 font-semibold text-xl xl:text-3xl text-lime-500"
                        >Unique English</span
                    >
                </div>
                <div
                    v-else
                    class="hidden md:flex items-center animate-fadeIn w-full justify-center"
                >
                    <i
                        class="fa-solid w-6 fa-arrow-right font-extrabold text-lime-500 transition-colors duration-200 text-2xl"
                    ></i>
                </div>

                <!-- Mobile Close Icon (only visible on mobile) -->
                <button
                    @click="sidebarStore.toggleSidebar()"
                    class="text-lime-500 hover:text-lime-600 md:hidden transition-colors"
                    title="Close Sidebar"
                >
                    <i
                        class="fas fa-times animate-spinIn font-extrabold text-lime-500 transition-colors duration-200 text-2xl"
                    ></i>
                </button>
            </div>

            <!-- Scrollable menu content -->
            <div class="flex-1 h-screen overflow-y-auto scrollbar">
                <!-- MAIN Section -->
                <div class="px-4 py-4">
                    <div>
                        <div
                            v-for="(item, index) in mainItems"
                            :key="index"
                            @click="selectContent(item?.route)"
                        >
                            <div
                                v-if="
                                    item.role == true
                                        ? true
                                        : authUser?.role === item.role
                                "
                                :class="{
                                    'bg-lime-100':
                                        selectedContent === item.route,
                                }"
                                class="flex items-center space-x-2 p-2 hover:bg-lime-100 rounded-lg cursor-pointer transition-all duration-200"
                                :title="item.description"
                            >
                                <i
                                    :class="{
                                        ['fa-' + item.icon]: true,
                                    }"
                                    class="fa-solid w-6 text-lime-500 transition-colors duration-200 text-lg"
                                ></i>
                                <span
                                    v-if="!sidebarCollapsed"
                                    class="text-gray-800 animate-fadeIn"
                                    >{{ item.label }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collapse/Expand Button at bottom right -->
            <button
                @click="sidebarStore.toggleCollapse()"
                class="absolute bottom-2 right-2 p-2 rounded-full bg-lime-300 transition-colors duration-200"
            >
                <i
                    :class="sidebarCollapsed ? 'rotate-0' : 'rotate-180'"
                    class="fa-solid fa-angle-left text-2xl w-6 h-6 text-white transform transition-transform duration-300"
                ></i>
            </button>
        </div>
    </aside>
</template>

<!-- Import Material Icons font and add custom animations -->
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes spinIn {
    from {
        transform: rotate(-90deg);
        opacity: 0;
    }

    to {
        transform: rotate(0deg);
        opacity: 1;
    }
}

.animate-spinIn {
    animation: spinIn 0.5s ease-in-out;
}
</style>
