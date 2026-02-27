<template>
    <div>
        <div
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6"
        >
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search tags..."
                    class="px-4 py-2 border rounded-lg flex-1 sm:flex-none sm:min-w-[200px]"
                    @input="debouncedSearch"
                />
            </div>
            <button
                @click="openTagForm()"
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
                <span class="hidden sm:inline">New Tag</span>
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
                                Tag Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Slug
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                            >
                                Posts Count
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
                            v-for="tag in tags"
                            :key="tag.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ tag.slug }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-800"
                                >
                                    {{ tag.posts_count || 0 }} posts
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm relative">
                                <button
                                    @click="toggleMenu(tag.id)"
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
                                    v-if="activeMenu === tag.id"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                >
                                    <button
                                        @click="
                                            openTagForm(tag);
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
                                            deleteTag(tag.id);
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
                v-if="!tags.length && !loading"
                class="text-center py-12 text-gray-500"
            >
                No tags found
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
                                @change="fetchTags(1)"
                                class="px-2 py-1 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-lime-700"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="15">15</option>
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
                            @click="fetchTags(pagination.current_page - 1)"
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
                            @click="fetchTags(pagination.current_page + 1)"
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

        <TagFormModal
            v-if="showModal"
            :editing="editingTag"
            @close="closeModal"
            @save="saveTag"
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
import TagFormModal from "./components/TagFormModal.vue";
import ToastNotification from "./components/ToastNotification.vue";

const tags = ref([]);
const loading = ref(false);
const submitting = ref(false);
const search = ref("");
const perPage = ref(15);
const pagination = ref({});
const activeMenu = ref(null);
const showModal = ref(false);
const editingTag = ref(null);
const showConfirm = ref(false);
const confirmMessage = ref("");
const deleteId = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchTags(1), 500);
};

const fetchTags = async (page = 1) => {
    loading.value = true;
    try {
        const params = { page, per_page: perPage.value };
        if (search.value) params.search = search.value;

        const response = await axios.get("/api/blog/tags", { params });
        tags.value = response.data.data;
        pagination.value = response.data.meta || {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
        };
    } catch (error) {
        console.error("Error fetching tags:", error);
        displayToast("Error fetching tags", "error");
    } finally {
        loading.value = false;
    }
};

const toggleMenu = (id) => {
    activeMenu.value = activeMenu.value === id ? null : id;
};

const openTagForm = (tag = null) => {
    editingTag.value = tag;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingTag.value = null;
};

const saveTag = async (tagData) => {
    submitting.value = true;
    try {
        if (editingTag.value) {
            await axios.put(`/api/blog/tags/${editingTag.value.id}`, tagData);
        } else {
            await axios.post("/api/blog/tags", tagData);
        }
        displayToast(
            `Tag ${editingTag.value ? "updated" : "created"} successfully!`,
            "success",
        );
        closeModal();
        fetchTags();
    } catch (error) {
        console.error("Error saving tag:", error);
        displayToast("Error saving tag. Please try again.", "error");
    } finally {
        submitting.value = false;
    }
};

const deleteTag = (id) => {
    deleteId.value = id;
    confirmMessage.value =
        "Are you sure you want to delete this tag? This action cannot be undone.";
    showConfirm.value = true;
};

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/blog/tags/${deleteId.value}`);
        displayToast("Tag deleted successfully", "success");
        showConfirm.value = false;
        fetchTags();
    } catch (error) {
        console.error("Error deleting tag:", error);
        displayToast("Error deleting tag", "error");
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

defineExpose({ fetchTags });

onMounted(() => {
    fetchTags();
});
</script>
