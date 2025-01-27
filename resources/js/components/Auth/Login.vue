<script setup>
    import { onMounted } from 'vue';
    import { storeToRefs } from 'pinia';

    import {useAppStore} from '@/store/useAppStore';

    const appStore = useAppStore();

    const { lang, frontLang } = storeToRefs(appStore);

    lang.value = 'am';
    onMounted(() => {
        appStore.fetchFrontLanguages();
    })
</script>
<template>
    <div>
        <div class="font-bold text-2xl text-center">
            <!-- <h1>this is translation of name</h1>
            <h1>{{ frontLang.am?.lang.name }}</h1> -->
            <h1>login page</h1>
            <h1 class="text-rose-500">"Inovate today lead tomorow !!"</h1>
            <h1 class="text-3xl font-extrabold text-sky-500">DEGAN TECHNOLOGIES PLC.</h1>
        </div>
    </div>
</template>