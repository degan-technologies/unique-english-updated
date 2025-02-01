import { defineStore } from "pinia";

export const useCartStore = defineStore("cart", {
  
  state: () => ({
    items: [],
  }),
  getters: {
    itemCount: (state) => state.items.length,
  },
  actions: {
    addToCart(course) {
      const exists = this.items.some((item) => item.id === course.id);
      if (!exists) {
        this.items.push(course);
        return { success: true, message: "Course added to cart" };
      } else {
        return { success: false, message: "Course already in cart" };
      }
    },
    removeFromCart(courseId) {
      const index = this.items.findIndex((item) => item.id === courseId);
      if (index !== -1) {
        this.items.splice(index, 1);
        return { success: true, message: "Course removed from cart" };
      } else {
        return { success: false, message: "Course not found in cart" };
      }
    },
  },
});
