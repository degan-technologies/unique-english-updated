<template>
    <div class="w-full bg-white shadow-lg rounded-lg p-6 space-y-8">
        <!-- Course Rating Section -->
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800">
                Rate This Course
            </h2>
            <div class="flex items-center justify-center gap-2">
                <!-- Five Star Rating -->
                <div
                    v-for="star in 5"
                    :key="star"
                    class="relative text-3xl cursor-pointer"
                    style="width: 1.5em; height: 1.5em"
                    @mousemove="handleHover($event, star)"
                    @mouseleave="hoverRating = 0"
                    @click="setRating($event, star)"
                >
                    <!-- Empty Star -->
                    <i
                        class="fas fa-star absolute top-0 left-0 text-gray-300"
                    ></i>

                    <!-- Half or Full Fill -->
                    <i
                        class="fas fa-star absolute top-0 left-0 text-yellow-400"
                        :style="{
                            clipPath: isStarHalf(star)
                                ? 'inset(0 50% 0 0)'
                                : isStarFull(star)
                                ? 'inset(0)'
                                : 'inset(0 100% 0 0)',
                        }"
                    ></i>
                </div>
            </div>
            <p class="mt-2 text-center text-gray-600">
                Your Rating: <span class="font-semibold">{{ rating }}</span> / 5
            </p>
        </div>

        <!-- Comment Section -->
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800">Comments</h2>
            <div class="space-y-6">
                <!-- Display Comments -->
                <div
                    v-for="(comment, index) in visibleComments"
                    :key="index"
                    class="bg-gray-100 p-4 rounded-lg flex space-x-4 items-center"
                >
                    <!-- User Icon -->
                    <div class="text-gray-500">
                        <i class="fas fa-user-circle text-3xl"></i>
                    </div>
                    <!-- Comment Content -->
                    <div class="flex-grow">
                        <p class="text-gray-800">{{ comment.text }}</p>
                    </div>
                    <!-- Like and Dislike Buttons -->
                    <div class="flex items-center space-x-4">
                        <button
                            @click="likeComment(index)"
                            class="text-green-500 hover:text-green-700 transition text-lg"
                        >
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                        <span class="text-gray-600">{{ comment.likes }}</span>
                        <button
                            @click="dislikeComment(index)"
                            class="text-red-500 hover:text-red-700 transition text-lg"
                        >
                            <i class="fas fa-thumbs-down"></i>
                        </button>
                        <span class="text-gray-600">{{
                            comment.dislikes
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- See More / Show Less -->
            <div v-if="comments.length > maxVisibleComments" class="mt-4">
                <button
                    @click="toggleShowMore"
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition"
                >
                    {{ showAll ? "Show Less" : "See More" }}
                </button>
            </div>

            <!-- Add New Comment -->
            <div class="flex mt-6 flex-wrap gap-2">
                <input
                    v-model="newComment"
                    type="text"
                    placeholder="Write a comment..."
                    class="flex-grow p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 w-full md:w-auto"
                />
                <button
                    @click="addComment"
                    class="bg-lime-500 text-white px-6 py-3 rounded-lg hover:bg-lime-600 transition w-full md:w-auto"
                >
                    Post
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Rating Data
const rating = ref(0);
const hoverRating = ref(0);

// Set Rating
const setRating = (event, star) => {
    const clickX = event.offsetX;
    const width = event.target.offsetWidth;
    const isHalf = clickX < width / 2; // Check if clicked on the left (half)
    rating.value = isHalf ? star - 0.5 : star;
};

// Handle Hover
const handleHover = (event, star) => {
    const hoverX = event.offsetX;
    const width = event.target.offsetWidth;
    const isHalf = hoverX < width / 2; // Check hover position
    hoverRating.value = isHalf ? star - 0.5 : star;
};

// Determine if Star is Full
const isStarFull = (star) => {
    return star <= Math.floor(hoverRating.value || rating.value);
};

// Determine if Star is Half
const isStarHalf = (star) => {
    return hoverRating.value
        ? star - 0.5 === hoverRating.value
        : star - 0.5 === rating.value;
};

// Comment Data
const comments = ref([]);
const newComment = ref("");

// Max Comments to Show Initially
const maxVisibleComments = 1;
const showAll = ref(false);

// Computed Property for Visible Comments
const visibleComments = computed(() => {
    return showAll.value
        ? comments.value
        : comments.value.slice(0, maxVisibleComments);
});

// Add New Comment
const addComment = () => {
    if (newComment.value.trim() !== "") {
        comments.value.push({ text: newComment.value, likes: 0, dislikes: 0 });
        newComment.value = "";
    }
};

// Like a Comment
const likeComment = (index) => {
    comments.value[index].likes += 1;
};

// Dislike a Comment
const dislikeComment = (index) => {
    comments.value[index].dislikes += 1;
};

// Toggle "See More" and "Show Less"
const toggleShowMore = () => {
    showAll.value = !showAll.value;
};
</script>
