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
        <!-- ── Loading ── -->
        <div
            v-if="loading"
            class="flex items-center justify-center min-h-screen"
        >
            <div class="text-center">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-lime-50 rounded-full mb-4"
                >
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-lime-500"
                    ></div>
                </div>
                <p class="text-gray-500 font-medium">Loading article…</p>
            </div>
        </div>

        <!-- ── Article ── -->
        <article v-else-if="post" class="w-full">
            <!-- Back button -->
            <div class="max-w-5xl mx-auto px-6 pt-6 pb-2">
                <button
                    @click="$router.back()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all text-sm font-semibold text-gray-700 shadow-sm"
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
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    Back
                </button>
            </div>

            <!-- Hero card -->
            <div class="max-w-5xl mx-auto px-6 py-6">
                <div
                    class="border border-gray-200 rounded-2xl bg-white overflow-hidden shadow-sm"
                >
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="w-full md:w-1/2 flex-shrink-0 bg-gray-50">
                            <img
                                v-if="post.featured_image"
                                :src="post.featured_image"
                                :alt="post.title"
                                class="w-full h-full object-cover"
                                style="min-height: 320px; max-height: 460px"
                            />
                            <div
                                v-else
                                class="w-full flex items-center justify-center bg-gradient-to-br from-lime-50 to-green-100"
                                style="min-height: 320px"
                            >
                                <svg
                                    class="w-20 h-20 text-lime-300"
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
                        </div>

                        <!-- Meta -->
                        <div
                            class="w-full md:w-1/2 flex flex-col justify-center px-8 py-10"
                        >
                            <!-- Category badge -->
                            <span
                                class="inline-block mb-5 px-4 py-1.5 w-fit rounded-full text-sm font-bold bg-lime-500 text-white"
                            >
                                {{ post.category.name }}
                            </span>

                            <!-- Title -->
                            <h1
                                class="text-3xl md:text-4xl font-extrabold leading-tight text-gray-900 mb-6"
                            >
                                {{ post.title }}
                            </h1>

                            <!-- Author / date / read time -->
                            <div
                                class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-600"
                            >
                                <span class="flex items-center gap-1.5">
                                    <svg
                                        class="w-4 h-4 text-gray-400"
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
                                    {{ post.author?.name || "Admin" }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg
                                        class="w-4 h-4 text-gray-400"
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
                                    class="flex items-center gap-1.5"
                                >
                                    <svg
                                        class="w-4 h-4 text-gray-400"
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Article body -->
            <div class="max-w-3xl mx-auto px-6 pb-16">
                <!-- Excerpt -->
                <div v-if="post.excerpt" class="mb-10">
                    <div
                        class="border-l-4 border-lime-500 bg-lime-50 pl-6 pr-4 py-5 rounded-r-lg"
                    >
                        <p
                            class="text-base md:text-lg text-gray-700 italic leading-relaxed"
                        >
                            {{ post.excerpt }}
                        </p>
                    </div>
                </div>

                <!-- Main content -->
                <div class="prose max-w-none mb-12" v-html="post.content"></div>

                <!-- Divider -->
                <hr class="border-gray-200 mb-10" />

                <!-- Tags -->
                <div v-if="post.tags.length" class="mb-8">
                    <p
                        class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3"
                    >
                        Tags
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in post.tags"
                            :key="tag.id"
                            @click.stop="goToTaggedPosts(tag.slug)"
                            class="px-4 py-1.5 rounded-full text-sm font-semibold border border-lime-400 text-lime-700 bg-lime-50 hover:bg-lime-100 transition-colors cursor-pointer"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
                </div>

                <!-- Share -->
                <div>
                    <p
                        class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3"
                    >
                        Share
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a
                            :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent($route.fullPath)}&text=${encodeURIComponent(post.title)}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold border border-lime-400 text-lime-700 bg-lime-50 hover:bg-lime-100 transition-colors"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                                />
                            </svg>
                            Twitter
                        </a>
                        <a
                            :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent($route.fullPath)}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold border border-lime-400 text-lime-700 bg-lime-50 hover:bg-lime-100 transition-colors"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
                                />
                            </svg>
                            LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </article>

        <!-- ── Error ── -->
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
                <p class="text-gray-500 mb-6">
                    The article you're looking for doesn't exist or has been
                    removed.
                </p>
                <button
                    @click="$router.push('/blog')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-lime-500 text-white rounded-xl hover:bg-lime-600 transition-colors font-semibold"
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
/* ── Prose (article body) ── */
.prose {
    color: #374151;
    line-height: 1.85;
    font-size: 1rem;
}
.prose h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    color: #111827;
}
.prose h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 2.25rem;
    margin-bottom: 1rem;
    color: #1f2937;
}
.prose h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    color: #374151;
}
.prose h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    color: #374151;
}
.prose p {
    margin-bottom: 1.5rem;
    line-height: 1.85;
}
.prose ul,
.prose ol {
    margin-bottom: 1.5rem;
    padding-left: 1.75rem;
}
.prose li {
    margin-bottom: 0.6rem;
    line-height: 1.7;
}
.prose a {
    color: #65a30d;
    text-decoration: underline;
    font-weight: 500;
}
.prose a:hover {
    color: #4d7c0f;
}
.prose strong {
    font-weight: 700;
    color: #111827;
}
.prose em {
    font-style: italic;
    color: #6b7280;
}

.prose img {
    border-radius: 0.75rem;
    margin: 2rem auto;
    display: block;
    max-width: 100%;
    max-height: 320px;
    object-fit: contain;
    background: #fff;
}

.prose blockquote {
    border-left: 4px solid #84cc16;
    padding: 1rem 1.5rem;
    font-style: italic;
    color: #6b7280;
    margin: 2rem 0;
    background-color: #f7fef0;
    border-radius: 0 0.5rem 0.5rem 0;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.2rem 0.45rem;
    border-radius: 0.3rem;
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
    background: transparent;
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
</style>
