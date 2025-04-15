<script setup>
import { ref, onMounted, watch, defineProps } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const props = defineProps({
  modelValue: String, // v-model binding from the parent
});

const emit = defineEmits(['update:modelValue']); // Emit the updated value

const description = ref(props.modelValue || ''); // Initialize with the prop value

let quillEditor = null;

// Initialize Quill editor
const initializeEditor = () => {
  quillEditor = new Quill('#overview-editor-container', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, 4, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ script: 'sub' }, { script: 'super' }],
        [{ color: [] }, { background: [] }],
        [{ font: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['link', 'image', 'blockquote', 'code-block'],
        ['clean'],
      ],
    },
    placeholder: 'Write your book description...',
  });

  // Set the initial content of the Quill editor
  quillEditor.root.innerHTML = description.value;

  // Listen for changes in the editor and update `description`
  quillEditor.on('text-change', () => {
    description.value = quillEditor.root.innerHTML;
    emit('update:modelValue', description.value); // Emit updated value to parent
  });
};

watch(
  () => props.modelValue, // Watch for changes in `modelValue` (v-model)
  (newDescription) => {
    if (newDescription !== description.value) {
      description.value = newDescription;
      if (quillEditor) {
        quillEditor.root.innerHTML = description.value;
      }
    }
  },
  { immediate: true }
);

onMounted(() => {
  initializeEditor();
});
</script>

<template>
  <div class="w-full bg-white flex justify-center items-start">
    <div class="bg-white rounded-lg p-1 w-full max-w-5xl">
      <div
        id="overview-editor-container"
        class="border border-gray-300 rounded-lg p-4 bg-white"
      ></div>
    </div>
  </div>
</template>

<style scoped>
#overview-editor-container {
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  line-height: 1.6;
  color: #333;
  min-height: 200px;
  overflow-y: hidden;
  transition: height 0.2s ease-in-out;
}
</style>
