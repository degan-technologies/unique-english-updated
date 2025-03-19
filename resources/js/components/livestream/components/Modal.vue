<template>
    <transition name="fade" @after-leave="afterLeave">
      <div
        v-if="show"
        class="fixed inset-0 flex overflow-y-auto px-4 py-6 sm:px-0 items-center z-50 transform transition-all"
      >
        <!-- Modal Background -->
        <div
          class="absolute inset-0 bg-gray-500/75 dark:bg-gray-900/75"
          @click="handleClose"
        />
        
        <!-- Modal Content -->
        <transition name="modal-slide">
          <div
            v-if="show"
            :class="['mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto', maxWidthClass]"
          >
            <slot></slot>
          </div>
        </transition>
      </div>
    </transition>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  
  // Define props for the modal
  const props = defineProps({
    show: {
      type: Boolean,
      default: false,
    },
    maxWidth: {
      type: String,
      default: '2xl',
    },
    closeable: {
      type: Boolean,
      default: true,
    },
    onClose: {
      type: Function,
      default: () => {},
    },
  });
  
  // Close function when modal is closed
  const handleClose = () => {
    if (props.closeable) {
      props.onClose();
    }
  };
  
  // Computed class for max-width
  const maxWidthClass = computed(() => {
    const classes = {
      sm: 'sm:max-w-sm',
      md: 'sm:max-w-md',
      lg: 'sm:max-w-lg',
      xl: 'sm:max-w-xl',
      '2xl': 'sm:max-w-2xl',
    };
    return classes[props.maxWidth] || classes['2xl'];
  });
  
  // After leave hook
  const afterLeave = () => {
    // You can trigger any additional cleanup or animation actions here
  };
  </script>
  
  <style scoped>
  /* Fade transition for background */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0;
  }
  
  /* Slide transition for the modal */
  .modal-slide-enter-active,
  .modal-slide-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
  }
  .modal-slide-enter, .modal-slide-leave-to /* .modal-slide-leave-active in <2.1.8 */ {
    transform: translateY(4rem);
    opacity: 0;
  }
  .modal-slide-enter-to, .modal-slide-leave {
    transform: translateY(0);
    opacity: 1;
  }
  </style>
  