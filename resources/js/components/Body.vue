<script setup>
	import { onMounted } from 'vue';
    import { storeToRefs } from 'pinia';
    import { RouterView } from 'vue-router';

    import { useAppStore } from '@/store/useAppStore';

    const appStore = useAppStore();
    const { authUser } = storeToRefs(appStore);

    onMounted(() => {
        appStore.fetchUserInfo();
        appStore.fetchFrontLanguages();
    })
</script>

<template>
    <div v-if="authUser">
        <RouterView />
    </div>
</template>
