<script setup>
import Axios from "axios";
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { UseStudentStore } from "@/store/UseStudentStore";

const emit = defineEmits(['feedback-added', 'feedback-updated']);

const studentStore = UseStudentStore();
const { bookOverviewTab, videoPlayerTab } = storeToRefs(studentStore);
const route = useRoute();

const props = defineProps({
    feedBacks: {
        type: Array,
        default: () => [],
    },
    averageRating: Number,
    starDistribution: Object,
    showOnly: Boolean,
    addFeedbackType: String,
});

// State
const newComment = ref("");
const commentError = ref("");
const feedbackType = ref(null);
const isLoading = ref(false);

// Rating state
const userRating = ref(0);
const userHoverRating = ref(0);
const userRatingError = ref("");

// Reviews display
const showAllReviews = ref(false);
const localFeedbacks = ref([...props.feedBacks]);

// Update localFeedbacks when props.feedBacks changes
watch(() => props.feedBacks, (newVal) => {
    localFeedbacks.value = [...newVal];
}, { immediate: true });

// Computed
const visibleFeedBacks = computed(() => {
    return showAllReviews.value ? localFeedbacks.value : localFeedbacks.value.slice(0, 2);
});

const shouldShowToggle = computed(() => {
    return localFeedbacks.value.length > 2;
});

const userHoverRatingOrValue = computed(() => {
    return userHoverRating.value || userRating.value;
});

// Methods
const getfeedbackTypes = () => {
    if (route.query.tab == videoPlayerTab.value) {
        feedbackType.value = "course";
    } else if (route.query.tab == bookOverviewTab.value) {
        feedbackType.value = "book";
    } else {
        feedbackType.value = props.addFeedbackType;
    }
};

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

const isStarFull = (star, rating) => star <= Math.floor(rating);
const isStarHalf = (star, rating) => rating === star - 0.5;

const addComment = async () => {
    // Clear previous errors
    commentError.value = "";
    userRatingError.value = "";

    // Validate
    if (!userRating.value) {
        userRatingError.value = "Please select a rating";
        return;
    }

    if (!newComment.value.trim()) {
        commentError.value = "Please write a review";
        return;
    }

    if (newComment.value.length > 500) {
        commentError.value = "Review must be less than 500 characters";
        return;
    }

    getfeedbackTypes();

    try {
        isLoading.value = true;
        const response = await Axios.post("/api/feedbacks", {
            rate: userRating.value,
            comment: newComment.value,
            slug: route.query.slug,
            feedbackType: feedbackType.value,
        });

        // Reset form
        newComment.value = "";
        userRating.value = 0;
        userHoverRating.value = 0;

        // Update local feedbacks and emit event
        localFeedbacks.value = [response.data.data, ...localFeedbacks.value];
        emit('feedback-added', response.data.data);

        // Show success message
        commentError.value = "Thank you for your feedback!";
        setTimeout(() => {
            commentError.value = "";
        }, 3000);

    } catch (error) {
        commentError.value = "Failed to submit feedback. Please try again.";
        setTimeout(() => {
            commentError.value = "";
        }, 3000);
    } finally {
        isLoading.value = false;
    }
};

const likeComment = async (comment, action) => {
    try {
        const response = await Axios.post(`/api/feedbacks/favorite/${comment.id}`, {
            action: action,
        });

        emit('feedback-updated', {
            id: comment.id,
            likes: response.data.like,
            dislikes: response.data.dislike
        });
    } catch (error) {
        console.error("Failed to update feedback reaction:", error);
    }
};

const getInitials = (name) => {
    return name?.charAt(0).toUpperCase() || "";
};

const toggleShowMore = () => {
    showAllReviews.value = !showAllReviews.value;
};

onMounted(() => {
    getfeedbackTypes();
});
</script>

<template>
    <div class="feedback-container">
        <!-- Rating Summary -->
        <div class="rating-summary">
            <h2 class="section-title">Student Feedback</h2>
            <div class="rating-content">
                <!-- Average Rating -->
                <div class="average-rating">
                    <div class="rating-value">{{ averageRating }}</div>
                    <div class="rating-meta">
                        <p class="rating-label">Course Rating</p>
                        <p class="rating-count">
                            Based on {{ localFeedbacks.length }} review{{ localFeedbacks.length !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>

                <!-- Star Distribution -->
                <div class="star-distribution">
                    <div v-for="(count, index) in starDistribution" :key="index" class="star-row">
                        <span class="star-label">{{ 5 - index }}</span>
                        <i class="fas fa-star star-icon"></i>
                        <div class="progress-bar">
                            <div class="progress-fill" :style="{ width: `${(count / localFeedbacks.length) * 100}%` }">
                            </div>
                        </div>
                        <span class="star-count">({{ count }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Rating Section -->
        <div v-if="!showOnly" class="user-rating-section">
            <h2 class="section-title">Your Rating</h2>

            <!-- Star Rating Input -->
            <div class="star-rating-input">
                <div v-for="star in 5" :key="star" class="star-container" @mousemove="handleHover($event, star)"
                    @mouseleave="userHoverRating = 0" @click="setUserRating($event, star)">
                    <i class="fas fa-star star-empty"></i>
                    <i class="fas fa-star star-filled" :style="{
                        clipPath: isStarHalf(star, userHoverRatingOrValue)
                            ? 'inset(0 50% 0 0)'
                            : isStarFull(star, userHoverRatingOrValue)
                                ? 'inset(0)'
                                : 'inset(0 100% 0 0)',
                    }"></i>
                </div>
            </div>

            <p class="rating-selection">
                You selected: <span class="selected-rating">{{ userRating }}</span> / 5
            </p>

            <!-- Review Form -->
            <div class="review-form">
                <textarea v-model="newComment" placeholder="Write a review..." class="review-input"
                    :disabled="isLoading" maxlength="500" rows="3"></textarea>
                <button @click="addComment" class="submit-button" :disabled="isLoading">
                    <span v-if="isLoading">Posting...</span>
                    <span v-else>Post</span>
                </button>
            </div>
            <p v-if="newComment.length > 0" class="text-right text-xs text-gray-500 mt-1">
                {{ newComment.length }}/500 characters
            </p>
        </div>

        <!-- Error Messages -->
        <div v-if="commentError || userRatingError" class="error-messages">
            <p v-if="userRatingError" class="error">{{ userRatingError }}</p>
            <p v-if="commentError" class="error" :class="{ 'text-green-500': commentError.includes('Thank you') }">
                {{ commentError }}
            </p>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="section-title">Reviews</h2>

            <template v-if="localFeedbacks.length > 0">
                <div v-for="feedback in visibleFeedBacks" :key="feedback.id" class="feedback-item">
                    <!-- User Info -->
                    <div class="user-info">
                        <div class="user-avatar">
                            {{ getInitials(feedback.user?.full_name) }}
                        </div>
                        <div class="user-meta">
                            <span class="user-name">{{ feedback.user?.full_name }}</span>
                            <div class="user-rating">
                                <div v-for="star in 5" :key="star" class="star-container small">
                                    <i class="fas fa-star star-empty"></i>
                                    <i class="fas fa-star star-filled" :style="{
                                        clipPath: isStarHalf(star, feedback.rating)
                                            ? 'inset(0 50% 0 0)'
                                            : isStarFull(star, feedback.rating)
                                                ? 'inset(0)'
                                                : 'inset(0 100% 0 0)',
                                    }"></i>
                                </div>
                            </div>
                            <span class="feedback-date">{{ feedback.timestamp }}</span>
                        </div>
                    </div>

                    <!-- Feedback Content -->
                    <p class="feedback-content">
                        {{ feedback.comment }}
                    </p>

                    <!-- Feedback Actions -->
                    <div class="feedback-actions">
                        <button @click="likeComment(feedback, 'liked')" class="action-button like"
                            :class="{ active: feedback.userLiked }">
                            <i class="fas fa-thumbs-up"></i>
                            <span>{{ feedback.likes || 0 }}</span>
                        </button>
                        <button @click="likeComment(feedback, 'disliked')" class="action-button dislike"
                            :class="{ active: feedback.userDisliked }">
                            <i class="fas fa-thumbs-down"></i>
                            <span>{{ feedback.dislikes || 0 }}</span>
                        </button>
                    </div>
                </div>

                <!-- Show More/Less Toggle -->
                <div v-if="shouldShowToggle" class="show-more-container">
                    <button @click="toggleShowMore" class="show-more-button">
                        {{ showAllReviews ? 'Show Less' : `Show More (${localFeedbacks.length - 2})` }}
                    </button>
                </div>
            </template>

            <div v-else class="no-reviews">
                No reviews yet. Be the first to review!
            </div>
        </div>
    </div>
</template>

<style scoped>
.feedback-container {
    @apply w-full bg-white p-4 md:p-8 space-y-8;
}

.error-messages {
    @apply space-y-2 mb-4;
}

.error {
    @apply text-red-500 text-sm;
}

.section-title {
    @apply text-lg md:text-xl font-bold text-gray-800 mb-4;
}

/* Rating Summary */
.rating-summary {
    @apply bg-white;
}

.rating-content {
    @apply flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6;
}

.average-rating {
    @apply flex flex-col items-center text-center space-y-2 md:w-1/3;
}

.rating-value {
    @apply text-xl md:text-5xl font-extrabold text-yellow-500;
}

.rating-meta {
    @apply space-y-1;
}

.rating-label {
    @apply text-gray-700 text-lg font-semibold;
}

.rating-count {
    @apply text-gray-500 text-sm;
}

.star-distribution {
    @apply flex-1 space-y-2 w-full;
}

.star-row {
    @apply flex items-center space-x-2;
}

.star-label {
    @apply font-medium text-gray-600 w-4;
}

.star-icon {
    @apply text-yellow-400;
}

.progress-bar {
    @apply w-full bg-gray-300 h-2 rounded-full overflow-hidden flex-1;
}

.progress-fill {
    @apply bg-yellow-400 h-full transition-all duration-300;
}

.star-count {
    @apply text-sm text-gray-600 w-8;
}

/* User Rating Section */
.user-rating-section {
    @apply bg-white space-y-4;
}

.star-rating-input {
    @apply flex items-center justify-center gap-2;
}

.star-container {
    @apply relative text-3xl cursor-pointer;
    width: 1.5em;
    height: 1.5em;
}

.star-empty {
    @apply absolute top-0 left-0 text-gray-300;
}

.star-filled {
    @apply absolute top-0 left-0 text-yellow-400 transition-all duration-200;
}

.rating-selection {
    @apply mt-2 text-center text-gray-600;
}

.selected-rating {
    @apply font-semibold;
}

.review-form {
    @apply flex flex-col md:flex-row mt-6 gap-2;
}

.review-input {
    @apply w-full p-3 border rounded-lg flex-grow focus:ring-2 focus:ring-blue-300 resize-none;
}

.submit-button {
    @apply bg-lime-500 text-white px-6 py-3 rounded-lg hover:bg-lime-600 transition w-full md:w-fit h-fit disabled:opacity-50;
}

/* Reviews Section */
.reviews-section {
    @apply bg-white space-y-6;
}

.feedback-item {
    @apply border-b pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0;
}

.user-info {
    @apply flex items-center space-x-3;
}

.user-avatar {
    @apply w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-sm font-bold text-gray-700;
}

.user-meta {
    @apply flex flex-col sm:flex-row sm:items-center sm:space-x-2;
}

.user-name {
    @apply font-semibold text-gray-800;
}

.user-rating {
    @apply flex items-center ml-1;
}

.star-container.small {
    @apply relative inline-block text-sm mr-1;
    width: 1em;
    height: 1em;
}

.feedback-date {
    @apply text-gray-500 text-sm;
}

.feedback-content {
    @apply text-gray-700 mt-2 ml-12;
}

.feedback-actions {
    @apply flex items-center space-x-4 mt-2 ml-12;
}

.action-button {
    @apply flex items-center space-x-1 text-gray-600 transition text-sm px-2 py-1 rounded;
}

.action-button:hover {
    @apply bg-gray-100;
}

.action-button.like {
    @apply hover:text-green-500;
}

.action-button.like.active {
    @apply text-green-500;
}

.action-button.dislike {
    @apply hover:text-red-500;
}

.action-button.dislike.active {
    @apply text-red-500;
}

.no-reviews {
    @apply text-gray-500 text-center py-4;
}

.show-more-container {
    @apply text-center mt-4;
}

.show-more-button {
    @apply text-lime-600 font-medium hover:underline px-4 py-2 rounded-lg hover:bg-lime-50 transition;
}
</style>