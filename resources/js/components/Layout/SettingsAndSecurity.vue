<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";

import { useAppStore } from "@/store/useAppStore";
const appStore = useAppStore();
const { profileUpdated, hero } = storeToRefs(appStore);

import Hero from "@/components/Layout/Hero.vue";

const settingTab = ref("setting");
const securityTab = ref("security");
const selectedTab = ref(settingTab.value);
const eExpandView = ref(true);
const isProcessing = ref(false);
const isDeleting = ref({
    logo: false,
    banner: false,
    background: false,
});

const logs = ref([]);
const showDeleteDialog = ref(false);
const deleteImageType = ref("");

// Add missing error handling variables
const showErrorDialog = ref(false);
const errorDialogMessage = ref("");

// Add missing cancel delete function
const cancelDelete = () => {
    showDeleteDialog.value = false;
    deleteImageType.value = "";
};

const handleLogoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    hero.value.selectedLogo = file;
    hero.value.logo = URL.createObjectURL(file);

    return;
};

const handleBannerUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    hero.value.selectedbanner = file;
    hero.value.banner = URL.createObjectURL(file);

    return;
};

const handlBackgroundUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    hero.value.selectedBackground = file;
    hero.value.background_image = URL.createObjectURL(file);

    return;
};

const deleteImage = async (imageType) => {
    if (isDeleting.value[imageType]) return;

    // Show custom dialog instead of window alert
    deleteImageType.value = imageType;
    showDeleteDialog.value = true;
};

const confirmDelete = async () => {
    const imageType = deleteImageType.value;
    showDeleteDialog.value = false;

    if (!imageType) return;

    isDeleting.value[imageType] = true;

    try {
        const response = await Axios.delete(`/api/hero-section/${imageType}`);

        // Clear the image from hero object
        switch (imageType) {
            case "logo":
                hero.value.logo = null;
                hero.value.selectedLogo = null;
                break;
            case "banner":
                hero.value.banner = null;
                hero.value.selectedbanner = null;
                break;
            case "background":
                hero.value.background_image = null;
                hero.value.selectedBackground = null;
                break;
        }

        // Update the store with fresh data
        appStore.getHeroSection();

        console.log(`${imageType} deleted successfully`);
    } catch (error) {
        console.error(`Error deleting ${imageType}:`, error);

        if (error.response?.status === 403) {
            errorDialogMessage.value =
                "Access denied. You don't have permission to delete images. Please contact your administrator.";
            showErrorDialog.value = true;
        } else if (error.response?.status === 401) {
            errorDialogMessage.value = "Session expired. Please log in again.";
            showErrorDialog.value = true;
        } else {
            const errorMessage =
                error.response?.data?.message ||
                `Failed to delete ${imageType}. Please try again.`;
            errorDialogMessage.value = errorMessage;
            showErrorDialog.value = true;
        }
    } finally {
        isDeleting.value[imageType] = false;
        deleteImageType.value = "";
    }
};

const closeErrorDialog = () => {
    showErrorDialog.value = false;
    errorDialogMessage.value = "";
};

function isFileObject(obj) {
    return (
        obj instanceof File &&
        typeof obj.name === "string" &&
        typeof obj.size === "number"
    );
}

function storeOrUpdate() {
    isProcessing.value = true;

    const formData = new FormData();

    formData.append("title", hero.value.title || "");
    formData.append("description", hero.value.description || "");

    if (isFileObject(hero.value.selectedLogo)) {
        formData.append("logo", hero.value.selectedLogo);
    }

    if (isFileObject(hero.value.selectedbanner)) {
        formData.append("banner", hero.value.selectedbanner);
    }

    if (isFileObject(hero.value.selectedBackground)) {
        formData.append("background_image", hero.value.selectedBackground);
    }

    Axios.post("/api/hero-section", formData)
        .then((res) => {
            console.log("Hero section updated successfully");
            // Update the store with fresh data
            appStore.getHeroSection();
        })
        .catch((error) => {
            console.error("Error updating hero section:", error);

            if (error.response?.status === 403) {
                errorDialogMessage.value =
                    "Access denied. You don't have permission to modify the hero section. Please contact your administrator.";
                showErrorDialog.value = true;
            } else if (error.response?.status === 401) {
                errorDialogMessage.value =
                    "Session expired. Please log in again.";
                showErrorDialog.value = true;
            } else {
                const errorMessage =
                    error.response?.data?.message ||
                    "Failed to update hero section. Please try again.";
                errorDialogMessage.value = errorMessage;
                showErrorDialog.value = true;
            }
        })
        .finally(() => {
            isProcessing.value = false;
        });
}

function onExpandView() {
    eExpandView.value = !eExpandView.value;
}

function onSelectTab(tab) {
    selectedTab.value = tab;
}

function getActivityLogs() {
    Axios.get("/api/activity-logs").then((res) => {
        logs.value = res.data.data;
    });
}

onMounted(() => {
    getActivityLogs();
    // Ensure hero section data is loaded
    appStore.getHeroSection();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Page Header -->
        <header class="bg-white shadow-sm border-b p-3 sm:p-4 lg:p-6">
            <div class="max-w-7xl mx-auto">
                <h1
                    class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900"
                >
                    Platform Settings & Security Management
                </h1>
            </div>
        </header>

        <!-- Tab Navigation -->
        <div class="bg-white border-b sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
                <nav class="flex space-x-1 sm:space-x-2" aria-label="Tabs">
                    <button
                        @click="onSelectTab(settingTab)"
                        :class="{
                            'border-lime-500 text-lime-600 ':
                                selectedTab === settingTab,
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300':
                                selectedTab !== settingTab,
                        }"
                        class="flex-1 sm:flex-none whitespace-nowrap py-3 sm:py-4 px-3 sm:px-6 border-b-2 font-medium text-xs sm:text-sm focus:outline-none transition-colors rounded-t-lg"
                    >
                        <i class="fas fa-cog mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Platform </span>Settings
                    </button>
                    <button
                        @click="onSelectTab(securityTab)"
                        :class="{
                            'border-lime-500 text-lime-600 bg-lime-50':
                                selectedTab === securityTab,
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300':
                                selectedTab !== securityTab,
                        }"
                        class="flex-1 sm:flex-none whitespace-nowrap py-3 sm:py-4 px-3 sm:px-6 border-b-2 font-medium text-xs sm:text-sm focus:outline-none transition-colors rounded-t-lg"
                    >
                        <i class="fas fa-shield-alt mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Security </span
                        >Management
                    </button>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-hidden">
            <div class="max-w-7xl mx-auto p-3 sm:p-4 lg:p-6">
                <!-- Platform Settings Panel -->
                <transition name="fade" mode="out-in">
                    <div
                        v-if="selectedTab === settingTab"
                        key="theme"
                        class="space-y-4 sm:space-y-6"
                    >
                        <!-- Dashboard Theme & Branding Card -->
                        <div
                            class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <!-- Card Header -->
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center">
                                    <div class="text-2xl sm:text-3xl mr-3">
                                        🎨
                                    </div>
                                    <div>
                                        <h2
                                            class="text-lg sm:text-xl font-bold text-gray-900"
                                        >
                                            Home & Landing Page Branding
                                        </h2>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Customize your platform's appearance
                                            and branding
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="p-4 sm:p-6">
                                <!-- Column layout - everything stacked vertically -->
                                <div class="space-y-6 lg:space-y-8">
                                    <!-- Customization Controls -->
                                    <div
                                        v-if="!eExpandView"
                                        class="space-y-4 sm:space-y-6"
                                    >
                                        <!-- Hero Title -->
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 mb-2"
                                            >
                                                Hero Title
                                            </label>
                                            <input
                                                v-model="hero.title"
                                                placeholder="Enter hero title"
                                                class="w-full px-3 py-2.5 sm:px-4 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                                            />
                                        </div>

                                        <!-- Hero Description -->
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 mb-2"
                                            >
                                                Hero Description
                                            </label>
                                            <textarea
                                                v-model="hero.description"
                                                placeholder="Enter hero description"
                                                rows="3"
                                                class="w-full px-3 py-2.5 sm:px-4 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base resize-none"
                                            ></textarea>
                                        </div>

                                        <!-- Upload Controls Grid -->
                                        <div class="space-y-4 sm:space-y-6">
                                            <h3
                                                class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-2"
                                            >
                                                <i
                                                    class="fas fa-cloud-upload-alt mr-2 text-lime-600"
                                                ></i>
                                                Media Assets
                                            </h3>

                                            <div
                                                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6"
                                            >
                                                <!-- Logo Upload Card -->
                                                <div
                                                    class="rounded-xl border border-blue-200 p-4 lg:p-5 transition-all duration-300"
                                                >
                                                    <div
                                                        class="flex items-center justify-between mb-3"
                                                    >
                                                        <label
                                                            class="flex items-center text-sm font-semibold text-gray-800"
                                                        >
                                                            <div
                                                                class="w-8 h-8 rounded-lg flex items-center justify-center mr-2"
                                                            >
                                                                <i
                                                                    class="fas fa-image text-white text-xs"
                                                                ></i>
                                                            </div>
                                                            Logo
                                                        </label>
                                                        <div
                                                            class="text-xs text-gray-600 px-2 py-1 rounded-full"
                                                        >
                                                            Brand Identity
                                                        </div>
                                                    </div>

                                                    <!-- Logo Preview -->
                                                    <div
                                                        v-if="hero.logo"
                                                        class="mb-3"
                                                    >
                                                        <div
                                                            class="relative group"
                                                        >
                                                            <img
                                                                :src="hero.logo"
                                                                alt="Logo preview"
                                                                class="w-full h-16 object-contain bg-white rounded-lg border border-gray-200"
                                                            />
                                                            <div
                                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"
                                                            ></div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-3">
                                                        <div class="relative">
                                                            <input
                                                                type="file"
                                                                accept="image/*"
                                                                @change="
                                                                    handleLogoUpload
                                                                "
                                                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-500 file:text-white hover:file:bg-blue-600 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-all focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                            />
                                                        </div>

                                                        <div
                                                            v-if="hero.logo"
                                                            class="flex justify-end"
                                                        >
                                                            <button
                                                                @click="
                                                                    deleteImage(
                                                                        'logo',
                                                                    )
                                                                "
                                                                :disabled="
                                                                    isDeleting.logo
                                                                "
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="Delete logo"
                                                            >
                                                                <i
                                                                    v-if="
                                                                        isDeleting.logo
                                                                    "
                                                                    class="fas fa-spinner fa-spin mr-1"
                                                                ></i>
                                                                <i
                                                                    v-else
                                                                    class="fas fa-trash mr-1"
                                                                ></i>
                                                                <span
                                                                    class="hidden sm:inline"
                                                                    >Delete</span
                                                                >
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <p
                                                        class="text-xs text-gray-600 mt-2"
                                                    >
                                                        Recommended: 200x60px,
                                                        PNG/JPG
                                                    </p>
                                                </div>

                                                <!-- Banner Upload Card -->
                                                <div
                                                    class="rounded-xl border border-purple-200 p-4 lg:p-5 hover:shadow-md transition-all duration-300"
                                                >
                                                    <div
                                                        class="flex items-center justify-between mb-3"
                                                    >
                                                        <label
                                                            class="flex items-center text-sm font-semibold text-gray-800"
                                                        >
                                                            <div
                                                                class="w-8 h-8 rounded-lg flex items-center justify-center mr-2"
                                                            >
                                                                <i
                                                                    class="fas fa-panorama text-white text-xs"
                                                                ></i>
                                                            </div>
                                                            Banner
                                                        </label>
                                                        <div
                                                            class="text-xs text-gray-600 px-2 py-1 rounded-full"
                                                        >
                                                            Hero Section
                                                        </div>
                                                    </div>

                                                    <!-- Banner Preview -->
                                                    <div
                                                        v-if="hero.banner"
                                                        class="mb-3"
                                                    >
                                                        <div
                                                            class="relative group"
                                                        >
                                                            <img
                                                                :src="
                                                                    hero.banner
                                                                "
                                                                alt="Banner preview"
                                                                class="w-full h-16 object-cover bg-white rounded-lg border border-gray-200"
                                                            />
                                                            <div
                                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"
                                                            ></div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-3">
                                                        <div class="relative">
                                                            <input
                                                                type="file"
                                                                accept="image/*"
                                                                @change="
                                                                    handleBannerUpload
                                                                "
                                                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-purple-500 file:text-white hover:file:bg-purple-600 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-all focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                            />
                                                        </div>

                                                        <div
                                                            v-if="hero.banner"
                                                            class="flex justify-end"
                                                        >
                                                            <button
                                                                @click="
                                                                    deleteImage(
                                                                        'banner',
                                                                    )
                                                                "
                                                                :disabled="
                                                                    isDeleting.banner
                                                                "
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="Delete banner"
                                                            >
                                                                <i
                                                                    v-if="
                                                                        isDeleting.banner
                                                                    "
                                                                    class="fas fa-spinner fa-spin mr-1"
                                                                ></i>
                                                                <i
                                                                    v-else
                                                                    class="fas fa-trash mr-1"
                                                                ></i>
                                                                <span
                                                                    class="hidden sm:inline"
                                                                    >Delete</span
                                                                >
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <p
                                                        class="text-xs text-gray-600 mt-2"
                                                    >
                                                        Recommended: 1200x400px,
                                                        PNG/JPG
                                                    </p>
                                                </div>

                                                <!-- Background Upload Card -->
                                                <div
                                                    class="sm:col-span-2 xl:col-span-1 rounded-xl border border-green-200 p-4 lg:p-5 hover:shadow-md transition-all duration-300"
                                                >
                                                    <div
                                                        class="flex items-center justify-between mb-3"
                                                    >
                                                        <label
                                                            class="flex items-center text-sm font-semibold text-gray-800"
                                                        >
                                                            <div
                                                                class="w-8 h-8 rounded-lg flex items-center justify-center mr-2"
                                                            >
                                                                <i
                                                                    class="fas fa-mountain text-white text-xs"
                                                                ></i>
                                                            </div>
                                                            Background
                                                        </label>
                                                        <div
                                                            class="text-xs text-gray-600 px-2 py-1 rounded-full"
                                                        >
                                                            Page Background
                                                        </div>
                                                    </div>

                                                    <!-- Background Preview -->
                                                    <div
                                                        v-if="
                                                            hero.background_image
                                                        "
                                                        class="mb-3"
                                                    >
                                                        <div
                                                            class="relative group"
                                                        >
                                                            <img
                                                                :src="
                                                                    hero.background_image
                                                                "
                                                                alt="Background preview"
                                                                class="w-full h-16 object-cover bg-white rounded-lg border border-gray-200"
                                                            />
                                                            <div
                                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"
                                                            ></div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-3">
                                                        <div class="relative">
                                                            <input
                                                                type="file"
                                                                accept="image/*"
                                                                @change="
                                                                    handlBackgroundUpload
                                                                "
                                                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-green-500 file:text-white hover:file:bg-green-600 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-all focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                            />
                                                        </div>

                                                        <div
                                                            v-if="
                                                                hero.background_image
                                                            "
                                                            class="flex justify-end"
                                                        >
                                                            <button
                                                                @click="
                                                                    deleteImage(
                                                                        'background',
                                                                    )
                                                                "
                                                                :disabled="
                                                                    isDeleting.background
                                                                "
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="Delete background"
                                                            >
                                                                <i
                                                                    v-if="
                                                                        isDeleting.background
                                                                    "
                                                                    class="fas fa-spinner fa-spin mr-1"
                                                                ></i>
                                                                <i
                                                                    v-else
                                                                    class="fas fa-trash mr-1"
                                                                ></i>
                                                                <span
                                                                    class="hidden sm:inline"
                                                                    >Delete</span
                                                                >
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <p
                                                        class="text-xs text-gray-600 mt-2"
                                                    >
                                                        Recommended:
                                                        1920x1080px, PNG/JPG
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Upload Tips -->
                                            <div
                                                class="border border-blue-200 rounded-xl p-4"
                                            >
                                                <div
                                                    class="flex items-start space-x-3"
                                                >
                                                    <div class="flex-shrink-0">
                                                        <i
                                                            class="fas fa-info-circle text-blue-500 mt-0.5"
                                                        ></i>
                                                    </div>
                                                    <div>
                                                        <h4
                                                            class="text-sm font-medium text-blue-800 mb-1"
                                                        >
                                                            Upload Tips
                                                        </h4>
                                                        <ul
                                                            class="text-xs text-gray-700 space-y-1"
                                                        >
                                                            <li>
                                                                • Use
                                                                high-quality
                                                                images for
                                                                better visual
                                                                impact
                                                            </li>
                                                            <li>
                                                                • Keep file
                                                                sizes under 2MB
                                                                for faster
                                                                loading
                                                            </li>
                                                            <li>
                                                                • Use consistent
                                                                brand colors
                                                                across all
                                                                assets
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Button -->
                                        <div
                                            class="pt-4 border-t border-gray-200"
                                        >
                                            <button
                                                @click="storeOrUpdate()"
                                                :disabled="isProcessing"
                                                class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-lime-500 to-lime-600 text-white font-semibold rounded-lg hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
                                            >
                                                <span
                                                    v-if="isProcessing"
                                                    class="flex items-center justify-center"
                                                >
                                                    <i
                                                        class="fas fa-spinner fa-spin mr-2"
                                                    ></i>
                                                    Processing...
                                                </span>
                                                <span v-else>
                                                    <i
                                                        class="fas fa-save mr-2"
                                                    ></i>
                                                    Save Changes
                                                </span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Live Preview Panel - Now below controls -->
                                    <div class="w-full">
                                        <div class="overflow-hidden">
                                            <!-- Preview Header -->
                                            <div
                                                class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center space-x-2"
                                                >
                                                    <span
                                                        class="text-sm font-medium text-gray-600"
                                                        >Live Preview</span
                                                    >
                                                </div>
                                                <div
                                                    class="flex items-center space-x-2"
                                                >
                                                    <div
                                                        class="text-xs text-gray-500 hidden sm:block"
                                                    >
                                                        Real-time updates
                                                    </div>
                                                    <button
                                                        @click="onExpandView()"
                                                        class="p-2 text-gray-500 hover:text-lime-600 hover:bg-lime-50 transition-all rounded-lg group"
                                                        :title="
                                                            eExpandView
                                                                ? 'Switch to Edit Mode'
                                                                : 'Expand Preview'
                                                        "
                                                    >
                                                        <i
                                                            :class="
                                                                eExpandView
                                                                    ? 'fas fa-edit'
                                                                    : 'fas fa-expand'
                                                            "
                                                            class="text-sm group-hover:scale-110 transition-transform"
                                                        ></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Preview Content -->
                                            <div class="p-4 sm:p-6">
                                                <div
                                                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden min-h-[400px] sm:min-h-[500px] relative"
                                                >
                                                    <!-- Hero Preview -->
                                                    <div
                                                        class="pointer-events-none relative overflow-hidden min-h-[350px] sm:min-h-[450px]"
                                                    >
                                                        <Hero
                                                            class="w-full h-full"
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Preview Footer -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Security Management Panel -->
                <transition name="fade" mode="out-in">
                    <div
                        v-if="selectedTab === securityTab"
                        key="security"
                        class="space-y-4 sm:space-y-6"
                    >
                        <div
                            class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <!-- Card Header -->
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center">
                                    <div class="text-2xl sm:text-3xl mr-3">
                                        🔒
                                    </div>
                                    <div>
                                        <h2
                                            class="text-lg sm:text-xl font-bold text-gray-900"
                                        >
                                            Security & Access Control
                                        </h2>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Monitor system activity and security
                                            logs
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="p-4 sm:p-6">
                                <!-- Activity Logs -->
                                <div>
                                    <h3
                                        class="text-base sm:text-lg font-semibold text-gray-800 mb-4"
                                    >
                                        <i
                                            class="fas fa-history mr-2 text-gray-600"
                                        ></i>
                                        Activity Logs
                                    </h3>

                                    <!-- Mobile: Card Layout -->
                                    <div class="block sm:hidden space-y-3">
                                        <div
                                            v-for="(log, index) in logs"
                                            :key="index"
                                            class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                                        >
                                            <div
                                                class="text-xs text-gray-500 mb-1"
                                            >
                                                {{ log.created_at }}
                                            </div>
                                            <div class="text-sm text-gray-900">
                                                {{ log.activity }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="!logs.length"
                                            class="text-center py-8 text-gray-500"
                                        >
                                            <i
                                                class="fas fa-inbox text-2xl mb-2"
                                            ></i>
                                            <p class="text-sm">
                                                No activity logs found
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Desktop: Table Layout -->
                                    <div
                                        class="hidden sm:block overflow-x-auto"
                                    >
                                        <div
                                            class="min-w-full border border-gray-200 rounded-lg overflow-hidden"
                                        >
                                            <table
                                                class="min-w-full divide-y divide-gray-200"
                                            >
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th
                                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                                        >
                                                            Timestamp
                                                        </th>
                                                        <th
                                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                                        >
                                                            Activity
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="bg-white divide-y divide-gray-200"
                                                >
                                                    <tr
                                                        v-for="(
                                                            log, index
                                                        ) in logs"
                                                        :key="index"
                                                        class="hover:bg-gray-50 transition-colors"
                                                    >
                                                        <td
                                                            class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap"
                                                        >
                                                            {{ log.created_at }}
                                                        </td>
                                                        <td
                                                            class="px-4 py-3 text-sm text-gray-900"
                                                        >
                                                            {{ log.activity }}
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!logs.length">
                                                        <td
                                                            colspan="2"
                                                            class="px-4 py-8 text-center text-gray-500"
                                                        >
                                                            <i
                                                                class="fas fa-inbox text-2xl mb-2 block"
                                                            ></i>
                                                            No activity logs
                                                            found
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </main>

        <!-- Custom Delete Confirmation Dialog -->
        <div
            v-if="showDeleteDialog"
            class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click="cancelDelete"
        >
            <div
                @click.stop
                class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 animate-fade-in"
            >
                <!-- Dialog Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center"
                        >
                            <i class="fas fa-trash text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Delete
                                {{
                                    deleteImageType.charAt(0).toUpperCase() +
                                    deleteImageType.slice(1)
                                }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                This action cannot be undone
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dialog Body -->
                <div class="p-6">
                    <p class="text-gray-700">
                        Are you sure you want to delete the
                        <span class="font-semibold text-gray-900">{{
                            deleteImageType
                        }}</span
                        >? This will permanently remove the image from your
                        platform.
                    </p>
                </div>

                <!-- Dialog Actions -->
                <div
                    class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3"
                >
                    <button
                        @click="cancelDelete"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium flex items-center space-x-2"
                    >
                        <i class="fas fa-trash text-sm"></i>
                        <span
                            >Delete
                            {{
                                deleteImageType.charAt(0).toUpperCase() +
                                deleteImageType.slice(1)
                            }}</span
                        >
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Dialog -->
        <div
            v-if="showErrorDialog"
            class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click="closeErrorDialog"
        >
            <div
                @click.stop
                class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 animate-fade-in"
            >
                <!-- Dialog Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center"
                        >
                            <i
                                class="fas fa-exclamation-triangle text-red-600 text-lg"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Error
                            </h3>
                            <p class="text-sm text-gray-600">
                                Something went wrong
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dialog Body -->
                <div class="p-6">
                    <p class="text-gray-700">
                        {{ errorDialogMessage }}
                    </p>
                </div>

                <!-- Dialog Actions -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end">
                    <button
                        @click="closeErrorDialog"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Custom file input styling for better mobile experience */
input[type="file"]::-webkit-file-upload-button {
    @apply rounded border-0 bg-gray-100 text-gray-700 text-xs py-1 px-3 mr-2 hover:bg-gray-200 transition-colors cursor-pointer;
}

/* Focus styles for better accessibility */
button:focus,
input:focus,
textarea:focus {
    outline: 2px solid #84cc16;
    outline-offset: 2px;
}

/* Mobile-friendly hover states */
@media (hover: hover) {
    .hover\:scale-\[1\.02\]:hover {
        transform: scale(1.02);
    }
}

/* Animation for dialog */
.animate-fade-in {
    animation: fadeInDialog 0.3s ease-out;
}

@keyframes fadeInDialog {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Custom scrollbar for preview area */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* For Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* Hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

/* Responsive flex behavior */
@media (max-width: 1023px) {
    .flex-col.lg\:flex-row {
        min-height: auto;
    }
}
</style>
