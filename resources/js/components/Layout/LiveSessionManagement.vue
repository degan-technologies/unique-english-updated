<script setup>
import { ref, nextTick } from 'vue';

import JetsiLive from '@/components/Live/JetsiLive.vue';

const activeTab = ref("live");
const sessions = ref([
    {
        id: 1,
        title: "Math 101",
        instructor: "Dr. Smith",
        startTime: "10:00 AM",
        status: "live",
        participants: 25,
    },
    {
        id: 2,
        title: "History 202",
        instructor: "Prof. Johnson",
        startTime: "11:30 AM",
        status: "scheduled",
        participants: 0,
    },
    {
        id: 3,
        title: "Science 303",
        instructor: "Dr. Lee",
        startTime: "12:15 PM",
        status: "issues",
        participants: 10,
    },
]);

const currentAttendance = ref(25);
const maxAttendance = ref(50);

const chatMessages = ref([
    { id: 1, sender: "Alice", text: "Hello everyone!", role: "student", flagged: false },
    { id: 2, sender: "Dr. Smith", text: "Welcome to the session.", role: "instructor", flagged: false },
]);

const newChatMessage = ref('');
const activeEngagementTab = ref("poll");

const currentPoll = ref({
    question: "What is the capital of France?",
    options: [
        { text: "Paris", votes: 10 },
        { text: "Berlin", votes: 2 },
        { text: "Madrid", votes: 1 },
    ],
});

const questions = ref([
    { id: 1, sender: "Bob", text: "Can you explain the theorem again?", upvotes: 3 },
    { id: 2, sender: "Carol", text: "Is the exam open book?", upvotes: 5 },
]);

const newQuestion = ref('');
const showScheduleModal = ref(false);

const newSession = ref({
    title: "",
    dateTime: "",
    duration: 60,
});

const chatContainer = ref(null);
function sessionStatusClass(status) {
    if (status === "live") return "bg-green-500 text-white";
    if (status === "scheduled") return "bg-yellow-500 text-white";
    if (status === "issues") return "bg-red-500 text-white";
    return "";
}

function joinSession(session) {
    alert(`Joining session: ${session.title}`);
}

function sendChatMessage() {
    if (newChatMessage.value.trim() === "") return;
    chatMessages.value.push({
        id: Date.now(),
        sender: "You",
        text: newChatMessage.value,
        role: "admin",
        flagged: false,
    });
    newChatMessage.value = "";
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

function votePoll(index) {
    currentPoll.value.options[index].votes++;
}

function totalPollVotes() {
    return currentPoll.value.options.reduce((sum, option) => sum + option.votes, 0);
}

function upvoteQuestion(question) {
    question.upvotes++;
}

function submitQuestion() {
    if (newQuestion.value.trim() === "") return;
    questions.value.push({
        id: Date.now(),
        sender: "You",
        text: newQuestion.value,
        upvotes: 0,
    });
    newQuestion.value = "";
}

function scheduleSession() {
    sessions.value.push({
        id: Date.now(),
        title: newSession.value.title,
        instructor: "You",
        startTime: new Date(newSession.value.dateTime).toLocaleTimeString(),
        status: "scheduled",
        participants: 0,
    });
    newSession.value = { title: "", dateTime: "", duration: 60 };
    showScheduleModal.value = false;
}
</script>


<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white shadow p-4 flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Live Sessions & Student Engagement</h1>
                <p class="text-sm text-gray-600">Monitor, host, and engage in live learning sessions</p>
            </div>
            <nav class="mt-2 md:mt-0">
                <ul class="flex space-x-4">
                    <li>
                        <button class="px-4 py-2 rounded hover:bg-blue-600 transition-colors"
                            :class="activeTab === 'live' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'"
                            @click="activeTab = 'live'">
                            Live Now
                        </button>
                    </li>
                    <li>
                        <button class="px-4 py-2 rounded hover:bg-green-600 transition-colors"
                            :class="activeTab === 'upcoming' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'"
                            @click="activeTab = 'upcoming'">
                            Upcoming Sessions
                        </button>
                    </li>
                    <li>
                        <button class="px-4 py-2 rounded hover:bg-gray-600 transition-colors"
                            :class="activeTab === 'recorded' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-800'"
                            @click="activeTab = 'recorded'">
                            Recorded Sessions
                        </button>
                    </li>
                </ul>
            </nav>
        </header>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4">
            <!-- Left Panel: Session List -->
            <aside class="md:col-span-1 bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold mb-4">Sessions</h2>
                <ul>
                    <li v-for="session in sessions" :key="session.id"
                        class="mb-4 border p-3 rounded hover:shadow transition-shadow duration-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold">{{ session.title }}</h3>
                                <p class="text-sm text-gray-500">{{ session.instructor }}</p>
                                <p class="text-xs text-gray-400">{{ session.startTime }}</p>
                            </div>
                            <div>
                                <span :class="sessionStatusClass(session.status)" class="px-2 py-1 text-xs rounded">
                                    {{ session.status }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors"
                                @click="joinSession(session)">
                                Join
                            </button>
                            <span class="text-sm text-gray-600">{{ session.participants }} participants</span>
                        </div>
                    </li>
                </ul>
            </aside>

            <!-- Central Panel: Live Video & Chat -->
            <main class="md:col-span-2 bg-white rounded shadow p-4 flex flex-col">
                <!-- Live Video Preview -->
                <div class="relative">
                    <div class="bg-black rounded overflow-hidden">
                        <!-- Embedded Video (demo link) -->
                        <div class="aspect-w-16 aspect-h-9">
                            <JetsiLive v-if="false" />
                        </div>
                    </div>
                    <!-- Live Indicator -->
                    <div
                        class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs flex items-center space-x-1">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-white opacity-75"></span>
                        <span>Live</span>
                    </div>
                    <!-- Attendance Tracker -->
                    <div class="absolute bottom-2 right-2 bg-gray-800 text-white px-2 py-1 rounded text-xs">
                        Attendance: {{ currentAttendance }} / {{ maxAttendance }}
                    </div>
                </div>

                <!-- Real-Time Chat Panel -->
                <section class="mt-4 flex-1 flex flex-col">
                    <h2 class="text-lg font-semibold mb-2">Live Chat</h2>
                    <div class="flex-1 overflow-y-auto border rounded p-2 bg-gray-50" ref="chatContainer">
                        <div v-for="msg in chatMessages" :key="msg.id" class="mb-2">
                            <span :class="{
                                'font-bold text-blue-600': msg.role === 'instructor',
                                'font-semibold text-red-600': msg.flagged
                            }" class="mr-2">
                                {{ msg.sender }}:
                            </span>
                            <span>{{ msg.text }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex">
                        <input v-model="newChatMessage" type="text" placeholder="Type your message..."
                            class="flex-1 border rounded-l px-3 py-2 focus:outline-none"
                            @keyup.enter="sendChatMessage()" />
                        <button class="bg-blue-500 text-white px-4 rounded-r hover:bg-blue-600 transition-colors"
                            @click="sendChatMessage()">
                            Send
                        </button>
                    </div>
                </section>
            </main>

            <!-- Right Panel: Interactive Tools (Poll & Q&A) -->
            <aside class="md:col-span-1 bg-white rounded shadow p-4">
                <!-- Engagement Tabs -->
                <div class="mb-4">
                    <div class="flex space-x-2 mb-2">
                        <button :class="{
                            'bg-blue-500 text-white': activeEngagementTab === 'poll',
                            'bg-gray-200 text-gray-800': activeEngagementTab !== 'poll'
                        }" class="flex-1 py-2 rounded" @click="activeEngagementTab = 'poll'">
                            Poll
                        </button>
                        <button :class="{
                            'bg-blue-500 text-white': activeEngagementTab === 'qa',
                            'bg-gray-200 text-gray-800': activeEngagementTab !== 'qa'
                        }" class="flex-1 py-2 rounded" @click="activeEngagementTab = 'qa'">
                            Q&amp;A
                        </button>
                    </div>

                    <!-- Poll Module -->
                    <div v-if="activeEngagementTab === 'poll'">
                        <h2 class="text-lg font-semibold mb-2">Live Poll</h2>
                        <div v-if="currentPoll">
                            <p class="mb-2">{{ currentPoll.question }}</p>
                            <ul>
                                <li v-for="(option, index) in currentPoll.options" :key="index" class="mb-2">
                                    <button
                                        class="w-full text-left border rounded px-3 py-2 hover:bg-gray-100 transition-colors"
                                        @click="votePoll(index)">
                                        {{ option.text }}
                                        <span v-if="option.votes"> - {{ option.votes }} votes</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div v-else>
                            <p>No active poll</p>
                        </div>
                    </div>

                    <!-- Q&A Module -->
                    <div v-else>
                        <h2 class="text-lg font-semibold mb-2">Q&amp;A Session</h2>
                        <div class="overflow-y-auto max-h-64 border rounded p-2 bg-gray-50">
                            <div v-for="question in questions" :key="question.id" class="mb-2 border-b pb-1">
                                <p class="font-bold">{{ question.sender }}:</p>
                                <p>{{ question.text }}</p>
                                <div class="text-xs text-gray-500 flex justify-between items-center">
                                    <span>{{ question.upvotes }} upvotes</span>
                                    <button class="text-blue-500" @click="upvoteQuestion(question)">
                                        Upvote
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input v-model="newQuestion" type="text" placeholder="Ask a question..."
                                class="w-full border rounded px-3 py-2 focus:outline-none"
                                @keyup.enter="submitQuestion()" />
                            <button
                                class="mt-2 w-full bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 transition-colors"
                                @click="submitQuestion()">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Engagement Metrics -->
                <div>
                    <h2 class="text-lg font-semibold mb-2">Engagement Metrics</h2>
                    <div class="text-sm text-gray-600">
                        <p>Chat Messages: {{ chatMessages.length }}</p>
                        <p>
                            Poll Participation:
                            {{ currentPoll ? totalPollVotes() : 0 }}
                        </p>
                        <p>Questions Asked: {{ questions.length }}</p>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Modal: Schedule New Session -->
        <transition name="fade">
            <div v-if="showScheduleModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-11/12 md:w-1/2">
                    <h2 class="text-xl font-semibold mb-4">Schedule New Session</h2>
                    <form @submit.prevent="scheduleSession()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Session Title</label>
                            <input v-model="newSession.title" type="text"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Date & Time</label>
                            <input v-model="newSession.dateTime" type="datetime-local"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                            <input v-model="newSession.duration" type="number"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100 transition-colors"
                                @click="showScheduleModal = false">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                                Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
        <!-- Footer with Quick Access -->
        <footer class="p-4">
            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors"
                @click="showScheduleModal = true">
                Schedule New Session
            </button>
        </footer>
    </div>
</template>

<style scoped>
/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>