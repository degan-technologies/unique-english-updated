<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="$emit('close')"
    >
        <div
            class="bg-white rounded-lg w-full max-w-sm sm:max-w-md md:max-w-2xl lg:max-w-3xl max-h-[90vh] overflow-y-auto"
        >
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">
                        {{ editing ? "Edit" : "Create" }} Post
                    </h2>
                    <button
                        @click="$emit('close')"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg
                            class="w-6 h-6"
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
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Title *</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Category *</label
                        >
                        <select
                            v-model="form.blog_category_id"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none"
                        >
                            <option value="">Select a category</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Excerpt</label
                        >
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Content *</label
                        >
                        <textarea
                            v-model="form.content"
                            rows="10"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none font-mono text-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Featured Image</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                            class="w-full px-4 py-2 border rounded-lg"
                        />
                        <img
                            v-if="imagePreview"
                            :src="imagePreview"
                            alt="Preview"
                            class="mt-3 w-full max-h-48 object-cover rounded-lg"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            >Tags</label
                        >
                        <div class="flex flex-wrap gap-2 mb-2">
                            <span
                                v-for="tagId in form.tags"
                                :key="tagId"
                                class="bg-lime-100 text-lime-800 px-3 py-1 rounded-full text-sm flex items-center gap-2"
                            >
                                {{ getTagName(tagId) }}
                                <button
                                    type="button"
                                    @click="removeTag(tagId)"
                                    class="text-lime-700 hover:text-lime-800"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                        <select
                            v-model="selectedTag"
                            @change="addTag"
                            class="w-full px-4 py-2 border rounded-lg"
                        >
                            <option value="">Add a tag</option>
                            <option
                                v-for="tag in tags"
                                :key="tag.id"
                                :value="tag.id"
                                :disabled="form.tags.includes(tag.id)"
                            >
                                {{ tag.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                >Status *</label
                            >
                            <select
                                v-model="form.status"
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-700 focus:outline-none"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="px-6 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 disabled:opacity-50"
                        >
                            {{ submitting ? "Saving..." : "Save Post" }}
                        </button>
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    editing: Object,
    categories: Array,
    tags: Array,
});

const emit = defineEmits(["close", "save"]);

const form = ref({
    title: "",
    blog_category_id: "",
    excerpt: "",
    content: "",
    featured_image: null,
    status: "draft",
    published_at: "",
    tags: [],
});

const selectedTag = ref("");
const imagePreview = ref("");
const submitting = ref(false);

watch(
    () => props.editing,
    (post) => {
        if (post) {
            form.value = {
                title: post.title,
                blog_category_id: post.category.id,
                excerpt: post.excerpt || "",
                content: post.content,
                status: post.status,
                published_at: post.published_at
                    ? post.published_at.slice(0, 16)
                    : "",
                tags: post.tags.map((t) => t.id),
            };
            if (post.featured_image) {
                imagePreview.value = post.featured_image;
            }
        } else {
            form.value = {
                title: "",
                blog_category_id: "",
                excerpt: "",
                content: "",
                featured_image: null,
                status: "draft",
                published_at: "",
                tags: [],
            };
            imagePreview.value = "";
        }
    },
    { immediate: true },
);

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.featured_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const addTag = () => {
    if (selectedTag.value && !form.value.tags.includes(selectedTag.value)) {
        form.value.tags.push(selectedTag.value);
    }
    selectedTag.value = "";
};

const removeTag = (tagId) => {
    form.value.tags = form.value.tags.filter((id) => id !== tagId);
};

const getTagName = (tagId) => {
    const tag = props.tags.find((t) => t.id === tagId);
    return tag ? tag.name : "";
};

const handleSubmit = () => {
    submitting.value = true;
    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (key === "tags") {
            form.value.tags.forEach((tagId) => {
                formData.append("tags[]", tagId);
            });
        } else if (
            key === "featured_image" &&
            form.value[key] instanceof File
        ) {
            formData.append(key, form.value[key]);
        } else if (form.value[key]) {
            formData.append(key, form.value[key]);
        }
    });
    emit("save", formData);
    submitting.value = false;
};
</script>
