<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/useAppStore";

const appStore = useAppStore();
const { isLoggedIn, logoImage, authUser } = storeToRefs(appStore);

import Login from "@/components/Auth/Login.vue";
import Body from "@/components/Body.vue";

onMounted(() => {
    appStore.fetchUserInfo();
});
</script>

<template>
    <div class="">
        <div class="overflow-hidden">
            <template v-if="isLoggedIn">
                <Body />
            </template>
            <template v-else>
                <Login />
            </template>
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
