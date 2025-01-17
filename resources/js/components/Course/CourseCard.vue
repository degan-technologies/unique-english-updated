<template>
    <div
        class="max-w-sm bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300"
    >
        <!-- Course Image -->
        <img
            :src="course.image"
            :alt="course.title"
            class="w-full h-48 object-cover"
        />

        <!-- Card Content -->
        <div class="p-4">
            <!-- Course Title -->
            <h3 class="text-lg font-semibold text-gray-800 truncate">
                {{ course.title }}
            </h3>

            <!-- Author -->
            <p class="text-sm text-gray-500 mt-1">By {{ course.author }}</p>

            <!-- Rating -->
            <div class="flex items-center mt-2">
                <div class="flex text-yellow-400">
                    <!-- Display stars based on the rating -->
                    <template v-for="n in 5" :key="n">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="currentColor"
                            :class="n <= course.rating ? '' : 'text-gray-300'"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
                            />
                        </svg>
                    </template>
                </div>
                <p class="text-sm text-gray-500 ml-2">({{ course.rating }})</p>
            </div>

            <!-- Price -->
            <p class="text-xl font-bold text-gray-800 mt-2">
                ${{ course.price }}
            </p>

            <!-- Buy Now Button -->
            <button
                class="w-full bg-blue-600 text-white text-sm font-medium py-2 mt-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                @click="handleBuyNow"
            >
                Buy Now
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "CourseCard",
    props: {
        course: {
            type: Object,
            required: true,
        },
    },
    methods: {
        handleBuyNow() {
            alert(`You have selected: ${this.course.title}`);
        },
    },
};
</script>

<style>
/* Add additional styles if needed */
</style>
