<script setup>
import Axios from "axios";
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { UseStudentStore } from "@/store/UseStudentStore";

const studentStore = UseStudentStore();
const { bookOverviewTab, videoPlayerTab   } = storeToRefs(studentStore);

const route = useRoute();

const props = defineProps({
    feedBacks: Object,
    averageRating: String,
    starDistribution: Object,
    showOnly: String,
});

const newComment = ref("");
const commentError = ref("");
const feedbackType = ref(null);

const userRating = ref(0);
const userHoverRating = ref(0);
const userRatingError = ref(""); 

const reportModalOpen = ref(false);
const currentReportFeedback = ref(null);
const reportIssueType = ref("");
const reportIssueDetails = ref("");

function getfeedbackTypes(){
    if(route.query.tab == videoPlayerTab.value) {
      return  feedbackType.value = "course";
    } 

    if(route.query.tab == bookOverviewTab.value) {
       return feedbackType.value = "book";
    }

    return;
}
 
const handleHover = (event, starIndex) => {
    const { offsetX, currentTarget } = event;
    const halfWidth = currentTarget.offsetWidth / 2;
    userHoverRating.value = offsetX < halfWidth ? starIndex - 0.5 : starIndex;
};

const setUserRating = (event, starIndex) => {
    const { offsetX, currentTarget } = event;
    const halfWidth = currentTarget.offsetWidth / 2;
    userRating.value = offsetX < halfWidth ? starIndex - 0.5 : starIndex;
    userRatingError.value = "";
};

const userHoverRatingOrValue = computed(() => {
    return userHoverRating.value || userRating.value;
});

const isStarFull = (star, rating) => star <= Math.floor(rating);
const isStarHalf = (star, rating) => rating === star - 0.5;
 
function addComment() {
    getfeedbackTypes();
    const payload = {
        rate: userRating.value, 
        comment: newComment.value,
        slug:route.query.slug,
        feedbackType: feedbackType.value
     };

    Axios
        .post("/api/feedbacks", payload )
        .then(res => { 
            comments.value = res.data.data;
            newComment.value = "";
            commentError.value = "";
            userRating.value = 0;
            userHoverRating.value = 0;
        })
};

function likeComment(comment, interact) {
    Axios
        .post(`/api/feedbacks/favorite/${comment.id}`, {
            action: interact,
        })
        .then((response) => {
            props.feedBacks = props.feedBacks.map(item => {
                if (item.id == comment.id) {
                    item.likes = response.data.like;
                    item.dislikes = response.data.dislike;
                }
            });
        })
};

const reportComment = (index) => {
    const feedback = props.feedBacks.value[index];
    currentReportFeedback.value = feedback;
    reportIssueType.value = "";
    reportIssueDetails.value = "";
    reportModalOpen.value = true;
};

function submitReport() {
    if (!reportIssueType.value.trim() || reportIssueDetails.value.trim().length < 10) {
        alert("Please fill in a valid issue type and details (min 10 characters).");
        return;
    }
   const payload = {
            issue_type: reportIssueType.value,
            issue_details: reportIssueDetails.value,
        };

        Axios
            .post( `/api/feedbacks/${currentReportFeedback.value.id}/report`, payload )
            .then(res=>{
                reportModalOpen.value = false;
            })
};

const cancelReport = () => {
    reportModalOpen.value = false;
}; 

const getInitials = (name) => {
    if (!name) return "";
    return name.charAt(0).toUpperCase();
};

onMounted(()=>{
    getfeedbackTypes();
})
</script>

<template>
    <div class="w-full bg-white p-4 md:p-8 space-y-8"> 
        <div class="bg-white ">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                Student Feedback
            </h2>
            <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
                <!-- Average Rating -->
                <div class="flex flex-col items-center text-center space-y-2 md:w-1/3">
                    <div class="text-5xl font-extrabold text-yellow-500">
                        {{ averageRating }}
                    </div>
                    <div>
                        <p class="text-gray-700 text-lg font-semibold">Course Rating</p>
                        <p class="text-gray-500 text-sm">
                            Based on {{ feedBacks?.length }} review<span v-if="feedBacks?.length !== 1">s</span>
                        </p>
                    </div>
                </div>
                <!-- Star Distribution -->
                <div class="flex-1 space-y-2">
                    <div v-for="(count, index) in starDistribution" :key="index" class="flex items-center space-x-2">
                        <!-- star label (5 -> 1) -->
                        <span class="font-medium text-gray-600">{{ 5 - index }}</span>
                        <i class="fas fa-star text-yellow-400"></i>
                        <!-- progress bar -->
                        <div class="w-full bg-gray-300 h-2 rounded-full overflow-hidden">
                            <div class="bg-yellow-400 h-full transition-all duration-300"
                                :style="{ width: ((count / feedBacks.length) * 100).toFixed(0) + '%' }"></div>
                        </div>
                        <!-- count of feedback for that star -->
                        <span class="text-sm text-gray-600">({{ count }})</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!showOnly" class="bg-white space-y-4">
            <h2 class="text-xl font-bold text-gray-800">
                Your Rating
            </h2>
            <div class="flex items-center justify-center gap-2">
                <div v-for="star in 5" :key="star" class="relative text-3xl cursor-pointer"
                    style="width: 1.5em; height: 1.5em" @mousemove="handleHover($event, star)"
                    @mouseleave="userHoverRating = 0" @click="setUserRating($event, star)">
                    <!-- Empty Star -->
                    <i class="fas fa-star absolute top-0 left-0 text-gray-300"></i>
                    <!-- Filled Star with possible half clip -->
                    <i class="fas fa-star absolute top-0 left-0 text-yellow-400 transition-all duration-200" :style="{
                        clipPath: isStarHalf(star, userHoverRatingOrValue)
                            ? 'inset(0 50% 0 0)'
                            : isStarFull(star, userHoverRatingOrValue)
                                ? 'inset(0)'
                                : 'inset(0 100% 0 0)',
                    }"></i>
                </div>
            </div>
            <p class="mt-2 text-center text-gray-600">
                You selected: <span class="font-semibold">{{ userRating }}</span> / 5
            </p>
            <p v-if="userRatingError" class="text-red-500 text-sm text-center">
                {{ userRatingError }}
            </p>

            <!-- Add New Review -->
            <div class="flex mt-6 flex-wrap gap-2">
                <input v-model="newComment" type="text" placeholder="Write a review..."
                    class="flex-grow p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 w-full md:w-auto" />
                <button @click="addComment"
                    class="bg-lime-500 text-white px-6 py-3 rounded-lg hover:bg-lime-600 transition w-full md:w-auto">
                    Post
                </button>
            </div>
            <p v-if="commentError" class="text-red-500 text-sm mt-2">
                {{ commentError }}
            </p>
        </div>

        <!-- REVIEWS SECTION (each with possible half-star rating) -->
        <div class="bg-white space-y-6">
            <h2 class="text-xl font-bold text-gray-800">Reviews</h2>
            <div v-for="(comment, index) in feedBacks" :key="comment.id"
                class="border-b pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
                <div class="flex items-center space-x-3">
                    <!-- Avatar/Initial -->
                    <div
                        class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-sm font-bold text-gray-700">
                        {{ getInitials(comment.user.first_name) }}
                    </div>
                    <!-- Name, star rating, time -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2">
                        <span class="font-semibold text-gray-800">{{ comment.user.first_name }}</span>
                        <div class="flex items-center ml-1">
                            <div v-for="star in 5" :key="star" class="relative inline-block text-sm mr-1"
                                style="width: 1em; height: 1em;">
                                <i class="fas fa-star absolute top-0 left-0 text-gray-300"></i>
                                <i class="fas fa-star absolute top-0 left-0 text-yellow-400" :style="{
                                    clipPath: isStarHalf(star, comment.rating)
                                        ? 'inset(0 50% 0 0)'
                                        : isStarFull(star, comment.rating)
                                            ? 'inset(0)'
                                            : 'inset(0 100% 0 0)',
                                }"></i>
                            </div>
                        </div>
                        <span class="text-gray-500 text-sm">{{ comment.timestamp }}</span>
                    </div>
                </div>
                <p class="text-gray-700 mt-2 ml-12">{{ comment.comment }}</p>
                <div class="flex items-center space-x-4 mt-2 ml-12">
                    <span class="text-sm text-gray-500">Was this review helpful?</span>
                    <!-- Like -->
                    <button @click="likeComment(comment, 'liked')"
                        class="flex items-center space-x-1 text-gray-600 hover:text-green-500 transition text-sm">
                        <i class="fas fa-thumbs-up"></i>
                        <span>{{ comment.likes || 0 }}</span>
                    </button>
                    <!-- Dislike -->
                    <button @click="likeComment(comment, 'disliked')"
                        class="flex items-center space-x-1 text-gray-600 hover:text-red-500 transition text-sm">
                        <i class="fas fa-thumbs-down"></i>
                        <span>{{ comment.dislikes || 0 }}</span>
                    </button>
                    <!-- Report -->
                    <button @click="reportComment(index)"
                        class="text-sm text-gray-600 hover:underline hover:text-blue-600">
                        Report
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- REPORT MODAL -->
    <div v-if="reportModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg p-6 w-11/12 max-w-md">
            <h3 class="text-xl font-bold mb-4">Report Feedback</h3>
            <div class="mb-4">
                <label class="block text-gray-700">Issue Type</label>
                <input type="text" v-model="reportIssueType" class="mt-1 w-full border border-gray-300 rounded-lg p-2"
                    placeholder="e.g., Harassment" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Issue Details</label>
                <textarea v-model="reportIssueDetails" class="mt-1 w-full border border-gray-300 rounded-lg p-2"
                    rows="4" placeholder="Enter details (min 10 characters)"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button @click="cancelReport" class="px-4 py-2 bg-gray-300 rounded-lg">
                    Cancel
                </button>
                <button @click="submitReport" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                    Submit
                </button>
            </div>
        </div>
    </div>
</template>


<style scoped>
/* Additional custom styles if needed */
</style>