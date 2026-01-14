<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">
            Instructors
        </h1>

        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            <div
                v-for="(instructor, index) in paginatedInstructors"
                :key="instructor.id"
                class="bg-white rounded-2xl shadow p-5 flex flex-col h-full justify-between"
            >
                <div>
                    <div class="flex flex-col items-center">
                        <img
                            :src="instructor.image"
                            alt="Instructor"
                            class="w-24 h-24 rounded-full shadow mb-3"
                        />
                        <h2 class="text-lg font-bold text-gray-800">
                            {{ instructor.name }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ instructor.title }}
                        </p>
                        <p class="mt-2 text-gray-600 text-center">
                            {{ instructor.bio }}
                        </p>

                        <!-- Rating -->
                        <div class="mt-3">
                            <div class="flex justify-center space-x-1">
                                <i
                                    v-for="i in 5"
                                    :key="i"
                                    :class="[
                                        'fa-star text-xl cursor-pointer transition duration-200',
                                        i <= instructor.rating
                                            ? 'fas text-yellow-400'
                                            : 'far text-gray-300',
                                    ]"
                                    @click="
                                        setRating(
                                            index + (currentPage - 1) * perPage,
                                            i
                                        )
                                    "
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div class="w-full mt-4">
                        <textarea
                            v-model="instructor.comment"
                            placeholder="Leave a comment"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-lime-500"
                            rows="3"
                        ></textarea>
                    </div>
                </div>

                <!-- Fixed position button inside card -->
                <div class="mt-4">
                    <button
                        class="w-full px-3 py-2 bg-lime-600 hover:bg-lime-700 text-white rounded-lg text-sm"
                        @click="
                            submitFeedback(index + (currentPage - 1) * perPage)
                        "
                    >
                        Submit Feedback
                    </button>
                    <p
                        v-if="instructor.submitted"
                        class="text-green-600 text-sm mt-1 text-center"
                    >
                        Thank you for your feedback!
                    </p>
                </div>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center items-center mt-6 space-x-2">
            <button
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded"
                :disabled="currentPage === 1"
                @click="currentPage--"
            >
                Prev
            </button>
            <span class="text-sm text-gray-700"
                >Page {{ currentPage }} of {{ totalPages }}</span
            >
            <button
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded"
                :disabled="currentPage === totalPages"
                @click="currentPage++"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";

const instructors = reactive([
    {
        id: 1,
        name: "Sarah Johnson",
        title: "Full Stack Developer",
        image: "https://i.pravatar.cc/150?img=6",
        bio: "10+ years of experience in modern web development.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    {
        id: 2,
        name: "John Doe",
        title: "AI & Data Scientist",
        image: "https://i.pravatar.cc/150?img=2",
        bio: "Specialist in machine learning and AI systems.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    {
        id: 3,
        name: "Emily Clark",
        title: "Cybersecurity Expert",
        image: "https://i.pravatar.cc/150?img=3",
        bio: "Helps students understand digital security.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    {
        id: 4,
        name: "Daniel Smith",
        title: "Mobile App Developer",
        image: "https://i.pravatar.cc/150?img=4",
        bio: "Builds scalable apps using Flutter and React Native.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    {
        id: 5,
        name: "Laura Brown",
        title: "Cloud Solutions Architect",
        image: "https://i.pravatar.cc/150?img=5",
        bio: "Expert in AWS and Azure cloud services.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    {
        id: 6,
        name: "Michael Green",
        title: "DevOps Engineer",
        image: "https://i.pravatar.cc/150?img=6",
        bio: "Streamlines development and operations processes.",
        rating: 0,
        comment: "",
        submitted: false,
    },
    // Add more instructors as needed...
]);

const currentPage = ref(1);
const perPage = 3;

const totalPages = computed(() => Math.ceil(instructors.length / perPage));

const paginatedInstructors = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return instructors.slice(start, start + perPage);
});

function setRating(index, value) {
    instructors[index].rating = value;
    instructors[index].submitted = false;
}

function submitFeedback(index) {
    const inst = instructors[index];
    console.log(`Feedback for ${inst.name}:`, {
        rating: inst.rating,
        comment: inst.comment,
    });
    inst.submitted = true;
}
</script>

<style scoped>
/* Add your custom styles here if needed */
</style>
