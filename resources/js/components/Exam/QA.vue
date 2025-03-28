<script setup>
import { ref, computed, onMounted } from "vue";
import Axios from "axios"; 

const props = defineProps({
   qaSections: Array,
   courseId: String,
});
 
const questions = ref([]);
const newQuestion = ref("");
const questionError = ref("");

const editableQa = ref({});
const replayQaId = ref(null); 
const actionReplay = ref('replay')
const actionEditReplay = ref(false)

const updatedData =ref( {
    question: null,
    qaSectionId: null,
    answer: null,
    answerId:null,
}); 

function addQuestion() {
     const payload = {
            question: newQuestion.value.trim(),
            course_id: props.courseId, 
        }; 

        Axios
            .post("/api/QASection", payload)
            .then(res => {
                questions.value.push(response.data.data);
                newQuestion.value = "";
                questionError.value = "";
            })
};

function editQuestion(qa, type = null) {
    replayQaId.value = null;

    if(actionReplay.value == type) {
        editableQa.value = {};
        return replayQaId.value = qa.id;
    } 

    editableQa.value = {... qa};
    return;
};

function saveQuestion() {
    Axios
        .put(`/api/QASection/${editableQa.value?.id}`, {question:editableQa.value?.question})
        .then(res=>{
            editableQa.value = {};
        });
};

function deleteQuestion(id){
    Axios
        .delete(`/api/QASection/${id}}`)
        .then(res=>{ });
};
 
function addAnswer(questionId){
    const payload = {
            answer: updatedData.value.answer,   
            question_id: questionId,       
        };

    Axios
        .post("/api/answers", payload).
        then(res => {

        })
};

function editReplay(replay){
    replayQaId.value = replay.question_id;
    updatedData.value.answer = replay.answer;
    updatedData.value.answerId = replay.id;
    actionEditReplay.value = true;
    return;
}

function saveAnswer() {
     const payload = { 
        answer: updatedData.value.answer, 
    };
     Axios
        .put(`/api/answers/${updatedData.value?.answerId }`, payload)
        .then(res=>{
            updatedData.value.answer = null,
            replayQaId.value = null,
            actionEditReplay.value = false;
        }) 
};

 
function deleteAnswer(replayId) { 
    Axios
        .delete(`/api/answers/${replayId}`)
        .then(res=>{});
};
</script>


<template>
    <div class="max-w-3xl mx-auto p-6">
        <!-- Ask a Question Section -->
        <div class="mb-8">
            <h2 class="text-md font-semibold text-gray-800 mb-4">Ask a Question</h2>
            <div class="mt-3 flex flex-row gap-2 w-full">
                <div class="flex-grow">
                    <textarea v-model="newQuestion" rows="3"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-500 transition duration-300"
                    placeholder="Type your question..."></textarea>
                </div>                 
                 <div 
                    @click="addQuestion()"
                    class="w-fit">
                    <i class="fa-solid px-4 hover:text-sky-500 active:text-sky-500 cursor-pointer fa-paper-plane text-2xl font-bold text-sky-500"></i>
                </div>
            </div> 
        </div>

        <!-- List Questions and Answers -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4"> Questions </h2>
            <div class="space-y-6">
                <div v-for="qa in qaSections" :key="qa.id"
                    class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex flex-col space-y-3">
                        <!-- Header: Avatar & User Full Name -->
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full overflow-hidden">
                                <img v-if="qa?.user" :src="qa?.user.profile"
                                    alt="User Profile" class="w-full h-full object-cover" />

                                <i v-else class="fas fa-user-circle text-lime-500 text-2xl"></i>
                            </div>
                            <span class="text-md font-semibold text-gray-800">
                                {{ qa.user ? qa.user.full_name : 'Anonymous' }}
                            </span>
                        </div>

                        <!-- Question Content -->
                        <div>
                            <p   class="text-gray-700 text-base">
                                {{ qa.question }}
                            </p>
                        </div>

                        <!-- Edit/Delete Buttons (for owner only) -->
                        <div class="flex space-x-3 text-sm">
                            <button 
                                v-if="qa.editable" 
                                @click="editQuestion(qa)"
                                class="text-blue-500 hover:text-blue-600">
                                Edit
                            </button> 
                            <button 
                                @click="deleteQuestion(qa.id)" 
                                class="text-red-500 hover:text-red-600">
                                Delete
                            </button>
                            <button 
                                @click="editQuestion(replayQaId ? null : qa, actionReplay)" 
                                class="text-lime-500 hover:text-lime-600">
                                {{ replayQaId ? 'X cancel' :'Answer'  }}
                            </button>
                        </div>
                        
                        <div v-if="editableQa.id == qa.id" class="mt-3 flex flex-row gap-2 w-full">
                            <div class="flex-grow">
                                <textarea v-model="editableQa.question" rows="2"
                                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-400 transition duration-300 resize-none"
                                    placeholder="Write your answer...">
                                </textarea>
                            </div>
                            <div class="w-fit">
                                <i 
                                    @click="saveQuestion()"
                                    class="fa-solid px-4 hover:text-sky-500 active:text-sky-500 cursor-pointer fa-paper-plane text-2xl font-bold text-sky-500"></i>
                            </div>
                        </div>
                    </div>

                    <!-- answer question -->
                    <div v-if="replayQaId === qa.id" class="mt-4 flex flex-row gap-2 w-full">
                        <div class="flex-grow">
                            <textarea v-model="updatedData.answer" rows="2"
                                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-400 transition duration-300 resize-none"
                                placeholder="Write your answer..."></textarea>
                        </div>
                        <div class="w-fit">
                            <i 
                                @click="actionEditReplay ? saveAnswer() : addAnswer(qa.id)"
                                class="fa-solid px-4 hover:text-sky-500 active:text-sky-500 cursor-pointer fa-paper-plane text-2xl font-bold text-sky-500"></i>
                        </div>
                    </div>
        
                    <div  class="mt-4 space-y-2">
                        <div v-for="replay in qa?.answers" :key="replay.id"
                            class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-lime-400"> 

                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 rounded-full overflow-hidden">
                                    <img v-if="replay?.user" :src="replay?.user.profile"
                                        alt="User Profile" class="w-full h-full object-cover" />

                                    <i v-else class="fas fa-user-circle text-lime-500 text-2xl"></i>
                                </div>
                                <span class="text-md font-semibold text-gray-800">
                                    {{ replay.user ? replay.user.full_name : 'Anonymous' }}
                                </span>
                            </div>

                            <!-- Answer Text (below the user info) -->
                            <div class="mt-2">
                                <p v-if="!replay.isEditing" class="text-gray-800 text-sm">
                                    {{ replay.answer }}
                                </p>
                            </div>

                            <!-- Edit/Delete Buttons for Answer (owner only) -->
                            <div v-if="replay.user.id" class="flex space-x-2 mt-2 text-sm">
                                <button 
                                    v-if="replay.editable" 
                                    @click="editReplay(replay)"
                                    class="text-blue-500 hover:underline">
                                    Edit
                                </button> 
                                <button  
                                    v-if="replay.editable" 
                                    @click="deleteAnswer(replay.id)" 
                                    class="text-red-500 hover:underline">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div> 

                </div>
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
