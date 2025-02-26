<script setup>
const skillLevelMapping = {
    "Beginner": 1,
    "Intermediate": 2,
    "Advance": 3,
    "Full Package": 4
    };
function onFileChange(field, event) {
        const file = event.target.files[0];
        if (file) {
            selectedCourse.value[field] = file;
        }
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
</script>
<template>
     <!-- Show Edit Course Form if editing -->
     <div  class="mt-6 bg-white p-6 shadow-lg rounded-lg border border-gray-200">
        <h3 class="text-xl font-semibold mb-4">Edit Course</h3>

        <label for="course_name" class="block text-gray-700">Course Name</label>
        <input v-model="selectedCourse.course_name" type="text" id="course_name"
        class="w-full p-2 mb-4 border border-gray-300 rounded" />

        <!-- PrimeVue Editor for Overview -->
        <label for="overview" class="block text-gray-700">Overview</label>
        <div>
        <h2>Course Overview</h2>

        <!-- Debugging: Regular Textarea to Compare -->
        <textarea v-model="selectedCourse.overview" rows="4" cols="50"></textarea>

        <!-- PrimeVue Editor: Should Show Content in the Typing Area -->
        <Editor v-model="selectedCourse.overview" editorStyle="height: 250px;"></Editor>
    </div>



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
</template>