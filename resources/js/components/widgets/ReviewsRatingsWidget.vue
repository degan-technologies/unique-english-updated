<script setup>
    import Axios from 'axios';
    import { ref, computed, onMounted, onUnmounted } from 'vue';

    const transactions = ref([]);

    const reviews = ref([]);
    const selectedRating = ref(null);
    const selectedReview = ref(null);
    const topBestSellers = ref([]);


    const fetchTransactions = async () => {
        try {
            const response = await Axios.get('/api/transaction');
            const data = response.data.data;

            transactions.value = data;

            const productMap = {}; 
            data.forEach(tx => {
                let key = null;
                let name = null;
                let type = null;
                let buyer = null;
                let averageRating = null;

                if (tx.type === 'course' && tx.course_id != null) {
                    key = `course-${tx.course_id}`;
                    name = tx.course_name;
                    type = 'course';
                    buyer = tx.course_owner;
                    averageRating = tx.course ? tx.course.averageRating : null;
                } else if (tx.type === 'book' && tx.book_id != null) {
                    key = `book-${tx.book_id}`;
                    name = tx.book_name;
                    type = 'book';
                    buyer = tx.book_owner;
                    averageRating = tx.book ? tx.book.averageRating : null;
                }

                if (key && name) {
                    if (!productMap[key]) {
                        productMap[key] = {
                            key,
                            name,
                            type,
                            sales: 0,
                            buyer,
                            averageRating,
                        };
                    }
                    productMap[key].sales += 1;
                }
            });

            const sorted = Object.values(productMap)
                .sort((a, b) => b.sales - a.sales)
                .slice(0, 10);

            topBestSellers.value = sorted;

            console.log("Top 10 Best Sellers:", topBestSellers.value);

        } catch (error) {
            console.error('Error fetching transactions:', error);
        }
    };

    const fetchReviews = async () => {
        await Axios
            .get('/api/feedbacks')
            .then((response) => {
                reviews.value = response.data.data;
            });
    };

    const filterByRating = (rating) => {
        selectedRating.value = selectedRating.value === rating ? null : rating;
    };

    // Compute filtered reviews based on selected rating
    const filteredReviews = computed(() => {
        return selectedRating.value
            ? reviews.value.filter(review => review.rating === selectedRating.value)
            : reviews.value;
    });

    // Calculate rating percentage for the distribution chart
    const getRatingPercentage = (star) => {
        if (reviews.value.length === 0) {
            return 0;
        }
        const count = reviews.value.filter(review => review.rating === star).length;
        return (count / reviews.value.length) * 100;
    };

    // Open the review details modal
    const openReviewDetails = (review) => {
        selectedReview.value = review;
    };

    // Auto-refresh reviews every 15 seconds
    let intervalId = null;
    onMounted(() => {
        fetchReviews();
        fetchTransactions();
        intervalId = setInterval(fetchReviews, 15000);
    });


    onUnmounted(() => {
        clearInterval(intervalId);
    });
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg w-full p-6 relative">
        <div v-if="topBestSellers.length"
            class="">
            <div class="flex items-center space-x-2 mb-4">
                <h3 class="text-lg font-bold text-gray-900"> 🏆 Top 10 Best Sellers </h3>
            </div>
            <div class="max-h-[400px] overflow-y-auto scrollable-container space-y-2 pr-1">
                <div class="space-y-3 ">
                    <div v-for="(item) in topBestSellers"
                        :key="item.key"
                        class="p-4 rounded-lg border border-gray-200 mb-3 cursor-pointer transition hover:bg-gray-100">
                        <div class="flex items-center justify-between mb-0.5">
                            <div class="text-xs text-gray-500 font-medium flex flex-row justify-between">
                                <p class="text-sm">Total Salse:</p>
                                <p class="font-bold text-sm">{{item.sales}}</p>
                            </div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium"
                                :class="item.type === 'course' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'">
                                {{ item.type === 'course' ? '🎓 Course' : '📚 Book' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <h4 class="text-md font-semibold">
                                {{ item.name }}
                            </h4>
                            
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-xs text-gray-600">
                                👤 
                                <span class="font-medium text-gray-800">{{ item.buyer }}</span>
                            </p>
                            <p class="text-sm text-gray-500">
                                ⭐ 
                                <span class="font-semibold text-gray-800">{{ item.averageRating }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .scrollable-container {
        overflow-y: auto;
    }

    .scrollable-container {
        scrollbar-width: thin;
        scrollbar-color: #A0AEC0 #F7FAFC;

    }

    .scrollable-container::-webkit-scrollbar {
        width: 4px;
    }

    .scrollable-container::-webkit-scrollbar-thumb {
        background-color: #A0AEC0;
        border-radius: 5px;
    }

    .scrollable-container::-webkit-scrollbar-track {
        background-color: #F7FAFC;
    }
</style>
