 <script setup>
    import Axios from "axios";
    import { storeToRefs } from "pinia";
    import { useRoute, useRouter } from "vue-router"
    import { ref, onMounted, watch, watchEffect} from "vue";

    import { useInstructorStore } from "@/store/useInstructorStore";
    
    import AddCourseModule from "@/components/Course/CourseModule/AddCourseModule.vue";

    const InstructorStore = useInstructorStore();
    const {instructorCourses, analytics, selectedCourse, courseEditTab, courseModuleTab, courseId } = storeToRefs(InstructorStore);

    const route = useRoute();
    const router = useRouter();
    const selectedCourseSlug =ref(route.query.slug);

    const loading = ref(true);

    const activeMenuId = ref(null);
    const rowsPerPageOptions = [5, 10, 15, 20];
    const rowsPerPage = ref(10)
    const pagination =ref('');
    const totalPages = ref('');
    const currentPage = ref(1);
    const skillLevelFilter = ref('');

    const editingCourseId = ref(null); 
    const actionType = ref('STORE');

    const props = defineProps({
                searchQuery: String,
                });

    function filteredCourses(page=1) {
        Axios
            .post(`/api/courses/search?page=${page}`, {
                searchQuery: props.searchQuery,
                rowsPerPageOptions:rowsPerPage.value,
                skillLevel: skillLevelFilter.value 
            })
            .then(res => {
                instructorCourses.value = res.data.data
                pagination.value = res.data.pagination;
                totalPages.value = res.data.pagination.last_page;
                currentPage.value = res.data.pagination.current_page;
                analytics.value.total = res.data.total;
                analytics.value.newToday = res.data.newToday;
                loading.value = false;
            })
    }

    function onNextPage() {
        if(currentPage.value == totalPages.value) return;

        filteredCourses(currentPage.value + 1);
    }

    function onPreviousPage() {
        if(currentPage.value <= 1) return;

        filteredCourses(currentPage.value - 1);
    }

    function coursePerPage(amount) {
        rowsPerPage.value = amount;
        filteredCourses(currentPage.value);
    }
    const toggleMenu = (id) => {
        activeMenuId.value = activeMenuId.value === id ? null : id;
    };

    function handleClickOutside(event) {
        if (!event.target.closest(".relative")) {
            activeMenuId.value = null;
        }
    };

    function formatDate(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

    function openModuleForm(id) {
        console.log(id);
        courseId.value = id; 
    };

    function getSelectedCourse(tab, course) {
        
        router.push({
            name: 'instructor',
            query: {
                currentTab:route.query.currentTab,
                selectedAction:tab,
                slug:course?.slug,
            }
        });

        editingCourseId.value = course.id;
        selectedCourse.value = course; 
    };

    watchEffect(()=>{
        if(!selectedCourse.value) {
            selectedCourse.value = instructorCourses.value.find(item => item?.slug == selectedCourseSlug.value);
        }
    });

    onMounted(() => {
        filteredCourses();
        document.addEventListener("click", handleClickOutside);
    });

    watch(
        [() => props.searchQuery, skillLevelFilter],
        () => {
            filteredCourses(1);
        }
    );

</script>

<template>
    <div class="max-w-full mx-auto">
        <div v-if="loading" class="flex flex-col justify-center items-center h-20 text-xl font-semibold">
            <div class="relative w-12 h-12">
                <div class="w-2 h-2 bg-blue-500 rounded-full absolute top-0 left-1/2 transform -translate-x-1/2 animate-[orbit_1.3s_linear_infinite]">
                </div>
            </div>
            Loading...
        </div>

        <div  v-if="!selectedCourse && !loading">
            <div class="flex justify-end mb-4 mr-5">
                <select 
                    v-model="skillLevelFilter"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" >
                    <option value="">All Skill Levels</option>
                    <option value="1">Beginner</option>
                    <option value="2">Intermediate</option>
                    <option value="3">Advance</option>
                    <option value="4">Full Package</option>
                </select>
            </div>
            <div v-if="instructorCourses.length === 0" class="text-center">No Courses Found</div>

            <div v-if="instructorCourses.length > 4 ">
                <div class="border border-gray-300 rounded-lg horizontal-scroll">
                    <table class="w-full text-left border-collapse">
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
                                v-for="course in instructorCourses"
                                :key="course.id"
                                class="border-b border-gray-200 hover:bg-gray-50 transition align-middle" >
                                <td class="py-3 px-4 flex items-center gap-3">
                                    <img 
                                        :src="course.thumbnail_url" 
                                        alt="Course Image" 
                                        class="w-12 h-12 rounded-md object-cover" />
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800">{{ course.course_name }}</h3>
                                        <p class="text-xs text-gray-500">
                                            Level: <span class="font-bold">{{ course.skill_level }}</span>
                                        </p>
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
                                    <div class="flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09L5.244 12 .49 7.91l6.07-.88L10 2l2.44 5.03 6.07.88-4.754 4.09 1.122 5.99z" />
                                        </svg>
                                        <span class="ml-1 text-center">{{ course.rating || 4.6 }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
                                    {{ course.total_enroll || 15 }}
                                </td>
                                <td class="py-3 px-4 text-gray-700 text-sm font-bold text-center">
                                    ${{ course.revenue || 25 }}
                                </td>
                                <td class="py-3 px-4 text-gray-700 text-sm text-center">
                                    {{ formatDate(course.created_at) || '2025-03-24' }}
                                </td>
                                <td class="py-3 px-4 h-full">
                                    <div class="flex items-center justify-center h-full gap-2">
                                        <button
                                            @click="getSelectedCourse(courseModuleTab, course)"
                                            class="bg-white border border-gray-200 px-3 py-1 rounded text-sm text-black hover:bg-gray-200 hover:text-gray-900 transition"
                                            >
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Footer -->
                <div  class="flex justify-between items-center mt-4 px-4">
                    <div class="flex space-x-2 items-center">
                        <span class="text-sm text-gray-600">Courses per page:</span>
                        <div 
                            v-for="option in rowsPerPageOptions" 
                            :key="option"
                            @click="coursePerPage(option)"
                            class="border border-gray-300 rounded-md px-4 py-2 text-sm cursor-pointer transition-all duration-200"
                            :class="{
                                'bg-blue-500 text-white font-bold': rowsPerPage === option, 
                                'bg-white text-gray-700 hover:bg-gray-200': rowsPerPage !== option
                            }">
                            {{ option }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            @click="onPreviousPage()"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50" >
                            Prev
                        </button>
                        <span class="text-sm text-gray-600"> Page {{ currentPage }} of {{ totalPages }} </span>
                        <button
                            @click="onNextPage() "
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50" >
                            Next
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="instructorCourses.length <= 4" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="course in instructorCourses"
                    :key="course.id"
                    class="bg-white rounded-lg border transition duration-300 overflow-hidden w-72">
                    <img
                        :src="course.thumbnail_url"
                        alt="Course Image"
                        class="w-full h-36 object-cover" />
                    <div class="p-3 space-y-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ course.course_name }}</h3>
                            <p class="text-xs text-gray-500">
                                Level: <span class="font-semibold">{{ course.skill_level }}</span>
                            </p>
                        </div>
                        <div class="text-gray-600 text-justify line-clamp-2" v-html="course.overview"></div> 
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center text-xs text-gray-700">
                                <svg class="w-3 h-3 text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09L5.244 12 .49 7.91l6.07-.88L10 2l2.44 5.03 6.07.88-4.754 4.09 1.122 5.99z" />
                                </svg>
                                <span class="ml-1 font-bold">{{ course.rating || 4.6 }}</span>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">
                                {{ course.total_enroll || 15 }} Enrolled
                            </div>
                            <div class="text-xs font-semibold text-gray-700">
                                ${{ course.revenue || 25 }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <button
                                @click="getSelectedCourse(courseModuleTab, course)"
                                class="bg-white border border-gray-200 px-2 py-1 rounded text-xs text-black hover:bg-gray-200 transition">
                                Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AddCourseModule 
            v-if="courseId" 
            :courseId="courseId"
            :actionType="actionType"/>
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
    .horizontal-scroll {
        overflow-x: auto; 
        overflow-y: hidden; 
        white-space: nowrap; 
    }

    .horizontal-scroll::-webkit-scrollbar {
        height: 8px; 
        display: block;
    }

    .horizontal-scroll::-webkit-scrollbar-thumb {
        background-color: #c1c1c1;
        border-radius: 4px;
    }

    .horizontal-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
</style>
