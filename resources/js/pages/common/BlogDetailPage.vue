<script setup>
import Axios from "axios";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

import Header from "@/components/Layout/Header.vue";
import Footer from "@/components/Layout/Footer.vue";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const loadingRelated = ref(false);
const errorMessage = ref("");

const post = ref(null);
const relatedPosts = ref([]);

const formattedContent = computed(() => {
    const content = post.value?.content || "";

    if (!content) {
        return "";
    }

    // If the content already contains HTML tags, render it as-is.
    if (/<\/?[a-z][\s\S]*>/i.test(content)) {
        return content;
    }

    const normalized = String(content).replace(/\r\n/g, "\n").trim();

    if (!normalized) {
        return "";
    }

    // Convert plain text into paragraphs and preserve single line breaks.
    return normalized
        .split(/\n{2,}/)
        .filter(Boolean)
        .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, "<br />")}</p>`)
        .join("");
});

const readMinutes = computed(() => {
    const content = post.value?.content || "";
    const plain = String(content).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    if (!plain) {
        return 1;
    }

    const words = plain.split(" ").length;
    return Math.max(1, Math.round(words / 200));
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

const getAuthorName = () => {
    const author = post.value?.author;
    if (!author) {
        return "Unique English Team";
    }

    return author.full_name || [author.first_name, author.middle_name, author.last_name].filter(Boolean).join(" ") || "Unique English Team";
};

const summarize = (content, limit = 70) => {
    if (!content) {
        return "";
    }

    const plain = String(content).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    return plain.length > limit ? `${plain.slice(0, limit)}...` : plain;
};

const escapeHtml = (value) => {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

const openPost = (slug) => {
    router.push({ name: "blogDetail", params: { slug } });
};

const loadRelated = async (excludeSlug) => {
    loadingRelated.value = true;

    try {
        const response = await Axios.get("/api/blogs", { params: { per_page: 4 } });
        const allPosts = response?.data?.data || [];
        relatedPosts.value = allPosts.filter((item) => item.slug !== excludeSlug).slice(0, 3);
    } catch {
        relatedPosts.value = [];
    } finally {
        loadingRelated.value = false;
    }
};

const loadPost = async () => {
    loading.value = true;
    errorMessage.value = "";
    post.value = null;

    try {
        const { slug } = route.params;
        const response = await Axios.get(`/api/blogs/${slug}`);
        post.value = response?.data?.data || null;

        await loadRelated(slug);
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || "Unable to load this article.";
        post.value = null;
        relatedPosts.value = [];
    } finally {
        loading.value = false;
    }
};

watch(
    () => route.params.slug,
    () => {
        loadPost();
    },
);

onMounted(loadPost);
</script>

<template>
    <div class="p-4 mt-20 md:p-8 max-w-7xl mx-auto bg-white rounded-2xl border border-stone-200">
        <Header />
        <main class=" pb-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="loading" class="space-y-6">
                    <div class="h-8 w-40 rounded bg-neutral-200 animate-pulse"></div>
                    <div class="h-12 w-full rounded bg-neutral-200 animate-pulse"></div>
                    <div class="h-80 w-full rounded-3xl bg-neutral-200 animate-pulse"></div>
                </div>

                <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700">
                    {{ errorMessage }}
                </div>

                <article v-else-if="post" >
                    <p class="text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        All articles
                        <span class="text-lime-500">SPEAKING</span>
                    </p>

                    <h1 class="font-serif text-4xl md:text-5xl leading-[1.08] mt-4">
                        {{ post.title }}
                    </h1>

                    <p class="text-neutral-700 leading-relaxed mt-5 text-lg">
                        {{ post.excerpt || summarize(post.content, 200) }}
                    </p>

                    <p class="text-sm text-neutral-500 mt-4">
                        By {{ getAuthorName() }}
                        <span>•</span>
                        {{ formatDate(post.published_at || post.created_at) }}
                        <span>•</span>
                        {{ readMinutes }} min read
                    </p>

                    <img
                        v-if="post.cover_image"
                        :src="post.cover_image"
                        :alt="post.title"
                        class="w-full rounded-3xl mt-8 h-[26rem] object-cover"
                    />

                    <div
                        class="prose prose-neutral max-w-none mt-8 text-[18px] leading-8 prose-headings:font-serif prose-headings:text-neutral-900 prose-p:text-neutral-800 prose-li:text-neutral-800"
                        v-html="formattedContent"
                    ></div>
                </article>
            </div>

            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
                <div class="border-t border-stone-300 pt-8">
                    <h2 class="font-serif text-3xl">Keep reading</h2>

                    <div v-if="loadingRelated" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div v-for="item in 3" :key="item" class="h-56 rounded-2xl bg-neutral-200 animate-pulse"></div>
                    </div>

                    <div v-else-if="relatedPosts.length" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <article
                            v-for="item in relatedPosts"
                            :key="item.id"
                            class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                            @click="openPost(item.slug)"
                        >
                            <img
                                v-if="item.cover_image"
                                :src="item.cover_image"
                                :alt="item.title"
                                class="h-36 w-full object-cover"
                            />
                            <div class="p-4">
                                <p class="text-[11px] uppercase tracking-[0.14em] text-lime-500">Grammar</p>
                                <h3 class="font-serif text-xl leading-snug mt-2 line-clamp-3 min-h-[5.2rem]">
                                    {{ item.title }}
                                </h3>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>
