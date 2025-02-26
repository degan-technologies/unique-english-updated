<script setup>
    import Axios from 'axios';
    import Editor from 'primevue/editor';
    import { storeToRefs } from 'pinia';
    import { onUnmounted, ref } from 'vue';
    import {  useRouter } from "vue-router";

    import { useInstructorStore } from "@/store/useInstructorStore";

    const InstructorStore = useInstructorStore();
    const { selectedCourse } = storeToRefs(InstructorStore);

    const router = useRouter();

    const course = ref({
        course_name: '',
        overview: '',
        tag: '',
        skill_level_id: '',
        price: '',
        discount: '',
        credit_hour: '',
        upload_file: null
    });

    const errors = ref({});
    const successMessage = ref('');
    const loading = ref(false);

    if(selectedCourse.value?.id) {
        course.value = { ...selectedCourse.value };
    }

    function onFileChange(field, event) {
        const file = event.target.files[0];
        if (file) {
            course.value['upload_file'] = file;
            course.value['create_thumbnail_url'] = URL.createObjectURL(file);
        }
    };


    function storeCourse() {
    loading.value = true;

    const formData = new FormData();

    formData.append("course_name", course.value.course_name);
    formData.append("overview", course.value.overview);
    formData.append("tag", course.value.tag);
    formData.append("skill_level", Number(course.value.skill_level_id));
    formData.append("price", Number(course.value.price)); 
    formData.append("discount", Number(course.value.discount)); 
    formData.append("credit_hour", Number(course.value.credit_hour)); 
    formData.append('thumbnail_url', course.value.upload_file);

    Axios
        .post('/api/courses/course', formData)
        .then(res => {
            successMessage.value = res.data.message;
            course.value = { course_name: '', overview: '', tag: '', skill_level_id: '', price: '', discount: '', credit_hour: '', thumbnail_url: '' };
        })
        .finally(() => {
            loading.value = false;
        });
    };

    function updateCourse() {
        loading.value = true;

    const formData = new FormData();

    formData.append("course_name", course.value.course_name);
    formData.append("overview", course.value.overview);
    formData.append("tag", course.value.tag);
    formData.append("skill_level", Number(course.value.skill_level_id));
    formData.append("price", Number(course.value.price)); 
    formData.append("discount", Number(course.value.discount)); 
    formData.append("credit_hour", Number(course.value.credit_hour)); 
    formData.append('thumbnail_url', course.value.upload_file);

    Axios
        .post(`/api/courses/update/${selectedCourse.value.id}`, formData)
        .then(res => {
            successMessage.value = res.data.message;
        })
        .finally(() => {
            loading.value = false;
        });
    };

    function goBack() {
        router.back();
    }; 

    onUnmounted(()=>{
    selectedCourse.value = null;
    });
</script>

<template>
    <button
        @click="goBack()"
        class="m-2 bg-lime-700 text-white left-2 text-gray-500 font-normal p-1 rounded-md hover:bg-lime-800 transition-all" >
        <i class="fas fa-arrow-left mr-2 text-sm"></i> 
        <span>Back</span>
    </button>
    <div class="flex justify-center items-center min-h-screen p-4 ">
        <div class="w-full p-6">
            <h2 class="text-xl font-bold text-start text-lime-700 mb-4"> {{ selectedCourse ? 'Edit Course' : 'Add a New Course' }} </h2>
            <transition name="fade">
                <div v-if="successMessage" 
                    class="mb-6 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg text-center">
                    {{ successMessage }}
                </div>
            </transition>

            <div class="grid md:grid-cols-4 gap-4 ">
                <div class="col-span-3 space-y-6">
                    <div class="form-group">
                        <label class="block text-gray-700 font-semibold mb-1">
                            Course Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="course.course_name" 
                            type="text"
                            class="form-input focus:ring-2 focus:ring-lime-700"
                            :class="{'border-red-500': errors.course_name}" 
                            placeholder="Enter course name" />
                        <p v-if="errors.course_name" class="error-text">{{ errors.course_name }}</p>
                    </div>
                    <div>
                        <img :src="course.create_thumbnail_url ? course.create_thumbnail_url : course?.thumbnail_url" 
                            alt="Course Thumbnail"  
                            class="h-64 rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl">
                        <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
                        <input
                            type="file"
                            @change="onFileChange('thumbnail_url', $event)"
                            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 font-semibold mb-1">Overview</label>
                        <Editor 
                            v-model="course.overview"
                            editorStyle="height: 200px"
                            :class="{'border-red-500': errors.overview}" />
                        <p v-if="errors.overview" class="error-text">{{ errors.overview }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="form-group">
                        <label class="block text-gray-700 font-semibold mb-1">Tags</label>
                        <input 
                            v-model="course.tag" 
                            type="text"
                            class="form-input focus:ring-2 focus:ring-lime-700"
                            :class="{'border-red-500': errors.tag}" 
                            placeholder="e.g. Programming, AI, Web Dev" />
                        <p v-if="errors.tag" class="error-text">{{ errors.tag }}</p>
                    </div>
                    <div>
                        <label class="block text-md font-medium text-gray-600 mb-1">
                            Skill Level <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="course.skill_level_id"
                            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700"
                            required >
                            <option value="1">BEGINNER</option>
                            <option value="2">INTERMEDIATE</option>
                            <option value="3">ADVANCE</option>
                            <option value="4">FULL PACKAGE</option>
                        </select>
                        <p v-if="errors.skill_level" class="error-text">{{ errors.skill_level }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="block text-gray-700 font-semibold mb-1">Price ($)</label>
                            <input 
                            v-model="course.price" 
                            type="number" min="0" step="0.01"
                            class="form-input focus:ring-2 focus:ring-lime-700"
                            :class="{'border-red-500': errors.price}" 
                            placeholder="e.g. 99.99" />
                            <p v-if="errors.price" class="error-text">{{ errors.price }}</p>
                        </div>

                        <div class="form-group">
                            <label class="block text-gray-700 font-semibold mb-1">Discount (%)</label>
                            <input 
                            v-model="course.discount" 
                            type="number" min="0" max="100" step="0.01"
                            class="form-input focus:ring-2 focus:ring-lime-700"
                            :class="{'border-red-500': errors.discount}" 
                            placeholder="e.g. 10" />
                            <p v-if="errors.discount" class="error-text">{{ errors.discount }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 font-semibold mb-1">Credit Hour</label>
                        <input 
                            v-model="course.credit_hour" 
                            type="number" min="1"
                            class="form-input focus:ring-2 focus:ring-lime-700"
                            :class="{'border-red-500': errors.credit_hour}" 
                            placeholder="e.g. 3" />
                        <p v-if="errors.credit_hour" class="error-text">{{ errors.credit_hour }}</p>
                    </div>
                </div>

            </div>
            <div class="flex justify-end">
                <button 
                    type="submit"
                    @click="selectedCourse ? updateCourse() : storeCourse()"
                    :disabled="loading"
                    class="w-fit bg-gradient-to-r from-lime-700 to-lime-600 text-white py-2 px-4 rounded-lg hover:opacity-90 transition duration-300 text-md mr-5" >
                    <span v-if="loading"> {{ selectedCourse ? 'Updating Course...' : 'Adding Course...' }}</span>
                    <span v-else> {{ selectedCourse ? 'Update Course' : 'Add Course' }} </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-group {
  @apply flex flex-col gap-1;
}

.form-group label {
  @apply font-semibold text-gray-700;
}

.form-input {
  @apply w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 transition duration-200;
}

.error-text {
  @apply text-red-600 text-sm mt-1;
}
</style>