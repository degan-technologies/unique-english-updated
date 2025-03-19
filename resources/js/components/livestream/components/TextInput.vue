<template>
    <input
      ref="localRef"
      v-bind="$attrs"
      :type="type"
      :class="computedClass"
    />
  </template>
  
  <script setup>
  import { ref, computed, onMounted, defineProps, defineExpose } from 'vue';
  
  // Define props
  const props = defineProps({
    type: {
      type: String,
      default: 'text'
    },
    className: {
      type: String,
      default: ''
    },
    isFocused: {
      type: Boolean,
      default: false
    }
  });
  
  // Create a ref for the input element
  const localRef = ref(null);
  
  // Expose a focus method for parent components (similar to useImperativeHandle)
  const focus = () => {
    if (localRef.value) {
      localRef.value.focus();
    }
  };
  defineExpose({ focus });
  
  // Auto-focus the input if isFocused is true when the component mounts
  onMounted(() => {
    if (props.isFocused && localRef.value) {
      localRef.value.focus();
    }
  });
  
  // Combine default classes with any additional classes provided via the className prop
  const computedClass = computed(() => {
    return (
      'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 ' +
      'focus:border-indigo-500 dark:focus:border-indigo-600 ' +
      'focus:ring-indigo-500 dark:focus:ring-indigo-600 ' +
      'rounded-md shadow-sm ' +
      props.className
    );
  });
  </script>
  