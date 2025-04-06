<script setup>
import Axios from "axios";
import { ref, defineEmits } from "vue";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["close"]);

const newPlan = ref({
    name: "",
    price: "",
    duration: "",
});

const errors = ref({});

const validateAndSubmit = async () => {
    errors.value = {};

    if (!newPlan.value.name.trim()) {
        errors.value.name = "Plan name is required.";
    }
    if (!newPlan.value.price) {
        errors.value.price = "Price is required.";
    } else if (newPlan.value.price <= 0) {
        errors.value.price = "Price must be a positive number.";
    }
    if (!newPlan.value.duration) {
        errors.value.duration = "Duration is required.";
    } else if (newPlan.value.duration <= 0 || !Number.isInteger(Number(newPlan.value.duration))) {
        errors.value.duration = "Duration must be a positive whole number.";
    }

    if (Object.keys(errors.value).length > 0) {
        toast.error("Please fix validation errors!", { position: "top-right" });
        return;
    }

    try {
        await Axios.post("/api/plans", newPlan.value);
        toast.success("Plan added successfully!", { position: "top-right" });

        newPlan.value = { name: "", price: "", duration: "" };

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
                <div>
                    <label class="block text-gray-700 font-medium">Price (in Birr)</label>
                    <input v-model="newPlan.price" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter price" />
                    <p v-if="errors.price" class="text-red-600 text-sm mt-1">{{ errors.price }}</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Duration (in months)</label>
                    <input v-model="newPlan.duration" type="number" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="Enter duration" />
                    <p v-if="errors.duration" class="text-red-600 text-sm mt-1">{{ errors.duration }}</p>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-lime-700 text-white px-6 py-2 rounded-lg hover:bg-lime-600 flex items-center justify-center gap-2">
                        Add Plan
                    </button>
                </div>
            </form>
    </div>
</template>
