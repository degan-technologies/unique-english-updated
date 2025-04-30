<script setup>
import Axios from 'axios';
import { ref } from 'vue';
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
 
import { useAuthStore } from '@/store/useAuthStore';
import { useAppStore } from '@/store/useAppStore';
import { UseStudentStore } from "@/store/UseStudentStore";

import Spinner from "@/components/Layout/Spinner.vue";

const loading = ref(true);
const plans = ref([]);

const appStore = useAppStore();
const AuthStore = useAuthStore();
const studentStore = UseStudentStore();

const { liveSchedulTab } = storeToRefs(studentStore);
const { isLoggedIn } = storeToRefs(appStore);
const { showLoginForm } = storeToRefs(AuthStore);

const fetchPlans = async () => { 
     await Axios
                .get("/api/get-plans")
                .then(res=>{                        
                    plans.value = res.data.data;
                })  
};

function enrollCourse(item, price_type) {
    
    if(!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }

    loading.value = true;

    if (item.isMyLive) {
        router.push({
            name: "student",
            query: { tab: liveSchedulTab.value, slug: item.slug },
        });
        selectedCourseSlug.value = item.slug;
        loading.value = false;
        return;
    }

    const selectedItem = [{ type: "live", slug: item.slug, live_price_type:price_type }];
    Axios
        .post("/api/initiate-payment", { cartItems: selectedItem })
        .then((res) => {
            const newWindow = window.open("", "_blank");
            if (newWindow) {
                newWindow.location.href = res.data.checkout_url;
            } else {
                console.error("Popup blocked. Please allow popups for this site.");
            }
            checkoutUrl.value = res.data.checkout_url;
            loading.value = false;
        })
        .catch((error) => {
            loading.value = false;
            console.error("Payment initiation error:", error);
        });
    }

onMounted(()=>{
    fetchPlans();
    loading.value = false;
})
</script>   

<template>
    <div class="w-[90%] my-24 mx-auto">
        <div  v-if="loading"
            class="flex justify-center items-center h-64">
            <Spinner />
        </div>
        
        <div v-if="!loading">
            <div class="flex flex-col text-center py-4">
                <h2 class="text-4xl text-gray-900 font-bold">Live Class Plan</h2>
                <p class="text-gray-500 mb-6">Perfect for online learning sessions</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="plan in plans"
                    :key="plan"
                    class="flex flex-col items-center justify-center  my-8 px-4">
                    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full text-center">
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-gray-900">{{ plan?.name }}</span> 
                        </div>

                        <ul class="text-gray-600 mb-8 space-y-4">
                            <li class="flex items-left justify-left gap-2">
                                ✅ 10 Live Classes per Month
                            </li> 
                            <li class="flex items-left justify-left gap-2">
                                ✅ Direct Chat with Instructor
                            </li>
                            <li class="flex items-left justify-left gap-2">
                                ✅ Certificate upon Completion
                            </li>
                        </ul>

                        <span class="text-gray-500 text-lg font-bold">Join Our Live</span>
                        <div class="flex flex-row gap-4 my-4 items-center justify-center w-full">
                            <div class="w-full">
                                <label class="block text-gray-700 font-medium py-2">{{ plan.one_to_one_price }} ETB</label>
                                <button @click="enrollCourse(plan, 'individual')"
                                    class="w-full bg-lime-600 hover:bg-lime-700 text-white font-semibold py-3 rounded-full transition">
                                    Individual
                                </button>
                            </div>
                            <div class="w-full">
                                <label class="block text-gray-700 font-medium py-2">{{ plan.group_price }} ETB</label>
                                <button @click="enrollCourse(plan, 'group')"
                                    class="w-full bg-lime-600 hover:bg-lime-700 text-white font-semibold py-3 rounded-full transition">
                                    Group
                                </button>
                            </div>
                        <div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>