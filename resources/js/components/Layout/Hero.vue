<script setup>
import { ref } from "vue";
import { storeToRefs } from "pinia";
import Test from "../Course/Test.vue";

import { useAppStore } from "@/store/useAppStore";


const appStore = useAppStore();
const { hero} = storeToRefs(appStore);

const activeTab = ref(null);

const setTab = (tabIndex) => {
    activeTab.value = tabIndex;
};

</script>

<template>
    <section class="bg-cover bg-center bg-no-repeat py-16" :style="{ backgroundImage: `url(${hero?.background_image})` }">
        <div class="container mx-auto flex flex-col-reverse lg:flex-row items-center px-6">
 
            <!-- Content Section -->
            <div class="text-center lg:text-left lg:max-w-lg">
                <h1 class="text-4xl md:text-5xl font-bold leading-tight text-gray-800">{{ hero?.title }}</h1>
                <p class="mt-4 text-gray-600 text-lg">{{ hero?.description }} </p>
                <div class="mt-6 flex justify-center lg:justify-start space-x-4">
                    <button @click="setTab(0)"
                        class="bg-lime-700 text-white px-6 py-3 rounded-md hover:bg-lime-800 transition">
                        Test Your Level
                    </button>
                    <button @click="setTab(1)"
                        class="bg-gray-800 text-white px-6 py-3 rounded-md hover:bg-gray-900 transition">
                        Explore Courses
                    </button>
                </div>
            </div>

            <!-- Image Section -->
            <div class="flex justify-center lg:justify-end w-full lg:w-1/2 mt-8 lg:mt-0">
                <img :src="hero?.banner" alt="E-learning Illustration" class="rounded-lg shadow-lg max-w-full" />
            </div>
        </div>
    </section>

    <!-- Tabs Content -->
    <div v-if="activeTab === 0">
       
        <Test />
    </div>
    <div v-if="activeTab === 1"></div>
</template> 