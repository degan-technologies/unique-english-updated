<script setup>
    import Axios from "axios";
    import { storeToRefs } from "pinia";
    import { onUnmounted, ref, onMounted, onBeforeUnmount } from "vue";
    import { useRouter, useRoute } from "vue-router"
    import videojs from 'video.js';
    import 'video.js/dist/video-js.css';

    import { useInstructorStore } from '@/store/useInstructorStore';

    import ManageQuiz from "@/components/Exam/ManageQuiz.vue";
    import AddCourseModule from "@/components/Course/CourseModule/AddCourseModule.vue";
    import ModuleContent from "@/components/Course/CourseModule/ModuleContent/ModuleContent.vue";
    import ExamManagement from "@/components/Layout/ExamManagement.vue";

    const InstructorStore = useInstructorStore();
    const { selectedCourse, editCourseModule, courseEditTab, courseId, instructorCourses } = storeToRefs(InstructorStore);

    const route = useRoute();
    const router = useRouter();

    const isPlaying = ref(false);

    const expandedModule = ref(null);
    const selectedModule = ref(null);

    const actionType = ref('');
    const dropdownOpen = ref(null);
    const showQuizPage = ref(false);
    const editingCourseId = ref(null);

    const selectedContent = ref(null);

    function startEditing(module) {
        editCourseModule.value = module;
        actionType.value = 'UPDATE';
    };

    function openModuleForm(id) {
        courseId.value = id;
        actionType.value = 'STORE';
    };

    function deleteCourse(id) {
        Axios
            .delete(`/api/courses/course/${id}`)
            .finally(res => {
                instructorCourses.value = instructorCourses.value.filter(course => course.id !== id);
                goBack();
            });
    };

    function deleteCourseModule(moduleId) {
        Axios
            .delete(`/api/courses/module/${moduleId}`)
            .finally(res => {
                selectedCourse.value.courseModules = selectedCourse.value.courseModules.filter(item => item.id !== moduleId);
            })
    };

    function addModuleContent(module) {
        selectedContent.value = null;
        selectedModule.value = module;
        expandedModule.value = selectedModule.value?.id;
    };
    function addQuiz(module) {
        selectedContent.value = null;
        selectedModule.value = module;
        showQuizPage.value = true;
    };
    function closeQuizPage() {
        showQuizPage.value = false;
    }

    function actionExpandModule(module) {
        selectedContent.value = null;
        selectedModule.value = null;
        expandedModule.value = expandedModule.value === module.id ? null : module.id;
    };

    function toggleDropdown(moduleId) {
        dropdownOpen.value = dropdownOpen.value === moduleId ? null : moduleId;
    };
    function handleClickOutside(event) {
        if (!event.target.closest(".relative")) {
            dropdownOpen.value = null;
        }
    };

    function handleCancelAddContent() {
        selectedModule.value = null;
    }


    function selectContent(content) {
        selectedModule.value = null;
        selectedContent.value = content;
    };

    function getSelectedCourse(tab, selectedCourse) {

        router.push({
            name: 'instructor',
            query: {
                currentTab: route.query.currentTab,
                selectedAction: tab,
                slug: selectedCourse.slug,
                reload: Date.now()
            }
        }).then(() => {
            router.go(0);
        });

        editingCourseId.value = selectedCourse.id;
        selectedCourse.value = selectedCourse;
    };

    function goBack() {
        router.back();
    };
    onMounted(() => {
        document.addEventListener("click", handleClickOutside);
    });
    onUnmounted(() => {
        selectedCourse.value = null;
    });
    const videoPlayer = ref(null);
    let playerInstance = null;
    onMounted(() => {
        if (videoPlayer.value) {
            playerInstance = videojs(videoPlayer.value, {
                controls: true,
                autoplay: false,
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
    <div v-if="!showQuizPage">
        <button @click="goBack()"
            class="px-4 py-2 bg-gray-100 text-black rounded-md hover:bg-gray-200 transition mr-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <div class="grid gap-4 grid-cols-1 md:grid-cols-2 w-full md:w-3/4 mx-auto p-3">
            <div class="w-full height-full p-2 flex flex-col items-start relative">
                <div class="overflow-hidden w-full aspect-video rounded-t-lg mt-3 relative cursor-pointer">
                    <div v-if="!isPlaying"
                        class="relative w-full h-full cursor-pointer"
                        @click="isPlaying = true">
                        <img :src="selectedCourse?.thumbnail_url"
                            alt="Course Thumbnail"
                            class="w-full h-full object-cover transition-transform duration-300 rounded-t-lg shadow-lg hover:shadow-xl">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="p-2 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                                <i class="fas fa-play-circle text-white text-md"></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedCourse && selectedCourse.intro_video_url"
                        class="relative w-full h-full">
                        <video ref="videoPlayer"
                            class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border">
                            <source :src="selectedCourse.intro_video_url"
                                type="video/mp4" />
                        </video>
                    </div>
                    <p v-else>No video available</p>
                </div>
            </div>
            <div class="p-6 h-fit justify-center">
                <h2 class="text-xl leading-9 font-semibold mb-4">
                    Course Details
                </h2>
                <div class="flex flex-col gap-4">
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                        <strong>Level:</strong>
                        {{ selectedCourse?.skill_level }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-language text-green-500 mr-2"></i>
                        <strong>Language:</strong>
                        {{ selectedCourse?.language }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        <strong>Duration:</strong>
                        {{ selectedCourse?.credit_hour }}
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->
        <div class="w-full md:w-3/4 mx-auto p-3 ">
            <div class="flex flex-col md:flex-row items-center justify-start">
                <h3 class="text-2xl text-gray-900 font-bold mb-2 md:mb-0">
                    {{ selectedCourse?.course_name }}
                </h3>
                <div class="flex items-center space-x-2 ml-2">
                    <button @click="getSelectedCourse(courseEditTab, selectedCourse)"
                        class="flex items-center space-x-1 px-2 py-2 text-lime-600 rounded-md hover:bg-slate-200 transition">
                        <i class="fas fa-edit text-sm"></i>
                        <span>Edit</span>
                    </button>
                    <button @click="openModuleForm(selectedCourse?.id)"
                        class="flex items-center space-x-1 px-2 py-2 text-black-600 rounded-md hover:bg-slate-100 transition">
                        <i class="fas fa-plus text-sm"></i>
                        <span>Add Module</span>
                    </button>
                    <button @click="deleteCourse(selectedCourse?.id)"
                        class="flex items-center space-x-1 px-2 py-2 text-black rounded-md hover:bg-slate-100 transition">
                        <i class="fas fa-trash text-sm"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
            <div class="p-3">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mt-4">Course Overview</h2>
                    <div class="text-gray-600 text-lg mt-2 text-justify"
                        v-html="selectedCourse.overview"></div>
                </div>
                <AddCourseModule v-if="editCourseModule"
                    :actionType="actionType" />

                <transition class=""
                    name="fade-slide">
                    <div class="space-y-2">
                        <h2 v-if="selectedCourse?.courseModules.length > 0"
                            class="text-lg font-semibold text-gray-800 my-4">What You Will Learn</h2>
                        <div v-for="(module, index) in selectedCourse?.courseModules"
                            :key="module.id"
                            :class="[
                                'relative p-2 rounded-lg',
                                dropdownOpen === module.id ? 'z-40' : 'z-10'
                            ]">
                            <div class="flex justify-between items-center cursor-pointer">
                                <h3 class="font-semibold text-lg text-blue-500 flex items-center cursor-pointer"
                                    @click="actionExpandModule(module)">
                                    <i
                                        class="fa-solid fa-folder transition-transform transform hover:scale-[1.02] mr-4 text-xl text-yellow-500"></i>
                                    {{ module.title }}
                                </h3>

                                <div class="flex items-center space-x-2">
                                    <button class="text-gray-500 text-sm"
                                        @click="actionExpandModule(module)">
                                        <i
                                            :class="expandedModule === module.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                    </button>
                                    <div class="relative">
                                        <button @click.stop="toggleDropdown(module.id)"
                                            class="p-1 rounded-full hover:bg-gray-200 transition duration-200"
                                            title="Actions">
                                            <i class="fas fa-ellipsis-v text-sm"></i>
                                        </button>
                                        <transition name="fade-slide">
                                            <div v-if="dropdownOpen === module.id"
                                                class="absolute right-0 bg-white shadow-md bg-gray-300 rounded-md z-50 w-40 text-sm">
                                                <button @click="startEditing(module)"
                                                    class="flex items-center w-full px-2 py-2 text-md hover:bg-blue-100 transition duration-200 rounded">
                                                    <i class="fas fa-edit text-md mr-1"></i> Edit
                                                </button>

                                                <button @click="deleteCourseModule(module.id)"
                                                    class="flex items-center w-full px-2 py-2 text-md hover:bg-red-100 transition duration-200 rounded">
                                                    <i class="fas fa-trash text-md mr-1"></i> Delete
                                                </button>

                                                <button @click="addModuleContent(module)"
                                                    class="flex items-center w-full px-2 py-2 text-md hover:bg-purple-100 transition duration-200 rounded">
                                                    <i class="fas fa-plus text-md mr-1"></i> Add Content
                                                </button>
                                                <button @click="addQuiz(module)"
                                                    class="flex items-center w-full px-2 py-2 text-md hover:bg-purple-100 transition duration-200 rounded">
                                                    <i class="fas fa-plus text-md mr-1"></i> Add Quiz
                                                </button>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <!-- Course Contents (Expandable) -->
                            <transition name="fade-slide">
                                <div v-if="expandedModule === module.id"
                                    class="space-y-4 p-2">
                                    <ModuleContent v-if="selectedModule?.id === module.id"
                                        :selectedContent="null"
                                        :selectedModule="selectedModule"
                                        @cancelEdit="handleCancelAddContent" />
                                    <div class="space-y-2">
                                        <div v-for="content in module.courseContents"
                                            :key="content.id"
                                            class="flex items-center p-2"
                                            @click="selectContent(content)">
                                            <div v-if="selectedContent?.id !== content?.id"
                                                class="flex">
                                                <i :class="{
                                                    'fa-circle-play': content.content_type == 1,
                                                    'fa-file-lines': content.content_type == 2,
                                                    'fa-image': content.content_type == 3,
                                                }"
                                                    class="fa-solid px-8 text-lg w-5 h-5">
                                                </i>
                                                <p class="text-gray-700 text-md cursor-pointer ">{{ content.title }}</p>
                                            </div>

                                            <!-- from here -->
                                            <transition name="fade-slide">
                                                <!-- edit content -->
                                                <ModuleContent
                                                    v-if="selectedContent?.module_id === module.id && selectedContent?.id === content?.id"
                                                    :selectedContent="selectedContent"
                                                    :selectedModule="null"
                                                    class="w-full max-w-[90%] sm:max-w-[80%] md:max-w-[90%] lg:max-w-[80%] mx-auto" />

                                            </transition>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
        <AddCourseModule v-if="courseId"
            :courseId="courseId"
            :actionType="actionType" />
    </div>
    <div v-else>
        <ExamManagement v-if="showQuizPage"
            :module="selectedModule"
            @backToModule="closeQuizPage" />
    </div>
</template>