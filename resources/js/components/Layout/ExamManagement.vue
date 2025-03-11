<script setup>
    import { ref, watch  } from "vue";

    import ManageTest from "@/components/Course/ManageTest.vue";
    import MetaDataForm from "@/components/Quize/MetaDataForm.vue";

    const currentPage = ref("manageTest");

    const props = defineProps({
                    module: Object
                });
    const emit = defineEmits(["backToModule"]);

    function tabClass(tab) {
        return currentPage.value === tab
            ? "border-b-2 border-lime-700 text-lime-700"
            : "text-gray-700";
    };

    watch(
        () => props.module,
        (newModule) => {
            console.log("Selected module passed:", newModule);
        },
        { immediate: true }
    );
    
</script>

<template>
    <div class="min-h-screen bg-gray-100 text-gray-800">
        <header
            class="bg-white border px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-start">
            <button
                @click="emit('backToModule')"
                class="px-4 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800 transition mr-4">
                ← Back to Module
            </button>
            <div>
                <h1 class="text-3xl font-bold text-lime-700">
                    Exam Management
                </h1>
                <p class="text-sm text-gray-500">Manage Tests</p>
            </div>
        </header>
        <div class="my-6">
            <div class="flex border-b">
                <button
                    :class="tabClass('manageTest')"
                    @click="currentPage = 'manageTest'"
                    class="p-2">
                    Manage Test
                </button>
                <button
                    :class="tabClass('metaDataForm')"
                    @click="currentPage = 'metaDataForm'"
                    class="p-2">
                    Meta Data Form
                </button>
            </div>
        </div>
        <div class="container mx-auto px-6 py-6">
            <div v-if="currentPage === 'manageTest'">
                <ManageTest />
            </div>
            <div v-if="currentPage === 'metaDataForm'">
                <MetaDataForm :courseID="module.course_id" :moduleID="module.id" />
            </div>
        </div>
    </div>
</template>


