import { defineStore, storeToRefs } from "pinia";
import { ref, computed, onMounted } from "vue";

import { useAppStore } from '@/store/useAppStore'

export const useSidebarStore = defineStore("useSidebarStore", () => {
    const sideBarOpen = ref(false);
    const sidebarCollapsed = ref(false);

    const appStore = useAppStore();
    const { authUser } = storeToRefs(appStore);

    const profile = ref("profile");
    const dashboard = ref("dashboard");
    const users = ref("users");
    const courses = ref("courses");
    const exams = ref("exams");
    const payments = ref("payments");
    const liveSssions = ref("liveSssions");
    const messaging = ref("messaging");
    const schedule=ref("schedule");
    const attendance = ref("attendance");
    const blogs = ref("blogs");
    const selectedContent = ref( authUser?.value?.role === 'systemAdmin' ?  dashboard.value : courses.value);

    function loadSidebarState() {
        sideBarOpen.value =
            window.innerWidth >= 1024
                ? JSON.parse(localStorage.getItem("sideBarOpen")) || false
                : false;

        sidebarCollapsed.value =
            JSON.parse(localStorage.getItem("sidebarCollapsed")) || false;
    }

    function toggleSidebar() {
        sideBarOpen.value = !sideBarOpen.value;
        localStorage.setItem("sideBarOpen", JSON.stringify(sideBarOpen.value));
    }

    function closeSidebarOnMobile() {
        if (window.innerWidth < 1024) {
            sideBarOpen.value = false;
            localStorage.setItem(
                "sideBarOpen",
                JSON.stringify(sideBarOpen.value)
            );
        }
    }

    function toggleCollapse() {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem(
            "sidebarCollapsed",
            JSON.stringify(sidebarCollapsed.value)
        );
    }

    const isSidebarOpen = computed(() => sideBarOpen.value);
    const isSidebarCollapsed = computed(() => sidebarCollapsed.value);

    onMounted(() => {
        loadSidebarState();
    });

    return {
        sideBarOpen,
        sidebarCollapsed,
        isSidebarOpen,
        isSidebarCollapsed,
        toggleSidebar,
        closeSidebarOnMobile,
        toggleCollapse,

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
    };
});
