<script setup>
    import { storeToRefs } from 'pinia';
    import { ref, onMounted, onUnmounted } from 'vue';

    import { useThemeStore } from '@/store/theme';
    import appRouter from "../../routes/AppRouter";
    import { useAppStore } from "@/store/useAppStore";
    import { useSidebarStore } from '@/store/useSidebarStore';


    const appStore = useAppStore(); 
    const sidebarStore = useSidebarStore();
    const { authUser, frontLang } = storeToRefs(appStore); 
    const { sideBarOpen, selectedContent } = storeToRefs(sidebarStore);

    const searchOpen = ref(false);
    const dropDownOpen = ref(false);
    const mobileMenuOpen = ref(false);

    const themeStore = useThemeStore();

    const toggleSidebar = () => {
        sidebarStore.toggleSidebar();
    }

    const openProfile = () => selectedContent.value = 'profile';

    const logout = async () => {
        try {
            const token = localStorage.getItem('authToken');
            if (!token) {
                appStore.logout();
                localStorage.removeItem('authToken');
                appRouter.navigate('/login');
                return;
            }

            await Axios.post('/api/logout', {}, {
                headers: {
                Authorization: `Bearer ${token}`,
            },
            });

            appStore.logout();
            localStorage.removeItem('authToken');
            this.$router.push('/login');
        } catch (error) {
            console.error("Logout failed:", error);

            if (error.response && error.response.status === 401) {
                console.warn("Token invalid or expired. Clearing local state.");
            }

            appStore.logout();
            localStorage.removeItem('authToken');
            this.$appRouter.push('/login');
    }
    };
</script>

<template>
    <div class="sticky top-0 z-40">
        <div
            class="w-full h-16 px-6 bg-gray-100  border-b flex items-center justify-between transition-colors duration-300"
            >  
            <div class="flex items-center">
             
                <button
                    class="lg:hidden text-gray-600 hover:text-lime-500 transition-colors duration-300"
                    @click="toggleSidebar"
                    title="Toggle Sidebar" >
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="relative ml-4">
                    <div class="hidden md:block">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="bg-white  h-10 w-64 px-5 rounded-lg border text-sm text-gray-700  focus:outline-none transition-colors duration-300" />
                        <button
                            type="submit"
                            class="absolute right-0 top-0 mt-2 mr-4 text-gray-600  hover:text-lime-500 transition-colors duration-300"
                            title="Search"
                            >
                            <i class="fa-solid fa-magnifing-glass text-xl"></i>
                        </button>
                    </div>
                    <div class="block md:hidden">
                        <template v-if="searchOpen">
                            <div class="flex items-center">
                            <button
                                @click="searchOpen = false"
                                class="mr-2 text-gray-600 hover:text-lime-500 transition-colors duration-300"
                                title="Back" >
                                <i class="fa-solid fa-moon text-xl"></i>
                            </button>
                            <input
                                type="text"
                                placeholder="Search..."
                                class="flex-1 h-10 px-4 rounded-lg border text-sm text-gray-700  bg-gray  focus:outline-none transition-colors duration-300"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <button
                                @click="searchOpen = true"
                                class="text-gray-600  hover:text-lime-500 transition-colors duration-300"
                                title="Search"
                                >
                                <i class="fa-solid fa-magnifing-glass text-xl"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4 text-gray-600 ">
                <button
                    @click="themeStore.toggleTheme"
                    class="hover:text-lime-500 transition-colors duration-300"
                    title="Toggle Dark Mode" >
                    <i class="fa-solid fa-moon text-xl"></i>
                </button>
                <div class="hidden md:flex items-center space-x-4">
                    <button
                        class="hover:text-lime-500 relative transition-colors duration-300"
                        title="Notifications" >
                        <i class="fa-solid fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full" >
                        3
                        </span>
                    </button>
                    <button
                        class="hover:text-lime-500 transition-colors duration-300"
                        title="Quick Messaging"
                        >
                        <i class="fa-solid fa-envelope text-xl"></i>
                    </button>
                    <button
                        class="hover:text-lime-500 transition-colors duration-300"
                        title="Switch Role (Admin ↔ Instructor)" >
                        <i class="fa-solid fa-arrow-right-arrow-left text-xl"></i>
                    </button>
                    <button
                        class="hover:text-lime-500 transition-colors duration-300"
                        title="Settings" >
                        <i class="fa-solid fa-gear text-xl"></i>
                    </button>
                    <div class="relative">
                        <img
                            :src="authUser.profile || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'"
                            alt="Profile Picture"
                            class="w-12 h-12 rounded-full shadow-lg cursor-pointer"
                            @click="dropDownOpen = !dropDownOpen"
                            title="Profile"
                            />

                        <div
                            v-if="dropDownOpen"
                            class="absolute top-14 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-48 transition-all duration-300">
                            <div @click="openProfile" class="block px-4 py-2 hover:bg-gray-200" title="Account">
                                Account
                            </div>
                            <div class="block px-4 py-2 hover:bg-gray-200" title="Settings">
                                Settings
                            </div>
                            <div @click="logout" class="block px-4 py-2 hover:bg-gray-200" title="Logout">
                                Logout
                            </div>
                        </div>
                    </div>

                </div>
                <div class="relative md:hidden">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="hover:text-lime-500 transition-colors duration-300"
                        title="More Options" >
                        <i class="fa-solid fa-ellipsis-vertical text-xl"></i>
                    </button>
                    <div
                        v-if="mobileMenuOpen"
                        class="absolute right-0 mt-2 bg-white  border border-gray-200  shadow-xl text-gray-700  rounded-lg w-48 transition-all duration-300"
                        >
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Notifications" >
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span class="ml-2">Notifications</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Quick Messaging" >
                            <i class="fa-solid fa-envelope text-xl"></i>
                            <span class="ml-2">Messaging</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Switch Role" >
                            <i class="fa-solid fa-arrow-right-arrow-left text-xl"></i>
                            <span class="ml-2">Switch Role</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Settings" >
                            <i class="fa-solid fa-gear text-xl"></i>
                            <span class="ml-2">Settings</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Profile"
                            >
                            <i class="fa-solid fa-circle-user text-xl"></i>
                            <span class="ml-2">Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .material-icons {
    font-size: 24px;
    transition: color 0.3s;
    }
</style>
