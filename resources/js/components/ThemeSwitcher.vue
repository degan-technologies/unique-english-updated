<script setup>
import { useThemeStore } from "@/store/theme";

const themeStore = useThemeStore();

</script>

<template>
  <button
    @click="themeStore.toggleTheme"
    class="px-4 py-2 rounded transition"
    :style="{ backgroundColor: 'var(--primary-color)', color: 'white' }"
  >
    {{ themeStore.theme === 'dark' ? 'Light Mode' : 'Dark Mode' }}
  </button>
</template>
