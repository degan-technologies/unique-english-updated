<template>
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl w-full">
        <!-- Header Section -->
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">Privacy Policy</h1>
        <p class="text-center text-gray-600 mb-12">
          We value your privacy. This Privacy Policy outlines how we collect, use, and protect your personal information.
        </p>
  
        <!-- Privacy Policy Content -->
        <div class="space-y-8 text-lg text-gray-700">
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introduction</h2>
            <p>
              Welcome to our Privacy Policy. Your privacy is important to us, and we are committed to protecting your personal information.
              This policy explains how we collect, use, and safeguard your data while providing our services.
            </p>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Information We Collect</h2>
            <p>
              We collect various types of information to provide you with a better user experience. The information we collect includes:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li><strong>Personal Information:</strong> Such as your name, email address, phone number, and payment details.</li>
              <li><strong>Usage Data:</strong> Information about how you interact with our services, such as browsing behavior, IP address, and device type.</li>
              <li><strong>Cookies:</strong> We use cookies to enhance your experience and track user behavior for analytics.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">How We Use Your Information</h2>
            <p>
              We use the information we collect for the following purposes:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>To provide, improve, and personalize our services for you.</li>
              <li>To process payments and communicate with you regarding transactions.</li>
              <li>To send you marketing materials, offers, and updates (if you have opted-in).</li>
              <li>To comply with legal obligations and protect our rights.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">How We Protect Your Information</h2>
            <p>
              We take the security of your personal information seriously and implement various measures to protect it, including:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>Encryption of sensitive data during transmission.</li>
              <li>Regular security audits and vulnerability assessments.</li>
              <li>Restricted access to your information to authorized personnel only.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sharing Your Information</h2>
            <p>
              We do not sell, trade, or rent your personal information to third parties. However, we may share your information in the following cases:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>With trusted service providers who assist us in delivering our services (e.g., payment processors, cloud services).</li>
              <li>When required by law or to protect our legal rights.</li>
              <li>In the case of a business transfer, such as a merger or acquisition.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Rights</h2>
            <p>
              You have the following rights regarding your personal information:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>Access: You can request a copy of your personal information.</li>
              <li>Correction: You can request updates or corrections to your data.</li>
              <li>Deletion: You can request to delete your data in certain circumstances.</li>
              <li>Opt-out: You can opt-out of marketing communications at any time.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Children's Privacy</h2>
            <p>
              Our services are not intended for individuals under the age of 13. We do not knowingly collect or solicit personal information from children under 13.
            </p>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Changes to This Privacy Policy</h2>
            <p>
              We may update our Privacy Policy from time to time. When we do, we will post the updated policy on this page and update the "Last Updated" date at the bottom. We encourage you to review this policy periodically.
            </p>
          </section>
  
          <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Contact Us</h2>
            <p>
              If you have any questions or concerns about this Privacy Policy, please contact us at:
            </p>
            <p class="font-semibold text-blue-500">support@yourcompany.com</p>
          </section>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'PrivacyPolicy',
  };
  </script>
  
  <style scoped>
  /* Add subtle animation to headings */
  h2 {
    transition: color 0.3s ease;
  }
  
  h2:hover {
    color: #4f46e5;
  }
  
  section ul {
    margin-left: 1.5rem;
    list-style-type: disc;
  }
  
  section p {
    line-height: 1.6;
  }
  </style>
  