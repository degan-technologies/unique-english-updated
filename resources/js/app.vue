<script setup>
    import { onMounted } from "vue";
    import { storeToRefs } from "pinia";

    import Body from "@/components/Body.vue";
    import Login from "@/components/Auth/Login.vue";
    import RegisterForm from "@/components/Auth/RegisterForm.vue"; 

    import { useAppStore } from '@/store/useAppStore';
    import { useSessionStore } from '@/store/useSessionStore';
    import { useAuthStore } from '@/store/useAuthStore';

    const appStore = useAppStore();
    const AuthStore = useAuthStore();
    const sessionStore = useSessionStore();

    const { showLoginForm, showRegistrationForm, } = storeToRefs(AuthStore);

    onMounted(async () => { 
        await Promise.all([
            appStore.fetchFrontLanguages(),
            appStore.getHeroSection(),
            sessionStore.initializeSession(),
        ]);
    })

</script>

<template>
    <div>
        <div class="overflow-hidden">
             <Body />
        </div>

        <transition v-if="showLoginForm || showRegistrationForm" name="fade">
            <div
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm z-50" >
                <div v-if="showLoginForm">
                    <Login/>
                </div>
                <div v-else-if="showRegistrationForm">
                    <RegisterForm/>
                </div>
            </div>
        </transition>

    </div>
</template>

<style>
/* Ensure components don't overlap */
body {
    margin: 0;
    padding: 0;
}
</style>
