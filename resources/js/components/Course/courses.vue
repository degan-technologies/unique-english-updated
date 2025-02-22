        <script setup>
        import { ref, onMounted,onUnmounted, computed} from "vue";
        import Axios from "axios";
        import AddCourseContent from "./AddCourseContent.vue";

        const courses = ref([]);
        const loading = ref(true);
        const error = ref("");
        const selectedCourse = ref(null);
        const selectedCourseForModules = ref(null); // Used for adding module
        const activeMenuId = ref(null);

        
          const props = defineProps({
                      searchQuery: {
                        type: String,
                        default: "",
                      },
                    });
                    const selectedFilter = ref("");
                    const filteredCourses = computed(() => {
  let filtered = courses.value;
  
  // Filter by search query (using course_name)
  if (props.searchQuery.trim() !== "") {
    const query = props.searchQuery.trim().toLowerCase();
    filtered = filtered.filter(course =>
      course.course_name.toLowerCase().includes(query)
    );
  }
  
  // Further filter by skill level if selected
  if (selectedFilter.value) {
    filtered = filtered.filter(course =>
      course.skill_level === selectedFilter.value
    );
  }
  
  return filtered;
});

// Pagination state
const currentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];

// Total pages computed based on the filtered courses
const totalPages = computed(() =>
  Math.ceil(filteredCourses.value.length / rowsPerPage.value)
);

// Courses for the current page
const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return filteredCourses.value.slice(start, start + rowsPerPage.value);
});

// Function to navigate pages
function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}




        const isPlaying = ref(false);

        const toggleMenu = (id) => {
          activeMenuId.value = activeMenuId.value === id ? null : id;
        };

        const closeMenu = () => {
          activeMenuId.value = null;
        };

        // Close menu when clicking outside
        const handleClickOutside = (event) => {
          if (!event.target.closest(".relative")) {
            closeMenu();
          }
        };

        // Add event listener on mount, remove on unmount
        onMounted(() => {
          document.addEventListener("click", handleClickOutside);
        });
        onUnmounted(() => {
          document.removeEventListener("click", handleClickOutside);
        });



        const fetchCourses = async () => {
          loading.value = true;
          error.value = "";

          try {
            const response = await Axios.get("/api/courses/course", {
              headers: {
                "Content-Type": "application/json",
              },
            });

            courses.value = response.data.data; // Assuming the API returns { data: [...] }
          } catch (err) {
            error.value = err.response?.data?.message || "Failed to fetch courses.";
          } finally {
            loading.value = false;
          }
        };

        // Fetch courses on mount
        onMounted(fetchCourses);

        const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (file) {
    selectedCourse.value[field] = file; // Ensure selectedCourse is a reactive object
  }
};
const skillLevelMapping = {
  "Beginner": 1,
  "Intermediate": 2,
  "Advance": 3,
  "Full Package": 4
};

const updateCourse = async () => {
  if (!selectedCourse.value || !selectedCourse.value.id) {
    console.error("No course selected for update.");
    error.value = "No course selected.";
    return;
  }

  try {
    console.log("Sending update request for:", selectedCourse.value);

    const allowedFields = [
      "course_name",
      "overview",
      "tag",
      "skill_level",
      "price",
      "discount",
      "credit_hour",
      "thumbnail_url",
    ];

    const formData = new FormData();

    allowedFields.forEach((key) => {
      let value = selectedCourse.value[key];

      if (value !== undefined && value !== null) {
        if (key === "thumbnail_url" && value instanceof File) {
          formData.append(key, value);
        } else if (key === "tag") {
          // Ensure the tag is sent as a single string in quotes
          let formattedTag = typeof value === "string"
            ? `"${value.trim()}"`
            : `"${value.join(", ").trim()}"`;
          formData.append(key, formattedTag);
        } else if (key === "skill_level") {
          // Convert skill level string to a number before appending
          formData.append(key, skillLevelMapping[value] || value);
        } else {
          formData.append(key, value);
        }
      }
    });

    // Append the method override for Laravel
    formData.append("_method", "PUT");

    const response = await Axios.post(
      `/api/courses/course/${selectedCourse.value.id}`, // Corrected API path
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    console.log("Update response:", response.data);

    // Update the course list reactively
    courses.value = courses.value.map((course) =>
      course.id === selectedCourse.value.id ? response.data.data : course
    );

    selectedCourse.value = null; // Clear selection after successful update
  } catch (err) {
    console.error("Update failed:", err.response?.data || err.message);
    error.value = err.response?.data?.message || "Failed to update course.";
  }
};





        // Delete course function
        const deleteCourse = async (id) => {
          if (!window.confirm("Are you sure you want to delete this course?")) {
            return;
          }

          try {
            console.log(`Deleting course with slug: ${id}`);

            const response = await Axios.delete(`/api/courses/course/${id}`, {
              headers: {
                "Content-Type": "application/json",
              },
            });

            console.log(response.data.message); // Debugging: Check if the request succeeds

            // Only update the UI if the API successfully deletes the course
            courses.value = courses.value.filter(course => course.id !== id);

            alert("Course successfully deleted!");
          } catch (err) {
            console.error("Error deleting course:", err.response?.data);
            alert(err.response?.data?.message || "Failed to delete course.");
          }
        };

        const editingCourseId = ref(null); // Track which course is being edited

        // Function to select course for editing
        const selectCourseForEdit = (course) => {
          editingCourseId.value = course.id; // Set editing course ID
          selectedCourse.value = { ...course }; // Make a copy of the course data
        };


        // Function to cancel editing
        const cancelEdit = () => {
          editingCourseId.value = null;
          selectedCourse.value = null;
        };

        const showModuleForm = ref(false); // Controls the form visibility
        const isAddingModule = ref(false);
        const newModule = ref({ title: "", sequence: "", description: "" }); // Form Data

        // Open the Add Course Module Form
        const openModuleForm = (course) => {
          selectedCourseForModules.value = course; // Set the selected course
          newModule.value = { title: "", sequence: "", description: "" }; // Reset the form
          showModuleForm.value = true; // Show the form
        };

        // Add Course Module Function
        const addCourseModule = async () => {
          if (!selectedCourseForModules.value || !selectedCourseForModules.value.id) {
            alert("Please select a course first.");
            return;
          }

          // Ensure title is not empty
          if (!newModule.value.title.trim()) {
            alert("Title is required.");
            return;
          }

          // Convert sequence to string before checking if it's empty
          if (!String(newModule.value.sequence).trim()) {
            alert("Sequence is required.");
            return;
          }

          try {
            const response = await Axios.post("/api/courses/module", {
              title: newModule.value.title,
              sequence: newModule.value.sequence, // Keep it as a number
              description: newModule.value.description,
              course_id: selectedCourseForModules.value.id,
            });

            // Append new module to the selected course dynamically
            if (!selectedCourseForModules.value.modules) {
              selectedCourseForModules.value.modules = [];
            }
            selectedCourseForModules.value.modules.push(response.data.data);

            alert("Course module added successfully!");
            newModule.value = { title: "", sequence: "", description: "" }; // Reset form
            showModuleForm.value = false; // Hide form after submission
          } catch (err) {
            alert(err.response?.data?.message || "Failed to add course module.");
          }
        };

        const module = ref([]);

        const showDetailModule = async (course) => { 
          if (!course || !course.id) {
            console.error("Invalid course:", course);
            error.value = "Invalid course selected.";
            return;
          }

          loading.value = true;
          error.value = "";
          
          try {
            
            const response = await Axios.get(`/api/courses/course/${course.id}`);
            console.log("Course modules fetched:", response.data);

            selectedCourse.value = response.data.data;

            // Debugging: Check if courseModules are populated correctly
            console.log("Course modules:", selectedCourse.value.courseModules);

            if (!selectedCourse.value.courseModules || selectedCourse.value.courseModules.length === 0) {
              error.value = "No modules found for this course.";
            }
          } catch (err) {
            console.error("API Error:", err.response?.data || err.message);
            error.value = "Failed to fetch course modules.";
          } finally {
            loading.value = false;
          }
        };

        const addContentModule = ref(false);
        const editCourseModule = ref(null); // Holds the content being edited
        const updatedModule = ref({ title: "", description: "",sequence: "",}); // Holds the new values

        const startEditing = (module) => {
          editCourseModule.value = module.id; // Set the content ID being edited
          updatedModule.value = { ...module }; // Clone the current content data
        };

        const updateCourseModule = async () => {
          if (!editCourseModule.value) return;

          try {
            const title = updatedModule.value.title?.trim();
            if (!title) {
              alert("Title field is required.");
              return;
            }

            const formData = new FormData();
            formData.append("_method", "PUT");  // 🔹 Laravel requires this for FormData
            formData.append("title", title);
            formData.append("description", updatedModule.value.description || "");
            formData.append("sequence", updatedModule.value.sequence || "");

            console.log("Sending FormData:", Object.fromEntries(formData.entries()));

            const response = await Axios.post(`/api/courses/module/${editCourseModule.value}`, formData, {
              headers: { "Content-Type": "multipart/form-data" },
            });

            console.log("Update successful!", response.data);
            editCourseModule.value = null;
          } catch (err) {
            console.error("Update failed:", err.response?.data || err.message);
            alert(err.response?.data?.message || "An error occurred while updating.");
          }
        };


        const deleteCourseModule = async (moduleId) => {
          if (!confirm("Are you sure you want to delete this module?")) return;

          try {
            await Axios.delete(`/api/courses/module/${moduleId}`);

            // Remove the deleted module from the list
            selectedCourse.value.courseModules = selectedCourse.value.courseModules.filter(item => item.id !== moduleId);
          } catch (err) {
            console.error("Delete failed:", err.response?.data || err.message);
          }
        };
        const selectedModule = ref({ courseId: null, moduleId: null });

        const addModuleContent = (courseId, moduleId) => {
          courseId = Number(courseId);
          moduleId = Number(moduleId);

          if (isNaN(courseId) || isNaN(moduleId)) {
            console.error("Invalid courseId or moduleId", { courseId, moduleId });
            return;
          }

          selectedModule.value = { courseId, moduleId }; // No need for `.value`
          addContentModule.value = true;
        };


        const cancelAddContent = () => {
          addContentModule.value = false;
        };

        const handleFileUpload = (field, event) => {
          const file = event.target.files[0];
          if (file) {
            form.value[field] = file; // Store file object instead of URL
          }
        };


        const dropdownOpen = ref(null);

        // Toggle dropdown visibility
        const toggleDropdown = (moduleId) => {
          dropdownOpen.value = dropdownOpen.value === moduleId ? null : moduleId;
        };

        const expandedModule = ref(null);

        const toggleModule = (moduleId) => {
          expandedModule.value = expandedModule.value === moduleId ? null : moduleId;
        };

        const selectedContent = ref(null);
        const selectContent = (content) => {
          selectedContent.value = content;
        };

        const editingContent = ref(false);
        const form = ref({});
        const errorMessage = ref("");

        // Edit content function
        const editSelectedContent = () => {
          if (!selectedContent.value) return;
          editingContent.value = true;
          form.value = { ...selectedContent.value }; // Populate form with selected content
        };

        // Cancel edit
        const canceledit = () => {
          editingContent.value = false;
          form.value = {};
        };

        // Update content function
        const updateSelectedContent = async () => {
  errorMessage.value = ""; // Reset error message

  if (form.value.title.length < 4) {
    errorMessage.value = "Title must be at least 4 characters.";
    return;
  }
  if (form.value.description.length < 10) {
    errorMessage.value = "Description must be at least 10 characters.";
    return;
  }

  const formData = new FormData();

  // Append text fields
  formData.append("title", form.value.title);
  formData.append("description", form.value.description);
  formData.append("content_type", form.value.content_type);
  formData.append("content_url", form.value.content_url);
  // Update key to match the controller's expectation (thumbnail_url)
  formData.append("thumbnail_url", form.value.thumbnail_url);
  formData.append("hour", form.value.hour);
  formData.append("status", form.value.status);
  formData.append("sequence", form.value.sequence);
  formData.append("note", form.value.note);

  // Append _method override so Laravel treats it as a PUT request
  formData.append("_method", "PUT");

  // Debug: log FormData entries
  for (let [key, value] of formData.entries()) {
    console.log(`${key}:`, value);
  }

  try {
    await Axios.post(`/api/courses/content/${selectedContent.value.id}`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    editingContent.value = false;
    alert("Content updated successfully");
    fetchCourseContent();
  } catch (error) {
    errorMessage.value = "Failed to update content";
  }
};


        // Delete content function
        const deleteSelectedContent = async (id) => {
          if (!confirm("Are you sure you want to delete this content?")) return;

          try {
            await Axios.delete(`/api/courses/content/${id}`);
            alert("Content deleted successfully");
            selectedContent.value = null; // Clear selected content
            fetchCourseContent();
          } catch (error) {
            console.error("Error deleting content:", error);
            alert("Failed to delete content");
          }
        };

        </script>

        <template>
          <div class="max-w-full mx-auto">
            <!-- Loading Indicator -->
            <div v-if="loading" class="flex flex-col justify-center items-center h-20 text-xl font-semibold">
                <div class="relative w-12 h-12">
                    <div class="w-2 h-2 bg-blue-500 rounded-full absolute top-0 left-1/2 transform -translate-x-1/2 animate-[orbit_1.3s_linear_infinite]">
                   </div>
                </div>
               Loading...
            </div>

        <div>
            <!-- Filter Section (Right Aligned) -->
            <div class="flex justify-end mb-4 mr-5">
              <select 
                v-model="selectedFilter"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Skill Levels</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advance">Advanced</option>
                <option value="Full Package">Full Package</option>
              </select>
            </div>
            
            <div v-if="filteredCourses.length == 0"> No Course Found</div>
            <!-- Course Table (Hidden when showing course modules) -->
            <div v-if="!selectedCourse">
   
            <div v-if="filteredCourses.length > 0" class="border border-gray-300 rounded-lg [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden">
            
      <table class="w-full text-left border-collapse">
        <!-- Table Header -->
        <thead class="bg-white rounded-t-lg">
          <tr class="border-b border-gray-300 text-gray-600 text-sm">
            <th class="py-3 px-4 font-normal">COURSES</th>
            <th class="py-3 px-4 font-normal">RATING</th>
            <th class="py-3 px-4 font-normal">TOTAL ENROLL</th>
            <th class="py-3 px-4 font-normal">REVENUE</th>
            <th class="py-3 px-4 font-normal">CREATED DATE</th>
            <th class="py-3 px-4 font-normal">ACTIONS</th>
          </tr>
        </thead>

        <tbody class="bg-white">
  <tr
    v-for="course in paginatedCourses"
    :key="course.id"
    class="border-b border-gray-200 hover:bg-gray-50 transition align-middle"
  >
    <!-- Course Details -->
    <td class="py-3 px-4 flex items-center gap-3">
      <img :src="course.thumbnail_url" alt="Course Image" class="w-12 h-12 rounded-md object-cover" />
      <div>
        <h3 class="text-sm font-semibold text-gray-800">{{ course.course_name }}</h3>
        <p class="text-xs text-gray-500">
          Level: <span class="font-bold">{{ course.skill_level }}</span>
        </p>
      </div>
    </td>

    <!-- Rating -->
    <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
      <div class="flex items-center justify-center">
        <svg class="w-4 h-4 text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
          <path d="M10 15l-5.878 3.09L5.244 12 .49 7.91l6.07-.88L10 2l2.44 5.03 6.07.88-4.754 4.09 1.122 5.99z" />
        </svg>
        <span class="ml-1 text-center">{{ course.rating || 4.6 }}</span>
      </div>
    </td>

    <!-- Total Enrolled -->
    <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
      {{ course.total_enroll || 15 }}
    </td>

    <!-- Revenue -->
    <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
      ${{ course.revenue || 25 }}
    </td>

    <!-- Created Date -->
    <td class="py-3 px-4 text-gray-700 text-sm text-center">
      {{ course.created_at || '2025-02-04 10:22:49' }}
    </td>

    <!-- Actions (Fixed alignment & spacing) -->
    <td class="py-3 px-4 h-full">
  <div class="flex items-center justify-center h-full gap-2">
    <!-- Details Button -->
    <button
      @click="showDetailModule(course)"
      class="bg-white border border-gray-200 px-3 py-1 rounded text-sm text-black hover:underline"
    >
      Details
    </button>

    <!-- Three-dot Menu -->
    <div class="relative">
      <button class="text-gray-600 hover:text-gray-900 focus:outline-none" @click.stop="toggleMenu(course.id)">
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <circle cx="12" cy="6" r="1.5" fill="currentColor" />
          <circle cx="12" cy="12" r="1.5" fill="currentColor" />
          <circle cx="12" cy="18" r="1.5" fill="currentColor" />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div
        v-if="activeMenuId === course.id"
        class="absolute right-0 bg-white shadow-md rounded-md z-50 w-32 text-sm border"
      >
        <button @click="selectCourseForEdit(course); closeMenu()" class="w-full px-2 py-1.5 text-left text-black hover:bg-blue-100 transition">
          Edit
        </button>
        <button @click="deleteCourse(course.id); closeMenu()" class="w-full px-2 py-1.5 text-left text-black hover:bg-red-100 transition">
          Delete
        </button>
        <button @click="openModuleForm(course); closeMenu()" class="w-full px-2 py-1.5 text-left text-black hover:bg-purple-100 transition">
          Add Module
        </button>
      </div>
    </div>
  </div>
</td>

  </tr>
</tbody>

      </table>
    </div>


    <!-- Pagination Footer -->
    <div v-if="filteredCourses.length > 0" class="flex justify-between items-center mt-4 px-4">
      <!-- Bottom Left: Rows per page selector -->
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">Rows per page:</span>
        <select
          v-model.number="rowsPerPage"
          class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="option in rowsPerPageOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </div>

      <!-- Bottom Right: Pagination controls -->
      <div class="flex items-center space-x-2">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
        >
          Prev
        </button>
        <span class="text-sm text-gray-600">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
   </div>
  </div>
        <div v-if="selectedCourse " class="w-full h-full bg-gray-100 p-6">
          <!-- Container -->
          <div class="relative w-full max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-lg  gap-6">
            <button
            @click="selectedCourse = null"
            class="absolute top-2 left-2 text-gray-500 font-normal p-1 rounded-full hover:bg-gray-200 transition-all"
          >
            <i class="fas fa-arrow-left text-sm"></i>
          </button>
          <br>
        <!-- Left Section: Course Details -->
        <div class="text-left">
                    <h3 class="text-2xl text-gray-900 font-bold">
                        {{ selectedCourse?.course_name }}
                    </h3>
                
                </div>
        <div class="w-full p-6 flex flex-col items-start relative">
          <div class="overflow-hidden w-full aspect-video rounded-t-lg mt-3 relative cursor-pointer">
  <img :src="selectedCourse.thumbnail_url" 
       alt="Course Thumbnail"  
       class="w-full h-full rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl">    

  <div class="absolute inset-0 flex items-center justify-center">
    <div class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
      <i class="fas fa-play-circle text-white text-6xl"></i>
    </div>
  </div>
</div>

       
        </div>

        <!-- Right Section: Course Modules & Contents -->
        <div class="w-full bg-white p-3 ">
          <div v-if="!editingCourseId">
            <h2 class="text-xl font-semibold text-gray-800 mt-4">Course Overview</h2>
<div class="text-gray-600 text-lg mt-2 text-justify" v-html="selectedCourse.overview"></div>

          <h2 class="text-lg font-semibold text-gray-800 mb-2 ml-5 mt-4">What You Will Learn</h2>

          <!-- Edit Form (Replaces Module List) -->
          <transition name="fade-slide">
            <div v-if="editCourseModule !== null">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Module</h3>

              <label class="block text-gray-700 font-medium">Title:</label>
              <input v-model="updatedModule.title" 
                    class="border border-lime-700 p-2 w-full rounded focus:ring-2 focus:ring-lime-700 transition-all" 
                    placeholder="Title" />

              <label class="block mt-4 text-gray-700 font-medium">Description:</label>
              <textarea v-model="updatedModule.description" 
                        class="border border-lime-700 p-2 w-full rounded focus:ring-2 focus:ring-lime-700 transition-all" 
                        placeholder="Description"></textarea>

              <label class="block mt-4 text-gray-700 font-medium">Sequence:</label>
              <input v-model="updatedModule.sequence" 
                    class="border border-lime-700 p-2 w-full rounded focus:ring-2 focus:ring-lime-700 transition-all" 
                    placeholder="Hour (HH:MM:SS)" />

              <!-- Action Buttons -->
              <div class="mt-5 flex justify-end space-x-3">
                <button @click="updateCourseModule" 
                        class="bg-lime-700 text-white px-4 py-2 rounded-lg shadow-md hover:bg-lime-800 transition-all">
                  Save
                </button>
                <button @click="editCourseModule = null" 
                        class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-gray-600 transition-all">
                  Cancel
                </button>
              </div>
            </div>
          </transition>

          <transition name="fade-slide">
  <div
    v-if="editCourseModule === null && !selectedContent"
    class="space-y-2 overflow-y-auto max-h-[70vh] p-2 ml-2 [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden"
  >
    <div
      v-for="(module, index) in selectedCourse.courseModules"
      :key="module.id"
      :class="[
        'relative p-3 bg-white transition-transform transform hover:scale-[1.02] rounded-lg ml-3',
        dropdownOpen === module.id ? 'z-50' : 'z-10'
      ]"
    >
      <!-- Module Title & Actions -->
      <div class="flex justify-between items-center cursor-pointer">
        <h3
          class="font-semibold text-lg text-blue-500 mt-2 flex items-center cursor-pointer"
          @click="toggleModule(module.id)"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            xml:space="preserve"
            width="24"
            height="24"
            viewBox="0 0 2048 2048"
            class="mr-2"
          >
            <g id="Layer_x0020_1">
              <!-- Remove unnecessary background paths -->
              <g id="_347303576">
                <path
                  id="_347307440"
                  d="M255.999 466.466h1448.02c48.391 0 87.983 39.595 87.983 87.983v1044.37c0 48.39-39.596 87.982-87.983 87.982H343.979c-48.387 0-87.983-39.588-87.983-87.982V466.469z"
                  style="fill:#ffb300"
                />
                <path
                  id="_347303672"
                  d="M312.87 361.198h625.472c31.282 0 56.87 26.395 56.87 58.655v46.61H256v-46.61c0-32.263 25.589-58.655 56.87-58.655z"
                  style="fill:#ffa000"
                />
                <path
                  id="_347298464"
                  style="fill:#fff"
                  d="M723.914 516.775h696.336V1588.41H625.072V606.442z"
                />
                <path
                  id="_347301224"
                  d="M341.303 745.551h565.502l119.513-96.03h677.697c46.92 0 85.304 39.603 85.304 87.98v861.322c0 48.376-38.399 87.98-85.304 87.98H341.305c-46.909 0-85.304-39.59-85.304-87.98v-765.29c0-48.393 38.385-87.982 85.304-87.982z"
                  style="fill:#ffd54f"
                />
                <path
                  id="_347299640"
                  style="fill:#bdbdbd"
                  d="m723.099 607.445-98.027-1.003 98.027-89.663z"
                />
              </g>
            </g>
          </svg>
          {{ module.title }}
        </h3>

        <div class="flex items-center space-x-2">
          <!-- Expand/Collapse Button -->
          <button class="text-gray-500 text-sm" @click="toggleModule(module.id)">
            <i :class="expandedModule === module.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
          </button>

          <!-- 3-dot Action Menu -->
          <div>
            <button
              @click.stop="toggleDropdown(module.id); toggleModule(module.id)"
              class="p-1 rounded-full hover:bg-gray-200 transition duration-200"
              title="Actions"
            >
              <i class="fas fa-ellipsis-v text-sm"></i>
            </button>

            <!-- Dropdown Menu -->
            <transition name="fade-slide">
              <div
                v-if="selectedCourse.courseModules && selectedCourse.courseModules.length > 0 && dropdownOpen === module.id"
                class="absolute right-0 bg-white shadow-md bg-gray-300 rounded-md z-50 w-32 text-sm"
              >
                <button
                  @click="startEditing(module)"
                  class="flex items-center w-full px-2 py-2 text-md hover:bg-blue-100 transition duration-200 rounded"
                >
                  <i class="fas fa-edit text-md mr-1"></i> Edit
                </button>

                <button
                  @click="deleteCourseModule(module.id)"
                  class="flex items-center w-full px-2 py-2 text-md hover:bg-red-100 transition duration-200 rounded"
                >
                  <i class="fas fa-trash text-md mr-1"></i> Delete
                </button>

                <button
                  @click="addModuleContent(module.course_id, module.id)"
                  class="flex items-center w-full px-2 py-2 text-md hover:bg-purple-100 transition duration-200 rounded"
                >
                  <i class="fas fa-plus text-md mr-1"></i> Add Content
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Course Contents (Expandable) -->
      <transition name="fade-slide">
        <div v-if="expandedModule === module.id" class="space-y-4 p-2">
          <ul class="space-y-2">
            <li
              v-for="content in module.courseContents"
              :key="content.id"
              class="flex items-center p-2 hover:shadow-md rounded ml-3 cursor-pointer"
              @click="selectContent(content)"
            >
              <!-- Disabled Checkbox SVG -->
              <svg
                version="1.1"
                id="Layer_1"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 61 61"
                style="enable-background:new 0 0 61 61"
                xml:space="preserve"
              >
                <g id="Youtube">
                  <path
                    d="M53.5 58h-50c-1.7 0-3-1.3-3-3V6c0-1.7 1.3-3 3-3h50c1.7 0 3 1.3 3 3v49c0 1.7-1.3 3-3 3z"
                    style="fill:#455a64;stroke:#424242;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                  />
                  <path
                    d="M57.5 58h-50c-1.7 0-3-1.3-3-3V6c0-1.7 1.3-3 3-3h50c1.7 0 3 1.3 3 3v49c0 1.7-1.3 3-3 3z"
                    style="fill:#546e7a;stroke:#424242;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                  />
                  <path
                    d="M6.5 55V6"
                    style="fill:none;stroke:#78909c;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                  />
                  <path
                    d="M58.5 55V6"
                    style="fill:none;stroke:#455a64;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                  />
                  <path
                    class="st8"
                    d="M17.5 55h-7c-.6 0-1-.4-1-1v-2c0-.6.4-1 1-1h7c.6 0 1 .4 1 1v2c0 .6-.4 1-1 1zM22.5 53h33"
                  />
                  <path
                    style="fill:#ec407a;stroke:#424242;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                    d="M4.5 13h56v35.5h-56z"
                  />
                  <path
                    style="fill:none;stroke:#f48fb1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                    d="M6.5 48.5V13"
                  />
                  <path
                    style="fill:none;stroke:#d81b60;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                    d="M58.5 48.5V13"
                  />
                  <path
                    class="st8"
                    d="m27 23.2 15 7.6-15 7.4z"
                  />
                  <path
                    style="fill:none;stroke:#424242;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10"
                    d="M4.5 13h56v35.5h-56z"
                  />
                  <circle class="st8" cx="45" cy="53" r="2" />
                </g>
              </svg>

              <p class="text-gray-700 text-md ml-3">{{ content.title }}</p>
            </li>
          </ul>

          <!-- Add Course Content Section (Only for the Selected Module) -->
          <transition name="fade-slide">
            <div v-if="addContentModule && selectedModule.moduleId === module.id">
              <button
                @click="cancelAddContent"
                class="text-black-500 text-md font-normal p-1 rounded-full hover:bg-gray-200 transition-all mt-2"
              >
                Back
              </button>
              <AddCourseContent
                :course-id="selectedModule.courseId"
                :module-id="selectedModule.moduleId"
              />
            </div>
          </transition>
        </div>
      </transition>
    </div>
  </div>
</transition>



          <!-- Selected Content Section (Replaces Module List) -->
          <transition name="fade-slide">
          <div v-if="selectedContent" class="p-4 border rounded shadow-md">
            <!-- Back Button -->
            <button @click="selectedContent = null ; isPlaying = false"
                    class="mb-4 text-gray-500 font-normal p-1 rounded-full hover:bg-gray-200 transition-all">
             Back 
            </button>

            <div class="w-full flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl">
      <!-- Thumbnail with Play Button -->
      <div v-if="!isPlaying" class="relative cursor-pointer" @click="isPlaying = true">
        <img :src="selectedContent.thumbnail_url" alt="Content Thumbnail"
             class="object-cover relative w-full aspect-video rounded-md shadow-md">
        <!-- Play Icon -->
        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 rounded-md">
          <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"></path>
          </svg>
        </div>
      </div>

      <!-- Video Player -->
      <div v-else class="object-cover relative w-full aspect-video rounded-md shadow-md">
        <!-- YouTube/Embedded Video -->
        <iframe v-if="selectedContent.content_url.includes('youtube.com') || selectedContent.content_url.includes('youtu.be')" 
                :src="selectedContent.content_url + '?autoplay=1'" 
                class="w-full h-full rounded-md shadow-md border" 
                frameborder="0" allowfullscreen>
        </iframe>

        <!-- Direct Video File -->
        <video v-else controls autoplay class="w-full h-full rounded-md shadow-md border">
          <source :src="selectedContent.content_url" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </div>

           <!-- Title and Description -->
<h3 class="text-lg font-bold text-gray-800 px-4 md:px-6">{{ selectedContent.title }}</h3>
<p class="text-gray-700 mt-2 text-justify px-4 md:px-6">{{ selectedContent.description }}</p>

<!-- Bottom Section (Watch Button + Actions) -->
<div class="flex justify-end items-center mt-6 w-full px-4 md:px-6">

  <!-- Action Buttons -->
  <div class="flex gap-3">
    <button @click="editSelectedContent"
            class="text-xs px-4 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800 transition-all flex items-center gap-1">
      <i class="fas fa-edit text-sm"></i> Edit
    </button>
    
    <button @click="deleteSelectedContent(selectedContent.id)"
            class="text-xs px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-all flex items-center gap-1">
      <i class="fas fa-trash text-sm"></i> Delete
    </button>
  </div>

</div>


            <!-- Edit Form (Hidden until Edit is Clicked) -->
            <div v-if="editingContent" class="mt-4 p-4 bg-white rounded-md border-t-4 ">
          <h4 class="text-lg font-semibold mb-4 text-center text-gray-800">Edit Course Content</h4>
          <form @submit.prevent="updateSelectedContent" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-md font-medium">Title <span class="text-red-500">*</span></label>
              <input v-model="form.title" type="text"
                    class=" w-full border p-2 text-md rounded-md focus:ring-2 focus:ring-lime-700 focus:outline-none" required>
            </div>
            <div class="col-span-2">
              <label class="block text-md font-medium">Description <span class="text-red-500">*</span></label>
              <textarea v-model="form.description" placeholder="Write a brief description..."
                        class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500" minlength="10"></textarea>
            </div>
            <div>
              <label class="block text-md font-medium">Content Type</label>
              <select v-model="form.content_type"
                      class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option :value="1">📹 Video</option>
                <option :value="2">📄 PDF</option>
                <option :value="3">🖼️ Image</option>
              </select>
            </div>
            <div>
                    <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
                    <input
                      type="file"
                      @change="handleFileUpload('thumbnail_url', $event)"
                      accept="image/*"
                      class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
            <div>
              <label class="block text-md font-medium">Content URL</label>
              <input type="file" @change="handleFileUpload('content_url', $event)"
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            </div>
            <div>
              <label class="block text-md font-medium">Duration</label>
              <input v-model="form.hour" type="time"
                    class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required/>
            </div>
            <div>
              <label class="block text-md font-medium">Status</label>
              <select v-model="form.status"
                      class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option :value="1">✔️ Published</option>
                <option :value="2">📝 Draft</option>
                <option :value="3">📦 Archived</option>
              </select>
            </div>
            <div>
              <label class="block text-md font-medium">Note</label>
              <textarea v-model="form.note" placeholder="Add any additional note..."
                        class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" minlength="10"></textarea>
            </div>
            <div class="col-span-2 flex justify-end space-x-3">
              <button type="submit" class="text-white bg-lime-700 rounded-md px-4 py-1 text-md">Update</button>
              <button type="button" @click="canceledit" class="bg-gray-500 text-white rounded-md px-4 py-1 text-md">Cancel</button>
            </div>
          </form>
        </div>

          </div>
        </transition>
        </div>


  <!-- Show Edit Course Form if editing -->
  <div v-else class="mt-6 bg-white p-6 shadow-lg rounded-lg border border-gray-200">
    <h3 class="text-xl font-semibold mb-4">Edit Course</h3>

    <label for="course_name" class="block text-gray-700">Course Name</label>
    <input v-model="selectedCourse.course_name" type="text" id="course_name"
      class="w-full p-2 mb-4 border border-gray-300 rounded" />

    <label for="overview" class="block text-gray-700">Overview</label>
    <textarea v-model="selectedCourse.overview" id="overview" 
      class="w-full p-2 mb-4 border border-gray-300 rounded"></textarea>

      <label for="skill_level" class="block text-gray-700">Skill Level</label>
<select v-model="selectedCourse.skill_level" id="skill_level"
  class="w-full p-2 mb-4 border border-gray-300 rounded">
  <option value="Beginner">Beginner</option>
  <option value="Intermediate">Intermediate</option>
  <option value="Advance">Advance</option>
  <option value="Full Package">Full Package</option>
</select>


    <label for="tag" class="block text-gray-700">Tag</label>
    <textarea v-model="selectedCourse.tag" id="tag"
      class="w-full p-2 mb-4 border border-gray-300 rounded"></textarea>

    <label for="price" class="block text-gray-700">Price</label>
    <input v-model="selectedCourse.price" type="number" id="price"
      class="w-full p-2 mb-4 border border-gray-300 rounded" />

    <label for="discount" class="block text-gray-700">Discount</label>
    <input v-model="selectedCourse.discount" type="number" id="discount"
      class="w-full p-2 mb-4 border border-gray-300 rounded" />

    <label for="credit_hour" class="block text-gray-700">Credit Hours</label>
    <input v-model="selectedCourse.credit_hour" type="number" id="credit_hour"
      class="w-full p-2 mb-4 border border-gray-300 rounded" />
      <div>
  <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
  <input
    type="file"
    accept="image/*"
    @change="onFileChange('thumbnail_url', $event)"
    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700"
  />
  <!-- If a file is selected, show its name -->
  <p v-if="selectedCourse.thumbnail_url && typeof selectedCourse.thumbnail_url === 'object'" class="text-sm text-gray-500 mt-1">
    Selected file: {{ selectedCourse.thumbnail_url.name }}
  </p>
  <!-- Otherwise, show the current thumbnail if available -->
  <div v-else-if="selectedCourse.thumbnail_url" class="mt-2">
    <img
      :src="selectedCourse.thumbnail_url"
      alt="Course Thumbnail"
      class="w-32 h-32 object-cover rounded-md shadow-md"
    />
  </div>
</div>


    <div class="flex gap-4 mt-4">
      <button @click="updateCourse" class="bg-green-500 text-white px-6 py-2 rounded">Update Course</button>
      <button @click="cancelEdit" class="bg-gray-500 text-white px-6 py-2 rounded">Cancel</button>
    </div>
  </div>
        </div>
        </div>
        </div>
        </div>

        <!-- Course Module Form (Shown when showModuleForm is true) -->
        <div v-if="showModuleForm" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
  <div class="w-10/12 sm:max-w-xl bg-white p-6 rounded-lg shadow-lg overflow-auto max-h-[85vh]">
    <h2 class="text-xl font-semibold mb-4">Add Course Module</h2>

    <label class="block text-sm font-medium text-gray-700">Title</label>
    <input v-model="newModule.title" type="text" 
           class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300" 
           placeholder="Module Title" />

    <label class="block text-sm font-medium text-gray-700 mt-3">Sequence</label>
    <input v-model="newModule.sequence" type="number" 
           class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300" 
           placeholder="Sequence Number" />

    <label class="block text-sm font-medium text-gray-700 mt-3">Description</label>
    <textarea v-model="newModule.description" 
              class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300" 
              placeholder="Module Description"></textarea>

    <div class="mt-4 flex justify-end">
      <button @click="showModuleForm = false" 
              class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">Cancel</button>
      <button @click="addCourseModule" 
              class="ml-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Add Module</button>
    </div>
  </div>
</div>
</template>
<style scoped>
@keyframes orbit {
  0% { transform: rotate(0deg) translateX(20px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(20px) rotate(-360deg); }
}
.st8 {
        fill: #B7C7CEFF;
        stroke: #D4C1C1FF;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-miterlimit: 10;
      }
</style>
