<script setup>
    import Axios from "axios";
    import { storeToRefs } from "pinia";
    import { onMounted, ref, watch, watchEffect } from "vue";

    import { UseStudentStore } from "@/store/UseStudentStore";
    import { useRoute, useRouter } from "vue-router";

    const studentStore = UseStudentStore();
    const { courses, selectedCourseSlug} = storeToRefs(studentStore);

    const route = useRoute();
    const router = useRouter()

    const checkoutUrl = ref(null);

    const selectedCourse = ref(null);
    const collapsModuleId = ref(null);
    
    selectedCourseSlug.value = route.query.slug;

    function toggleModuleLesson(id) {
        if(collapsModuleId.value == id){
            return collapsModuleId.value = null;
        }
        collapsModuleId.value = id;
    };

    function enrollCourse(item){
        let  selectedItem = [
            {
                type:'course',
                slug:item.slug,
            },
        ];

        Axios
            .post('/api/initiate-payment',{cartItems:selectedItem} )
            .then(res=>{
                checkoutUrl.value = res.data.checkout_url;
                window.open(checkoutUrl.value, "_blank");
            })
            .catch(error => {});

    }

    onMounted(async ()=>{
        studentStore.fetchCourses();
    });

    watchEffect(()=>{
        if(!courses.value.length) return;
        selectedCourse.value = courses.value.find(item => item?.slug == selectedCourseSlug.value);
    })

    watch(
        ()=> route.query.slug,
        ()=> {
           selectedCourseSlug.value = route.query.slug;
           selectedCourse.value = courses.value.find(course => course.slug = selectedCourseSlug.value);           
        },
    )
</script>

<template>
    <div 
        v-if="selectedCourseSlug"
        class="p-6  mt-24 pb-16 rounded-lg bg-slate-50 " >
        <div class="grid w-[90%]  mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
             <div class="">
                <div class="text-left">
                    <h1 class="text-4xl text-gray-900 font-bold">{{ selectedCourse?.course_name }}</h1>
                    <div class="flex my-2 gap-4">
                        <div>
                            <img 
                                src="/images/course-1.jpg" 
                                :alt="selectedCourse?.user.first_name"
                                class="w-12 h-12 object-cover mt-4 rounded-full">
                        </div>
                        <div class="text-lg  text-gray-700 mt-2">
                            <p> A course by</p>
                            <p class="font-bold">{{ selectedCourse?.user.first_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden max-w-full h-auto rounded-t-lg mt-6 relative cursor-pointer" >
                    <img
                        src="/images/course-1.jpg"
                        alt="Course Image"
                        class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl" />
                    <div
                        class="absolute inset-0 flex items-center justify-center" >
                        <div
                            class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center" >
                            <i class="fas fa-play-circle text-white text-6xl" ></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 py-8 rounded-b-lg ">
                    <div class="text-left mb-6">
                        <div class="flex items-left justify-between">
                            <h2 class="text-3xl text-slate-600 font-semibold"> Course Overview </h2>
                        </div>
                        <p class="py-4 text-lg leading-9">{{ selectedCourse?.overview }}</p>
                    <div class="flex items-left justify-between">
                            <h2 class="text-3xl text-slate-600 font-semibold"> What you will learn</h2>
                        </div>
                    </div>
                    <div
                        v-for="(courseModule, courseModuleIndex) in selectedCourse?.courseModules"
                        :key="courseModuleIndex"
                        class="mb-3 px-2" >
                        <div class="flex justify-between items-center">
                            <button
                                @click="toggleModuleLesson(courseModule.id)"
                                class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300" >
                                <span class="font-semibold text-lg">{{
                                    courseModule.title
                                }}</span>
                                <i
                                    :class="[
                                        { 'rotate-90': collapsModuleId == courseModule.id },
                                    ]"
                                    class="fa-solid fa-angle-right text-xl transition-transform duration-300" > </i>
                            </button>
                        </div>

                        <div v-if="collapsModuleId == courseModule.id" class="ml-4 mt-2">
                            <ul class="list-none pl-0">
                                <li
                                    v-for="( courseContent, courseContentIndex ) in courseModule.courseContents"
                                    :key="courseContentIndex"
                                    class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1" >
                                    <i 
                                        :class="{
                                            'fa-circle-play':courseContent.content_type == 1,
                                            'fa-file-lines':courseContent.content_type == 2,
                                            'fa-image':courseContent.content_type == 3,
                                        }"
                                        class="fa-solid px-8 text-lg w-5 h-5"></i>
                                    {{ courseContent.title }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Course Details -->
            <div class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-0 h-fit justify-center" >
                <button
                    @click="enrollCourse(selectedCourse)"
                    class="bg-lime-600 text-white px-6 py-2 rounded-lg mb-4 hover:bg-lime-700 transition-colors w-full" >
                    Enroll Now
                </button>
                <h2 class="text-xl leading-9 font-semibold mb-4">Course Details</h2>
                <div class="flex flex-col gap-4">
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                        <strong>Level:</strong> {{ selectedCourse?.skill_level }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-language text-green-500 mr-2"></i>
                        <strong>Language:</strong> {{ selectedCourse?.language }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        <strong>Duration:</strong> {{ selectedCourse?.credit_hour }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-tasks text-red-500 mr-2"></i>
                        <strong>Activities:</strong> 30
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-tv text-purple-500 mr-2"></i>
                        <strong>Access on:</strong> Mobile, Desktop, and TV
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-users text-indigo-500 mr-2"></i>
                        <strong>Lifetime access to the community</strong>
                    </div>
                    <a href="#" class="text-blue-500 hover:underline leading-relaxed text-lg py-2">
                        <i class="fas fa-certificate text-teal-500 mr-2"></i>
                        <strong>Certificate of completion</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-green-500:hover,
.bg-blue-500:hover,
.bg-gray-500:hover {
    transition: background-color 0.3s ease;
}

.bg-green-500 {
    background-color: #38a169;
}

.bg-blue-500 {
    background-color: #3182ce;
}

.bg-gray-500 {
    background-color: #6b7280;
}
@keyframes breathe {
    0% {
        transform: scale(1);
        color: #ffffff;
        opacity: 1;
        box-shadow: 0 0 15px rgba(132, 204, 22, 0.6); /* lime-500 shadow */
    }
    50% {
        transform: scale(1.1);
        color: #ffffff;
        opacity: 1;
        box-shadow: 0 0 25px rgba(101, 163, 13, 0.7); /* lime-600 shadow */
    }
    100% {
        transform: scale(1);
        color: #ffffff;
        opacity: 0.4;
        box-shadow: 0 0 35px rgba(77, 124, 15, 0.4); /* lime-700 shadow */
    }
}

.animate-breathe {
    animation: breathe 2s infinite;
}
</style>
