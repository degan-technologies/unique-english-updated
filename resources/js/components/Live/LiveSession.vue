<template>
    <div class="max-w-6xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">
            Class Scheduling System with Weekly View and Live Session
        </h1>

        <div class="max-w-6xl mx-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Schedule or Edit a Class Section -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">
                        {{ isEditing ? "Edit Class" : "Schedule a Class" }}
                    </h2>

                    <label
                        for="studentName"
                        class="block text-gray-700 font-semibold mb-2"
                    >
                        Student Name:
                    </label>
                    <input
                        type="text"
                        v-model="newClass.studentName"
                        class="w-full mb-2 p-2 border rounded"
                        placeholder="Enter student name"
                    />
                    <span
                        v-if="errors.studentName"
                        class="text-red-500 text-sm"
                    >
                        {{ errors.studentName }}
                    </span>

                    <label
                        for="scheduleDay"
                        class="block text-gray-700 font-semibold mb-2"
                    >
                        Select a Day:
                    </label>
                    <select
                        v-model="newClass.day"
                        class="w-full mb-2 p-2 border rounded"
                    >
                        <option value="" disabled>Select a day</option>
                        <option v-for="day in weekDays" :key="day" :value="day">
                            {{ day }}
                        </option>
                    </select>
                    <span v-if="errors.day" class="text-red-500 text-sm">
                        {{ errors.day }}
                    </span>

                    <label
                        for="scheduleTime"
                        class="block text-gray-700 font-semibold mb-2"
                    >
                        Select a Time:
                    </label>
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
                    <span v-if="errors.time" class="text-red-500 text-sm">
                        {{ errors.time }}
                    </span>

                    <div class="text-center mt-4">
                        <button
                            @click="isEditing ? saveEdit() : scheduleClass()"
                            class="bg-lime-600 hover:bg-lime-700 text-white px-6 py-2 rounded-lg"
                        >
                            {{ isEditing ? "Save Changes" : "Schedule Class" }}
                        </button>
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
                            class="text-lime-600"
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

                                    <p class="mt-2">
                                        <span
                                            :class="{
                                                'text-green-500':
                                                    classItem.status === 'Live',
                                                'text-gray-500':
                                                    classItem.status ===
                                                    'Finished',
                                                'text-lime-600':
                                                    classItem.status ===
                                                    'Upcoming',
                                            }"
                                            class="font-semibold"
                                        >
                                            {{ classItem.status }}
                                        </span>
                                    </p>

                                    <div class="flex items-center gap-2 mt-2">
                                        <i
                                            class="fas fa-trash text-red-500 text-xl cursor-pointer hover:text-red-700"
                                            @click="
                                                deleteClass(classItem.index)
                                            "
                                        ></i>
                                        <i
                                            class="fas fa-edit text-yellow-500 text-xl cursor-pointer hover:text-yellow-700"
                                            @click="editClass(classItem.index)"
                                        ></i>
                                        <i
                                            class="fas fa-video text-lime-600 text-xl cursor-pointer hover:text-lime-700"
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
    </div>
</template>
<script setup>
import { ref, computed, onMounted } from "vue";

const newClass = ref({ studentName: "", day: "", time: "" });
const errors = ref({ studentName: "", day: "", time: "" });
const scheduledClasses = ref([]);
const currentSession = ref(null);
const jitsiAPI = ref(null);
const isEditing = ref(false);
const editIndex = ref(null);
const weekDays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
const timeSlots = [
    "9:00 AM - 10:00 AM",
    "10:00 AM - 11:00 AM",
    "11:00 AM - 12:00 PM",
    "1:00 PM - 2:00 PM",
    "2:00 PM - 3:00 PM",
    "3:00 PM - 4:00 PM",
];

const availableSlots = computed(() => {
    const reservedSlots = scheduledClasses.value
        .filter((_, index) => index !== editIndex.value)
        .map((classItem) => classItem.time);
    return timeSlots.filter((slot) => !reservedSlots.includes(slot));
});

const groupedClasses = computed(() => {
    return weekDays.map((day) => ({
        day,
        classes: scheduledClasses.value
            .filter((classItem) => classItem.day === day)
            .map((classItem, index) => ({ ...classItem, index })),
    }));
});

const validateInputs = () => {
    errors.value = { studentName: "", day: "", time: "" };
    if (!newClass.value.studentName || newClass.value.studentName.length < 2) {
        errors.value.studentName = "Please enter a valid name.";
    }
    if (!newClass.value.day) errors.value.day = "Please select a day.";
    if (!newClass.value.time) errors.value.time = "Please select a time slot.";
    return !Object.values(errors.value).some((error) => error);
};

const scheduleClass = () => {
    if (validateInputs()) {
        scheduledClasses.value.push({ ...newClass.value, status: "Upcoming" });
        newClass.value = { studentName: "", day: "", time: "" };
        saveToLocalStorage();
    }
};

const deleteClass = (index) => {
    scheduledClasses.value.splice(index, 1);
    saveToLocalStorage();
};

const editClass = (index) => {
    newClass.value = { ...scheduledClasses.value[index] };
    isEditing.value = true;
    editIndex.value = index;
};

const saveEdit = () => {
    if (validateInputs()) {
        scheduledClasses.value[editIndex.value] = {
            ...newClass.value,
            status: "Upcoming",
        };
        newClass.value = { studentName: "", day: "", time: "" };
        isEditing.value = false;
        editIndex.value = null;
        saveToLocalStorage();
    }
};

const startLiveSession = (index) => {
    currentSession.value = scheduledClasses.value[index];
    scheduledClasses.value[index].status = "Live";
    saveToLocalStorage();

    if (!window.JitsiMeetExternalAPI) {
        const script = document.createElement("script");
        script.src = "https://meet.jit.si/external_api.js";
        script.onload = initializeJitsi;
        document.body.appendChild(script);
    } else {
        initializeJitsi();
    }
};

const initializeJitsi = () => {
    const domain = "meet.jit.si";
    const options = {
        roomName: `LiveClass_${Date.now()}`,
        parentNode: document.querySelector("#jitsi-video"),
    };
    jitsiAPI.value = new JitsiMeetExternalAPI(domain, options);
};

const endLiveSession = () => {
    if (jitsiAPI.value) {
        jitsiAPI.value.dispose();
        jitsiAPI.value = null;
    }
    currentSession.value = null;
    scheduledClasses.value = scheduledClasses.value.filter(
        (item) => item.status !== "Live"
    );
    saveToLocalStorage();
};

const saveToLocalStorage = () => {
    localStorage.setItem(
        "scheduledClasses",
        JSON.stringify(scheduledClasses.value)
    );
};

const loadFromLocalStorage = () => {
    const classes = JSON.parse(localStorage.getItem("scheduledClasses"));
    if (classes) scheduledClasses.value = classes;
};

onMounted(() => {
    loadFromLocalStorage();
});
</script>

<style scoped>
/* Add custom styles here */
</style>
