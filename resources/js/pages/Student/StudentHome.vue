<script setup>
    import { ref } from 'vue';

    const name = ref('');

</script>
<template>
    
    <div>
        student page
        {{ name }}
        <input type="text" placeholder="nput form" v-model="name">
    </div>
</template>