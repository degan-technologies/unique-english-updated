<script setup>
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";

import Login from "@/components/Auth/Login.vue";
import RegisterForm from "@/components/Auth/RegisterForm.vue";
import VerifyOtp from "@/components/Auth/VerifyOtp.vue";
import Body from "@/components/Body.vue";

import { useIdleTimeout } from "@/composables/useIdleTimeout";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";

const appStore = useAppStore();
const AuthStore = useAuthStore();

const { authUser, otpEmail } = storeToRefs(appStore);
const { showLoginForm, showRegistrationForm } = storeToRefs(AuthStore);

// Mount idle-timeout handler — logs out after 15 min of inactivity.
// Safe to call unconditionally; it only activates when isLoggedIn is true.
useIdleTimeout();

// Session-expired / logout reason banner
const sessionMessage = ref("");
let sessionMessageTimer = null;

onMounted(() => {
    appStore.fetchUserInfo();
    appStore.fetchFrontLanguages();
    appStore.getHeroSection();

    // Show a banner if a forced logout just happened (e.g. token expiry / idle)
    const reason = sessionStorage.getItem("logoutReason");
    if (reason) {
        sessionStorage.removeItem("logoutReason");
        sessionMessage.value = reason;
        // Auto-dismiss after 5 seconds
        sessionMessageTimer = setTimeout(() => {
            sessionMessage.value = "";
        }, 5000);
    }
});
</script>

<template>
    <div>
        <!-- Session-expired / logout reason banner -->
        <Teleport to="body">
            <div
                v-if="sessionMessage"
                class="session-banner"
                role="alert"
                @click="sessionMessage = ''"
            >
                {{ sessionMessage }}
            </div>
        </Teleport>

        <div class="overflow-hidden">
            <Body />
        </div>

        <!-- Auth modals — stacked at z-50 -->
        <Teleport to="body">
            <Login v-if="showLoginForm" />
            <RegisterForm v-else-if="showRegistrationForm" />
            <!-- OTP modal shown after registration or unverified email login -->
            <VerifyOtp v-else-if="otpEmail" />
        </Teleport>
    </div>
</template>

<style>
/* Ensure components don't overlap */
body {
    margin: 0;
    padding: 0;
}

/* Session-expired / forced-logout banner */
.session-banner {
    position: fixed;
    top: 1.25rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background: #ef4444;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    cursor: pointer;
    white-space: nowrap;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-1rem);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>
