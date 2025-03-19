<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100 p-4">
    <div
      class="bg-cover bg-center shadow-lg rounded-lg relative"
      :style="{ backgroundImage: 'url(' + backgroundImageUrl + ')', width: '1123px', height: '794px', backgroundSize: 'cover' }"
    >
      <div class="absolute inset-0 flex flex-col justify-center items-center  px-36 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800  uppercase mb-6">
          Course Completion Certificate
        </h1>

        <!-- Logo -->
        <img src="/images/logo.jpg" alt="Logo" class="mx-auto my-6 w-28 h-28 rounded-full" />

        <!-- Student Info -->
        <h2 class="text-3xl font-semibold text-gray-800">{{ studentName }}</h2>
        <p class="text-xl text-gray-700 my-2">has successfully completed the</p>
        <h3 class="text-2xl font-medium text-gray-800">{{ courseName }}</h3>

        <!-- Date & Score -->
        <div class="flex justify-between w-full mt-6 text-lg">
          <span class="text-gray-600">Date: {{ completionDate }}</span>
          <span class="text-gray-600">Score: {{ score }}%</span>
        </div>

        <!-- Digital Signature Section -->
        <div class="flex justify-between items-center w-full mt-12 px-18">
          <div class="text-center">
            <img src="/images/signature.png" alt="Signature" class="h-28 w-36 mx-auto" />
            <p class="text-gray-700 font-semibold text-lg">{{ issuedBy }}</p>
            <p class="text-gray-600 text-base">{{ issuedRole }}</p>
          </div>
        </div>

        <!-- Download Button -->
        <!-- <div class="mt-12">
          <button
            @click="downloadCertificate"
            class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-700 transition"
          >
            Download Certificate
          </button>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
// import certificateBg from '/images/certificate.png'; // Use local certificate background

export default {
  data() {
    return {
      studentName: 'John Doe',
      courseName: 'Vue.js for Beginners',
      completionDate: new Date().toLocaleDateString(),
      score: 95,
      issuedBy: 'Mehari', // Instructor name
      issuedRole: 'Course Instructor', // Instructor role
      // backgroundImageUrl: certificateBg, // A4 background image
    };
  },
  methods: {
    downloadCertificate() {
      alert('Downloading Certificate...');
    },
  },
};
</script>
