<script setup>
    import Axios from "axios";
    import Popper from 'vue3-popper';
    import { storeToRefs } from "pinia";
    import {  useRouter } from "vue-router";
    import { ref } from "vue";
   

    import { useAppStore } from "@/store/useAppStore"; 
    import { useCartStore } from "@/store/useCartStore";
    import { UseStudentStore } from "@/store/UseStudentStore";

    const cartStore = useCartStore();
    const studentStore = UseStudentStore();

    const router = useRouter()

    const checkoutUrl = ref(null);

    const {items, itemCount, image, totalPrice} = storeToRefs(cartStore);
    const { courses, selectedCourseSlug, landingPageTab} = storeToRefs(studentStore);

    const isMenuOpen = ref(false);
    const isMenuVisible = ref(true);

    const toggleMenu = () => isMenuOpen.value = !isMenuOpen.value;

    function enrollCourse(){
        Axios
            .post('/api/initiate-payment',{cartItems:items.value} )
            .then(res=>{
                checkoutUrl.value = res.data.checkout_url;
                window.open(checkoutUrl.value, "_blank");
            })
            .catch(error => {});

    }

    function removeItem(item) {
      let  selectedItem = {
            type:'course',
            slug:item.slug,
            price: item.price,
            name:item.course_name,
            image:item.thumbnail_url
        };
        cartStore.removeFromCart(selectedItem);
    }

    function changeTab() {
        router.push({
            name: 'student',
            query: {
                tab:landingPageTab.value,
            }
        });

        selectedCourseSlug.value = null;
    }
   
</script>

<template>
    <header
        class="bg-lime-700 text-white shadow-md fixed top-0 left-0 w-full z-10 transition-transform duration-300 ease-in-out"
        :class="{
            'translate-y-0': isMenuVisible,
            '-translate-y-full': !isMenuVisible,
        }" >
        <nav class="container mx-auto flex items-center justify-between p-4">
            <a href="/" class="flex items-center gap-2">
                <img
                    src="/images/logo.jpg"
                    alt="Logo"
                    class="h-12 w-12 rounded-full object-cover border-2 border-white"
                />
                <span class="text-xl font-bold">UniqueEnglish</span>
            </a>

            <ul class="hidden md:flex gap-6">
                <li>
                    <button  
                        @click="changeTab"
                        class="hover:text-gray-300">Home
                    </button>
                </li>
                <li>
                    <a href="#about" class="hover:text-gray-300">About</a>
                </li>
                <li>
                    <a href="#courses" class="hover:text-gray-300">Courses</a>
                </li>
                <li>
                    <a href="#contact" class="hover:text-gray-300">Contact</a>
                </li>
                <div class="relative">
                    <Popper 
                        :offset-distance="'0'"
                        placement="bottom-start" >
                        <div class="relative text-2xl flex items-center">
                                <i class="fa-solid fa-cart-plus text-white cursor-pointer w-10 h-10"></i>
                            <span v-if="itemCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ itemCount }}
                            </span>
                        </div>
                        <template #content>
                            <div class="absolute top-12 right-0 bg-white text-black shadow-lg w-96 h-96 overflow-y-auto scrollbar p-4 rounded">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-bold text-lg">Your Cart</h3>
                                </div>
                                <div 
                                    v-if="!itemCount"
                                    class="h-20 flex justify-center items-center">
                                    <h1>No Item add to cart</h1>
                                </div>
                                <div>
                                    <ul v-if="itemCount">
                                        <li v-for="item in items" :key="item.id" class="flex items-center justify-between mb-2 border-b pb-2">
                                            <img :src="item.image" alt="Item Image" class="h-12 w-12 object-cover rounded" />
                                            <div class="flex-1 ml-2">
                                                <span class="block font-semibold">{{ item?.name }}</span>
                                                <span class="text-gray-500 text-sm">${{ item?.price.toFixed(2) }}</span>
                                            </div>
                                            <i 
                                                @click="removeItem(item)"
                                                class="fa-solid fa-minus text-red-500 hover:text-red-700"></i>
                                        </li>
                                    </ul>
                                    <div class="mt-4 font-bold text-lg">Total: ${{ totalPrice.toFixed(2) }}</div>
                                    <button 
                                        @click="enrollCourse()" 
                                        class="mt-2 bg-green-600 text-white px-4 py-2 rounded w-full hover:bg-green-700">
                                        Proceed to Checkout
                                    </button>
                                </div>
                                
                            </div>
                        </template>
                    </Popper>
                </div>
            </ul>

            <button @click="toggleMenu" class="block md:hidden text-2xl focus:outline-none">
                <span v-if="!isMenuOpen">☰</span>
                <span v-else>✖</span>
            </button>
        </nav>

        <!-- Mobile Menu -->
        <transition name="mobile-menu">
            <div
                v-if="isMenuOpen"
                class="md:hidden bg-lime-600 text-white shadow-lg" >
                <ul class="flex flex-col items-center gap-4 py-4">
                    <li>
                        <a
                            href="#home"
                            class="hover:text-gray-300"
                            @click="closeMenu" >
                            Home
                        </a>
                    </li>
                    <li>
                        <a
                            href="#about"
                            class="hover:text-gray-300"
                            @click="closeMenu" >
                            About
                        </a>
                    </li>
                    <li>
                        <a
                            href="#courses"
                            class="hover:text-gray-300"
                            @click="closeMenu" >
                            Courses
                        </a>
                    </li>
                    <li>
                        <a
                            href="#contact"
                            class="hover:text-gray-300"
                            @click="closeMenu" >
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
        </transition>
    </header>
</template>


<style scoped>
header {
    transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
    transform: translateY(-10%);
    opacity: 0;
}

.mobile-menu-enter-to,
.mobile-menu-leave-from {
    transform: translateY(0);
    opacity: 1;
}
</style>
