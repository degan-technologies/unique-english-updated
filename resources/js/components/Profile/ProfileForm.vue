<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { storeToRefs } from "pinia";

import ProfileSettings from './profileSetting.vue';
import ChangePassword from './ChangePassword.vue';
import BankAccount from './Bank/BankAccount.vue';

import { useSessionStore } from "@/store/useSessionStore";

const sessionStore = useSessionStore();
const { authUser } = storeToRefs(sessionStore);
const route = useRoute();
const router = useRouter();

const activeTab = ref('profile');

onMounted(() => {
    if (route.query.currentTab !== 'profile') {
        router.replace({
             name: route.name, 
             query: { currentTab: 'profile' } });
    }
});

function setActiveTab(tab) {
    if(tab === 'account' && authUser.role =='student'){
        return;        
    }
    activeTab.value = tab;
}
</script>
<template>
    <div class="flex flex-col md:flex-row bg-gray-100">
        <section class="mt-5 w-full">
            <div class="text-xl font-bold mb-5">Account Settings</div>
            <div class="flex flex-wrap gap-6">
                <nav class="flex-none w-full md:w-64 bg-white p-4 rounded-lg shadow h-fit">
                    <ul>
                        <li @click="setActiveTab('profile')"
                            :class="{
                                'bg-lime-700 text-white': activeTab === 'profile',
                                'hover:bg-lime-500': activeTab !== 'profile'
                            }"
                            class="py-2 px-3 cursor-pointer">
                            👤 Profile Settings
                        </li>
                        <li @click="setActiveTab('password')"
                            :class="{
                                'bg-lime-700 text-white': activeTab === 'password',
                                'hover:bg-lime-500': activeTab !== 'password'
                            }"
                            class="py-2 px-3 cursor-pointer">
                            🔒 Password
                        </li>
                        <li @click="setActiveTab('account')"
                            v-if="authUser.role!=='student'"
                            :class="{
                                'bg-lime-700 text-white': activeTab === 'account',
                                'hover:bg-lime-500': activeTab !== 'account'
                            }"
                            class="py-2 px-3 cursor-pointer">
                            💳 Add Account
                        </li>
                    </ul>
                </nav>

                <div class="flex-1 bg-white p-5 rounded-lg shadow">
                    <ProfileSettings v-if="activeTab === 'profile'" />
                    <ChangePassword v-if="activeTab === 'password'" />
                    <BankAccount v-if="activeTab === 'account' && authUser.role !== 'student'" />
                </div>
            </div>
        </section>
    </div>
</template>
