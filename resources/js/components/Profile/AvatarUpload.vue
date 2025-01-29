        <script>
        import { ref, reactive, onMounted } from "vue";
        import { storeToRefs } from "pinia"; // Ensure storeToRefs is imported
        import PasswordChangeForm from "./PasswordChangeForm.vue";
        import ProfileForm from "./ProfileForm.vue";
        import AboutUs from "../../pages/common/AboutUs.vue";
        import TermsOfService from "../../pages/common/Terms of Service Page.vue";
        import SignUp from "../Auth/RegisterForm.vue";
        import FAQPage from "../../pages/common/FAQ Page.vue";
        import ContactUs from "../../pages/common/Contact Us Page.vue";
        import PrivacyPolicy from "../../pages/common/Privacy Policy Page.vue";

        import { useAppStore } from "@/store/useAppStore";
        import Axios from "axios";
        import appRouter from "../../routes/AppRouter";

        export default {
        name: "AccountSettings",
        components: {
            PasswordChangeForm,
            ProfileForm,
            AboutUs,
            TermsOfService,
            SignUp,
            FAQPage,
            ContactUs,
            PrivacyPolicy,
        },
        setup() {
            const appStore = useAppStore(); // Use Pinia store inside setup
            const { authUser, frontLang } = storeToRefs(appStore); // Destructure reactive properties, including frontLang

            const logout = async () => {
            try {
                const token = localStorage.getItem('authToken');
                if (!token) {
                console.error("No auth token found. Logging out locally.");
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

                console.log("Logout successful.");
                appStore.logout(); // Clear app state
                localStorage.removeItem('authToken');
                this.$router.push('/login');
            } catch (error) {
                console.error("Logout failed:", error);

                // Handle 401 error or general failure
                if (error.response && error.response.status === 401) {
                console.warn("Token invalid or expired. Clearing local state.");
                }

                // Clear local storage and app state regardless
                appStore.logout();
                localStorage.removeItem('authToken');
                this.$appRouter.push('/login');
            }
            };

            const currentView = ref("PasswordChangeForm");
            const isSidebarOpen = ref(false);
            const isProfileMenuOpen = ref(false);
            const isMobile = ref(window.innerWidth < 768);

            const menuItems = reactive([
            { name: "Password Change", component: "PasswordChangeForm", icon: "fas fa-key" },
            { name: "Profile Settings", component: "ProfileForm", icon: "fas fa-user" },
            { name: "About Us", component: "AboutUs", icon: "fas fa-info-circle" },
            { name: "Sign Up", component: "SignUp", icon: "fas fa-user-plus" },
            { name: "Contact Us", component: "ContactUs", icon: "fas fa-envelope" },
            { name: "FAQ", component: "FAQPage", icon: "fas fa-question-circle" },
            { name: "Privacy Policy", component: "PrivacyPolicy", icon: "fas fa-lock" },
            { name: "Terms of Service", component: "TermsOfService", icon: "fas fa-file-alt" },
            ]);

            const changeComponent = (newComponent) => {
            currentView.value = newComponent;
            
            };

            const toggleSidebar = () => {
            isSidebarOpen.value = !isSidebarOpen.value;
            };

            const toggleProfileMenu = () => {
            isProfileMenuOpen.value = !isProfileMenuOpen.value;
            };

            const handleResize = () => {
            isMobile.value = window.innerWidth < 768;
            };

            onMounted(() => {
            window.addEventListener("resize", handleResize);
            });

            return {
            currentView,
            isSidebarOpen,
            isProfileMenuOpen,
            isMobile,
            menuItems,
            changeComponent,
            toggleSidebar,
            toggleProfileMenu,
            logout,
            authUser,
            frontLang, // Make frontLang available in the template if needed
            };
        },
        };
        </script>
    <template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside
        :class="{
            'bg-green-500 text-white p-5 fixed md:static top-0 left-0 z-40 transition-all duration-300 ease-in-out': true,
            'mt-0': isMobile,
            'h-screen': isMobile,
            'block': isSidebarOpen,
            'hidden': !isSidebarOpen && isMobile,
        }"
        style="width: 200px;"
        >
        <!-- Close Button -->
        <button
            class="absolute top-4 right-4 text-white text-xl md:hidden"
            @click="isSidebarOpen = false"
        >
            &times;
        </button>
        <div v-if="frontLang && frontLang.lang" class="text-xl font-bold mb-5"> {{ frontLang.lang.uniquee  }} </div>

        <nav>
            <ul>
            <li
                v-for="item in menuItems"
                :key="item.name"
                @click="changeComponent(item.component)"
                class="py-2 px-3 cursor-pointer hover:bg-gray-700 flex items-center space-x-3"
                :class="{ 'bg-gray-500': currentView === item.component }"
            >
                <i :class="item.icon" class="mr-2"></i>
                <span v-if="frontLang && frontLang.lang">{{ frontLang.lang[item.component] }}</span>
            </li>
            </ul>
        </nav>
        </aside>

        <!-- Main Content -->
        <div
        :class="{
            'flex-1 flex flex-col transition-all duration-300 ease-in-out': true,
            'ml-0': isMobile,
            'ml-[0px]': !isMobile,
        }"
        >
        <header class="bg-green-400 text-white px-4 py-3 shadow-md flex justify-between items-center w-full">
            <div class="text-lg sm:text-2xl font-bold flex-shrink-0">
            {{ currentView }}
            </div>

            <!-- Desktop Container -->
            <div class="hidden md:flex items-center gap-4 ml-auto">
            <div class="flex items-center gap-2">
                <input
                type="text"
                placeholder="Search..."
                class="border rounded-full text-gray-800 px-3 py-2 w-[150px] sm:w-[200px] lg:w-[250px] text-sm sm:text-base"
                />
                <button class="bg-blue-500 text-white rounded-lg px-4 py-2 text-sm sm:text-base">
                🔍
                </button>
            </div>
            <div class="relative">
                <button class="text-gray-400 hover:text-white text-lg">🔔</button>
                <span
                class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full px-1"
                >
                5
                </span>
            </div>
            <div class="relative">
                <button
                @click="toggleProfileMenu"
                class="flex items-center gap-2 focus:outline-none"
                >
                <img
                    src="https://via.placeholder.com/32"
                    alt="Profile"
                    class="rounded-full w-8 h-8"
                />
                <span v-if="frontLang && frontLang.lang" class="text-sm">{{ frontLang.lang.fullname }}</span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-gray-400 transition-transform transform"
                    :class="{ 'rotate-180': isProfileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                    />
                </svg>
                </button>
                <div
                v-if="isProfileMenuOpen"
                class="absolute right-0 mt-2 bg-white text-gray-800 rounded-lg shadow-md w-40 z-10"
                >
                <button
                    @click="logout"
                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 w-full text-left"
                >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>{{frontLang.lang.logout}}</span>
                </button>
                </div>
            </div>
            </div>

            <!-- Mobile Menu Button -->
            <button
            @click="toggleSidebar"
            class="md:hidden text-white bg-green-500 px-3 py-2 rounded-lg focus:outline-none"
            >
            ☰
            </button>
        </header>

        <!-- Mobile Menu -->
        <div class="md:hidden bg-[#f5f5f5] text-black p-4">
            <div class="flex items-center gap-2 mb-3">
            <input
                type="text"
                placeholder="Search..."
                class="border rounded-full text-gray-800 px-3 py-2 w-full text-sm"
            />
            <button
                class="bg-blue-500 text-white rounded-lg px-4 py-2 text-sm"
            >
                🔍
            </button>
            </div>
            <div class="flex justify-between items-center">
            <div class="relative">
                <button class="text-gray-400 hover:text-white text-lg">🔔</button>
                <span
                class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full px-1"
                >
                5
                </span>
            </div>
            <div class="relative">
                <button
                @click="toggleProfileMenu"
                class="flex items-center gap-2 focus:outline-none"
                >
                <img
                    src="https://via.placeholder.com/32"
                    alt="Profile"
                    class="rounded-full w-8 h-8"
                />
                <span class="text-sm">John Doe</span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-gray-400 transition-transform transform"
                    :class="{ 'rotate-180': isProfileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                    />
                </svg>
                </button>
                <div
                v-if="isProfileMenuOpen"
                class="absolute right-0 mt-2 bg-white text-gray-800 rounded-lg shadow-md w-40 z-10"
                >
                <button
                    @click="logout"
                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 w-full text-left"
                >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>{{frontLang.lang.logout}}</span>
                </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <main
            class="flex-grow p-5 bg-white overflow-y-auto h-[calc(100vh-100px)]"
        >
            <component :is="currentView" class="mt-5"></component>
        </main>
        </div>

        <!-- Sidebar Overlay (Mobile Only) -->
        <div
        v-if="isSidebarOpen && isMobile"
        @click="toggleSidebar"
        class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 z-30"
        ></div>
    </div>
    </template>

