import { defineStore } from "pinia";
import { ref, watch } from "vue";

export const useCartStore = defineStore("useCartStore", () => {
    const items = ref([]);
    const itemCount = ref(0);
    const totalPrice = ref(0); 
    const image = ref("/images/course-1.jpg");

    function addToCart(selectedItem) {
        const exist = items.value.find(
            (item) =>
                item.type === selectedItem.type &&
                item.slug === selectedItem.slug
        );
        if (exist) return;

        items.value.push(selectedItem);
        itemCount.value += 1;
        totalPrice.value += selectedItem.price;
    }

    function removeFromCart(selectedItem) {
        const index = items.value.findIndex(
            (item) =>
                item.type === selectedItem.type &&
                item.slug === selectedItem.slug
        );

        if (index !== -1) {
            items.value.splice(index, 1);
            itemCount.value -= 1;
            totalPrice.value -= selectedItem.price;
        }
    }

    function setCart(newItems) {
        items.value = newItems;
        itemCount.value = newItems.length;
        totalPrice.value = newItems.reduce((sum, item) => sum + item.price, 0);
    }

    // In your useCartStore.js
    function clearCart() {
        this.items = [];
        this.itemCount = 0;
        this.totalPrice = 0;
        localStorage.removeItem("cartItems"); // Directly remove from localStorage
    }

    // Load cart from localStorage on initialization
    const saved = localStorage.getItem("cartItems");
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            setCart(parsed);
        } catch (e) {
            console.warn("Failed to parse cartItems from localStorage:", e);
        }
    }

    // Watch for changes and persist to localStorage
    watch(
        items,
        (newItems) => {
            localStorage.setItem("cartItems", JSON.stringify(newItems));
        },
        { deep: true }
    );

    return {
        items,
        itemCount,
        totalPrice,
        image,
        addToCart,
        removeFromCart,
        setCart,
        clearCart, // Make sure to expose the new function
    };
});
