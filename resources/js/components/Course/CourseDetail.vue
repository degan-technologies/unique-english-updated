<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { UseStudentStore } from "@/store/UseStudentStore";
import Certificate from "@/components/Course/certificate.vue";

const studentStore = UseStudentStore();
const { videoPlayerTab, courses, selectedCourseSlug } = storeToRefs(studentStore);
const route = useRoute();
const router = useRouter();

const checkoutUrl = ref(null);
const selectedCourse = ref(null);
const collapsModuleId = ref(null);
const showCertificateModal = ref(false); // controls modal display

// Ref for certificate instance (for mobile download)
const certificateComponentRef = ref(null);

selectedCourseSlug.value = route.query.slug;

function toggleModuleLesson(id) {
  if (collapsModuleId.value === id) {
    collapsModuleId.value = null;
  } else {
    collapsModuleId.value = id;
  }
}

function enrollCourse(item) {
  const selectedItem = [{ type: "course", slug: item.slug }];
  Axios.post("/api/initiate-payment", { cartItems: selectedItem })
    .then((res) => {
      checkoutUrl.value = res.data.checkout_url;
      window.open(checkoutUrl.value, "_blank");
    })
    .catch((error) => {
      console.error("Payment initiation error:", error);
    });
}

function changeTabTemporaryFunction(slug) {
  router.push({
    name: "student",
    query: { tab: videoPlayerTab.value, slug: slug },
  });
  selectedCourseSlug.value = slug;
}

async function handleCertificateClick() {
  try {
    // Call the new API endpoint to get certificate status.
    const response = await Axios.get(`/api/courses/${selectedCourse.value.id}/certificate-status`);
    const { certificate_active, allContentsCompleted, allQuizzesCompleted } = response.data;

    if (!certificate_active) {
      // You might want to notify the user with a message.
      alert("Certificate is not available yet. Please complete all course content and quizzes.");
      return;
    }

    if (window.innerWidth < 768) {
      if (certificateComponentRef.value && certificateComponentRef.value.downloadCertificate) {
        certificateComponentRef.value.downloadCertificate();
      }
    } else {
      // Desktop view:
      showCertificateModal.value = true;
    }
  } catch (error) {
    console.error("Error checking certificate status:", error);
  }
}

onMounted(async () => {
  await studentStore.fetchCourses();
  selectedCourse.value = courses.value.find(
    (item) => item.slug === selectedCourseSlug.value
  );
});

    watch(
        () => route.query.slug,
        () => {
            selectedCourseSlug.value = route.query.slug;
            selectedCourse.value = courses.value.find(
                (course) => course.slug === selectedCourseSlug.value
            );
        }
    ); 
</script>

<template>
  <div v-if="selectedCourseSlug" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
    <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
      <!-- Left Section: Course Content -->
      <div>
        <div class="text-left">
          <h1 class="text-4xl text-gray-900 font-bold">{{ selectedCourse?.course_name }}</h1>
          <div class="flex my-2 gap-4">
            <div>
              <img src="/images/course-1.jpg" :alt="selectedCourse?.user.first_name" class="w-12 h-12 object-cover mt-4 rounded-full"/>
            </div>
            <div class="text-lg text-gray-700 mt-2">
              <p>A course by</p>
              <p class="font-bold">{{ selectedCourse?.user.first_name }}</p>
            </div>
          </div>
        </div>
        <div class="overflow-hidden max-w-full h-auto rounded-t-lg mt-6 relative cursor-pointer">
          <img src="/images/course-1.jpg" alt="Course Image" class="w-full h-auto rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl"/>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
              <i class="fas fa-play-circle text-white text-6xl"></i>
            </div>
          </div>
        </div>
        <div class="bg-white p-4 py-8 rounded-b-lg">
          <div class="text-left mb-6">
            <div class="flex items-left justify-between">
              <h2 class="text-3xl text-slate-600 font-semibold">Course Overview</h2>
            </div>
            <p class="py-4 text-lg leading-9">{{ selectedCourse?.overview }}</p>
            <div class="flex items-left justify-between">
              <h2 class="text-3xl text-slate-600 font-semibold">What you will learn</h2>
            </div>
          </div>
          <div
            v-for="(courseModule, courseModuleIndex) in selectedCourse?.courseModules"
            :key="courseModuleIndex"
            class="mb-3 px-2"
          >
            <div class="flex justify-between items-center">
              <button
                @click="toggleModuleLesson(courseModule.id)"
                class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300"
              >
                <span class="font-semibold text-lg">{{ courseModule.title }}</span>
                <i
                  :class="{'rotate-90': collapsModuleId === courseModule.id}"
                  class="fa-solid fa-angle-right text-xl transition-transform duration-300"
                ></i>
              </button>
            </div>
            <div v-if="collapsModuleId === courseModule.id" class="ml-4 mt-2">
              <ul class="list-none pl-0">
                <li
                  v-for="(courseContent, courseContentIndex) in courseModule.courseContents"
                  :key="courseContentIndex"
                  class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1"
                >
                  <i
                    :class="{
                      'fa-circle-play': courseContent.content_type === 1,
                      'fa-file-lines': courseContent.content_type === 2,
                      'fa-image': courseContent.content_type === 3
                    }"
                    class="fa-solid px-8 text-lg w-5 h-5"
                  ></i>
                  {{ courseContent.title }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Section: Course Details & Certificate Link -->
      <div class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-0 h-fit justify-center">
        <button
          @click="changeTabTemporaryFunction(selectedCourse.slug)"
          class="bg-lime-600 text-white px-6 py-2 rounded-lg mb-4 hover:bg-lime-700 transition-colors w-full"
        >
          continue
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
          <!-- Certificate link -->
          <a
            href="#"
            @click.prevent="handleCertificateClick"
            class="text-blue-500 hover:underline leading-relaxed text-lg py-2"
          >
            <i class="fas fa-certificate text-teal-500 mr-2"></i>
            <strong>Certificate of completion</strong>
          </a>
        </div>
      </div>
    </div>

   <!-- Off-screen Certificate instance for mobile download (rendered but off-screen) -->
<div style="position: absolute; top: -10000px; left: -10000px;">
  <Certificate ref="certificateComponentRef" :courseName="selectedCourse?.course_name" />
</div>


    <!-- Modal for desktop certificate display -->
 <!-- Modal for desktop certificate display -->
<div
  v-if="showCertificateModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
>
  <div class="bg-white p-4 relative">
    <button class="absolute top-0 right-0 m-2" @click="showCertificateModal = false">
      Close
    </button>
    <!-- Pass the course name as a prop to Certificate -->
    <Certificate :showDownload="true" :courseName="selectedCourse?.course_name" />
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
@keyframes pulse-circle {
  0% {
    transform: scale(1);
    opacity: 0.7;
    box-shadow: 0 0 5px rgba(132, 204, 22, 0.6);
  }
  50% {
    transform: scale(1.6);
    opacity: 0.4;
    box-shadow: 0 0 20px rgba(91, 150, 9, 0.8);
  }
  100% {
    transform: scale(2);
    opacity: 0;
    box-shadow: 0 0 30px rgba(72, 118, 12, 0.5);
  }
}
.animate-pulse-circle {
  width: 70px;
  height: 70px;
  background-color: rgba(132, 204, 22, 0.5);
  border-radius: 50%;
  animation: pulse-circle 2s infinite ease-out;
  position: absolute;
}
</style>
