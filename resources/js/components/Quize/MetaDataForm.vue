<script setup>
    import Axios from "axios";
    import { onMounted, ref, computed } from "vue";
    import { useToast } from "vue-toastification";

    import AddQuestion from "@/components/Quize/AddQuestion.vue";

    const toast = useToast();

    const qMetaDatas = ref([]);
    const selectedExam = ref(null);
    const oppenCollaps = ref(true);
    const prepaireForUpdate = ref(false);
    const selectedQuestion = ref(null);
    const isNewExamMode = ref(false);

    const currentQuestionPage = ref(1);
    const questionsPerPage = 5;
    
    const metaData = ref({
        title: "",
        instraction: "",
    });

    const props = defineProps({
        courseID: Number,
        moduleID: Number
    });

    console.log("Course ID:", props.courseID);
    console.log("Module ID:", props.moduleID);

    const visibleQuestions = computed(() => {
        if (selectedExam.value && selectedExam.value.questions) {
            const start = (currentQuestionPage.value - 1) * questionsPerPage;
            return selectedExam.value.questions.slice(
                start,
                start + questionsPerPage
            );
        }
        return [];
    });

    const totalQuestionPages = computed(() => {
        if (selectedExam.value && selectedExam.value.questions) {
            return Math.ceil(
                selectedExam.value.questions.length / questionsPerPage
            );
        }
        return 0;
    });

    const confirmDialog = ref({
        visible: false,
        message: "",
        onConfirm: null,
    });

    function showConfirm(message, onConfirm) {
        confirmDialog.value = { visible: true, message, onConfirm };
    }

    function hideConfirm() {
        confirmDialog.value.visible = false;
    }

    function onSelectExam(exam) {
        isNewExamMode.value = false;
        if (selectedExam.value && selectedExam.value.id === exam.id) {
            selectedExam.value = null;
        } else {
            const instractionVal = exam.instraction || exam.instruction || "";
            selectedExam.value = { ...exam, instraction: instractionVal };
            metaData.value = {
                title: exam.title || "",
                instraction: instractionVal,
            };
            oppenCollaps.value = true;
            currentQuestionPage.value = 1;
            selectedQuestion.value = null;
        }
    }

    function onCollapsExam() {
        oppenCollaps.value = !oppenCollaps.value;
    }

    function onPrepareForUpdate(type) {
        prepaireForUpdate.value =
            selectedExam.value && selectedExam.value[type] !== metaData.value[type];
    }

    function onPrepareForUpdateQuestion(question) {
        selectedQuestion.value = question;
        oppenCollaps.value = false;
    }

    function toggleNewExam() {
        isNewExamMode.value = true; 
        selectedExam.value = null;
        oppenCollaps.value = true;
        selectedQuestion.value = null;
        metaData.value = { title: "", instraction: "" };
    }

    function showAddQuestionForm() {
        selectedQuestion.value = null;
        oppenCollaps.value = false;
    }

    function fetchExams() {
        Axios.get("/api/exams")
            .then((res) => {
                qMetaDatas.value = res.data.data;
            })
            .catch(() => {
                toast.error("Failed to fetch exams");
            });
    }

    function storeMetaData() {
        if (!metaData.value.title.trim()) {
            toast.error("Exam title is required");
            return;
        }
        const payload = {
            title: metaData.value.title,
            instraction: metaData.value.instraction,
        };
        Axios.post("/api/QMetaData", payload)
            .then((res) => {
                selectedExam.value = res.data.data;
                qMetaDatas.value.push(res.data.data);
                oppenCollaps.value = true;
                isNewExamMode.value = false; 
                toast.success("Exam created successfully");
            })
            .catch(() => {
                toast.error("Failed to create exam");
            });
    }

    // API call: Update Existing Exam Meta Data
    function UpdateMetaData() {
        if (!prepaireForUpdate.value) return;
        if (!metaData.value.title.trim()) {
            toast.error("Exam title is required");
            return;
        }
        const payload = {
            title: metaData.value.title,
            instraction: metaData.value.instraction,
        };
        Axios.put(`/api/QMetaData/${selectedExam.value?.id}`, payload)
            .then((res) => {
                selectedExam.value = res.data.data;
                qMetaDatas.value = qMetaDatas.value.map((q) =>
                    q.id === res.data.data.id ? res.data.data : q
                );
                prepaireForUpdate.value = false;
                toast.success("Exam updated successfully");
            })
            .catch(() => {
                toast.error("Failed to update exam");
            });
    }

    // API call: Delete Exam
    function deleteExam(examId) {
        showConfirm("Are you sure you want to delete this exam?", () => {
            Axios.delete(`/api/QMetaData/${examId}`)
                .then(() => {
                    qMetaDatas.value = qMetaDatas.value.filter(
                        (q) => q.id !== examId
                    );
                    if (selectedExam.value && selectedExam.value.id === examId) {
                        selectedExam.value = null;
                    }
                    toast.success("Exam deleted successfully");
                })
                .catch(() => {
                    toast.error("Error deleting exam");
                })
                .finally(() => {
                    hideConfirm();
                });
        });
    }

    // API call: Delete Question
    function deleteQuestion(questionId) {
        showConfirm("Are you sure you want to delete this question?", () => {
            Axios.delete(`/api/quize/${questionId}`)
                .then(() => {
                    toast.success("Question deleted successfully");
                    selectedExam.value.questions =
                        selectedExam.value.questions.filter(
                            (q) => q.id !== questionId
                        );
                    if (currentQuestionPage.value > totalQuestionPages.value) {
                        currentQuestionPage.value = 1;
                    }
                })
                .catch(() => {
                    toast.error("Error deleting question");
                })
                .finally(() => {
                    hideConfirm();
                });
        });
    }

    // Change question page for pagination
    function changeQuestionPage(page) {
        if (page >= 1 && page <= totalQuestionPages.value) {
            currentQuestionPage.value = page;
        }
    }

    onMounted(() => {
        fetchExams();
        toggleNewExam();
    });
</script>

<template>
    <div class="container mx-auto p-4">
        <div
            v-if="confirmDialog.visible"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <h2
                    class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="mb-4">{{ confirmDialog.message }}</p>
                <div class="flex justify-center space-x-4">
                    <button
                        @click="confirmDialog.onConfirm()"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
                    >
                        Delete
                    </button>
                    <button
                        @click="hideConfirm()"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-3 rounded-lg">
            <!-- Left Panel: Exams List with Questions -->
            <div class="col-span-1 bg-white p-4 shadow-md">
                <div
                    class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4"
                >
                    <h1 class="font-bold text-lg">Exams</h1>
                    <!-- Clicking the plus icon always toggles to new exam mode -->
                    <i
                        @click="toggleNewExam()"
                        class="fa-solid fa-plus text-lg text-gray-800 cursor-pointer"
                    ></i>
                </div>
                <div>
                    <div
                        v-for="qMetaData in qMetaDatas"
                        :key="qMetaData.id"
                        class="mb-2"
                    >
                        <div
                            class="flex items-center justify-between p-2 bg-gray-100 rounded hover:bg-gray-200 cursor-pointer"
                            @click="onSelectExam(qMetaData)"
                        >
                            <div class="flex items-center gap-1">
                                <i
                                    :class="{
                                        'fa-chevron-down':
                                            selectedExam &&
                                            selectedExam.id === qMetaData.id,
                                        'fa-chevron-right':
                                            !selectedExam ||
                                            selectedExam.id !== qMetaData.id,
                                    }"
                                    class="fa-solid text-sm text-gray-500"
                                ></i>
                                <h1 class="text-sm capitalize font-medium">
                                    {{ qMetaData.title }}
                                </h1>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click.stop="deleteExam(qMetaData.id)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Questions List for Selected Exam -->
                        <div
                            v-if="
                                selectedExam && selectedExam.id === qMetaData.id
                            "
                            class="mt-2 space-y-1 pl-4"
                        >
                            <div
                                v-for="(question, index) in visibleQuestions"
                                :key="question.id"
                                class="flex items-center justify-between border-l border-gray-200 pl-3 py-1"
                            >
                                <div class="flex items-center">
                                    <span class="font-bold pr-3"
                                        >{{
                                            (currentQuestionPage - 1) *
                                                questionsPerPage +
                                            index +
                                            1
                                        }}.</span
                                    >
                                    <h1
                                        @click="
                                            onPrepareForUpdateQuestion(question)
                                        "
                                        class="text-sm capitalize cursor-pointer hover:text-lime-600"
                                    >
                                        {{ question.question }}
                                    </h1>
                                </div>
                                <button
                                    @click.stop="deleteQuestion(question.id)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                            <!-- Pagination Controls for Questions -->
                            <div
                                v-if="
                                    selectedExam &&
                                    selectedExam.questions &&
                                    selectedExam.questions.length >
                                        questionsPerPage
                                "
                                class="mt-2 flex justify-center space-x-2"
                            >
                                <button
                                    @click="
                                        changeQuestionPage(
                                            currentQuestionPage - 1
                                        )
                                    "
                                    :disabled="currentQuestionPage === 1"
                                    class="px-4 py-1 text-lime-700 rounded hover:text-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </button>

                                <button
                                    @click="
                                        changeQuestionPage(
                                            currentQuestionPage + 1
                                        )
                                    "
                                    :disabled="
                                        currentQuestionPage ===
                                        totalQuestionPages
                                    "
                                    class="px-4 py-1 text-lime-700 rounded hover:text-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Exam Meta Data & Add/Update Question Form -->
            <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 shadow-md">
                <!-- Exam Header and Save Option for Existing Exams -->
                <div
                    v-if="selectedExam"
                    class="flex items-center justify-between mb-4"
                >
                    <div
                        @click="onCollapsExam()"
                        class="flex items-center cursor-pointer"
                    >
                        <h1 class="text-sm font-bold text-gray-500 capitalize">
                            {{ selectedExam.title }}
                        </h1>
                    </div>
                    <div
                        v-if="oppenCollaps && prepaireForUpdate"
                        @click="UpdateMetaData()"
                        class="flex items-center gap-2"
                    >
                        <i
                            class="fa-solid fa-save text-lg text-lime-600 hover:text-lime-800 cursor-pointer"
                        ></i>
                        <p class="text-sm text-gray-500">Save</p>
                    </div>
                </div>
                <!-- Exam Meta Data Form (visible only when open) -->
                <form v-if="oppenCollaps" class="space-y-6">
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Exam Title
                        </label>
                        <input
                            v-model="metaData.title"
                            @input="onPrepareForUpdate('title')"
                            type="text"
                            class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                            placeholder="Add Exam Title"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Exam Instruction (optional)
                        </label>
                        <textarea
                            v-model="metaData.instraction"
                            @input="onPrepareForUpdate('instraction')"
                            placeholder="Add exam instruction if necessary"
                            class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                        ></textarea>
                    </div>
                    <!-- Show Create Exam button only when in new exam mode -->
                    <button
                        type="button"
                        v-if="isNewExamMode"
                        @click="storeMetaData()"
                        class="w-full py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition duration-200"
                    >
                        Create Exam
                    </button>
                    <!-- For existing exams, show Add New Question button if meta form is open -->
                    <div v-if="selectedExam && !isNewExamMode" class="mt-4">
                        <button
                            type="button"
                            @click="showAddQuestionForm"
                            class="w-full py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700 transition duration-200"
                        >
                            Add New Question
                        </button>
                    </div>
                </form>
                <!-- Add/Update Question Form (visible when meta form is collapsed) -->
                <div v-if="selectedExam && !oppenCollaps" class="mt-6">
                    <AddQuestion
                        :selectedExam="selectedExam"
                        :selectedQuestion="selectedQuestion"
                        @quizAdded="fetchExams"
                    />
                </div>
            </div>
        </div>
    </div>
</template>


