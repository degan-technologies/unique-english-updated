          <template>
            <div class="max-w-5xl mx-auto p-2 bg-white  mb-2 h-auto [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden">
                <h2 class="text-lg font-semibold mb-6 text-center text-black">Add Course Content</h2>

                <form @submit.prevent="submitModuleContent" class="grid grid-cols-1 gap-4 p-4 bg-white rounded-lg ">
                  <!-- Title -->
                  <div class="relative">
                    <label class="block text-md font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <input 
                        v-model="form.title"
                        type="text"
                        placeholder="Enter course title..."
                        class="w-full p-2 pl-10 border rounded-md  text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                      />
                      <i class="fas fa-heading absolute left-3 top-3 text-gray-400"></i>
                    </div>
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Description (Min 10 characters)</label>
                    <textarea
                      v-model="form.description"
                      placeholder="Write a brief description..."
                      class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      minlength="10"
                    ></textarea>
                  </div>

                  <!-- Content Type -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Content Type <span class="text-red-500">*</span></label>
                    <select 
                      v-model="form.content_type"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required
                    >
                      <option :value="1">📹 Video</option>
                      <option :value="2">📄 PDF</option>
                      <option :value="3">🖼️ Image</option>
                    </select>
                  </div>

                  <!-- Upload Content -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Upload Content</label>
                    <input
                      type="file"
                      @change="handleFileUpload('content_url', $event)"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <!-- Upload Thumbnail -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
                    <input
                      type="file"
                      @change="handleFileUpload('thumbnail_url', $event)"
                      accept="image/*"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <!-- Duration -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Duration (HH:MM) <span class="text-red-500">*</span></label>
                    <input 
                      v-model="form.hour"
                      type="time"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required
                    />
                  </div>

                  <!-- Status -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                    <select 
                      v-model="form.status"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required
                    >
                      <option :value="1">✔️ Published</option>
                      <option :value="2">📝 Draft</option>
                      <option :value="3">📦 Archived</option>
                    </select>
                  </div>

                  <!-- Note -->
                  <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Note (Min 10 characters)</label>
                    <textarea
                      v-model="form.note"
                      placeholder="Add any additional note..."
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      minlength="10"
                    ></textarea>
                  </div>

                  <!-- Error Messages -->
                  <div v-if="errorMessage" class="text-red-500 text-center text-md mt-2">
                    {{ errorMessage }}
                  </div>

                  <!-- Buttons -->
                  <div class="mt-4 flex justify-end">
            <div class="flex space-x-4">
              <button
                type="submit"
                class="bg-lime-700 text-white text-xs px-3 py-2 rounded-md hover:bg-lime-600 transition duration-300"
              >
                Add
              </button>

              <button
                type="button"
                @click="resetForm"
                class="bg-gray-300 text-black text-xs px-3 py-2 rounded-md hover:bg-gray-600 transition duration-300"
              >
                Cancel
              </button>
            </div>
          </div>

                </form>
              

            </div>
          </template>

            
          <script setup>
          import { ref, onMounted, defineProps  } from "vue";
          import { useRoute, useRouter } from "vue-router";
          import Axios from "axios";

          const route = useRoute();
          const router = useRouter();
          const props = defineProps({
            courseId: Number,
            moduleId: Number
          });

          console.log("Received Course ID:", props.courseId, "Module ID:", props.moduleId);

          const form = ref({
            title: "",
            description: "",
            content_type: 1, // Default to Video
            content_url: null,
            thumbnail_url: null,
            hour: "",
            status: 2, // Default to Draft
            note: "",
          });

          const errorMessage = ref("");
          const content = ref([]);
          const loading = ref(true);

          const handleFileUpload = (field, event) => {
            form.value[field] = event.target.files[0];
          };


          const submitModuleContent = async () => {
            errorMessage.value = ""; // Reset error message

            // Basic validation
            if (form.value.title.length < 4) {
                errorMessage.value = "Title must be at least 4 characters.";
                return;
            }
            if (form.value.description && form.value.description.length < 10) {
                errorMessage.value = "Description must be at least 10 characters.";
                return;
            }
            if (form.value.note && form.value.note.length < 10) {
                errorMessage.value = "Note must be at least 10 characters.";
                return;
            }

            // Ensure content_type and status are sent as integers
            const formData = new FormData();
            formData.append("course_id", props.courseId); // Convert to number for courseId
            formData.append("course_module_id", props.moduleId); // Convert to number for moduleId
            formData.append("title", form.value.title);
            formData.append("description", form.value.description);
            formData.append("content_type", Number(form.value.content_type)); // Ensure it's a number
            if (form.value.content_url) {
                formData.append("content_url", form.value.content_url);
            }
            if (form.value.thumbnail_url) {
                formData.append("thumbnail_url", form.value.thumbnail_url);
            }
            formData.append("hour", form.value.hour);
            formData.append("status", Number(form.value.status)); // Ensure it's a number
            formData.append("note", form.value.note);

            try {
                const response = await Axios.post("/api/courses/content", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

            } catch (error) {
                console.error("Error adding course content:", error.response?.data);
                errorMessage.value = error.response?.data?.message || "Failed to add content";
            }
          };

          // Fetch Course Content for the Module
          const fetchCourseContent = async () => {
            loading.value = true;
            try {
              // Access moduleId correctly from props
              const numericModuleId = Number(props.moduleId);

              if (isNaN(numericModuleId)) {
                console.error("Invalid module ID:", props.moduleId);
                return;
              }

              const response = await Axios.get(`/api/courses/module/${numericModuleId}`);
              console.log("Fetched Content Data:", response.data);
              content.value = response.data.data.courseContents;
              console.log("Parsed Content:", content.value);
            } catch (error) {
              console.error("Error fetching course content:", error);
              errorMessage.value = "Failed to fetch course content";
            } finally {
              loading.value = false;
            }
          };

          // Handle content edit
          const editingContent = ref(null);
          const editContent = (item) => {
            editingContent.value = item;
            form.value = { ...item }; // Copy content into form for editing
          };
          const cancelEdit = () => {
            editingContent.value = null; // Hide the form without saving any changes
            form.value = {}; // Optionally reset the form values
          };

          // Update content
          const updateContent = async () => {
            errorMessage.value = ""; // Reset error message

            // Basic validation
            if (form.value.title.length < 4) {
              errorMessage.value = "Title must be at least 4 characters.";
              return;
            }
            if (form.value.description.length < 10) {
              errorMessage.value = "Description must be at least 10 characters.";
              return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append("_method", "PUT");
            formData.append("title", form.value.title);
            formData.append("description", form.value.description);
            formData.append("content_type", form.value.content_type);
            if (form.value.content_url) {
                formData.append("content_url", form.value.content_url);
            }
            if (form.value.thumbnail_url) {
                formData.append("thumbnail_url", form.value.thumbnail_url);
            }
            formData.append("hour", form.value.hour);
            formData.append("status", form.value.status);
            formData.append("note", form.value.note);

            try {
              await Axios.put(`/api/courses/content/${editingContent.value.id}`, formData, {
              headers: {
                'Content-Type': 'multipart/form-data',
              },
            });
              editingContent.value = null; // Close the edit modal
              alert("Content updated successfully");
              fetchCourseContent(); // Refresh the content list
            } catch (error) {
              errorMessage.value = "Failed to update content";
            }
          };
          const deleteContent = async (id) => {
            if (!confirm("Are you sure you want to delete this content?")) return;

            try {
              await Axios.delete(`/api/courses/content/${id}`);
              alert("Content deleted successfully");
              fetchCourseContent(); // Refresh content list
            } catch (error) {
              console.error("Error deleting content:", error);
              alert("Failed to delete content");
            }
          };


          onMounted(() => {
            fetchCourseContent();
          });
          </script>

            
            <style scoped>
            html, body {
              height: 100%;
              margin: 0;
            }
            
            body {
              display: flex;
              flex-direction: column;
            }
            
            .max-w-3xl {
              min-height: 100%;
            }
            
            h2 {
              font-size: 1.25rem;
              margin-bottom: 1rem;
            }
            
            form {
              display: grid;
              grid-template-columns: 1fr;
              grid-gap: 1rem;
            }
            
            form > div {
              display: flex;
              flex-direction: column;
            }
            
            form .col-span-2 {
              grid-column: span 2;
            }
            
            @media (min-width: 768px) {
              form {
                grid-template-columns: 1fr 1fr;
              }
              .col-span-2 {
                grid-column: span 2;
              }
            }
            
            button {
              width: 100%;
            }
            
            .bg-gray-100 {
              background-color: #f7fafc;
            }
            
            .shadow-md {
              box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            p {
              margin-bottom: 0.5rem;
            }
            
            img {
              object-fit: cover;
            }
            </style>
            