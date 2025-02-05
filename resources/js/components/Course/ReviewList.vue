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

                    <!-- Filled Star -->
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
            <p v-if="ratingError" class="text-red-500 text-sm text-center">
                {{ ratingError }}
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
                    <div class="text-gray-500 flex items-center space-x-2">
                        <font-awesome-icon
                            icon="user-circle"
                            class="text-3xl"
                        />
                        <span class="text-gray-700 font-semibold">user123</span>
                    </div>

                    <!-- Editable Comment -->
                    <div class="flex-grow">
                        <p v-if="!comment.editing" class="text-gray-800">
                            {{ comment.text }}
                        </p>
                        <div v-else>
                            <input
                                v-model="comment.editedText"
                                class="w-full p-2 border rounded-lg"
                            />
                            <p
                                v-if="comment.editError"
                                class="text-red-500 text-sm"
                            >
                                {{ comment.editError }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-4">
                        <!-- Like & Dislike -->
                        <button
                            @click="likeComment(index)"
                            class="text-green-500 hover:text-green-700 transition text-lg"
                        >
                            <font-awesome-icon icon="thumbs-up" />
                        </button>
                        <span class="text-gray-600">{{ comment.likes }}</span>
                        <button
                            @click="dislikeComment(index)"
                            class="text-red-500 hover:text-red-700 transition text-lg"
                        >
                            <font-awesome-icon icon="thumbs-down" />
                        </button>
                        <span class="text-gray-600">{{
                            comment.dislikes
                        }}</span>

                        <!-- Edit & Delete -->
                        <button
                            v-if="!comment.editing"
                            @click="editComment(index)"
                            class="text-blue-500 hover:text-blue-700 transition text-lg"
                        >
                            <font-awesome-icon icon="edit" />
                        </button>
                        <button
                            v-if="comment.editing"
                            @click="saveEdit(index)"
                            class="text-green-500 hover:text-green-700 transition text-lg"
                        >
                            <font-awesome-icon icon="check-circle" />
                        </button>
                        <button
                            @click="deleteComment(index)"
                            class="text-red-500 hover:text-red-700 transition text-lg"
                        >
                            <font-awesome-icon icon="trash-alt" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- See More / Show Less -->
            <div v-if="comments.length > maxVisibleComments" class="mt-4">
                <button
                    @click="toggleShowMore"
                    class="bg-lime-500 text-white px-6 py-2 rounded-lg hover:bg-lime-600 transition"
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
            <p v-if="commentError" class="text-red-500 text-sm mt-2">
                {{ commentError }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faStar,
    faThumbsUp,
    faThumbsDown,
    faEdit,
    faTrashAlt,
    faCheckCircle,
    faUserCircle,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

// Add FontAwesome Icons
library.add(
    faStar,
    faThumbsUp,
    faThumbsDown,
    faEdit,
    faTrashAlt,
    faCheckCircle,
    faUserCircle
);

// Rating Data
const rating = ref(0);
const hoverRating = ref(0);
const ratingError = ref("");

// Set Rating
const setRating = (event, star) => {
    rating.value = star;
    ratingError.value = ""; // Clear error when user selects rating
};

// Handle Hover
const handleHover = (event, star) => {
    hoverRating.value = star;
};

// Determine if Star is Full
const isStarFull = (star) =>
    star <= Math.floor(hoverRating.value || rating.value);

// Determine if Star is Half
const isStarHalf = (star) =>
    hoverRating.value
        ? star - 0.5 === hoverRating.value
        : star - 0.5 === rating.value;

// Comment Data
const comments = ref([]);
const newComment = ref("");
const commentError = ref("");
const maxVisibleComments = 1;
const showAll = ref(false);

const visibleComments = computed(() =>
    showAll.value ? comments.value : comments.value.slice(0, maxVisibleComments)
);

// Add New Comment with Validation
const addComment = () => {
    if (newComment.value.trim().length < 3) {
        commentError.value = "Comment must be at least 3 characters.";
        return;
    }
    if (rating.value === 0) {
        ratingError.value = "Please give a rating before posting a comment.";
        return;
    }
    comments.value.push({
        text: newComment.value,
        editedText: newComment.value,
        likes: 0,
        dislikes: 0,
        editing: false,
        editError: "",
    });
    newComment.value = "";
    commentError.value = "";
};

// Edit & Delete Comments
const editComment = (index) => (comments.value[index].editing = true);
const saveEdit = (index) => {
    if (comments.value[index].editedText.trim().length < 3) {
        comments.value[index].editError =
            "Edited comment must be at least 3 characters.";
        return;
    }
    comments.value[index].text = comments.value[index].editedText;
    comments.value[index].editing = false;
    comments.value[index].editError = "";
};
const deleteComment = (index) =>
    confirm("Delete this comment?") && comments.value.splice(index, 1);

// Like/Dislike
const likeComment = (index) => comments.value[index].likes++;
const dislikeComment = (index) => comments.value[index].dislikes++;

// Toggle Show More
const toggleShowMore = () => (showAll.value = !showAll.value);
</script>
