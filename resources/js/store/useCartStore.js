import { defineStore } from "pinia";
import { ref } from "vue";


export const useCartStore = defineStore("useCartStore", ()=> {
    const items = ref([]);
    const itemCount = ref(0);
    const totalPrice = ref(0);
    const image = ref("/images/course-1.jpg");

    function addToCart(selectedItem) {
        let exist = items.value.find(item => item.type == selectedItem.type && item.slug == selectedItem.slug)
        if(exist) return;

        items.value.push(selectedItem);
        itemCount.value += 1; 
        totalPrice.value +=selectedItem.price;
    }

    function  removeFromCart(selectedItem) {
            const index = this.items.findIndex(item => item.type == selectedItem.type && item.slug == selectedItem.slug);

            if (index !== -1) {
                this.items.splice(index, 1);
                itemCount.value -=1;
                totalPrice.value -=selectedItem.price;
            } 
        }

    return {
        items,
        itemCount,
        totalPrice,

        image,

        addToCart,
        removeFromCart,
    }
});
