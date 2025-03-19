<script setup>
import { ref, onMounted, watch } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import katex from 'katex';
import 'katex/dist/katex.min.css';

const props = defineProps({
  selectedLesson: {
    type: Object,
    required: true,
  },
  selectedCourse: {
    type: Object,
    required: true,
  }
});

// Reactive state for the note content and editor instance
const content = ref('');
const quillEditor = ref(null);

// Function to adjust the height of the editor container dynamically
const adjustHeight = (container) => {
  if (container) {
    container.style.height = 'auto';
    container.style.height = container.scrollHeight + 'px';
  }
};

// Function to initialize the Quill editor
const initializeEditor = () => {
  console.log('Initializing editor with lesson:', props.selectedLesson);
  const toolbarOptions = [
    [{ header: [1, 2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ script: 'sub' }, { script: 'super' }],
    [{ color: [] }, { background: [] }],
    [{ font: [] }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ align: [] }],
    ['link', 'image', 'blockquote', 'code-block'],
    ['math'],
    ['clean'],
  ];

  quillEditor.value = new Quill('#editor-container', {
    theme: 'snow',
    modules: { toolbar: toolbarOptions },
    placeholder: 'Start writing your content here...',
  });

  // Load the note for the current lesson from localStorage
  loadNoteForLesson();
  
  // Listen to text changes to update the reactive content and adjust height
  const editorContainer = document.querySelector('#editor-container');
  quillEditor.value.on('text-change', () => {
    content.value = quillEditor.value.root.innerHTML;
    adjustHeight(editorContainer);
  });
};

// Function to load note content for the currently selected lesson
const loadNoteForLesson = () => {
  if (props.selectedLesson?.id) {
    const savedNote = localStorage.getItem(`lesson-note-${props.selectedLesson.id}`);
    // If there is a saved note, use it; otherwise fall back to the lesson's original content
    const noteContent = savedNote !== null ? savedNote : (props.selectedLesson.content || '');
    content.value = noteContent;
    if (quillEditor.value) {
      quillEditor.value.root.innerHTML = noteContent;
      // Optionally adjust height after updating the content
      const editorContainer = document.querySelector('#editor-container');
      adjustHeight(editorContainer);
    }
  }
};

// Function to save the current note content to localStorage
const saveContent = () => {
  if (props.selectedLesson?.id) {
    localStorage.setItem(`lesson-note-${props.selectedLesson.id}`, content.value);
    alert('Content saved successfully!');
  }
};

// Initialize the editor when the component is mounted (if selectedLesson exists)
onMounted(() => {
  if (props.selectedLesson) {
    content.value = props.selectedLesson.content || '';
    initializeEditor();
  } else {
    console.error("selectedLesson prop is missing or invalid.");
  }
});

watch(() => props.selectedLesson, (newLesson, oldLesson) => {
  if (newLesson && newLesson.id) {
    console.log('Lesson changed:', newLesson);
    // When the selected lesson changes, load the corresponding note
    loadNoteForLesson();
  }
});
</script>
<template>
    <div class="w-full bg-gray-50 flex justify-center items-start">
      <div class="bg-white shadow-lg rounded-lg p-1 w-full max-w-5xl">
        <!-- Editor Container -->
        <div
          id="editor-container"
          class="border border-gray-300 rounded-lg p-4 bg-gray-50 overflow-hidden"
        ></div>
  
        <!-- Action Buttons -->
        <div class="flex justify-end mt-4">
          <button
            @click="saveContent"
            class="bg-lime-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-lime-700 transition"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </template>

  <style scoped>
  #editor-container {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    min-height: 200px; /* Minimum height for the editor */
    overflow-y: hidden; /* Hide overflow to make height adjustment seamless */
    transition: height 0.2s ease-in-out; /* Smooth height changes */
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
  }
  
  th {
    background-color: #f9fafb;
    font-weight: bold;
    text-align: center;
  }
  
  /* Responsive padding adjustments */
  @media (max-width: 640px) {
    .min-h-screen {
      padding-top: 0; /* No extra padding at the top */
    }
  
    #editor-container {
      min-height: 150px; /* Adjust the minimum height for smaller screens */
    }
  }
  </style>
  