<template>
    <div class="blog-admin">
        <div class="container mx-auto px-4 py-4 bg-white">
            <BlogHeader />

            <div class="mb-6 border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="switchTab(tab.id)"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors focus:outline-none',
                            activeTab === tab.id
                                ? 'border-lime-700 text-lime-700'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <div class="tab-content">
                <PostManagement
                    v-show="activeTab === 'posts'"
                    :categories="categories"
                    :tags="tags"
                />

                <CategoryManagement v-show="activeTab === 'categories'" />

                <TagManagement v-show="activeTab === 'tags'" />
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";
import CategoryManagement from "./CategoryManagement.vue";
import BlogHeader from "./components/BlogHeader.vue";
import PostManagement from "./PostManagement.vue";
import TagManagement from "./TagManagement.vue";

const activeTab = ref("posts");
const tabs = [
    { id: "posts", name: "Posts" },
    { id: "categories", name: "Categories" },
    { id: "tags", name: "Tags" },
];

const categories = ref([]);
const tags = ref([]);

const switchTab = (tabId) => {
    activeTab.value = tabId;
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/blog/categories", {
            params: { per_page: 1000 },
        });
        categories.value = response.data.data;
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

const fetchTags = async () => {
    try {
        const response = await axios.get("/api/blog/tags", {
            params: { per_page: 1000 },
        });
        tags.value = response.data.data;
    } catch (error) {
        console.error("Error fetching tags:", error);
    }
};

onMounted(() => {
    fetchCategories();
    fetchTags();
});
</script>
