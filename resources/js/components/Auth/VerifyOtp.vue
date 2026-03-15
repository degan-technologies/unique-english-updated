<script setup>
import { useAppStore } from "@/store/useAppStore";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { computed, onMounted, onUnmounted, ref } from "vue";

const appStore = useAppStore();
const { otpEmail, isEmailVerification } = storeToRefs(appStore);

const otp = ref(["", "", "", "", "", ""]);
const otpLoading = ref(false);
const otpError = ref("");
const otpSuccess = ref("");
const activeInput = ref(0);

const timeLeft = ref(300);
let interval = null;

// Add function to close OTP modal
const closeOTPModal = () => {
    // Clear the OTP email to hide this component
    appStore.clearOtpEmail();
    // Clear any timers
    if (interval) {
        clearInterval(interval);
    }
};

const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
});

const isTimeExpired = computed(() => timeLeft.value <= 0);

const handleOtpSubmit = async () => {
    const otpCode = otp.value.join("");

    if (otpCode.length !== 6) {
        otpError.value = "Please enter a complete 6-digit OTP";
        shakeInputs();
        setTimeout(() => (otpError.value = ""), 3000);
        return;
    }

    otpLoading.value = true;
    otpError.value = "";

    try {
        // Use the same endpoint for both registration and login email verification
        const response = await Axios.post("/api/verify-otp", {
            contact_info: otpEmail.value,
            registration_method: "email",
            otp: otpCode,
        });

        otpSuccess.value = response.data.message;
        // Token is in the HttpOnly cookie set by the server —
        // store the expiry time so the proactive logout timer can fire.
        appStore.setTokenExpiry(response.data.expires_at);
        appStore.changeLoginStatus(true);
        appStore.clearOtpEmail();

        setTimeout(() => {
            otpSuccess.value = "";
        }, 2000);
    } catch (err) {
        otpError.value =
            err.response?.data?.message ||
            "Verification failed. Please try again.";
        shakeInputs();
        setTimeout(() => (otpError.value = ""), 3000);
    } finally {
        otpLoading.value = false;
    }
};

const focusNext = (index, event) => {
    if (event.target.value && index < 5) {
        activeInput.value = index + 1;
        document.getElementById(`otp-${index + 1}`).focus();
    }
};

const handlePaste = (e) => {
    e.preventDefault();
    const pasteData = e.clipboardData.getData("text/plain").trim();
    if (/^\d{6}$/.test(pasteData)) {
        otp.value = pasteData.split("").slice(0, 6);
        activeInput.value = 5;
        document.getElementById(`otp-5`).focus();
    }
};

const handleKeyDown = (index, e) => {
    if (e.key === "Backspace" && !otp.value[index] && index > 0) {
        activeInput.value = index - 1;
        document.getElementById(`otp-${index - 1}`).focus();
    } else if (e.key === "ArrowLeft" && index > 0) {
        activeInput.value = index - 1;
        document.getElementById(`otp-${index - 1}`).focus();
    } else if (e.key === "ArrowRight" && index < 5) {
        activeInput.value = index + 1;
        document.getElementById(`otp-${index + 1}`).focus();
    }
};

const shakeInputs = () => {
    const inputs = document.querySelectorAll('[id^="otp-"]');
    inputs.forEach((input) => {
        input.classList.add("animate-shake");
        setTimeout(() => input.classList.remove("animate-shake"), 500);
    });
};

const resendOtp = async () => {
    if (isTimeExpired.value) {
        timeLeft.value = 300;
        startTimer();
    }

    otpLoading.value = true;
    otpError.value = "";

    try {
        // Use the same endpoint for both registration and login email verification resend
        await Axios.post("/api/resend-otp", {
            contact_info: otpEmail.value,
            registration_method: "email",
        });

        otpSuccess.value = "New OTP sent successfully!";
        setTimeout(() => (otpSuccess.value = ""), 3000);

        // Reset OTP fields
        otp.value = ["", "", "", "", "", ""];
        activeInput.value = 0;
        document.getElementById(`otp-0`).focus();
    } catch (err) {
        otpError.value =
            err.response?.data?.message ||
            "Failed to resend OTP. Please try again.";
        setTimeout(() => (otpError.value = ""), 3000);
    } finally {
        otpLoading.value = false;
    }
};

const startTimer = () => {
    clearInterval(interval);
    interval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(interval);
        }
    }, 1000);
};

onMounted(() => {
    startTimer();
    // Focus first input on mount
    document.getElementById(`otp-0`)?.focus();
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <div
        @click="closeOTPModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-3 sm:p-4 md:p-6 lg:p-8 z-50 backdrop-blur-sm"
    >
        <div
            @click.stop
            class="bg-white p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-3xl shadow-xl w-full max-w-[90vw] sm:max-w-lg md:max-w-xl lg:max-w-2xl xl:max-w-md transform transition-all duration-300 animate-fade-in mx-3 sm:mx-4 md:mx-6 lg:mx-8 max-h-[95vh] overflow-y-auto relative"
        >
            <!-- Close Icon -->
            <button
                @click="closeOTPModal"
                class="absolute top-4 right-4 sm:top-5 sm:right-5 text-gray-400 hover:text-gray-600 transition-colors z-10 p-1"
            >
                <i class="fas fa-times text-lg sm:text-xl md:text-2xl"></i>
            </button>

            <div class="text-center mb-4 sm:mb-6 pt-8 sm:pt-10">
                <div
                    class="w-12 h-12 sm:w-16 sm:h-16 bg-lime-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 sm:h-8 sm:w-8 text-lime-700"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">
                    {{
                        isEmailVerification
                            ? "Verify Email to Login"
                            : "Verify Your Email"
                    }}
                </h1>
                <p class="text-sm sm:text-base text-gray-600">
                    {{
                        isEmailVerification
                            ? "Please verify your email address to continue with login."
                            : "We sent a 6-digit code to"
                    }}
                    <span class="font-medium text-gray-800">{{
                        otpEmail
                    }}</span>
                </p>
            </div>

            <!-- OTP Input Fields -->
            <div class="flex justify-between mb-4 sm:mb-6 gap-1 sm:gap-2">
                <input
                    v-for="(digit, index) in otp"
                    :id="`otp-${index}`"
                    :key="index"
                    v-model="otp[index]"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    :class="[
                        'w-full h-12 sm:h-14 text-center text-lg sm:text-2xl border-2 rounded-lg focus:outline-none transition-all duration-200',
                        otpError
                            ? 'border-red-400 bg-red-50'
                            : 'border-gray-200 focus:border-lime-500 focus:ring-2 focus:ring-lime-200',
                        activeInput === index
                            ? 'ring-2 ring-lime-300 border-lime-500'
                            : '',
                    ]"
                    @input="focusNext(index, $event)"
                    @keydown="handleKeyDown(index, $event)"
                    @focus="activeInput = index"
                    @paste="handlePaste"
                />
            </div>

            <!-- Timer -->
            <div class="text-center mb-4 sm:mb-6">
                <p class="text-xs sm:text-sm text-gray-600">
                    you will receive Code with in
                    <span
                        :class="{
                            'font-semibold': true,
                            'text-red-500': timeLeft < 60,
                            'text-gray-700': timeLeft >= 60,
                        }"
                    >
                        {{ formattedTime }}
                    </span>
                </p>
            </div>

            <!-- Submit Button -->
            <button
                @click="handleOtpSubmit"
                :disabled="otpLoading"
                class="w-full bg-lime-600 hover:bg-lime-700 text-white py-2.5 sm:py-3 px-4 rounded-lg font-medium transition duration-300 flex items-center justify-center shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
            >
                <span v-if="otpLoading" class="flex items-center">
                    <svg
                        class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Verifying...
                </span>
                <span v-else>Verify & Continue</span>
            </button>

            <!-- Resend OTP Link -->
            <div class="text-center mt-3 sm:mt-4">
                <p class="text-xs sm:text-sm text-gray-600">
                    Didn't receive the code?
                    <button
                        @click="resendOtp"
                        :disabled="otpLoading || !isTimeExpired"
                        :class="{
                            'text-lime-600 hover:text-lime-700 hover:underline':
                                !otpLoading && isTimeExpired,
                            'text-gray-400 cursor-not-allowed':
                                otpLoading || !isTimeExpired,
                        }"
                        class="font-medium focus:outline-none transition"
                    >
                        Resend OTP
                    </button>
                </p>
            </div>

            <!-- Feedback Messages -->
            <transition name="fade">
                <div
                    v-if="otpSuccess"
                    class="mt-3 sm:mt-4 p-2.5 sm:p-3 bg-green-50 text-green-700 text-xs sm:text-sm rounded-lg text-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 sm:h-5 sm:w-5 inline mr-1"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ otpSuccess }}
                </div>
            </transition>

            <transition name="fade">
                <div
                    v-if="otpError"
                    class="mt-3 sm:mt-4 p-2.5 sm:p-3 bg-red-50 text-red-700 text-xs sm:text-sm rounded-lg text-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 sm:h-5 sm:w-5 inline mr-1"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ otpError }}
                </div>
            </transition>
        </div>
    </div>
</template>

<style>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-shake {
    animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }

    20%,
    60% {
        transform: translateX(-5px);
    }

    40%,
    80% {
        transform: translateX(5px);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>
