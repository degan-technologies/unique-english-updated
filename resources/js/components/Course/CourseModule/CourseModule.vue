<script setup>
    import Axios from "axios";
    import { onUnmounted, ref } from "vue";
    import { storeToRefs } from "pinia";
    import {  useRouter } from "vue-router"

    import { useInstructorStore } from '@/store/useInstructorStore';

    import AddCourseModule from "@/components/Course/CourseModule/AddCourseModule.vue";
    import ModuleContent from "@/components/Course/CourseModule/ModuleContent/ModuleContent.vue";

    const InstructorStore = useInstructorStore();
    const { selectedCourse, editCourseModule} = storeToRefs(InstructorStore);

    const router = useRouter();

    const actionType = ref('UPDATE');

    const expandedModule = ref(null);
    const selectedModule = ref(null);

    const dropdownOpen = ref(null);

    const selectedContent = ref(null);

    function startEditing (module) {
        editCourseModule.value = module; 
    };

    function deleteCourseModule(moduleId) {
        Axios
            .delete(`/api/courses/module/${moduleId}`)
            .finally(res=>{
                selectedCourse.value.courseModules = selectedCourse.value.courseModules.filter(item => item.id !== moduleId);
            })
    };

    function addModuleContent(module){
        selectedContent.value = null;
        selectedModule.value =  module
        expandedModule.value = selectedModule.value?.id;
    };

    function actionExpandModule(module){
        selectedContent.value = null;
        selectedModule.value =  null;
        expandedModule.value = expandedModule.value === module.id ? null : module.id;
    };

    function toggleDropdown(moduleId) {
        dropdownOpen.value = dropdownOpen.value === moduleId ? null : moduleId;
    };

    function selectContent(content) {
        selectedModule.value =  null;
        selectedContent.value = content;
    };  
    function goBack() {
        router.back();
    }; 
    
    onUnmounted(() => {
        selectedCourse.value = null;
    });
</script>
 
<template>
<div>
    <button
        @click="goBack()"
        class="m-2 bg-lime-700 text-white left-2 text-gray-500 font-normal p-1 rounded-md hover:bg-lime-800 transition-all" >
        <i class="fas fa-arrow-left mr-2 text-sm"></i> 
        <span>Back</span>
    </button>
    
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 w-full md:w-3/4 mx-auto">
        <div class="w-full p-6 flex flex-col items-start relative">
        <div class="overflow-hidden w-full  aspect-video rounded-t-lg mt-3 relative cursor-pointer">
            <img :src="selectedCourse?.thumbnail_url" 
                alt="Course Thumbnail"  
                class="w-full h-full rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl">    
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                    <i class="fas fa-play-circle text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Card: Course Details -->
    <div class="p-6 h-fit justify-center" >
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
        <div class="text-left">
            <h3 class="text-2xl text-gray-900 font-bold">
                {{ selectedCourse?.course_name }}
            </h3>
        </div>
        <div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mt-4">Course Overview</h2>
                <div class="text-gray-600 text-lg mt-2 text-justify" v-html="selectedCourse.overview"></div>            
            </div>
                <AddCourseModule 
                    v-if="editCourseModule" 
                    :actionType="actionType"/>

                <transition class="" name="fade-slide">
                    <div class="space-y-2">
                        <h2 
                            v-if="selectedCourse?.courseModules.length > 0" 
                            class="text-lg font-semibold text-gray-800 my-4">What You Will Learn</h2>
                        <div
                        v-for="(module, index) in selectedCourse?.courseModules"
                        :key="module.id"
                        :class="[
                            'relative p-2 rounded-lg',
                            dropdownOpen === module.id ? 'z-40' : 'z-10'
                        ]" >
                        <!-- Module Title & Actions -->
                        <div class="flex justify-between items-center cursor-pointer">
                            <h3
                            class="font-semibold text-lg text-blue-500 flex items-center cursor-pointer"
                            @click="actionExpandModule(module)"
                            >
                            <i class="fa-solid fa-folder transition-transform transform hover:scale-[1.02] mr-4 text-xl text-yellow-500"></i>
                            {{ module.title }}
                            </h3>

                            <div class="flex items-center space-x-2">
                            <!-- Expand/Collapse Button -->
                            <button class="text-gray-500 text-sm" @click="actionExpandModule(module)">
                                <i :class="expandedModule === module.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                            </button>

                            <!-- 3-dot Action Menu -->
                            <div>
                                <button
                                    @click.stop="toggleDropdown(module.id)"
                                    class="p-1 rounded-full hover:bg-gray-200 transition duration-200"
                                    title="Actions" >
                                <i class="fas fa-ellipsis-v text-sm"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <transition name="fade-slide">
                                <div
                                    v-if="dropdownOpen === module.id"
                                    class="absolute right-0 bg-white shadow-md bg-gray-300 rounded-md z-50 w-32 text-sm" >
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
                                    @click="addModuleContent(module)"
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
                                <!-- add content -->
                                <ModuleContent
                                    v-if="selectedModule?.id === module.id"
                                    :selectedContent="null"
                                    :selectedModule="selectedModule"/>
                            <div class="space-y-2">
                                <div
                                    v-for="content in module.courseContents"
                                        :key="content.id"
                                        class="flex items-center p-2"
                                        @click="selectContent(content)" >
                                    <div 
                                        v-if="selectedContent?.id !== content?.id"
                                        class="flex">
                                        <i
                                            :class="{
                                                'fa-circle-play': content.content_type == 1,
                                                'fa-file-lines': content.content_type == 2,
                                                'fa-image': content.content_type == 3,
                                            }"
                                            class="fa-solid px-8 text-lg w-5 h-5" >
                                        </i>
                                    <p class="text-gray-700 text-md cursor-pointer ">{{ content.title }}</p>
                                    </div>

                                    <!-- from here -->
                                    
                                     <!--  -->
                                    <transition name="fade-slide">
                                            <!-- edit content -->
                                        <ModuleContent
                                            v-if="selectedContent?.module_id === module.id && selectedContent?.id === content?.id"
                                            :selectedContent="selectedContent"
                                            :selectedModule="null"/>
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
</div>
</template>