<script setup>
    import { ref, watch, computed, onMounted } from "vue";
    import ManageTest from "@/components/Course/ManageTest.vue";
    import MetaDataForm from "@/components/Quize/MetaDataForm.vue";

    const props = defineProps({
        module: Object
    });
    const emit = defineEmits(["backToModule"]);

    const currentPage = ref("manageTest");

    const hasModule = computed(() => !!props.module);

    onMounted(() => {
        if (hasModule.value) {
            currentPage.value = "metaDataForm";
        }
    });

    function tabClass(tab) {
        return currentPage.value === tab
            ? "border-b-2 border-lime-700 text-lime-700"
            : "text-gray-700";
    }

    watch(
        () => props.module,
        (newModule) => {
            if (newModule) {
                currentPage.value = "metaDataForm";
            }
        },
        { immediate: true }
    );
</script>

<template>
    <div class="min-h-screen bg-gray-100 text-gray-800">
        <header class="bg-white border px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-start">
            <button v-if="hasModule"
                @click="emit('backToModule')"
                class="px-4 py-2 bg-gray-100 text-black rounded-md hover:bg-gray-200 transition mr-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>

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
                <!-- Only display the ManageTest tab if there's no module -->
                <button v-if="!hasModule"
                    :class="tabClass('manageTest')"
                    @click="currentPage = 'manageTest'"
                    class="p-2">
                    Manage Test
                </button>
                <button v-if="hasModule"
                    :class="tabClass('metaDataForm')"
                    @click="currentPage = 'metaDataForm'"
                    class="p-2">
                    Meta Data Form
                </button>
            </div>
        </div>
        <div class="container mx-auto px-6 py-6">
            <!-- Show ManageTest only if no module is provided -->
            <div v-if="currentPage === 'manageTest' && !hasModule">
                <ManageTest />
            </div>
            <div v-if="currentPage === 'metaDataForm'">
                <MetaDataForm :courseID="module.course_id"
                    :moduleID="module.id" />
            </div>
        </div>
    </div>
</template>
