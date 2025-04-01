<script setup>
     import { onMounted, computed } from 'vue';
     import { storeToRefs } from 'pinia'; 
     import { useAppStore } from '@/store/useAppStore';
     import { useRoute } from "vue-router";

     import StudentHome from "@/pages/Student/StudentHome.vue";
     import InstructorHome from "@/pages/Instructor/InstructorHome.vue";

     const appStore = useAppStore();
     const { authUser, isLoggedIn } = storeToRefs(appStore);

     const route = useRoute();

     const currentComponentName = computed(() => {
          return route.name;
     });
</script>

<template>
    <div>
       <div v-if="authUser?.role === 'systemAdmin'">
            <InstructorHome/>
       </div>
       <div v-else-if="authUser?.role === 'instructor'">
            <InstructorHome/>
       </div>
       <div v-else-if="authUser?.role === 'student'">
            <div v-if="currentComponentName === 'InvoicePage' ">
               <RouterView/>
            </div>
            <div v-else>
               <StudentHome/>
            </div>

       </div>
       <div v-else-if="!isLoggedIn">
            <StudentHome/>
       </div>
    </div>
</template>
