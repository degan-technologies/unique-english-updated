<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";

import { useAuthStore } from "@/store/useAuthStore";
import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import Spinner from "@/components/Layout/Spinner.vue";

// Constants
const PRICE_TYPES = {
    INDIVIDUAL: 'individual',
    GROUP: 'group'
};

// Refs
const loading = ref(true);
const isLoading = ref(false);
const selectedPriceType = ref(null);
const plans = ref([]);
const checkoutUrl = ref('');

// Stores
const appStore = useAppStore();
const authStore = useAuthStore();
const studentStore = UseStudentStore();
const router = useRouter();

// Store refs
const { liveSchedulTab, selectedCourseSlug } = storeToRefs(studentStore);
const { isLoggedIn } = storeToRefs(appStore);
const { showLoginForm } = storeToRefs(authStore);

// Methods
const fetchPlans = async () => {
    try {
        const response = await Axios.get("/api/get-plans");
        plans.value = response.data.data;
    } catch (error) {
        console.error("Error fetching plans:", error);
    } finally {
        loading.value = false;
    }
};

const handleEnrollment = async (item, priceType) => {
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }

    isLoading.value = true;
    selectedPriceType.value = priceType;    

    try {
        // if (item.isMyLive) {
        //     await handleExistingCourse(item);
        //     return;
        // }

        await initiatePayment(item, priceType);
    } catch (error) {
        console.error("Enrollment error:", error);
    } finally {
        isLoading.value = false;
        selectedPriceType.value = null;
    }
};

const handleExistingCourse = (item) => {
    router.push({
        name: "student",
        query: { tab: liveSchedulTab.value, slug: item.slug },
    });
    selectedCourseSlug.value = item.slug;
};

const initiatePayment = async (item, priceType) => {
    const selectedItem = [{
        type: "live",
        slug: item.slug,
        live_price_type: priceType
    }];

    const response = await Axios.post("/api/initiate-payment", {
        cartItems: selectedItem
    });

    checkoutUrl.value = response.data.checkout_url;
    openPaymentWindow(response.data.checkout_url);
};

const openPaymentWindow = (url) => {
    const newWindow = window.open("", "_blank");
    if (newWindow) {
        newWindow.location.href = url;
    } else {
        console.error("Popup blocked. Please allow popups for this site.");
    }
};

// Lifecycle
onMounted(fetchPlans);
</script>

<template>
    <div class="w-[90%] my-24 mx-auto">
        <div v-if="loading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>

        <div v-else>
            <div class="flex flex-col text-center py-4">
                <h2 class="text-4xl text-lime-700 font-bold">
                    Live Class Plan
                </h2>
                <h4 class="text-gray-500 mb-6">
                    Perfect for online learning sessions
                </h4>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="plan in plans" :key="plan.id" class="flex flex-col items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full text-center">
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-lime-700">
                                {{ plan.name }}
                            </span>
                        </div>

                        <ul class="text-gray-600 mb-8 space-y-4">
                            <li class="flex items-center gap-3">
                                <i class="fa fa-check text-lime-600 text-lg"></i>
                                <span class="text-left">10 Live Classes per Month</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fa fa-check text-lime-600 text-lg"></i>
                                <span class="text-left">Direct Chat with Instructor</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fa fa-check text-lime-600 text-lg"></i>
                                <span class="text-left">Certificate upon Completion</span>
                            </li>
                        </ul>

                        <span class="text-gray-500 text-lg font-bold">Join Our Live</span>
                        <div class="flex flex-row gap-4 my-4 items-center justify-center w-full">
                            <div class="w-full">
                                <label class="block text-gray-700 font-medium py-2">
                                    {{ plan.one_to_one_price }} ETB
                                </label> 
                                <button  @click="handleEnrollment(plan, PRICE_TYPES.INDIVIDUAL)" :disabled="isLoading || plan.planType == PRICE_TYPES.INDIVIDUAL"
                                    class="mt-4 w-full bg-lime-600 hover:bg-lime-700 text-white py-2 px-4 rounded-lg font-semibold transition flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed">
                                    <template v-if="isLoading && selectedPriceType==PRICE_TYPES.INDIVIDUAL">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Processing...
                                    </template>
                                    <template v-else>
                                      {{ plan.planType == PRICE_TYPES.INDIVIDUAL ? 'Paid' : 'Individual' }}
                                    </template>
                                </button>
                            </div>
                            <div class="w-full">
                                <label class="block text-gray-700 font-medium py-2">
                                    {{ plan.group_price }} ETB
                                </label> 
                                 <button  @click="handleEnrollment(plan, PRICE_TYPES.GROUP)" :disabled="isLoading || plan.planType == PRICE_TYPES.GROUP"
                                    class="mt-4 w-full bg-lime-600 hover:bg-lime-700 text-white py-2 px-4 rounded-lg font-semibold transition flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed">
                                    <template v-if="isLoading && selectedPriceType==PRICE_TYPES.GROUP" >
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Processing...
                                    </template>
                                    <template v-else>
                                     {{ plan.planType == PRICE_TYPES.GROUP ? 'Paid' : 'Group' }} 
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>