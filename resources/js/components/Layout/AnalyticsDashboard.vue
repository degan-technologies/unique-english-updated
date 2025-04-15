<script setup>
    defineProps({
        label: {
            type: String,
            required: true
        },
        total: {
            type: Number,
            required: true
        },
        newToday: {
            type: Number,
            required: true
        }
    })
</script>
<template>
    <aside class="flex flex-col sm:flex-row items-center gap-4 bg-white rounded-lg p-3 w-full md:w-2/3">
        <div class="flex items-center gap-4">
            <div class="flex items-center px-3 py-2 rounded">
                <p class="text-xs font-medium text-gray-600 mr-1">Total {{ label }}</p>
                <p class="text-lg font-bold text-lime-700">{{ total }}</p>
            </div>
            <div class="flex items-center px-3 py-2 rounded">
                <p class="text-xs font-medium text-gray-600 mr-1">New Today</p>
                <p class="text-lg font-bold">{{ newToday }}</p>
            </div>
        </div>
    </aside>
</template>