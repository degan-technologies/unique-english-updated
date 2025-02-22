import { ref, computed, onMounted } from "vue";
import { defineStore } from "pinia";

export const useSidebarStore = defineStore("useSidebarStore", () => {
    const sideBarOpen = ref(false);
    const sidebarCollapsed = ref(false);
    const selectedContent = ref('dashboard');

    function loadSidebarState() {
        sideBarOpen.value =
        window.innerWidth >= 1024
            ? JSON.parse(localStorage.getItem("sideBarOpen")) || false
            : false;

        sidebarCollapsed.value = JSON.parse(localStorage.getItem("sidebarCollapsed")) || false;
    }

    function toggleSidebar() {
        sideBarOpen.value = !sideBarOpen.value;
        localStorage.setItem("sideBarOpen", JSON.stringify(sideBarOpen.value));
    }

    function closeSidebarOnMobile() {
        if (window.innerWidth < 1024) {
        sideBarOpen.value = false;
        localStorage.setItem("sideBarOpen", JSON.stringify(sideBarOpen.value));
        }
    }

    function toggleCollapse() {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem("sidebarCollapsed", JSON.stringify(sidebarCollapsed.value));
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
    };
});
