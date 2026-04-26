<script setup>
import Axios from "axios";
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
 
const router = useRouter();

const loading = ref(false);
const errorMessage = ref("");
const posts = ref([]);
const perPage = ref(12);
const currentPage = ref(1);
const totalPages = ref(1);
const totalPosts = ref(0);

const hasPosts = computed(() => Array.isArray(posts.value) && posts.value.length > 0);

const paginationPages = computed(() => {
    const maxVisible = 5;
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= maxVisible) {
        return Array.from({ length: total }, (_, index) => index + 1);
    }

    const half = Math.floor(maxVisible / 2);
    let start = current - half;
    let end = current + half;

    if (start < 1) {
        start = 1;
        end = maxVisible;
    }

    if (end > total) {
        end = total;
        start = total - maxVisible + 1;
    }

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
});

const formatDate = (value) => {
    if (!value) {
        return "";
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return "";
    }

    return date.toLocaleDateString(undefined, {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const summarize = (content, limit = 150) => {
    if (!content) {
        return "";
    }

    const plain = String(content).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    return plain.length > limit ? `${plain.slice(0, limit)}...` : plain;
};

const openPost = (slug) => {
    router.push({ name: "blogDetail", params: { slug } });
};

const goToPage = async (page) => {
    if (loading.value || page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    await loadPosts(page);
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const loadPosts = async (page = 1) => {
    loading.value = true;
    errorMessage.value = "";

    try {
        const response = await Axios.get("/api/blogs", {
            params: {
                per_page: perPage.value,
                page,
            },
        });

        const payload = response?.data || {};
        const meta = payload?.meta || payload;

        posts.value = Array.isArray(payload?.data) ? payload.data : [];
        currentPage.value = Number(meta?.current_page) || page;
        totalPages.value = Number(meta?.last_page) || 1;
        totalPosts.value = Number(meta?.total) || posts.value.length;
        perPage.value = Number(meta?.per_page) || perPage.value;
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || "Unable to load blog posts.";
        posts.value = [];
        currentPage.value = 1;
        totalPages.value = 1;
        totalPosts.value = 0;
    } finally {
        loading.value = false;
    }
};

onMounted(loadPosts);
</script>

<template>
    <div class="p-4 mt-20 md:p-8 max-w-7xl mx-auto bg-white rounded-2xl border border-stone-200">
        <div class="text-center my-8">
            <h1 class="text-4xl md:text-5xl font-black leading-tight text-slate-900 font-serif">
                Blog Posts
                <span class="text-lime-500 block">English Learning Articles</span>
            </h1>  
            <p  class="mx-auto text-center text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                Practical guides, expression breakdowns, and study tactics to help you sound natural and confident.
            </p>
        </div>

        <main class="pt-20 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div
                        v-for="item in 6"
                        :key="item"
                        class="h-80 rounded-2xl bg-neutral-200 animate-pulse"
                    ></div>
                </div>

                <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700">
                    {{ errorMessage }}
                </div>

                <div v-else-if="hasPosts">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <article
                            v-for="post in posts"
                            :key="post.id"
                            class="bg-white rounded-2xl overflow-hidden border border-stone-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                            @click="openPost(post.slug)"
                        >
                            <img
                                v-if="post.cover_image"
                                :src="post.cover_image"
                                :alt="post.title"
                                class="w-full h-52 object-cover"
                            />
                            <div class="p-5">
                                <p class="text-[11px] uppercase tracking-[0.16em] text-orange-500 mb-3">Article</p>
                                <h2 class="font-serif text-2xl leading-tight line-clamp-2 min-h-[4.1rem]">
                                    {{ post.title }}
                                </h2>
                                <p class="text-sm text-neutral-500 mt-3">
                                    {{ formatDate(post.published_at || post.created_at) }}
                                </p>
                                <p class="text-neutral-700 mt-3 line-clamp-3 min-h-[4.5rem]">
                                    {{ post.excerpt || summarize(post.content) }}
                                </p>
                            </div>
                        </article>
                    </div>

                    <div class="mt-10 flex flex-col items-center gap-4 md:flex-row md:justify-between">
                        <p class="text-sm text-neutral-600">
                            Showing page {{ currentPage }} of {{ totalPages }} ({{ totalPosts }} total posts)
                        </p>

                        <nav class="flex items-center gap-2" aria-label="Blog pagination">
                            <button
                                type="button"
                                class="px-3 py-2 rounded-lg border border-stone-300 text-sm text-neutral-700 hover:bg-stone-100 disabled:opacity-40 disabled:cursor-not-allowed"
                                :disabled="currentPage === 1 || loading"
                                @click="goToPage(currentPage - 1)"
                            >
                                Previous
                            </button>

                            <button
                                v-for="page in paginationPages"
                                :key="page"
                                type="button"
                                class="min-w-10 h-10 px-3 rounded-lg border text-sm transition-colors"
                                :class="page === currentPage
                                    ? 'border-lime-500 bg-lime-500 text-white'
                                    : 'border-stone-300 text-neutral-700 hover:bg-stone-100'"
                                :disabled="loading"
                                @click="goToPage(page)"
                            >
                                {{ page }}
                            </button>

                            <button
                                type="button"
                                class="px-3 py-2 rounded-lg border border-stone-300 text-sm text-neutral-700 hover:bg-stone-100 disabled:opacity-40 disabled:cursor-not-allowed"
                                :disabled="currentPage === totalPages || loading"
                                @click="goToPage(currentPage + 1)"
                            >
                                Next
                            </button>
                        </nav>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-stone-200 bg-white p-6 text-neutral-600">
                    No blog posts are published yet.
                </div>
            </div>
        </main>
    </div>
</template>
