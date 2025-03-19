<script setup>
    import Axios from "axios";
    import { onMounted, ref } from "vue";

    const banklists = ref([]);
    const searchBanks = ref([]);
    const myBakInfo = ref({});

    const password = ref(null);
    const bank = ref({
        full_name: null,
        bank_name: null,
        bank_code: null,
        account_number: null,
    });

    function getBankLists() {
        Axios.get("/api/bank-lists")
            .then(res => {
                banklists.value = res.data.data.data;
            });
    }

    function getMyBankInfo() {
        Axios.get("/api/my-bank-info")
            .then(res => {
                myBakInfo.value = res.data.data;
                bank.value = {... myBakInfo.value};
            });
    }

    function filterBanks(keyword) {
        bank.value.bank_code = null;
        searchBanks.value = banklists.value.filter(item =>
            item.name.toLowerCase().includes(keyword.toLowerCase())
        );
    }

    function selectBank(selectedBank) {
        const selected = banklists.value.find(item => item.name === selectedBank);
        if (selected) {
            bank.value.bank_name = selectedBank;
            bank.value.bank_code = selected.id;
            searchBanks.value = [];
        }
    }

    function validateBank() {
        const match = banklists.value.find(item => item.name === bank.value.bank_name);
        if (!match) {
            bank.value.bank_name = "";
            bank.value.bank_code = null;
            searchBanks.value = [];
        }
    }

    function storeBankInfo() {
        bank.value.password = password.value;
        Axios
            .post('/api/bank-info', bank.value)
            .then(res => {
                myBakInfo.value = res.data.data;
                bank.value = {... myBakInfo.value};
                 bank.value.password = null;
            });
    }

    function updateBankInfo(id) {
        bank.value.password = password.value;
        Axios
            .patch(`/api/bank-info/${id}`, bank.value)
            .then(res => {
                myBakInfo.value = res.data.data;
                bank.value = {... myBakInfo.value};
                bank.value.password = null;
            });
    }

    onMounted(() => {
        getBankLists();
        getMyBankInfo();
    });
</script>

<template>
    <div class="w-full h-auto flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 space-y-6 w-full">
            <h2 class="text-xl font-bold text-left text-lime-700">
                Add Bank Account 
            </h2>
        
            <form class="space-y-6 w-full">
                <div class="w-full">
                    <label class="block font-semibold text-gray-700 mb-1">
                        Account Name
                    </label>
                    <input
                        v-model="bank.full_name"
                        type="text"
                        placeholder="Enter Account Name"
                        required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">
                        Account Number
                    </label>
                    <input
                        v-model="bank.account_number"
                        type="text"
                        placeholder="Enter Account Number"
                        required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                </div>
                
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">
                        Bank Name
                    </label>
                    <input
                        v-model="bank.bank_name"
                        type="text"
                        placeholder="Enter Bank"
                        @input="filterBanks(bank.bank_name)"
                        @blur="validateBank"
                        required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    
                    <div class="relative">
                        <div v-if="searchBanks.length" class="absolute bg-white max-h-64 overflow-y-auto scrollbar p-4 min-w-48 border border-gray-300 rounded-lg">
                        <div 
                            v-for="bankItem in searchBanks"
                            :key="bankItem.id"
                            class="cursor-pointer hover:bg-gray-200 p-2"
                            @mousedown="selectBank(bankItem.name)">
                            <h1>{{ bankItem.name }}</h1>
                        </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">
                        Password
                    </label>
                    <input
                        v-model="password"
                        autocomplete="new-password"
                        type="password"
                        placeholder="Enter your password"
                        required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                </div>
            
                <button
                    type="button"
                    @click="myBakInfo ? updateBankInfo(myBakInfo.id) : storeBankInfo()"
                    class="w-full bg-lime-700 text-white py-3 rounded-lg hover:bg-lime-800 transition flex justify-center">
                    {{ myBakInfo ? 'update Account' : 'Add Account' }}
                </button>
            </form>
        </div>
    </div>
</template>
