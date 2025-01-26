<template>
    <div class="max-w-6xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">
            Class Scheduling System with Weekly View and Live Session
        </h1>

        <div class="max-w-6xl mx-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Schedule a Class Section -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Schedule a Class</h2>

                    <label
                        for="studentName"
                        class="block text-gray-700 font-semibold mb-2"
                        >Student Name:</label
                    >
                    <input
                        type="text"
                        v-model="newClass.studentName"
                        class="w-full mb-2 p-2 border rounded"
                        placeholder="Enter your name"
                    />
                    <span
                        v-if="errors.studentName"
                        class="text-red-500 text-sm"
                        >{{ errors.studentName }}</span
                    >

                    <label
                        for="scheduleDay"
                        class="block text-gray-700 font-semibold mb-2"
                        >Select a Day:</label
                    >
                    <select
                        v-model="newClass.day"
                        class="w-full mb-2 p-2 border rounded"
                    >
                        <option value="" disabled>Select a day</option>
                        <option v-for="day in weekDays" :key="day" :value="day">
                            {{ day }}
                        </option>
                    </select>
                    <span v-if="errors.day" class="text-red-500 text-sm">{{
                        errors.day
                    }}</span>

                    <label
                        for="scheduleTime"
                        class="block text-gray-700 font-semibold mb-2"
                        >Select a Time:</label
                    >
                    <select
                        v-model="newClass.time"
                        class="w-full mb-2 p-2 border rounded"
                    >
                        <option value="" disabled>Select a time</option>
                        <option
                            v-for="slot in availableSlots"
                            :key="slot"
                            :value="slot"
                        >
                            {{ slot }}
                        </option>
                    </select>
                    <span v-if="errors.time" class="text-red-500 text-sm">{{
                        errors.time
                    }}</span>

                    <div class="text-center mt-4">
                        <i
                            class="fas fa-calendar-check text-blue-500 text-2xl cursor-pointer hover:text-blue-700"
                            @click="scheduleClass"
                        ></i>
                    </div>
                </div>

                <!-- Weekly Schedule Section -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Weekly Schedule</h2>

                    <div
                        v-for="group in groupedClasses"
                        :key="group.day"
                        class="mb-6"
                    >
                        <h3 class="text-lg font-bold text-gray-700">
                            {{ group.day }}
                        </h3>

                        <div
                            v-if="group.classes.length === 0"
                            class="text-blue-500"
                        >
                            No schedule for this day.
                        </div>

                        <div v-else class="bg-gray-50 p-4 rounded-lg shadow">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <div
                                    v-for="(classItem, index) in group.classes"
                                    :key="index"
                                    class="bg-white p-4 rounded-lg shadow border border-gray-200"
                                >
                                    <h4 class="font-bold text-gray-800">
                                        {{ classItem.studentName }}
                                    </h4>
                                    <p class="text-gray-600">
                                        {{ classItem.time }}
                                    </p>

                                    <!-- Status Display -->
                                    <p class="mt-2">
                                        <span
                                            :class="{
                                                'text-green-500':
                                                    classItem.status ===
                                                    'Ongoing',
                                                'text-gray-500':
                                                    classItem.status ===
                                                    'Passed',
                                                'text-blue-500':
                                                    classItem.status ===
                                                    'Upcoming',
                                            }"
                                            class="font-semibold"
                                            >{{ classItem.status }}</span
                                        >
                                    </p>

                                    <div class="flex items-center gap-2 mt-2">
                                        <i
                                            class="fas fa-edit text-yellow-500 text-xl cursor-pointer hover:text-yellow-700"
                                            @click="editClass(classItem.index)"
                                        ></i>
                                        <i
                                            class="fas fa-trash text-red-500 text-xl cursor-pointer hover:text-red-700"
                                            @click="
                                                deleteClass(classItem.index)
                                            "
                                        ></i>
                                        <i
                                            class="fas fa-video text-blue-500 text-xl cursor-pointer hover:text-blue-700"
                                            v-if="
                                                classItem.status === 'Upcoming'
                                            "
                                            @click="
                                                startLiveSession(
                                                    classItem.index
                                                )
                                            "
                                        ></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentSession" class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">
                Live Session: {{ currentSession.studentName }} at
                {{ currentSession.time }}
            </h2>
            <div id="jitsi-video" class="w-full h-96 bg-gray-200"></div>
            <div class="mt-4 text-center">
                <button
                    @click="endLiveSession"
                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg"
                >
                    End Session
                </button>
            </div>
        </div>
        <div
            v-if="sessionFinishedMessage"
            class="mt-4 text-center text-red-600 font-bold"
        >
            {{ sessionFinishedMessage }}
        </div>
    </div>
</template>

<script>
export default {
    name: "ClassSchedulingSystem",
    data() {
        return {
            newClass: {
                studentName: "",
                time: "",
                day: "",
            },
            errors: {
                studentName: "",
                time: "",
                day: "",
            },
            scheduledClasses: [],
            currentSession: null,
            jitsiAPI: null,
            sessionFinishedMessage: "",
            weekDays: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            timeSlots: [
                "9:00 AM - 10:00 AM",
                "10:00 AM - 11:00 AM",
                "11:00 AM - 12:00 PM",
                "1:00 PM - 2:00 PM",
                "2:00 PM - 3:00 PM",
                "3:00 PM - 4:00 PM",
            ],
        };
    },
    computed: {
        availableSlots() {
            const reservedSlots = this.scheduledClasses.map(
                (classItem) => classItem.time
            );
            return this.timeSlots.filter(
                (slot) => !reservedSlots.includes(slot)
            );
        },
        groupedClasses() {
            return this.weekDays.map((day) => ({
                day,
                classes: this.scheduledClasses
                    .filter((classItem) => classItem.day === day)
                    .map((classItem, index) => ({ ...classItem, index })),
            }));
        },
    },
    methods: {
        validateInputs() {
            this.errors.studentName = "";
            this.errors.time = "";
            this.errors.day = "";

            if (
                !this.newClass.studentName ||
                this.newClass.studentName.length < 2
            ) {
                this.errors.studentName =
                    "Please enter a valid name (at least 2 characters).";
            }
            if (!this.newClass.day) {
                this.errors.day = "Please select a valid day.";
            }
            if (!this.newClass.time) {
                this.errors.time = "Please select a valid time slot.";
            }
            return (
                !this.errors.studentName &&
                !this.errors.time &&
                !this.errors.day
            );
        },
        scheduleClass() {
            if (this.validateInputs()) {
                this.scheduledClasses.push({
                    ...this.newClass,
                    status: "Upcoming",
                });
                this.newClass = { studentName: "", time: "", day: "" };
                this.saveClassesToLocalStorage();
            }
        },
        editClass(index) {
            this.scheduledClasses[index].isEditing = true;
        },
        deleteClass(index) {
            this.scheduledClasses.splice(index, 1);
            this.saveClassesToLocalStorage();
        },
        startLiveSession(index) {
            const classItem = this.scheduledClasses[index];
            if (classItem.status !== "Upcoming") return;

            this.currentSession = classItem;
            this.scheduledClasses[index].status = "Live";
            this.saveClassesToLocalStorage();

            // Load Jitsi API dynamically if not already loaded
            if (!window.JitsiMeetExternalAPI) {
                const script = document.createElement("script");
                script.src = "https://meet.jit.si/external_api.js";
                script.onload = this.initializeJitsi;
                document.body.appendChild(script);
            } else {
                this.initializeJitsi();
            }
        },
        initializeJitsi() {
            const domain = "meet.jit.si";
            const options = {
                roomName: `LiveClass_${Date.now()}`,
                width: "100%",
                height: "100%",
                parentNode: document.querySelector("#jitsi-video"),
            };
            this.jitsiAPI = new JitsiMeetExternalAPI(domain, options);
        },
        endLiveSession() {
            if (this.jitsiAPI) {
                this.jitsiAPI.dispose();
                this.jitsiAPI = null;
            }
            this.currentSession = null;
            this.sessionFinishedMessage = "Session finished.";
            setTimeout(() => {
                this.sessionFinishedMessage = "";
            }, 5000);
        },
        saveClassesToLocalStorage() {
            localStorage.setItem(
                "scheduledClasses",
                JSON.stringify(this.scheduledClasses)
            );
        },
        loadClassesFromLocalStorage() {
            const classes = localStorage.getItem("scheduledClasses");
            if (classes) {
                this.scheduledClasses = JSON.parse(classes);
            }
        },
    },
    mounted() {
        this.loadClassesFromLocalStorage();
    },
};
</script>

<style scoped>
/* Add custom styles here */
</style>
