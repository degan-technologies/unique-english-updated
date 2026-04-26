<script setup>
import Axios from "axios";
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const loading = ref(false);
const posts = ref([]);
const errorMessage = ref("");

const hasPosts = computed(() => Array.isArray(posts.value) && posts.value.length > 0);

const formatDate = (value) => {
    if (!value) {
        return "";
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return "";
    }

    return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const summarize = (content, limit = 150) => {
    if (!content) {
        return "";
    }

    const plain = String(content).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    return plain.length > limit ? `${plain.slice(0, limit)}...` : plain;
};

const getAuthorName = (post) => {
    const author = post?.author;
    if (!author) {
        return "Unique English Team";
    }

    const fullName = (author.full_name || "").trim();
    if (fullName !== "") {
        return fullName;
    }

    const fallback = [author.first_name, author.middle_name, author.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return fallback || "Unique English Team";
};

const fetchBlogs = async () => {
    loading.value = true;
    errorMessage.value = "";

    try {
        const response = await Axios.get("/api/blogs", {
            params: { per_page: 3 },
        });

        posts.value = response?.data?.data || [];
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || "Unable to load blog posts right now.";
        posts.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchBlogs);

const openPost = (slug) => {
    router.push({ name: "blogDetail", params: { slug } });
};

const openBlogList = () => {
    router.push({ name: "blogList" });
};
</script>

<template>
    <section class="p-4 mt-20 md:p-8 max-w-7xl mx-auto bg-white rounded-2xl border border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-sky-600 font-semibold">Insights</p>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">From Our Blog</h2>
                </div>
                <button
                    class="text-sm font-semibold text-lime-700 hover:text-lime-600"
                    @click="openBlogList"
                >
                    View all articles
                </button>
            </div>

            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="item in 3"
                    :key="item"
                    class="h-72 rounded-2xl bg-slate-100 animate-pulse"
                ></div>
            </div>

            <div
                v-else-if="errorMessage"
                class="rounded-2xl border border-red-200 bg-red-50 text-red-700 p-5"
            >
                {{ errorMessage }}
            </div>

            <div v-else-if="hasPosts" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                    @click="openPost(post.slug)"
                >
                    <img
                        v-if="post.cover_image"
                        :src="post.cover_image"
                        :alt="post.title"
                        class="w-full h-48 object-cover"
                    />
                    <div class="p-5">
                        <p class="text-xs text-slate-500 mb-2">
                            {{ formatDate(post.published_at || post.created_at) }}
                            <span class="mx-2">•</span>
                            {{ getAuthorName(post) }}
                        </p>

                        <h3 class="text-xl font-semibold text-slate-900 line-clamp-2 min-h-[3.5rem]">
                            {{ post.title }}
                        </h3>

                        <p class="text-slate-600 mt-3 whitespace-pre-line">
                            {{ post.excerpt || summarize(post.content) }}
                        </p>
                    </div>
                </article>
            </div>

            <div v-else class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
                No blog posts are published yet.
            </div>
        </div>
    </section>
</template>
