<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();
const post = ref(null);
const loading = ref(false);

const fetchPost = async () => {
    loading.value = true;
    try {
        const slug = route.params.slug;
        const response = await axios.get(`/api/blog/posts/${slug}`);
        post.value = response.data.data;
    } catch (error) {
        console.error("Error fetching post:", error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const goToTaggedPosts = (tagSlug) => {
    router.push(`/blog?tag=${tagSlug}`);
};

onMounted(() => {
    fetchPost();
});
</script>
<template>
    <div class="blog-detail min-h-screen bg-white pt-20">
        <!-- Loading State -->
        <div
            v-if="loading"
            class="flex items-center justify-center min-h-screen"
        >
            <div class="text-center">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-lime-50 rounded-full mb-4"
                >
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-lime-600"
                    ></div>
                </div>
                <p class="text-gray-600 font-medium">Loading article...</p>
            </div>
        </div>

        <!-- Blog Content -->
        <article v-else-if="post" class="w-full">
            <!-- Navigation -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="max-w-5xl mx-auto flex justify-start">
                    <button
                        @click="$router.back()"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-white border border-lime-200 rounded-lg hover:border-lime-400 hover:text-lime-600 transition-all font-semibold text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-lime-300"
                    >
                        <span
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-lime-100 text-lime-600 mr-2"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>
                        </span>
                        <span>Back</span>
                    </button>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="relative w-full flex justify-center items-center mt-6">
                <div
                    class="relative w-full max-w-4xl border border-gray-200 rounded-2xl bg-white"
                >
                    <div
                        class="flex flex-col md:flex-row items-center md:items-end gap-0 md:gap-8"
                    >
                        <div
                            class="w-full md:w-2/5 flex justify-center items-end p-4 md:p-8"
                        >
                            <template v-if="post.featured_image">
                                <img
                                    :src="post.featured_image"
                                    :alt="post.title"
                                    class="rounded-xl object-contain w-full max-h-56 md:max-h-72 bg-white border"
                                    style="background: #fff"
                                />
                            </template>
                            <template v-else>
                                <div
                                    class="flex items-center justify-center rounded-xl bg-gray-100 border w-full max-h-56 md:max-h-72 h-40 md:h-56"
                                >
                                    <svg
                                        class="w-16 h-16 text-gray-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 48 48"
                                    >
                                        <rect
                                            x="8"
                                            y="8"
                                            width="32"
                                            height="32"
                                            rx="6"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            fill="none"
                                        />
                                        <path
                                            d="M16 32l6-8 6 8 6-12"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            fill="none"
                                        />
                                        <circle
                                            cx="18"
                                            cy="20"
                                            r="2"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div
                            class="w-full md:w-3/5 px-6 py-8 text-gray-900 flex flex-col justify-end"
                        >
                            <span
                                class="inline-block mb-4 px-4 py-1.5 w-fit rounded-full text-sm font-semibold bg-lime-500 text-white"
                            >
                                {{ post.category.name }}
                            </span>
                            <h1
                                class="text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight mb-4 text-gray-900"
                            >
                                {{ post.title }}
                            </h1>
                            <div
                                class="flex flex-wrap items-center gap-4 text-sm text-gray-700"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-full bg-lime-500 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-4 h-4 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                    </div>
                                    <span>{{
                                        post.author.name || "Admin"
                                    }}</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <span>{{ formatDate(post.created_at) }}</span>
                                <span class="text-gray-300">|</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Article Content -->
            <div class="w-full bg-transparent">
                <div class="max-w-4xl mx-auto px-6 py-12">
                    <!-- Excerpt -->
                    <div v-if="post.excerpt" class="mb-12">
                        <div
                            class="text-xl md:text-2xl text-gray-700 leading-relaxed font-light italic border-l-4 border-lime-500 pl-6 bg-lime-50 py-6 rounded-r-lg"
                        >
                            {{ post.excerpt }}
                        </div>
                    </div>
                    <!-- Main Content -->
                    <div
                        class="prose prose-lg prose-gray max-w-none mb-12"
                        v-html="post.content"
                    ></div>
                    <!-- Tags -->
                    <div v-if="post.tags.length" class="border-t pt-8">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-gray-700 font-semibold text-sm"
                                >Tagged in:</span
                            >
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    @click.stop="goToTaggedPosts(tag.slug)"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-gray-100 text-gray-700 hover:bg-lime-100 hover:text-lime-700 transition-colors cursor-pointer"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Error State -->
        <div v-else class="flex items-center justify-center min-h-screen">
            <div class="text-center max-w-md mx-auto px-6">
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
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                        />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Article not found
                </h2>
                <p class="text-gray-600 mb-6">
                    The article you're looking for doesn't exist or has been
                    removed.
                </p>
                <button
                    @click="$router.push('/blog')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-lime-600 text-white rounded-xl hover:bg-lime-700 transition-colors font-semibold"
                >
                    Browse Articles
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
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.prose {
    color: #374151;
    line-height: 1.8;
}

.prose h1 {
    font-size: 2.25rem;
    font-weight: 800;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    color: #111827;
}

.prose h2 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    color: #1f2937;
}

.prose h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #374151;
}

.prose h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #374151;
}

.prose p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
}

.prose ul,
.prose ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.prose li {
    margin-bottom: 0.75rem;
    line-height: 1.7;
}

.prose a {
    color: #84cc16;
    text-decoration: underline;
    font-weight: 500;
}

.prose a:hover {
    color: #65a30d;
}

.prose img {
    border-radius: 1rem;
    margin: 2rem auto;
    display: block;
    max-width: 100%;
    max-height: 320px;
    object-fit: contain;
    background: #fff;
}

.prose blockquote {
    border-left: 4px solid #84cc16;
    padding-left: 1.5rem;
    padding-top: 1rem;
    padding-bottom: 1rem;
    font-style: italic;
    color: #6b7280;
    margin: 2rem 0;
    background-color: #f9fafb;
    border-radius: 0 0.5rem 0.5rem 0;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #dc2626;
}

.prose pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.75rem;
    overflow-x: auto;
    margin: 2rem 0;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.prose pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
    font-weight: normal;
}

.prose table {
    width: 100%;
    margin: 2rem 0;
    border-collapse: collapse;
}

.prose th,
.prose td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.prose th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.prose strong {
    font-weight: 700;
    color: #111827;
}

.prose em {
    font-style: italic;
    color: #6b7280;
}
</style>
