import axios from "axios";
import { defineStore } from "pinia";

export const useBlogStore = defineStore("blog", {
    state: () => ({
        posts: [],
        currentPost: null,
        categories: [],
        tags: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchPosts(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get("/api/blog/posts", { params });
                this.posts = response.data.data;
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchPost(slug) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get(`/api/blog/posts/${slug}`);
                this.currentPost = response.data.data;
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const response = await axios.get("/api/blog/categories");
                this.categories = response.data;
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            }
        },

        async fetchTags() {
            try {
                const response = await axios.get("/api/blog/tags");
                this.tags = response.data;
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            }
        },

        async createPost(formData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post("/api/blog/posts", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updatePost(id, formData) {
            this.loading = true;
            this.error = null;
            try {
                formData.append("_method", "PUT");
                const response = await axios.post(
                    `/api/blog/posts/${id}`,
                    formData,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    },
                );
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deletePost(id) {
            this.loading = true;
            this.error = null;
            try {
                await axios.delete(`/api/blog/posts/${id}`);
                this.posts = this.posts.filter((post) => post.id !== id);
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.loading = false;
            }
        },
    },

    getters: {
        publishedPosts: (state) =>
            state.posts.filter((post) => post.status === "published"),
        draftPosts: (state) =>
            state.posts.filter((post) => post.status === "draft"),
    },
});
