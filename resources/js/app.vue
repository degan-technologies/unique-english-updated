<script setup>
    import { onMounted } from "vue";
    import { storeToRefs } from "pinia";

import Body from "@/components/Body.vue";
import { useAppStore } from '@/store/useAppStore';

    const appStore = useAppStore();
    const { authUser } = storeToRefs(appStore);

    onMounted(() => { 
        appStore.fetchUserInfo();
        appStore.fetchFrontLanguages();
    })

</script>

<template>
    <div>
        <div class="overflow-hidden">
             <Body />
        </div>
    </div>
</template>

<style>
/* Ensure components don't overlap */
body {
    margin: 0;
    padding: 0;
}
</style>
