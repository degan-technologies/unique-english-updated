<script setup>
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const posts = ref([]);
const categories = ref([]);
const loading = ref(false);
const selectedCategory = ref(null);
const selectedTag = ref(null);
const hasMore = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const categoryScroll = ref(null);
const showLeftScroll = ref(false);
const showRightScroll = ref(false);
const blogListSection = ref(null);

const fetchPosts = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            per_page: 6,
            page: page,
        };
        if (selectedCategory.value) {
            params.category = selectedCategory.value;
        }
        if (selectedTag.value) {
            params.tag = selectedTag.value;
        }

        const response = await axios.get("/api/blog/posts", { params });
        posts.value = response.data.data;
        currentPage.value = response.data.meta.current_page;
        totalPages.value = response.data.meta.last_page;
        hasMore.value = response.data.meta.total > 6;
    } catch (error) {
        console.error("Error fetching posts:", error);
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/blog/categories", {
            params: { per_page: 100 },
        });
        categories.value = response.data.data.filter((cat) => cat.is_active);
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

const viewPost = (slug) => {
    router.push(`/blog/${slug}`);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const stripHtml = (html) => {
    const tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
};

const clearTagFilter = () => {
    selectedTag.value = null;
    router.push("/blog");
    fetchPosts(1);
};

const scrollCategories = (direction) => {
    const container = categoryScroll.value;
    if (!container) return;

    const scrollAmount = 300;
    if (direction === "left") {
        container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
};

const checkScroll = () => {
    const container = categoryScroll.value;
    if (!container) return;

    showLeftScroll.value = container.scrollLeft > 0;
    showRightScroll.value =
        container.scrollLeft <
        container.scrollWidth - container.clientWidth - 10;
};

function scrollToBlogListSection() {
    // Use setTimeout to ensure DOM is updated
    setTimeout(() => {
        if (blogListSection.value) {
            blogListSection.value.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        }
    }, 100);
}

onMounted(() => {
    // Check if there's a tag query parameter
    const route = router.currentRoute.value;
    if (route.query.tag) {
        selectedTag.value = route.query.tag;
        scrollToBlogListSection();
    }

    fetchPosts();
    fetchCategories();

    // Check scroll buttons visibility after categories load
    setTimeout(() => {
        checkScroll();
    }, 100);
});

// Watch for tag changes (e.g., when navigating from BlogDetail)
watch(
    () => router.currentRoute.value.query.tag,
    (newTag, oldTag) => {
        if (newTag) {
            selectedTag.value = newTag;
            scrollToBlogListSection();
        }
    },
);
</script>
<template>
    <div class="blog-list min-h-screen bg-white pt-10">
        <!-- Blog List Anchor for Scroll -->
        <div ref="blogListSection"></div>
        <!-- Header Section -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="text-center mb-8">
                    <h1
                        class="text-4xl md:text-5xl font-bold text-gray-900 mb-4"
                    >
                        Learn & Grow
                    </h1>
                    <p
                        class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed"
                    >
                        Expert insights and practical tips to master English
                        faster
                    </p>
                </div>

                <!-- Category Filter Carousel -->
                <div class="relative">
                    <!-- Tag Filter Indicator -->
                    <div
                        v-if="selectedTag"
                        class="mb-4 flex items-center gap-2"
                    >
                        <span class="text-sm text-gray-600"
                            >Filtering by tag:</span
                        >
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-lime-100 text-lime-800 rounded-full text-sm font-semibold"
                        >
                            {{ selectedTag }}
                            <button
                                @click="clearTagFilter"
                                class="hover:bg-lime-200 rounded-full p-0.5 transition-colors"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </span>
                    </div>

                    <!-- Left Scroll Button -->
                    <button
                        v-if="showLeftScroll"
                        @click="scrollCategories('left')"
                        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white border-2 border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors"
                    >
                        <svg
                            class="w-5 h-5 text-gray-700"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>

                    <div
                        ref="categoryScroll"
                        @scroll="checkScroll"
                        class="overflow-x-auto scrollbar-hide"
                    >
                        <div class="flex gap-3 p-2 min-w-max">
                            <button
                                @click="
                                    selectedCategory = null;
                                    fetchPosts(1);
                                "
                                :class="[
                                    'px-6 py-3 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap',
                                    !selectedCategory
                                        ? 'bg-gray-900 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                ]"
                            >
                                All
                            </button>
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="
                                    selectedCategory = category.slug;
                                    fetchPosts(1);
                                "
                                :class="[
                                    'px-6 py-3 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap',
                                    selectedCategory === category.slug
                                        ? 'bg-gray-900 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                ]"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Right Scroll Button -->
                    <button
                        v-if="showRightScroll"
                        @click="scrollCategories('right')"
                        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white border-2 border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors"
                    >
                        <svg
                            class="w-5 h-5 text-gray-700"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-16">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-20">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-lime-50 rounded-full mb-4"
                    >
                        <div
                            class="animate-spin rounded-full h-8 w-8 border-b-2 border-lime-600"
                        ></div>
                    </div>
                    <p class="text-gray-600 font-medium">Loading articles...</p>
                </div>
            </div>

            <!-- Blog Posts Grid -->
            <div
                v-else-if="posts.length"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16"
            >
                <article
                    v-for="post in posts"
                    :key="post.id"
                    @click="viewPost(post.slug)"
                    class="group cursor-pointer bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg hover:border-gray-300 transition-all duration-300"
                >
                    <!-- Featured Image -->
                    <div
                        class="relative h-48 mb-6 overflow-hidden rounded-xl bg-gray-100 -mx-6 -mt-6 mb-6"
                    >
                        <img
                            v-if="post.featured_image"
                            :src="post.featured_image"
                            :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100"
                        >
                            <svg
                                class="w-12 h-12 text-lime-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                />
                            </svg>
                        </div>
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4">
                            <span
                                class="bg-white/90 backdrop-blur-sm text-lime-700 px-3 py-1.5 rounded-full text-xs font-semibold"
                            >
                                {{ post.category.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div>
                        <!-- Meta Info -->
                        <div
                            class="flex items-center gap-4 text-xs text-gray-500 mb-3"
                        >
                            <span>{{ formatDate(post.created_at) }}</span>
                        </div>

                        <!-- Title -->
                        <h3
                            class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-lime-600 transition-colors leading-tight"
                        >
                            {{ post.title }}
                        </h3>

                        <!-- Excerpt -->
                        <p
                            class="text-gray-600 text-sm line-clamp-3 mb-4 leading-relaxed"
                        >
                            {{
                                post.excerpt ||
                                stripHtml(post.content).substring(0, 120) +
                                    "..."
                            }}
                        </p>

                        <!-- Tags -->
                        <div
                            v-if="post.tags.length"
                            class="flex flex-wrap gap-2 mb-4"
                        >
                            <span
                                v-for="tag in post.tags.slice(0, 2)"
                                :key="tag.id"
                                class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full"
                            >
                                {{ tag.name }}
                            </span>
                        </div>

                        <!-- Read More -->
                        <div
                            class="flex items-center text-lime-600 font-semibold text-sm group-hover:gap-2 transition-all"
                        >
                            Read More
                            <svg
                                class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20">
                <div class="max-w-md mx-auto">
                    <div
                        class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6"
                    >
                        <svg
                            class="w-10 h-10 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                            />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        No articles yet
                    </h3>
                    <p class="text-gray-600">
                        We're working on creating amazing content for you. Check
                        back soon!
                    </p>
                </div>
            </div>

            <!-- Responsive Pagination Carousel -->
            <div
                v-if="posts.length && totalPages > 1"
                class="flex flex-wrap justify-center items-center gap-2 py-4"
            >
                <button
                    @click="fetchPosts(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-2 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                >
                    <svg
                        class="w-5 h-5 inline mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <button
                    :class="[
                        'px-4 py-2 rounded-lg transition-colors font-medium',
                        'bg-lime-600 text-white',
                    ]"
                    disabled
                >
                    {{ currentPage }}
                </button>
                <button
                    @click="fetchPosts(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-2 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                >
                    <svg
                        class="w-5 h-5 inline ml-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Smooth scrolling */
.overflow-x-auto {
    scroll-behavior: smooth;
}
</style>
