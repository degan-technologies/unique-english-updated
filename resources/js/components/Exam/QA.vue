<script setup>
import { ref, computed, onMounted } from "vue";
import Axios from "axios";
import { useAppStore } from "@/store/useAppStore";

// Get the authenticated user from the store
const appStore = useAppStore();
const authUser = appStore.authUser; // { id, full_name, ... }

// Props: The parent passes in the selected course.
const props = defineProps({
  selectedCourse: {
    type: Object,
    required: true,
  },
});

// State for Q&A Section
const questions = ref([]);
const newQuestion = ref("");
const questionsToShow = ref(2);
const questionError = ref("");

// ------------------------------
// API Integration: Fetch Questions & Their Answers
// ------------------------------
const fetchQuestions = async () => {
  try {
    const response = await Axios.get("/api/QASection", {
      params: { course_id: props.selectedCourse.id },
    });

    const questionsData = response.data.data.filter(item => !item.parent_id);
    console.log("Fetched Questions Data:", questionsData);

    await Promise.all(
      questionsData.map(async (question) => {
        const answersResponse = await Axios.get("/api/answers", {
          params: { question_id: question.id },
        });

        // Ensure answers is always an array, even if empty or missing
        question.answers = Array.isArray(answersResponse.data.data) ? answersResponse.data.data : [];
        console.log(`Fetched Answers for Question ID ${question.id}:`, question.answers);
      })
    );

    questions.value = questionsData;
    console.log("Updated Questions with Answers:", questions.value);
  } catch (error) {
    console.error("Error fetching questions and answers:", error);
  }
};








// ------------------------------
// API Integration: Add a New Question
// ------------------------------
const addQuestion = async () => {
  if (newQuestion.value.trim() === "") {
    questionError.value = "Question cannot be empty.";
    return;
  }
  try {
    const payload = {
      question: newQuestion.value.trim(),
      user_id: authUser.id,
      course_id: props.selectedCourse.id, // Use selected course's ID
      // No parent_id for a top-level question.
    };
    const response = await Axios.post("/api/QASection", payload);
    questions.value.push(response.data.data);
    newQuestion.value = "";
    questionError.value = "";
  } catch (error) {
    console.error("Error adding question:", error);
    questionError.value = "Error submitting question.";
  }
};

// ------------------------------
// API Integration: Add a Reply (Answer) to a Question
// ------------------------------
const addAnswer = async (index) => {
  const qa = questions.value[index];
  if (qa.newAnswer.trim() === "") {
    qa.answerError = "Answer cannot be empty.";
    return;
  }
  try {
    const payload = {
      answer: qa.newAnswer.trim(),  // The answer text
      question_id: qa.id,             // Link the answer to the question
      course_id: props.selectedCourse.id, // Associate with the course
      user_id: authUser.id,           // The current user
    };
    // Post to the separate Answers endpoint
    const response = await Axios.post("/api/answers", payload);
    // Append the newly created answer to the question's answers array
    if (!qa.answers) {
      qa.answers = [];
    }
    qa.answers.push(response.data.data);
    qa.newAnswer = "";
    qa.showReplyField = false;
    qa.answerError = "";
  } catch (error) {
    console.error("Error adding answer:", error);
    qa.answerError = "Error submitting answer.";
  }
};

// ------------------------------
// Editing & Deleting: Questions
// ------------------------------
const editQuestion = (index) => {
  if (questions.value[index].user && questions.value[index].user.id === authUser.id) {
    questions.value[index].isEditing = true;
    questions.value[index].editedQuestion = questions.value[index].question;
  }
};

const saveQuestion = async (index) => {
  if (questions.value[index].editedQuestion.trim() !== "") {
    try {
      const payload = { question: questions.value[index].editedQuestion.trim() };
      const response = await Axios.put(`/api/QASection/${questions.value[index].id}`, payload);
      questions.value[index].question = response.data.data.question;
      questions.value[index].isEditing = false;
    } catch (error) {
      console.error("Error saving question:", error);
    }
  } else {
    questions.value[index].isEditing = false;
  }
};

const deleteQuestion = async (index) => {
  if (questions.value[index].user && questions.value[index].user.id === authUser.id) {
    try {
      await Axios.delete(`/api/QASection/${questions.value[index].id}`);
      questions.value.splice(index, 1);
    } catch (error) {
      console.error("Error deleting question:", error);
    }
  }
};

// ------------------------------
// Editing & Deleting: Answers (Replies)
// ------------------------------
// Edit an Answer: Allow editing only if the answer belongs to the current user.
const editAnswer = (qIndex, aIndex) => {
  // Check if qIndex is valid
  if (qIndex < 0 || qIndex >= questions.value.length) {
    console.error(`Invalid question index: ${qIndex}`);
    return;
  }

  const qa = questions.value[qIndex];

  // Check if qa exists and if qa.answers is an array
  if (!qa) {
    console.error(`Question at index ${qIndex} is missing`);
    return;
  }

  if (!Array.isArray(qa.answers)) {
    console.error(`No answers available for question ${qa.id} or 'qa.answers' is not an array`);
    return;
  }

  if (qa.answers.length === 0) {
    console.log(`No answers available for question ${qa.id}, consider adding a new answer.`);
    return; // Optionally, you can allow adding an answer instead
  }

  // Check if the answer exists at the specified index
  const reply = qa.answers[aIndex];
  if (!reply) {
    console.error(`No answer found at index ${aIndex} for question ${qa.id}`);
    return;
  }

  // Check if the answer belongs to the current user
  if (reply.user.id === authUser.id) {
    reply.isEditing = true;
    reply.editedText = reply.answer; // Copy the current answer text to editedText
  } else {
    console.error("User is not authorized to edit this answer.");
  }
};







// Save an Answer: Update the answer via the Answer endpoint.
const saveAnswer = async (qIndex, aIndex) => {
  const qa = questions.value[qIndex];
  if (!qa || !Array.isArray(qa.answers) || !qa.answers[aIndex]) {
    console.error("No answers available for this question or 'qa.answers' is not an array.");
    return;
  }

  const reply = qa.answers[aIndex];
  if (reply.editedText.trim() !== "") {
    try {
      const payload = { answer: reply.editedText.trim() };
      // Use the Answer endpoint for updating
      const response = await Axios.put(`/api/answers/${reply.id}`, payload);
      // Update the answer text in our local state
      reply.answer = response.data.data.answer;
      reply.isEditing = false;
    } catch (error) {
      console.error("Error saving answer:", error);
    }
  } else {
    reply.isEditing = false;
  }
};


// Delete an Answer: Only allow deletion if the answer belongs to the current user.
const deleteAnswer = async (qIndex, aIndex) => {
  const reply = questions.value[qIndex].answers[aIndex];
  if (reply.user.id === authUser.id) {
    try {
      // Use the Answer endpoint for deletion
      await Axios.delete(`/api/answers/${reply.id}`);
      // Remove the answer from the local state
      questions.value[qIndex].answers.splice(aIndex, 1);
    } catch (error) {
      console.error("Error deleting answer:", error);
    }
  }
};


// ------------------------------
// Toggle Reply Field for a Question
// ------------------------------
const toggleReplyField = (index) => {
  questions.value[index].showReplyField = !questions.value[index].showReplyField;
};

// ------------------------------
// Load More / Show Less Questions
// ------------------------------
const loadMoreQuestions = () => {
  questionsToShow.value += 2;
};

const showLessQuestions = () => {
  questionsToShow.value = 2;
};

const visibleQuestions = computed(() => questions.value.slice(0, questionsToShow.value));

const getProfileUrl = (profilePath) => {
  // Ensure the profilePath exists and doesn't already include the base URL
  return profilePath ? `http://127.0.0.1:8000/storage/${profilePath}` : '';
};


onMounted(() => {
  fetchQuestions();
});
</script>


<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <!-- Ask a Question Section -->
    <div class="mb-8">
  <h2 class="text-xl font-semibold text-gray-800 mb-3">Ask a Question</h2>
  <div class="flex flex-col space-y-2">
    <textarea
      v-model="newQuestion"
      rows="3"
      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-500 transition duration-300"
      placeholder="Type your question..."
    ></textarea>
    <button
      @click="addQuestion"
      class="self-end bg-lime-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-lime-600 transition duration-300"
    >
      Submit
    </button>
  </div>
  <p v-if="questionError" class="text-red-500 mt-2 text-sm">{{ questionError }}</p>
</div>


    <!-- List Questions and Answers -->
    <div>
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Comments</h2>
      <div class="space-y-6">
        <div
          v-for="(qa, index) in visibleQuestions"
          :key="qa.id"
          class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200"
        >
        <div class="flex flex-col space-y-3">
  <!-- Header: Avatar & User Full Name -->
  <div class="flex items-center space-x-3">
    <div class="w-6 h-6 rounded-full overflow-hidden">
    <img
  v-if="qa.user && qa.user.profile"
  :src="getProfileUrl(qa.user.profile)"
  alt="User Profile"
  class="w-full h-full object-cover"
/>

      <i v-else class="fas fa-user-circle text-lime-500 text-2xl"></i>
    </div>
    <span class="text-lg font-medium text-gray-800">
      {{ qa.user ? qa.user.full_name : 'Anonymous' }}
    </span>
  </div>

  <!-- Question Content -->
  <div>
    <p v-if="!qa.isEditing" class="text-gray-700 text-base">
      {{ qa.question }}
    </p>
    <textarea
      v-else
      v-model="qa.editedQuestion"
      rows="3"
      class="w-full p-3 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-lime-400"
    ></textarea>
  </div>

  <!-- Edit/Delete Buttons (for owner only) -->
  <div v-if="qa.user && qa.user.id === authUser.id" class="flex space-x-3 text-sm">
    <button
      v-if="!qa.isEditing"
      @click="editQuestion(index)"
      class="text-blue-500 hover:underline"
    >
      Edit
    </button>
    <button
      v-if="qa.isEditing"
      @click="saveQuestion(index)"
      class="text-green-500 hover:underline"
    >
      Save
    </button>
    <button
      @click="deleteQuestion(index)"
      class="text-red-500 hover:underline"
    >
      Delete
    </button>
  </div>
</div>
<div v-if="Array.isArray(qa.answers) && qa.answers.length > 0" class="mt-3 space-y-2">
  <div
    v-for="(reply, aIndex) in qa.answers"
    :key="reply.id"
    class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-lime-400"
  >
    <!-- User Info: Avatar and Full Name side by side -->
    <div class="flex items-center space-x-2">
      <div class="w-6 h-6 rounded-full overflow-hidden">
        <img
          v-if="reply.user && reply.user.profile"
          :src="getProfileUrl(reply.user.profile)"
          alt="User Profile"
          class="w-full h-full object-cover"
        />
        <img
          v-else-if="reply.user_id === authUser.id && authUser.profile"
          :src="authUser.profile"
          alt="User Profile"
          class="w-full h-full object-cover"
        />
        <i
          v-else
          class="fas fa-user-circle text-gray-500 text-xl"
        ></i>
      </div>
      <span class="text-sm font-semibold text-gray-700">
        {{ reply.user ? reply.user.full_name : 'Anonymous' }}
      </span>
    </div>
    
    <!-- Answer Text (below the user info) -->
    <div class="mt-2">
      <p v-if="!reply.isEditing" class="text-gray-800 text-sm">
        {{ reply.answer }}
      </p>
      <textarea
        v-else
        v-model="reply.editedText"
        rows="2"
        class="border rounded p-2 w-full resize-none"
      ></textarea>
    </div>

    <!-- Edit/Delete Buttons for Answer (owner only) -->
    <div v-if="reply.user.id === authUser.id" class="flex space-x-2 mt-2 text-sm">
      <button
        v-if="!reply.isEditing"
        @click="editAnswer(index, aIndex)"
        class="text-blue-500 hover:underline"
      >
        Edit
      </button>
      <button
        v-if="reply.isEditing"
        @click="saveAnswer(index, aIndex)"
        class="text-green-500 hover:underline"
      >
        Save
      </button>
      <button
        @click="deleteAnswer(index, aIndex)"
        class="text-red-500 hover:underline"
      >
        Delete
      </button>
    </div>
  </div>
</div>

<!-- Fallback message for no answers -->
<div v-else>
  <p class="text-gray-500">No answers available for this question.</p>
</div>




          <!-- Reply Section -->
          <div class="mt-4">
            <button
              @click="toggleReplyField(index)"
              class="text-lime-500 bg-lime-100 px-4 py-2 rounded-full shadow-md hover:bg-lime-200 transition duration-300 text-sm"
            >
              <span v-if="!qa.showReplyField">Reply</span>
              <span v-else>Cancel</span>
            </button>

            <div v-if="qa.showReplyField" class="mt-3">
              <textarea
                v-model="qa.newAnswer"
                rows="2"
                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-400 transition duration-300 resize-none"
                placeholder="Write your answer..."
              ></textarea>
              <button
                @click="addAnswer(index)"
                class="bg-lime-500 text-white px-4 py-2 rounded-lg shadow hover:bg-lime-600 transition duration-300 mt-2 w-full"
              >
                Submit Answer
              </button>
              <p v-if="qa.answerError" class="text-red-500 mt-2 text-sm">{{ qa.answerError }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More / Show Less -->
    <div class="mt-6 text-center">
      <div class="space-x-4">
        <button
          v-if="visibleQuestions.length < questions.length"
          @click="loadMoreQuestions"
          class="bg-gray-200 text-lime-500 px-5 py-2 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 text-sm"
        >
          <i class="fas fa-chevron-down mr-1"></i> See More
        </button>
        <button
          v-if="visibleQuestions.length > 1"
          @click="showLessQuestions"
          class="bg-gray-200 text-lime-500 px-5 py-2 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 text-sm"
        >
          <i class="fas fa-chevron-up mr-1"></i> Show Less
        </button>
      </div>
    </div>
  </div>
</template>



<style scoped>
.text-blue-500 {
  color: #3b82f6;
}
.text-red-500 {
  color: #ef4444;
}
.text-green-500 {
  color: #10b981;
}
</style>
