<script setup>
    import Axios from 'axios';
    import { onMounted, ref } from 'vue';
    import { storeToRefs } from 'pinia';

    import { useAppStore } from '@/store/useAppStore';

    const appStore = useAppStore();
    const { commission } = storeToRefs(appStore);

    function getCurrentComission() {
        Axios
            .get('/api/get-comission')
            .then(res => {
                commission.value = res.data.fees * 100;
            })
    }


    function handleCommissionChange() {
        Axios
            .post('/api/change-comission', {fees:commission.value})
            .then(res=>{
                commission.value = res.data.fees * 100;
            })
    }

    onMounted(()=>{
        getCurrentComission();
    })

</script>

<template>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center mb-4">
            <div class="text-3xl text-purple-500 mr-2">
                💲
            </div>
            <h2 class="text-xl font-semibold">Commission Settings</h2>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Percentage: {{ Math.floor(commission) }}%</label>
                <input
                    type="range"
                    min="0"
                    max="100"
                    step="1"
                    v-model="commission"
                    @change="handleCommissionChange"
                    class="w-full" />
            </div>
        </div>
    </div>
</template>