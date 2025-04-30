<script setup>
import Axios from "axios";
import { ref, defineEmits } from "vue";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["close"]);

const newPlan = ref({
    name: "",
    one_to_one_price: "",
    group_price: "",
});

const errors = ref({});

const validateAndSubmit = async () => {
    errors.value = {};

    if (!newPlan.value.name.trim()) {
        errors.value.name = "Plan name is required.";
    }
    if (!newPlan.value.one_to_one_price) {
        errors.value.one_to_one_price = "one to one price is required.";
    } else if (newPlan.value.one_to_one_price <= 0) {
        errors.value.one_to_one_price = "one to one price must be a positive number.";
    } 

    if (!newPlan.value.group_price) {
        errors.value.group_price = "group_price price is required.";
    } else if (newPlan.value.group_price <= 0) {
        errors.value.group_price = "group_price price must be a positive number.";
    } 

    if (Object.keys(errors.value).length > 0) {
        toast.error("Please fix validation errors!", { position: "top-right" });
        return;
    }

    try {
        await Axios.post("/api/plans", newPlan.value);
        toast.success("Plan added successfully!", { position: "top-right" });

        newPlan.value = { 
            name: "",  
            one_to_one_price: "", 
            group_price: "", 
        };

        emit("close");
    } catch (err) {
        console.error("Error adding plan:", err);
        toast.error("Failed to add plan. Please try again.", { position: "top-right" });
    }
};
</script>

<template>
    <div class="bg-white p-4 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl relative">
            <button @click="emit('close')" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h1 class="text-2xl font-bold text-lime-700 mb-6 text-center flex items-center justify-center gap-1">
                Add New Plan
            </h1>
            <form @submit.prevent="validateAndSubmit" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-medium">Name</label>
                    <input v-model="newPlan.name" type="text" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter plan name" />
                    <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
                </div>

                <label class="block text-gray-700 font-medium">Price <span class="font-bold">ETB</span></label>
                <div class="grid grid-cols-1  sm:grid-cols-2 sm:gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium">One to One</label>
                        <input v-model="newPlan.one_to_one_price" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter one to one price" />
                        <p v-if="errors.one_to_one_price" class="text-red-600 text-sm mt-1">{{ errors.one_to_one_price }}</p>
                    </div> 
                    <div>
                        <label class="block text-gray-700 font-medium">Group</label>
                        <input v-model="newPlan.group_price" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter group price" />
                        <p v-if="errors.group_price" class="text-red-600 text-sm mt-1">{{ errors.group_price }}</p>
                    </div> 
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-lime-700 text-white px-6 py-2 rounded-lg hover:bg-lime-600 flex items-center justify-center gap-2">
                        Add Plan
                    </button>
                </div>
            </form>
    </div>
</template>
