<script setup>
    import Axios from 'axios';
    import { storeToRefs } from 'pinia';
    import { onUnmounted, ref, watch, toRaw, onMounted, onBeforeUnmount } from 'vue';
    import { useRouter, useRoute } from "vue-router";
    import videojs from 'video.js';
    import 'video.js/dist/video-js.css';
    import overviewEditor from '@/components/Layout/overviewEditor.vue';

    import { useInstructorStore } from "@/store/useInstructorStore";

    const InstructorStore = useInstructorStore();
    const { selectedCourse, courseModuleTab } = storeToRefs(InstructorStore);

    const router = useRouter();
    const route = useRoute();

    const thumbnail_url = ref('thumbnail_url')
    const intro_video = ref('intro_video')

    const course = ref({
        course_name: '',
        overview: '',
        tag: '',
        skill_level_id: '',
        price: '',
        discount: '',
        credit_hour: '',
        upload_thumbnail: null,
        intro_video: null,
        language: '',
    });

    const errors = ref({});
    const successMessage = ref('');
    const loading = ref(false);

    const showNextButton = ref(false);
    const isSavingDraft = ref(false);
    const isStoringCourse = ref(false);
    const isUpdatingCourse = ref(false);

    const videoPlayer = ref(null);
    let playerInstance = null;


    function goToDetails(tab, selectedCourse) {
        const rawCourse = toRaw(selectedCourse);

        router.push({
            name: 'instructor',
            query: {
                currentTab: route.query.currentTab,
                selectedAction: tab,
                slug: rawCourse.slug,
                reload: Date.now()
            }
        }).then(() => {
            router.go(0);
        });
    }

    if (selectedCourse.value?.id) {
        course.value = { ...selectedCourse.value };
    }

    function handleFileUpload(field, event) {
        const file = event.target.files[0];
        if (file) {
            if (field === thumbnail_url.value) {
                course.value['upload_thumbnail'] = file;
                course.value['create_thumbnail_url'] = URL.createObjectURL(file);
                return;
            }
            course.value['upload_intro_video'] = file;
            course.value['create_intro_video'] = URL.createObjectURL(file);

            // Update the Video.js player with the new source:
            if (playerInstance) {
                playerInstance.src({
                    src: course.value['create_intro_video'],
                    type: 'video/mp4'
                });
                playerInstance.play();
            }
        }
    }

    function storeCourse() {
        isStoringCourse.value = true;

        const formData = new FormData();

        formData.append("course_name", course.value.course_name);
        formData.append("overview", course.value.overview);
        formData.append("tag", course.value.tag);
        formData.append("skill_level", Number(course.value.skill_level_id));
        formData.append("price", Number(course.value.price));
        formData.append("discount", Number(course.value.discount));
        formData.append("credit_hour", Number(course.value.credit_hour));
        formData.append('thumbnail_url', course.value.upload_thumbnail);
        formData.append('intro_video', course.value.upload_intro_video);
        formData.append('language', course.value.language);
        formData.append("status", "published");

        Axios
            .post('/api/courses/course', formData)
            .then(res => {
                successMessage.value = res.data.message;
                course.value = { course_name: '', overview: '', tag: '', skill_level_id: '', price: '', discount: '', credit_hour: '', thumbnail_url: '', intro_video: '', language: '' };
                isStoringCourse.value = false;
            })
            .finally(() => {
                loading.value = false;
            });
    };

    function storeDraft() {
        isSavingDraft.value = true;

        const formData = new FormData();

        formData.append("course_name", course.value.course_name);
        formData.append("overview", course.value.overview);
        formData.append("tag", course.value.tag);
        formData.append("skill_level", Number(course.value.skill_level_id));
        formData.append("price", Number(course.value.price));
        formData.append("discount", Number(course.value.discount));
        formData.append("credit_hour", Number(course.value.credit_hour));
        formData.append('thumbnail_url', course.value.upload_thumbnail);
        formData.append('intro_video', course.value.upload_intro_video);
        formData.append('language', course.value.language);
        formData.append("status", "draft");

        Axios
            .post('/api/courses/course', formData)
            .then(res => {
                successMessage.value = res.data.message;
                course.value = { course_name: '', overview: '', tag: '', skill_level_id: '', price: '', discount: '', credit_hour: '', thumbnail_url: '', intro_video: '', language: '' };
                selectedCourse.value = res.data.data;
                console.log('Selected Course:', selectedCourse.value);
                showNextButton.value = true;
                isSavingDraft.value = false;
            }) 
            .finally(() => {
                loading.value = false;
            });
    };

    function updateCourse() {
        isUpdatingCourse.value = true;

        const formData = new FormData();

        formData.append("course_name", course.value.course_name);
        formData.append("overview", course.value.overview);
        formData.append("tag", course.value.tag);
        formData.append("skill_level", Number(course.value.skill_level_id));
        formData.append("price", Number(course.value.price));
        formData.append("discount", Number(course.value.discount));
        formData.append("credit_hour", Number(course.value.credit_hour));
        course.value.upload_thumbnail instanceof File && formData.append('thumbnail_url', course.value.upload_thumbnail);
        course.value.upload_intro_video instanceof File && formData.append('intro_video', course.value.upload_intro_video);
        formData.append('language', course.value.language);
        formData.append("status", course.value.status || "published");

        Axios
            .post(`/api/courses/update/${selectedCourse.value.id}`, formData)
            .then(res => {
                successMessage.value = res.data.message;
                isUpdatingCourse.value = false;
            }) 
            .finally(() => {
                loading.value = false;
            });
    };

    const updateOverview = (newOverview) => {
        course.value.overview = newOverview;
    };

    watch(
        [() => successMessage.value, () => route.query.slug],
        ([newSuccessMessage, newSlug]) => {
            if (newSuccessMessage) {
            setTimeout(() => {
                successMessage.value = null;
            }, 2000);
            }
        },
        { immediate: true }
    );

    onUnmounted(() => {
        selectedCourse.value = null;
    });

    onMounted(() => {
        if (videoPlayer.value) {
            playerInstance = videojs(videoPlayer.value, {
                controls: true,
                autoplay: true,
                responsive: true,
                fluid: true,
            });
        }
    });
    onBeforeUnmount(() => {
        if (playerInstance) {
            playerInstance.dispose();
        }
    });
</script>

<template>
    <div class="flex justify-center items-center min-h-screen p-6">
        <div class="w-full max-w-5xl bg-white rounded-lg p-8">
            <h2 class="text-2xl font-bold text-lime-700 mb-6">
                {{ selectedCourse ? 'Edit Course' : 'Add a New Course' }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Course Name <span
                                class="text-red-500">*</span>
                        </label>
                        <input v-model="course.course_name"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': errors.course_name }"
                            placeholder="Enter course name" />
                        <p v-if="errors.course_name"
                            class="mt-1 text-red-500 text-sm">{{ errors.course_name }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div v-if="course.create_thumbnail_url || course?.thumbnail_url">
                                <img :src="course.create_thumbnail_url ? course.create_thumbnail_url : course?.thumbnail_url"
                                    alt="Course Thumbnail"
                                    class="w-full h-40 object-cover rounded-md shadow-md transition transform hover:scale-105" />
                            </div>
                            <label class="block text-gray-700 font-medium mt-2 mb-1 text-sm">Upload Thumbnail</label>
                            <input type="file"
                                @change="handleFileUpload(thumbnail_url, $event)"
                                class="w-full border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-lime-700 text-sm" />
                        </div>
                        <div>
                            <div
                                v-if="course.create_intro_video || (course.intro_video_url && course.intro_video_url !== 'no-intro_video.png')">
                                <!-- Video.js Player -->
                                <video ref="videoPlayer"
                                    class="video-js vjs-default-skin w-full h-40 rounded-md shadow-md border"
                                    controls
                                    autoplay
                                    preload="auto">
                                    <source
                                        :src="course.create_intro_video ? course.create_intro_video : course.intro_video_url"
                                        type="video/mp4" />
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <label class="block text-gray-700 font-medium mt-2 mb-1 text-sm">Upload Intro Video</label>
                            <input type="file"
                                @change="handleFileUpload(intro_video, $event)"
                                class="w-full border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-lime-700 text-sm" />
                        </div>
                    </div>

                    <div>
                        <overviewEditor :selectedCourse="course"
                            @update-overview="updateOverview" />
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Tags</label>
                        <input v-model="course.tag"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': errors.tag }"
                            placeholder="e.g. Programming, AI, Web Dev" />
                        <p v-if="errors.tag"
                            class="mt-1 text-red-500 text-sm">{{ errors.tag }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Skill Level <span class="text-red-500">*</span>
                        </label>
                        <select v-model="course.skill_level_id"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            required>
                            <option value="1">BEGINNER</option>
                            <option value="2">INTERMEDIATE</option>
                            <option value="3">ADVANCE</option>
                            <option value="4">FULL PACKAGE</option>
                        </select>
                        <p v-if="errors.skill_level"
                            class="mt-1 text-red-500 text-sm">{{ errors.skill_level }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Price ($)</label>
                            <input v-model="course.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                                :class="{ 'border-red-500': errors.price }"
                                placeholder="e.g. 99.99" />
                            <p v-if="errors.price"
                                class="mt-1 text-red-500 text-sm">{{ errors.price }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Discount (%)</label>
                            <input v-model="course.discount"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                                :class="{ 'border-red-500': errors.discount }"
                                placeholder="e.g. 10" />
                            <p v-if="errors.discount"
                                class="mt-1 text-red-500 text-sm">{{ errors.discount }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Credit Hour</label>
                        <input v-model="course.credit_hour"
                            type="number"
                            min="1"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': errors.credit_hour }"
                            placeholder="e.g. 3" />
                        <p v-if="errors.credit_hour"
                            class="mt-1 text-red-500 text-sm">{{ errors.credit_hour }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Language</label>
                        <input v-model="course.language"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': errors.language }"
                            placeholder="e.g.  amharic, english" />
                        <p v-if="errors.language"
                            class="mt-1 text-red-500 text-sm">{{ errors.language }}</p>
                    </div>
                </div>
            </div>
            <div class=" space-y-4 mt-4">
                <transition name="fade">
                    <div v-if="successMessage"
                        class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-center">
                        {{ successMessage }}
                    </div>
                </transition>
                <div class="flex justify-end mt-8 space-x-4">
                    <button v-if="!selectedCourse"
                        type="button"
                        @click="storeDraft()"
                        :disabled="isSavingDraft"
                        class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition duration-300 text-md">
                        <span v-if="isSavingDraft">
                            Saving Draft...
                        </span>
                        <span v-else>
                            Save as Draft
                        </span>
                    </button>
                    <button type="submit"
                        @click="selectedCourse ? updateCourse() : storeCourse()"
                        :disabled="isStoringCourse || isUpdatingCourse"
                        class="bg-gradient-to-r from-lime-700 to-lime-600 text-white py-2 px-4 rounded-lg hover:opacity-90 transition duration-300 text-md">
                        <span v-if="isStoringCourse || isUpdatingCourse">
                            {{ selectedCourse ? 'Updating Course...' : 'Adding Course...' }}
                        </span>
                        <span v-else>
                            {{ selectedCourse ? 'Update Course' : 'Add Course' }}
                        </span>
                    </button>

                    <!-- Next Button -->
                    <button v-if="showNextButton"
                        @click="goToDetails(courseModuleTab, selectedCourse)"
                        :disabled="isSavingDraft || isStoringCourse || isUpdatingCourse"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                        Next
                    </button>
                </div>
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