<template>
    <div>
        <div
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6"
        >
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search posts..."
                    class="px-4 py-2 border rounded-lg flex-1 sm:flex-none sm:min-w-[200px]"
                    @input="debouncedSearch"
                />
                <select
                    v-model="statusFilter"
                    @change="fetchPosts(1)"
                    class="px-4 py-2 border rounded-lg"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <button
                @click="openPostForm()"
                class="px-4 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 flex items-center justify-center gap-2 whitespace-nowrap"
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
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                <span class="hidden sm:inline">New Post</span>
                <span class="sm:hidden">New</span>
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Image
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Title
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Category
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Views
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="post in posts"
                            :key="post.id"
                            class="hover:bg-gray-50 relative"
                        >
                            <td class="px-6 py-4">
                                <img
                                    v-if="post.featured_image"
                                    :src="post.featured_image"
                                    alt="Featured"
                                    class="w-16 h-16 object-cover rounded-lg"
                                />
                                <div
                                    v-else
                                    class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-8 h-8 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ post.title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    by {{ post.author.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ post.category.name }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        post.status === 'published'
                                            ? 'bg-green-100 text-green-800'
                                            : post.status === 'draft'
                                              ? 'bg-yellow-100 text-yellow-800'
                                              : 'bg-gray-100 text-gray-800',
                                    ]"
                                >
                                    {{ post.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ post.views_count }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(post.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-sm relative">
                                <button
                                    @click="toggleMenu(post.id)"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-600"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"
                                        />
                                    </svg>
                                </button>
                                <div
                                    v-if="activeMenu === post.id"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                >
                                    <button
                                        @click="
                                            openPostForm(post);
                                            activeMenu = null;
                                        "
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 text-lime-700 hover:text-lime-800 font-medium flex items-center gap-2 rounded-t-lg"
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
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                        Edit
                                    </button>
                                    <button
                                        @click="
                                            deletePost(post.id);
                                            activeMenu = null;
                                        "
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 hover:text-red-800 font-medium flex items-center gap-2 rounded-b-lg"
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
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="!posts.length && !loading"
                class="text-center py-12 text-gray-500"
            >
                No posts found
            </div>

            <div
                v-if="pagination.total"
                class="px-6 py-4 border-t border-gray-200"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4 w-full"
                    >
                        <div class="text-sm text-gray-700">
                            Showing {{ pagination.from || 0 }} to
                            {{ pagination.to || 0 }} of
                            {{ pagination.total || 0 }} results
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600"
                                >Rows per page:</label
                            >
                            <select
                                v-model="perPage"
                                @change="fetchPosts(1)"
                                class="px-2 py-1 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-lime-700"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="12">12</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div
                        v-if="pagination.last_page > 1"
                        class="flex gap-2 w-full sm:w-auto justify-start sm:justify-end items-center"
                    >
                        <button
                            @click="fetchPosts(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>

                        <div
                            class="px-3 py-1 border rounded-lg bg-gray-100 text-sm text-gray-700"
                        >
                            Page {{ pagination.current_page }} of
                            {{ pagination.last_page }}
                        </div>

                        <button
                            @click="fetchPosts(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page === pagination.last_page
                            "
                            class="px-3 py-1 border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <PostFormModal
            v-if="showModal"
            :editing="editingPost"
            :categories="categories"
            :tags="tags"
            @close="closeModal"
            @save="savePost"
        />

        <ConfirmDialog
            v-if="showConfirm"
            :message="confirmMessage"
            :submitting="submitting"
            @confirm="confirmDelete"
            @cancel="showConfirm = false"
        />

        <ToastNotification
            v-if="showToast"
            :message="toastMessage"
            :type="toastType"
        />
    </div>
</template>

<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";
import ConfirmDialog from "./components/ConfirmDialog.vue";
import PostFormModal from "./components/PostFormModal.vue";
import ToastNotification from "./components/ToastNotification.vue";

const props = defineProps({
    categories: Array,
    tags: Array,
});

const posts = ref([]);
const loading = ref(false);
const submitting = ref(false);
const search = ref("");
const statusFilter = ref("");
const perPage = ref(12);
const pagination = ref({});
const activeMenu = ref(null);
const showModal = ref(false);
const editingPost = ref(null);
const showConfirm = ref(false);
const confirmMessage = ref("");
const deleteId = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchPosts(1), 500);
};

const fetchPosts = async (page = 1) => {
    loading.value = true;
    try {
        const params = { page, per_page: perPage.value };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;

        const response = await axios.get("/api/blog/posts", { params });
        posts.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        console.error("Error fetching posts:", error);
        displayToast("Error fetching posts", "error");
    } finally {
        loading.value = false;
    }
};

const toggleMenu = (id) => {
    activeMenu.value = activeMenu.value === id ? null : id;
};

const openPostForm = (post = null) => {
    editingPost.value = post;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingPost.value = null;
};

const savePost = async (formData) => {
    submitting.value = true;
    try {
        if (editingPost.value) {
            formData.append("_method", "PUT");
            await axios.post(
                `/api/blog/posts/${editingPost.value.id}`,
                formData,
                {
                    headers: { "Content-Type": "multipart/form-data" },
                },
            );
        } else {
            await axios.post("/api/blog/posts", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        }
        displayToast("Post saved successfully!", "success");
        closeModal();
        fetchPosts();
    } catch (error) {
        console.error("Error saving post:", error);
        displayToast("Error saving post. Please try again.", "error");
    } finally {
        submitting.value = false;
    }
};

const deletePost = (id) => {
    deleteId.value = id;
    confirmMessage.value =
        "Are you sure you want to delete this post? This action cannot be undone.";
    showConfirm.value = true;
};

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/blog/posts/${deleteId.value}`);
        displayToast("Post deleted successfully", "success");
        showConfirm.value = false;
        fetchPosts();
    } catch (error) {
        console.error("Error deleting post:", error);
        displayToast("Error deleting post", "error");
    }
};

const displayToast = (message, type = "success") => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

onMounted(() => {
    fetchPosts();
});
</script>
