<template>
    <div class="flex items-center">
        <span
            v-for="index in maxStars"
            :key="index"
            class="cursor-pointer text-2xl"
            :class="{
                'text-yellow-400': index <= fullRating,
                'text-yellow-200':
                    index === Math.ceil(rating) && !Number.isInteger(rating),
                'text-gray-300': index > rating,
            }"
            @click="setRating(index)"
        >
            <template
                v-if="
                    index <= fullRating ||
                    (index === Math.ceil(rating) && !Number.isInteger(rating))
                "
            >
                ★
            </template>
            <template v-else> ☆ </template>
        </span>
        <p v-if="rating > 0" class="ml-4 text-lg text-gray-700">
            You rated this course: {{ rating }} stars
        </p>
    </div>
</template>

<script>
export default {
    name: "Rating",
    props: {
        initialRating: {
            type: Number,
            default: 0,
        },
        maxStars: {
            type: Number,
            default: 5,
        },
        allowHalf: {
            type: Boolean,
            default: true, // Enable half-star ratings by default
        },
    },
    data() {
        return {
            rating: this.initialRating,
        };
    },
    computed: {
        fullRating() {
            return Math.floor(this.rating); // Get the full stars
        },
    },
    methods: {
        setRating(star) {
            if (
                this.allowHalf &&
                star - this.rating < 1 &&
                star - this.rating > 0
            ) {
                // Allow half-star if the difference is less than 1 but greater than 0
                this.rating = Math.floor(star) + 0.5;
            } else {
                this.rating = star;
            }
        },
    },
};
</script>

<style scoped>
/* No additional scoped styles are required as Tailwind CSS is used */
</style>
