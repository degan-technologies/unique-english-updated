<script setup>
import Axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'

import { useAppStore } from '@/store/useAppStore'
const appStore = useAppStore();
const { profileUpdated, hero } = storeToRefs(appStore);


import Hero from "@/components/Layout/Hero.vue"; 

const settingTab = ref('setting');
const securityTab = ref('security');
const selectedTab = ref(settingTab.value); 
const eExpandView = ref(true);
const isProcessing = ref(false);

const logs = ref([]);

const handleLogoUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    hero.value.selectedLogo = file;
    hero.value.logo = URL.createObjectURL(file);

    return;
 }

 const handleBannerUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    hero.value.selectedbanner = file;
    hero.value.banner = URL.createObjectURL(file);

    return;
 }

 const handlBackgroundUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    hero.value.selectedBackground = file;
    hero.value.background_image = URL.createObjectURL(file);

    return;
 }

 function isFileObject(obj) {
    return obj instanceof File && typeof obj.name === 'string' && typeof obj.size === 'number';
}

function storeOrUpdate() {
    isProcessing.value = true;

    const formData = new FormData()

    formData.append('title', hero.value.title);
    formData.append('description', hero.value.description);

    if (isFileObject(hero.value.selectedLogo)) {
        formData.append('logo', hero.value.selectedLogo);
    } else {
        formData.delete('logo');
    }
    
    if (isFileObject(hero.value.selectedbanner)) {
        formData.append('banner', hero.value.selectedbanner);
    } else {
        formData.delete('banner');
    }

    if (isFileObject(hero.value.selectedBackground)) {
        formData.append('background_image', hero.value.selectedBackground);
    } else {
        formData.delete('background_image');
    }
      

    Axios
        .post('/api/hero-section', formData)
        .then(res => { isProcessing.value = false});
 
}

function onExpandView() {
    eExpandView.value = ! eExpandView.value
}

function onSelectTab(tab) {
    selectedTab.value = tab;
}


function getActivityLogs() {
    Axios
        .get('/api/activity-logs')
        .then(res => {
            logs.value = res.data.data
        });
}

onMounted(()=>{
    getActivityLogs();
})
   
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Page Header -->
        <header class="bg-white shadow p-4 flex items-center">
            <h1 class="text-2xl font-semibold text-gray-800 flex-1">
                Platform Settings & Security Management
            </h1>
        </header>

        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex"
                aria-label="Tabs">
                <button  
                    @click="onSelectTab(settingTab)"
                    :class="{
                        'border-lime-700 text-lime-700': selectedTab === settingTab,
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': selectedTab !== settingTab,
                    }"
                    class=" 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none'">
                    Platform Settings
                </button>
                <button  
                    @click="onSelectTab(securityTab)"
                    :class="{
                        'border-lime-700 text-lime-700': selectedTab === securityTab,
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': selectedTab !== securityTab,
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                    Security Management
                </button>
            </nav>
        </div>

        <div class="flex flex-1 w-full overflow-hidden"> 
            <main class="flex-1">
                <!-- Platform Settings Panel -->
                <transition name="fade" mode="out-in">
                    <div v-if="selectedTab === settingTab" key="theme">
                        <!-- Dashboard Theme & Branding Card -->
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <div class="flex items-center mb-4">
                                <div class="text-3xl text-blue-500 mr-2">
                                    🎨
                                </div>
                                <h2 class="text-xl font-semibold">Home & Landing Page Branding</h2>
                            </div>
                            <div class="flex flex-col md:flex-row gap-6">
                                <!-- Customization Controls -->
                                <div v-if="!eExpandView" 
                                    class="flex-1 md:max-w-[30%]  space-y-4"> 
                                    <!-- Font Selector --> 
                                     <div>
                                        <label class="block text-gray-700 font-medium mb-1">Hero Title</label>
                                        <input v-model="hero.title" class="w-full border rounded p-2"> 
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1">Hero Description</label>
                                        <textarea
                                            v-model="hero.description"
                                            placeholder="Enter test question"
                                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                                        ></textarea>
                                    </div>
                                    <!-- Logo Upload -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1">Upload Logo</label>
                                        <input type="file" @change="handleLogoUpload"
                                            class="border rounded p-1 w-full" />
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1">Upload Banner</label>
                                        <input type="file" @change="handleBannerUpload"
                                            class="border rounded p-1 w-full" />
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1">Upload background</label>
                                        <input type="file" @change="handlBackgroundUpload"
                                            class="border rounded p-1 w-full" />
                                    </div>
                                     
                                    <!-- Reset Button -->
                                    <div class="flex flex-row gap-4"> 
                                        <button @click="storeOrUpdate()"
                                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                                            <span v-if="isProcessing">
                                                <span
                                                    class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-lime-600 mb-2">
                                                </span>
                                                <span class="text-sm">Processing ...</span>
                                            </span>
                                            <span v-else >save</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Live Preview Panel -->
                                <div class="flex-1 relative bg-gray-50 rounded-lg shadow-inner p-4">
                                    <!-- Actions disabled --> 
                                    <div class="pointer-events-none">
                                        <Hero class="w-full h-full pointer-events-none " />
                                    </div>
                                    <div class="z-50 my-2 h-fit w-full rounded-full justify-end right-6">
                                        <i 
                                            @click="onExpandView()"
                                            :class="{
                                                'fa-edit': eExpandView,
                                                'fa-expand': !eExpandView,
                                            }"
                                            class="fa-solid font-bold text-2xl text-blue-600"></i>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>
                </transition>

                <!-- Security Management Panel -->
                <transition name="fade" mode="out-in">
                    <div v-if="selectedTab === securityTab" key="security" class="h-full">
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <div class="flex items-center mb-4">
                                <div class="text-3xl text-red-500 mr-2">
                                    🔒
                                </div>
                                <h2 class="text-xl font-semibold">Security & Access Control</h2>
                            </div>  
                            <!-- Activity Logs -->
                            <div>
                                <h3 class="font-medium text-gray-700 mb-2">Activity Logs</h3> 
                                <div class="h-full">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="py-2 px-4 text-left">Timestamp</th>
                                                <th class="py-2 px-4 text-left">Activity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(log, index) in logs" :key="index"
                                                class="border-b hover:bg-gray-50">
                                                <td class="py-2 px-4">{{ log.created_at }}</td>
                                                <td class="py-2 px-4">{{ log.activity }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </main>
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
</style>