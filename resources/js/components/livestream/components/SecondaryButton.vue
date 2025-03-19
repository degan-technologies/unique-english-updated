<template>
    <button
      :type="type"
      :disabled="disabled"
      class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
      :class="[{'opacity-25': disabled}, className]"
      v-bind="props"
    >
      <slot />
    </button>
  </template>
  
  <script setup>
  import { defineProps } from 'vue';
  
  defineProps({
    type: {
      type: String,
      default: 'button',
    },
    className: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    props: {
      type: Object,
      default: () => ({}),
    },
  });
  </script>
  