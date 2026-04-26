<script setup>
import Axios from "axios";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useToast } from "vue-toastification";

const toast = useToast();

const posts = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const showDeleteModal = ref(false);
const deletingPost = ref(null);

const search = ref("");
const filterStatus = ref("");
const rowsPerPageOptions = [5, 10, 15, 20];
const rowsPerPage = ref(10);
const currentPage = ref(1);
const totalPages = ref(1);
const pagination = ref({});

const isEditing = ref(false);
const showForm = ref(false);

const form = ref({
    id: null,
    title: "",
    excerpt: "",
    content: "",
    cover_image: "",
    cover_image_file: null,
    tags_text: "",
    status: "draft",
});
const imagePreviewUrl = ref("");
const selectedImageBlobUrl = ref("");
const fileInputRef = ref(null);
const fileInputKey = ref(0);

const submitLabel = computed(() => (isEditing.value ? "Update Post" : "Create Post"));
const totalBlogs = computed(() => Number(pagination.value?.total || 0));
const publishedBlogs = computed(() => posts.value.filter((post) => post.status === "published").length);
const draftBlogs = computed(() => posts.value.filter((post) => post.status === "draft").length);

const formatDate = (value) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return date.toISOString().split("T")[0];
};

const clearImagePreviewBlob = () => {
    if (selectedImageBlobUrl.value) {
        URL.revokeObjectURL(selectedImageBlobUrl.value);
        selectedImageBlobUrl.value = "";
    }
};

const resetFileInput = () => {
    fileInputKey.value += 1;
};

const resetForm = () => {
    clearImagePreviewBlob();
    isEditing.value = false;
    showForm.value = false;
    form.value = {
        id: null,
        title: "",
        excerpt: "",
        content: "",
        cover_image: "",
        cover_image_file: null,
        tags_text: "",
        status: "draft",
    };
    imagePreviewUrl.value = "";
    resetFileInput();
};

const parseTags = (value) => {
    if (!value || !value.trim()) {
        return [];
    }

    return value
        .split(",")
        .map((tag) => tag.trim())
        .filter(Boolean);
};

const fetchPosts = async (page = 1) => {
    loading.value = true;

    try {
        const response = await Axios.get("/api/blogs-manage", {
            params: {
                per_page: rowsPerPage.value,
                page,
                search: search.value || undefined,
                status: filterStatus.value || undefined,
            },
        });

        posts.value = response?.data?.data || [];
        pagination.value = response?.data?.pagination || {};
        currentPage.value = Number(pagination.value?.current_page || 1);
        totalPages.value = Number(pagination.value?.last_page || 1);
    } catch (error) {
        toast.error(error?.response?.data?.message || "Failed to fetch blog posts");
        posts.value = [];
        pagination.value = {};
        currentPage.value = 1;
        totalPages.value = 1;
    } finally {
        loading.value = false;
    }
};

const onNextPage = () => {
    if (currentPage.value >= totalPages.value) {
        return;
    }

    fetchPosts(currentPage.value + 1);
};

const onPreviousPage = () => {
    if (currentPage.value <= 1) {
        return;
    }

    fetchPosts(currentPage.value - 1);
};

const onRowsPerPageChange = () => {
    fetchPosts(1);
};

const openCreateForm = () => {
    resetForm();
    showForm.value = true;
};

const openEditForm = (post) => {
    clearImagePreviewBlob();
    isEditing.value = true;
    showForm.value = true;

    form.value = {
        id: post.id,
        title: post.title || "",
        excerpt: post.excerpt || "",
        content: post.content || "",
        cover_image: post.cover_image || "",
        cover_image_file: null,
        tags_text: Array.isArray(post.tags) ? post.tags.join(", ") : "",
        status: post.status || "draft",
    };
    imagePreviewUrl.value = post.cover_image || "";
    resetFileInput();
};

const handleImageUpload = (event) => {
    const file = event?.target?.files?.[0] || null;
    form.value.cover_image_file = file;

    if (file) {
        clearImagePreviewBlob();
        const blobUrl = URL.createObjectURL(file);
        selectedImageBlobUrl.value = blobUrl;
        imagePreviewUrl.value = blobUrl;
    }
};

const savePost = async () => {
    if (!form.value.title.trim() || !form.value.content.trim()) {
        toast.error("Title and content are required");
        return;
    }

    saving.value = true;

    const payload = new FormData();
    payload.append("title", form.value.title);
    payload.append("excerpt", form.value.excerpt || "");
    payload.append("content", form.value.content);
    payload.append("status", form.value.status);

    const tags = parseTags(form.value.tags_text);
    tags.forEach((tag, index) => {
        payload.append(`tags[${index}]`, tag);
    });

    if (form.value.cover_image_file) {
        payload.append("cover_image_file", form.value.cover_image_file);
    }

    try {
        if (isEditing.value) {
            await Axios.post(`/api/blogs/${form.value.id}`, payload);
            toast.success("Blog post updated successfully");
        } else {
            await Axios.post("/api/blogs", payload);
            toast.success("Blog post created successfully");
        }

        resetForm();
        await fetchPosts(currentPage.value);
    } catch (error) {
        toast.error(error?.response?.data?.message || "Failed to save blog post");
    } finally {
        saving.value = false;
    }
};

const setStatus = async (post, status) => {
    try {
        await Axios.post(`/api/blogs/${post.id}/status`, { status });
        toast.success(`Post moved to ${status}`);
        await fetchPosts(currentPage.value);
    } catch (error) {
        toast.error(error?.response?.data?.message || "Failed to update post status");
    }
};

const confirmDelete = (post) => {
    deletingPost.value = post;
    showDeleteModal.value = true;
};

const deletePost = async () => {
    if (!deletingPost.value) {
        return;
    }

    deleting.value = true;

    try {
        await Axios.delete(`/api/blogs/${deletingPost.value.id}`);
        toast.success("Blog post deleted successfully");
        showDeleteModal.value = false;
        deletingPost.value = null;
        await fetchPosts(currentPage.value);
    } catch (error) {
        toast.error(error?.response?.data?.message || "Failed to delete blog post");
    } finally {
        deleting.value = false;
    }
};

onMounted(fetchPosts);

onBeforeUnmount(() => {
    clearImagePreviewBlob();
});
</script>

<template>
    <div class="min-h-screen">
        <header class="bg-white shadow-sm px-6 py-4 border-b sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-800">Blog Management</h1>
            <nav class="text-gray-500 text-sm mt-1">
                <ol class="list-reset flex">
                    <li>Dashboard</li>
                    <li><span class="mx-2">/</span></li>
                    <li>Blogs</li>
                </ol>
            </nav>
        </header>

        <div class="pt-6">
            <div v-if="showForm" class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ isEditing ? "Edit Blog Post" : "Create Blog Post" }}</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea
                            v-model="form.excerpt"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cover Image Upload</label>
                        <input
                            :key="fileInputKey"
                            ref="fileInputRef"
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                        <p class="text-xs text-gray-500 mt-1">Upload JPG, PNG, WEBP, or GIF (max 4MB).</p>
                        <p v-if="form.cover_image_file" class="text-xs text-lime-700 mt-1">
                            Selected: {{ form.cover_image_file.name }}
                        </p>
                        <img
                            v-if="imagePreviewUrl"
                            :src="imagePreviewUrl"
                            alt="Current cover"
                            class="mt-2 h-20 w-32 object-cover rounded-md border border-gray-200"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <input
                            v-model="form.tags_text"
                            type="text"
                            placeholder="speaking, grammar, exam"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <textarea
                            v-model="form.content"
                            rows="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                            placeholder="Write article content. You can use simple HTML tags if needed."
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button
                        @click="resetForm"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                    >
                        Back To List
                    </button>
                    <button
                        @click="savePost"
                        :disabled="saving"
                        class="px-4 py-2 rounded-lg bg-lime-600 text-white hover:bg-lime-700 disabled:opacity-70"
                    >
                        {{ saving ? "Saving..." : submitLabel }}
                    </button>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div class="w-full bg-white py-2 flex flex-wrap items-center justify-between gap-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full md:w-auto md:min-w-[740px]">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by title or content"
                            class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />

                        <select
                            v-model="filterStatus"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option value="">All statuses</option>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>

                        <div class="flex gap-2 justify-between">
                            <button
                                @click="fetchPosts(1)"
                                class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800"
                            >
                                Filter
                            </button>
                            <button
                                @click="openCreateForm"
                                class="px-4 py-2 rounded-lg bg-lime-600 text-white hover:bg-lime-700"
                            >
                                New Post
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div v-if="loading" class="p-8 text-center text-gray-500">Loading blog posts...</div>

                    <div v-else-if="posts.length === 0" class="p-8 text-center text-gray-500">
                        No blog posts found.
                    </div>

                    <div v-else class="overflow-x-auto scrollbar">
                        <table class="min-w-[1200px] divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Blog</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Published Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Created Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2">
                                        <img
                                            :src="post.cover_image || '/images/default-hero.jpg'"
                                            alt="Blog Image"
                                            class="w-12 h-12 rounded-md object-cover"
                                        />
                                        <div>
                                            <p class="font-semibold text-gray-900 whitespace-nowrap">{{ post.title }}</p>
                                            <p class="text-xs text-gray-500 mt-1 whitespace-nowrap">{{ post.slug }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            :class="[
                                                'px-2 py-1 rounded-full text-xs font-semibold',
                                                post.status === 'published'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : post.status === 'archived'
                                                    ? 'bg-gray-200 text-gray-700'
                                                    : 'bg-amber-100 text-amber-700',
                                            ]"
                                        >
                                            {{ post.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(post.published_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(post.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
                                            <button
                                                @click="openEditForm(post)"
                                                class="bg-white border border-gray-200 px-3 py-1 rounded text-xs text-black hover:bg-gray-200 transition"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                v-if="post.status !== 'published'"
                                                @click="setStatus(post, 'published')"
                                                class="px-3 py-1 text-xs rounded-md bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                                            >
                                                Publish
                                            </button>

                                            <button
                                                v-if="post.status !== 'draft'"
                                                @click="setStatus(post, 'draft')"
                                                class="px-3 py-1 text-xs rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200"
                                            >
                                                Draft
                                            </button>

                                            <button
                                                v-if="post.status !== 'archived'"
                                                @click="setStatus(post, 'archived')"
                                                class="px-3 py-1 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300"
                                            >
                                                Archive
                                            </button>

                                            <button
                                                @click="confirmDelete(post)"
                                                class="px-3 py-1 text-xs rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-white flex flex-row items-center justify-between border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                            <select
                                v-model="rowsPerPage"
                                @change="onRowsPerPageChange"
                                class="text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-lime-500"
                            >
                                <option v-for="option in rowsPerPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button
                                @click="onPreviousPage"
                                :disabled="currentPage === 1"
                                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                Prev
                            </button>

                            <span class="text-sm text-gray-600">Page {{ currentPage }} of {{ totalPages }}</span>

                            <button
                                @click="onNextPage"
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md bg-white rounded-xl border border-gray-200 shadow-xl p-5">
                <h3 class="text-lg font-semibold text-gray-900">Delete blog post?</h3>
                <p class="text-sm text-gray-600 mt-2">
                    This action cannot be undone. Post: <span class="font-semibold">{{ deletingPost?.title }}</span>
                </p>

                <div class="flex justify-end gap-2 mt-5">
                    <button
                        @click="showDeleteModal = false; deletingPost = null"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deletePost"
                        :disabled="deleting"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-70"
                    >
                        {{ deleting ? "Deleting..." : "Delete" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
