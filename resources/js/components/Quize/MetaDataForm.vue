<script setup>
import Axios from "axios";
import { onMounted, ref } from "vue";

import AddQuestion from "@/components/Quize/AddQuestion.vue";
import { Newlybuild } from "@icon-park/vue-next";

const metaData = ref({
    title: "",
    instruction: "",
});

const qMetaDatas = ref([]);
const selectedExam = ref(null);
const oppenCollaps = ref(true);
const prepaireForUpdate = ref(false);

const selectedQuestion = ref(null);

function onSelctExam(exam) {
    selectedExam.value = exam;
    metaData.value = { ...exam };
    oppenCollaps.value = false;
}

function onCollapsExam() {
    oppenCollaps.value = !oppenCollaps.value;
}

function onPrepareForUpdate(type) {
    if (selectedExam.value[type] !== metaData.value[type]) {
        prepaireForUpdate.value = true;
    } else {
        prepaireForUpdate.value = false;
    }
}

function onPrepareForUpdateQuestion(question) {
    selectedQuestion.value = question;
}

function toggleNewExam() {
    selectedExam.value = null;
    oppenCollaps.value = true;
    selectedQuestion.value = false;

    metaData.value = {
        title: "",
        instruction: "",
    };
}

function fetchExams() {
    Axios.get("/api/exams").then((res) => {
        qMetaDatas.value = res.data.data;
    });
}

function storeMetaData() {
    Axios.post("/api/QMetaData", metaData.value).then((res) => {
        selectedExam.value = res.data.data;
        qMetaDatas.value.push(res.data.data);
        oppenCollaps.value = false;
    });
}

function UpdateMetaData() {
    if (!prepaireForUpdate.value) return;
    Axios.put(`/api/QMetaData/${selectedExam.value?.id}`, metaData.value).then(
        (res) => {
            selectedExam.value = res.data.data;
            qMetaDatas.value = qMetaDatas.value.map((qMetaData) => {
                if (qMetaData.id === res.data.data.id) {
                    return res.data.data;
                }
                prepaireForUpdate.value = false;
                return qMetaData;
            });
        }
    );
}

onMounted(() => {
    fetchExams();
});
</script>

<template>
    <div class="grid grid-cols-3 h-full min-h-[60%]">
        <div class="col-span-1 bg-white">
            <div class="flex justify-between p-2 border-b border-gray-200">
                <h1>Exams</h1>
                <i
                    @click="toggleNewExam()"
                    class="fa-solid fa-plus text-lg text-gray-800"
                ></i>
            </div>

            <div class="h-min-screen p-2">
                <div v-for="qMetaData in qMetaDatas" :key="qMetaData.id">
                    <div
                        class="w-full cursor-pointer flex flex-row justify-between mt-1 rounded-md hover:bg-gray-50 p-2"
                    >
                        <div
                            @click="onSelctExam(qMetaData)"
                            class="flex flex-row flex-grow"
                        >
                            <i
                                class="fa-solid fa-cube self-center text-sm text-gray-400 pr-3"
                            ></i>
                            <h1 class="text-left text-sm capitalize">
                                {{ qMetaData.title }}
                            </h1>
                        </div>
                        <i
                            class="fa-solid fa-ellipsis-vertical self-center text-sm text-gray-400 pl-3"
                        ></i>
                    </div>

                    <div v-if="selectedExam?.id === qMetaData.id" class="mt-2">
                        <div
                            v-for="(question, index) in qMetaData?.questions"
                            :key="question.id"
                            class="border-l border-gray-200 p-2 ml-2 flex flex-row"
                        >
                            <h1 class="font-bold text-md w-fit h-fit px-3">
                                {{ index + 1 }}.
                            </h1>
                            <h1
                                @click="onPrepareForUpdateQuestion(question)"
                                class="text-left text-sm capitalize"
                            >
                                {{ question.question }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-2 bg-gray-50 p-4">
            <div>
                <div
                    v-if="selectedExam"
                    class="py-4 flex flex-row justify-between"
                >
                    <div
                        @click="onCollapsExam()"
                        class="flex flex-row flex-grow"
                    >
                        <i
                            :class="{
                                'fa-angle-right': !oppenCollaps,
                                'fa-chevron-down': oppenCollaps,
                            }"
                            class="fa-solid self-center text-md text-gray-600 pr-3"
                        ></i>
                        <h1
                            class="text-left text-sm font-bold text-gray-500 capitalize"
                        >
                            {{ selectedExam.title }}
                        </h1>
                    </div>
                    <div
                        v-if="oppenCollaps && prepaireForUpdate"
                        @click="UpdateMetaData()"
                        class="flex flex-row gap-2"
                    >
                        <i
                            class="fa-solid fa-save self-center text-lg text-lime-600 hover:text-lime-800 cursor-pointer ml-8"
                        ></i>
                        <p class="text-sm self-center text-gray-500">save</p>
                    </div>
                </div>
            </div>

            <form v-if="oppenCollaps" class="mt-2 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">
                        Exam Title
                    </label>
                    <input
                        v-model="metaData.title"
                        @input="onPrepareForUpdate('title')"
                        type="text"
                        class="w-full mt-2 p-2 border text-sm rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                        placeholder="Add Exam Title"
                    />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">
                        Exam Instruction (optional)
                    </label>
                    <textarea
                        v-model="metaData.instruction"
                        @input="onPrepareForUpdate('instruction')"
                        placeholder="Add exam instruction if neccery"
                        class="w-full mt-2 p-2 border text-sm rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                    ></textarea>
                </div>
                <button
                    type="button"
                    v-if="!selectedExam"
                    @click="storeMetaData()"
                    class="border p-2 mt-2 px-4 bg-lime-700 border-gray-400 text-white rounded-lg text-center hover:bg-lime-800 text-sm w-fit mx-auto"
                >
                    Create Question
                </button>
            </form>

            <div v-if="selectedExam && !oppenCollaps" class="my-4">
                <AddQuestion
                    :selectedExam="selectedExam"
                    :selectedQuestion="selectedQuestion"
                />
            </div>
        </div>
    </div>
</template>
