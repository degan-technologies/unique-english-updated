<script setup>
import Axios from "axios";
import { onMounted, ref } from "vue";

const banklists = ref([]);
const myBankInfo = ref(null);
const isLoading = ref(false);
const showPassword = ref(false);
const showBankDropdown = ref(false);
const errors = ref({
    full_name: "",
    account_number: "",
    bank_name: "",
    password: "",
    general: "",
});

const password = ref("");
const bank = ref({
    full_name: "",
    bank_name: "",
    bank_code: "",
    account_number: "",
});

function getBankLists() {
    Axios.get("/api/bank-lists")
        .then((res) => {
            banklists.value = res.data.data.data;
        })
        .catch(() => {
            errors.value.general = "Failed to load bank list";
        });
}

function getMyBankInfo() {
    Axios.get("/api/my-bank-info")
        .then((res) => {
            myBankInfo.value = res.data.data;
            bank.value = { ...myBankInfo.value };
        })
        .catch(() => {
            // No existing bank info is okay
        });
}

function selectBankFromDropdown() {
    const selected = banklists.value.find(
        (item) => item.name === bank.value.bank_name,
    );
    if (selected) {
        bank.value.bank_code = selected.id;
        errors.value.bank_name = "";
    }
}

function toggleBankDropdown() {
    showBankDropdown.value = !showBankDropdown.value;
}

function selectBankFromList(selectedBank) {
    bank.value.bank_name = selectedBank.name;
    bank.value.bank_code = selectedBank.id;
    showBankDropdown.value = false;
    errors.value.bank_name = "";
}

function validateForm() {
    let isValid = true;
    errors.value = {
        full_name: "",
        account_number: "",
        bank_name: "",
        password: "",
        general: "",
    };

    if (!bank.value.full_name.trim()) {
        errors.value.full_name = "Account name is required";
        isValid = false;
    }

    if (!bank.value.account_number) {
        errors.value.account_number = "Account number is required";
        isValid = false;
    } else if (!/^\d{10,}$/.test(bank.value.account_number)) {
        errors.value.account_number = "Invalid account number";
        isValid = false;
    }

    if (!bank.value.bank_name) {
        errors.value.bank_name = "Bank selection is required";
        isValid = false;
    }

    if (!password.value) {
        errors.value.password = "Password is required";
        isValid = false;
    }

    return isValid;
}

async function submitBankInfo() {
    if (!validateForm()) return;

    isLoading.value = true;
    bank.value.password = password.value;

    try {
        const endpoint = myBankInfo.value
            ? `/api/bank-info/${myBankInfo.value.id}`
            : "/api/bank-info";

        const method = myBankInfo.value ? "patch" : "post";

        const response = await Axios[method](endpoint, bank.value);
        myBankInfo.value = response.data.data;
        bank.value = { ...myBankInfo.value };
        password.value = "";
        errors.value.general = "Bank information saved successfully";
    } catch (error) {
        if (error.response?.data?.errors) {
            for (const [field, message] of Object.entries(
                error.response.data.errors,
            )) {
                errors.value[field] = message[0];
            }
        } else {
            errors.value.general =
                error.response?.data?.message ||
                "An error occurred while saving bank information";
        }
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getBankLists();
    getMyBankInfo();
});
</script>

<template>
    <div class="w-full bg-white overflow-hidden space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ myBankInfo ? "Update Bank Account" : "Add Bank Account" }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Securely link your bank account for transactions
            </p>
        </div>

        <!-- Error/Success Messages -->
        <div
            v-if="errors.general"
            :class="{
                'bg-green-50 text-green-800': !errors.general.includes('error'),
                'bg-red-50 text-red-800': errors.general.includes('error'),
            }"
            class="p-3 rounded-lg text-sm"
        >
            {{ errors.general }}
        </div>

        <form @submit.prevent="submitBankInfo" class="space-y-5">
            <!-- Account Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Account Name <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="bank.full_name"
                    type="text"
                    placeholder="John Doe"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                    :class="{ 'border-red-500': errors.full_name }"
                />
                <p v-if="errors.full_name" class="mt-1 text-sm text-red-600">
                    {{ errors.full_name }}
                </p>
            </div>

            <!-- Account Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Account Number <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="bank.account_number"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    placeholder="1234567890"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                    :class="{ 'border-red-500': errors.account_number }"
                />
                <p
                    v-if="errors.account_number"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.account_number }}
                </p>
            </div>

            <!-- Bank Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Bank Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div
                        @click="toggleBankDropdown"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500 bg-white cursor-pointer flex items-center justify-between"
                        :class="{
                            'border-red-500': errors.bank_name,
                            'ring-2 ring-lime-500': showBankDropdown,
                        }"
                    >
                        <span
                            :class="
                                bank.bank_name
                                    ? 'text-gray-900'
                                    : 'text-gray-400'
                            "
                        >
                            {{ bank.bank_name || "Select your bank" }}
                        </span>
                        <svg
                            class="w-5 h-5 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': showBankDropdown }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </div>

                    <!-- Custom Dropdown List -->
                    <div
                        v-if="showBankDropdown"
                        class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-lg border border-gray-200 max-h-60 overflow-hidden"
                    >
                        <div
                            class="overflow-y-auto max-h-60 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
                        >
                            <div
                                v-for="bankItem in banklists"
                                :key="bankItem.id"
                                @click="selectBankFromList(bankItem)"
                                class="px-4 py-3 hover:bg-lime-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0"
                                :class="{
                                    'bg-lime-100':
                                        bank.bank_name === bankItem.name,
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-900">{{
                                        bankItem.name
                                    }}</span>
                                    <span
                                        v-if="bank.bank_name === bankItem.name"
                                        class="text-lime-600"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="errors.bank_name" class="mt-1 text-sm text-red-600">
                    {{ errors.bank_name }}
                </p>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter your password"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                    >
                        <i
                            :class="
                                showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'
                            "
                        ></i>
                    </button>
                </div>
                <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                    {{ errors.password }}
                </p>
            </div>

            <!-- Submit Button -->
            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-colors"
                    :disabled="isLoading"
                >
                    <span v-if="isLoading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Processing...
                    </span>
                    <span v-else>
                        {{ myBankInfo ? "Update Account" : "Add Account" }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Smooth transitions for hover effects */
input,
button {
    transition: all 0.2s ease;
}

/* Better focus styles */
input:focus {
    outline: none;
    box-shadow: 0 0 0 1px rgba(240, 124, 42, 0.8);
}

/* Loading spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

/* Custom Scrollbar for bank dropdown */
.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Firefox scrollbar */
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f1f1f1;
}
</style>
