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
            per_page: 3,
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
    <div class="blog-list min-h-screen bg-white">
        <!-- Blog List Anchor for Scroll -->
        <div ref="blogListSection"></div>

        <!-- ── Hero Section ── -->
        <div
            class="hero-section relative overflow-hidden pt-20 pb-10 text-center"
        >
            <div class="relative z-10 max-w-3xl mx-auto px-6">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 bg-lime-500 text-white text-sm font-semibold px-5 py-2 rounded-full mb-6 shadow-sm"
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2l1.8 5.4H19l-4.5 3.3 1.7 5.3L12 13l-4.2 3 1.7-5.3L5 7.4h5.2z"
                        />
                    </svg>
                    Your Learning Journey
                </div>

                <!-- Heading -->
                <h1
                    class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-5 leading-tight"
                >
                    Learn &amp; Grow
                </h1>

                <!-- Subtitle -->
                <p
                    class="text-lg md:text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto"
                >
                    Expert insights and practical tips to master English faster.
                    Explore articles crafted by language professionals.
                </p>
            </div>
        </div>

        <!-- ── Category Filter Bar ── -->
        <div
            class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm"
        >
            <div class="max-w-7xl mx-auto px-6 py-4">
                <!-- Tag filter indicator -->
                <div v-if="selectedTag" class="mb-3 flex items-center gap-2">
                    <span class="text-sm text-gray-500">Filtering by tag:</span>
                    <span
                        class="inline-flex items-center gap-2 px-4 py-1.5 bg-lime-100 text-lime-800 rounded-full text-sm font-semibold"
                    >
                        {{ selectedTag }}
                        <button
                            @click="clearTagFilter"
                            class="hover:bg-lime-200 rounded-full p-0.5 transition-colors"
                        >
                            <svg
                                class="w-3.5 h-3.5"
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

                <!-- Carousel wrapper -->
                <div class="relative flex items-center gap-2">
                    <!-- Left arrow -->
                    <button
                        v-if="showLeftScroll"
                        @click="scrollCategories('left')"
                        class="flex-shrink-0 w-9 h-9 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 text-gray-600"
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

                    <!-- Pills -->
                    <div
                        ref="categoryScroll"
                        @scroll="checkScroll"
                        class="overflow-x-auto scrollbar-hide flex-1"
                    >
                        <div class="flex gap-2.5 py-1 min-w-max">
                            <button
                                @click="
                                    selectedCategory = null;
                                    fetchPosts(1);
                                "
                                :class="[
                                    'px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap border',
                                    !selectedCategory
                                        ? 'bg-lime-500 text-white border-lime-500 shadow-sm'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-lime-400 hover:text-lime-600',
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
                                    'px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap border',
                                    selectedCategory === category.slug
                                        ? 'bg-lime-500 text-white border-lime-500 shadow-sm'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-lime-400 hover:text-lime-600',
                                ]"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Right arrow -->
                    <button
                        v-if="showRightScroll"
                        @click="scrollCategories('right')"
                        class="flex-shrink-0 w-9 h-9 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 text-gray-600"
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

        <!-- ── Posts Grid ── -->
        <div class="max-w-7xl mx-auto px-6 py-14">
            <!-- Loading -->
            <div v-if="loading" class="flex justify-center py-24">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-lime-50 rounded-full mb-4"
                    >
                        <div
                            class="animate-spin rounded-full h-8 w-8 border-b-2 border-lime-500"
                        ></div>
                    </div>
                    <p class="text-gray-500 font-medium">Loading articles…</p>
                </div>
            </div>

            <!-- Grid -->
            <div
                v-else-if="posts.length"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-14"
            >
                <article
                    v-for="post in posts"
                    :key="post.id"
                    @click="viewPost(post.slug)"
                    class="group cursor-pointer bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-xl hover:border-gray-300 transition-all duration-300 flex flex-col"
                >
                    <!-- Image -->
                    <div
                        class="relative h-52 overflow-hidden bg-gray-100 flex-shrink-0"
                    >
                        <img
                            v-if="post.featured_image"
                            :src="post.featured_image"
                            :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-lime-50 to-green-100"
                        >
                            <svg
                                class="w-14 h-14 text-lime-300"
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
                        <!-- Category badge on image -->
                        <span
                            class="absolute top-3 left-3 bg-lime-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow"
                        >
                            {{ post.category.name }}
                        </span>
                    </div>

                    <!-- Card body -->
                    <div class="flex flex-col flex-1 p-6">
                        <!-- Meta row: date + read time -->
                        <div
                            class="flex items-center gap-4 text-xs text-gray-500 mb-3"
                        >
                            <span class="flex items-center gap-1">
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                {{ formatDate(post.created_at) }}
                            </span>
                            <span
                                v-if="post.reading_time"
                                class="flex items-center gap-1"
                            >
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                {{ post.reading_time }} min read
                            </span>
                        </div>

                        <!-- Title -->
                        <h3
                            class="text-lg font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-lime-600 transition-colors leading-snug"
                        >
                            {{ post.title }}
                        </h3>

                        <!-- Excerpt -->
                        <p
                            class="text-gray-500 text-sm line-clamp-3 leading-relaxed flex-1 mb-4"
                        >
                            {{
                                post.excerpt ||
                                stripHtml(post.content).substring(0, 130) + "…"
                            }}
                        </p>

                        <!-- Footer: tags + read more -->
                        <div
                            class="flex items-center justify-between mt-auto pt-3 border-t border-gray-100"
                        >
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="tag in post.tags.slice(0, 2)"
                                    :key="tag.id"
                                    class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 text-lime-600 font-semibold text-sm whitespace-nowrap group-hover:gap-2 transition-all"
                            >
                                Read More
                                <svg
                                    class="w-4 h-4 group-hover:translate-x-1 transition-transform"
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
                            </span>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-24">
                <div class="max-w-md mx-auto">
                    <div
                        class="w-20 h-20 bg-lime-50 rounded-full flex items-center justify-center mx-auto mb-6"
                    >
                        <svg
                            class="w-10 h-10 text-lime-400"
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
                    <p class="text-gray-500">
                        We're working on creating amazing content for you. Check
                        back soon!
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="posts.length && totalPages > 1"
                class="flex justify-center items-center gap-2 pt-4"
            >
                <button
                    @click="fetchPosts(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="w-9 h-9 flex items-center justify-center bg-white border border-gray-300 rounded-full hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
                >
                    <svg
                        class="w-4 h-4 text-gray-600"
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
                    class="w-9 h-9 flex items-center justify-center bg-lime-500 text-white rounded-full font-bold text-sm shadow-sm cursor-default select-none"
                    disabled
                >
                    {{ currentPage }}
                </button>

                <button
                    @click="fetchPosts(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="w-9 h-9 flex items-center justify-center bg-white border border-gray-300 rounded-full hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
                >
                    <svg
                        class="w-4 h-4 text-gray-600"
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
/* ── Hero blobs ── */
.hero-section {
    background-color: white;
}

/* ── Utilities ── */
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
.overflow-x-auto {
    scroll-behavior: smooth;
}
</style>
