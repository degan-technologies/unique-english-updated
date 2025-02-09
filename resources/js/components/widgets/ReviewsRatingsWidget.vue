<template>
  <div class="bg-white rounded-lg shadow p-4 w-full mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-2">
        <i class="fas fa-star text-lime-700"></i>
        <h3 class="text-lg font-bold">Recent Reviews & Ratings</h3>
      </div>
      <button @click="fetchReviews" class="text-gray-500 hover:text-gray-800">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>

    <!-- Rating Filter Buttons -->
    <div class="flex flex-wrap gap-2 mb-4">
      <button 
        v-for="star in [5, 4, 3, 2, 1]" 
        :key="star"
        @click="filterByRating(star)"
        :class="['px-3 py-1 rounded text-sm font-medium transition-all', selectedRating === star ? 'bg-lime-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300']"
      >
        {{ star }} ★
      </button>
    </div>

    <!-- Reviews List -->
    <div class="space-y-3 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400">
      <transition-group name="fade" tag="div">
        <div 
          v-for="review in filteredReviews" 
          :key="review.id"
          class="p-4 border border-gray-200 rounded-lg hover:bg-gray-100 cursor-pointer transition-all"
          @click="openReviewDetails(review)">
          <div class="flex justify-between items-center">
            <h4 class="text-md font-semibold">{{ review.name }}</h4>
            <span class="text-sm text-gray-500">{{ formatTime(review.timestamp) }}</span>
          </div>
          <div class="flex items-center mt-1 space-x-1">
            <span v-for="star in review.rating" :key="star" class="text-lime-700">★</span>
            <span v-for="star in 5 - review.rating" :key="'empty' + star" class="text-gray-300">★</span>
          </div>
          <p class="text-sm text-gray-600 truncate">{{ review.comment }}</p>
        </div>
      </transition-group>
    </div>

    <!-- No Reviews Placeholder -->
    <div v-if="filteredReviews.length === 0" class="text-gray-500 text-center py-6">
      No reviews available.
    </div>

    <!-- Rating Distribution Chart -->
    <div class="mt-6">
      <h4 class="text-sm font-semibold mb-2">Rating Distribution</h4>
      <div class="flex items-end space-x-2">
        <div 
          v-for="star in [5, 4, 3, 2, 1]" 
          :key="'bar' + star" 
          class="relative w-10 bg-gray-200 rounded-lg overflow-hidden h-24 flex flex-col justify-end">
          <div class="bg-lime-700 transition-all duration-500" :style="{ height: getRatingPercentage(star) + '%' }"></div>
          <span class="absolute bottom-[-20px] text-xs text-gray-600">{{ star }}★</span>
        </div>
      </div>
    </div>

    <!-- Review Details Modal -->
    <Teleport to="body">
      <div v-if="selectedReview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center px-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
          <div class="flex justify-between items-center mb-3">
            <h4 class="text-lg font-semibold">{{ selectedReview.name }}</h4>
            <button @click="selectedReview = null" class="text-gray-500 hover:text-gray-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="flex items-center space-x-1">
            <span v-for="star in selectedReview.rating" :key="'full' + star" class="text-lime-700">★</span>
            <span v-for="star in 5 - selectedReview.rating" :key="'empty' + star" class="text-gray-300">★</span>
          </div>
          <p class="mt-2">{{ selectedReview.comment }}</p>
          <div class="text-right mt-4">
            <button class="px-3 py-1 text-sm bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400" 
                    @click="selectedReview = null">
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// Sample Data (Replace with API Call)
const reviews = ref([
  { id: 1, name: "Alice Johnson", rating: 5, comment: "Fantastic course! Highly recommend!", timestamp: "2024-02-01T14:00:00Z" },
  { id: 2, name: "Bob Smith", rating: 4, comment: "Very informative but a bit fast-paced.", timestamp: "2024-01-31T16:30:00Z" },
  { id: 3, name: "Charlie Adams", rating: 3, comment: "Average experience, needs more examples.", timestamp: "2024-01-30T12:15:00Z" }
]);

const selectedRating = ref(null);
const selectedReview = ref(null);

// Fetch Reviews (Simulated API Call)
const fetchReviews = () => {
  // Simulate data refresh (Replace with API call)
  reviews.value.push({
    id: reviews.value.length + 1,
    name: "New User",
    rating: Math.floor(Math.random() * 5) + 1,
    comment: "Random review for testing.",
    timestamp: new Date().toISOString()
  });
};

// Filter Reviews by Rating
const filterByRating = (rating) => {
  selectedRating.value = selectedRating.value === rating ? null : rating;
};

// Filtered Reviews Computed Property
const filteredReviews = computed(() => {
  return selectedRating.value 
    ? reviews.value.filter(review => review.rating === selectedRating.value)
    : reviews.value;
});

// Get Rating Percentage for Chart
const getRatingPercentage = (star) => {
  const count = reviews.value.filter(review => review.rating === star).length;
  return (count / reviews.value.length) * 100 || 5; // Ensure a minimum height for visibility
};

// Open Review Details Modal
const openReviewDetails = (review) => {
  selectedReview.value = review;
};

// Format Time
const formatTime = (isoString) => {
  return new Date(isoString).toLocaleDateString();
};

// Auto-refresh every 15 seconds
onMounted(() => {
  setInterval(fetchReviews, 15000);
});
</script>

<style scoped>
/* Fade animation */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* Scrollbar styles */
.scrollbar-thin {
  scrollbar-width: thin;
}
.scrollbar-thumb-gray-400 {
  scrollbar-color: gray lightgray;
}
</style>
