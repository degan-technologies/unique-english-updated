<template>
    <div class="">
      <!-- Header Section -->
      <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">Frequently Asked Questions</h1>
      <p class="text-center text-gray-600 mb-12">Got questions? We've got answers! Explore the most common questions
        below.</p>

      <!-- FAQ Section -->
      <div class="space-y-6">
        <div v-for="(item, index) in faqs" :key="index" class="border-b border-gray-200 pb-6">
          <button @click="toggle(index)"
            class="w-full text-left text-xl font-semibold text-gray-800 focus:outline-none flex justify-between items-center">
            <span>{{ item.question }}</span>
            <svg :class="{ 'rotate-180': item.open }" class="w-6 h-6 text-gray-600 transition-transform duration-300"
              xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
            </svg>
          </button>
          <div v-if="item.open" class="mt-4 text-lg text-gray-600">
            <p>{{ item.answer }}</p>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'FAQPage',
  data() {
    return {
      faqs: [
        {
          question: 'How can I contact customer support?',
          answer: 'You can reach our customer support through the contact form on our website or directly via email.',
          open: false,
        },
        {
          question: 'What payment methods do you accept?',
          answer: 'We accept all major credit cards, PayPal, and bank transfers for payments.',
          open: false,
        },
        {
          question: 'How do I reset my password?',
          answer: 'Click on the "Forgot Password" link on the login page, and we will send you instructions to reset your password.',
          open: false,
        },
        {
          question: 'Can I change my subscription plan?',
          answer: 'Yes, you can upgrade or downgrade your subscription plan at any time from your account settings.',
          open: false,
        },
        {
          question: 'Is my data safe with you?',
          answer: 'Yes, we take data privacy very seriously. Your data is encrypted and stored securely with the highest standards of protection.',
          open: false,
        },
      ],
    };
  },
  methods: {
    toggle(index) {
      this.faqs[index].open = !this.faqs[index].open;
    },
  },
};
</script>

<style scoped>
/* Add a transition to the FAQ answer section for smooth appearance */
button:focus {
  outline: none;
}

button svg {
  transition: transform 0.3s ease;
}

button:hover {
  background-color: rgba(79, 70, 229, 0.1);
}

div[role="button"] {
  cursor: pointer;
}
</style>