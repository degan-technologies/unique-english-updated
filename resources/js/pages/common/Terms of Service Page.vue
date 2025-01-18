<template>
    <div class="bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
      <div class="bg-white p-10 rounded-lg shadow-2xl max-w-5xl w-full">
        <!-- Header Section -->
        <h1 class="text-5xl font-extrabold text-center text-gray-800 mb-8">Terms of Service</h1>
        <p class="text-center text-lg text-gray-600 mb-12">
          Please read these Terms of Service ("Terms", "Terms of Service") carefully before using our platform. By accessing or using our services, you agree to comply with these terms.
        </p>
  
        <!-- Terms of Service Content -->
        <div class="space-y-10 text-lg text-gray-700">
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">1. Acceptance of Terms</h2>
            <p>
              By accessing or using our services, you confirm that you have read, understood, and agreed to these Terms of Service. If you do not agree, you must immediately stop using our platform.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">2. Use of the Service</h2>
            <p>
              You are granted a limited, non-exclusive, non-transferable license to access and use our services. You agree to use the platform only for lawful purposes and in accordance with these Terms.
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>You must not attempt to hack, disrupt, or compromise the security of our systems.</li>
              <li>You may not use our services to distribute harmful, illegal, or inappropriate content.</li>
              <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">3. Account Responsibilities</h2>
            <p>
              When creating an account, you agree to:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>Provide accurate and up-to-date information.</li>
              <li>Keep your password secure and confidential.</li>
              <li>Notify us immediately of unauthorized account access or breaches.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">4. Prohibited Conduct</h2>
            <p>
              You agree not to:
            </p>
            <ul class="list-inside list-disc pl-5 space-y-2">
              <li>Engage in fraudulent, deceptive, or harmful activities.</li>
              <li>Upload or transmit malware, viruses, or malicious code.</li>
              <li>Infringe on the intellectual property rights of others.</li>
              <li>Use the service in a way that violates applicable laws and regulations.</li>
            </ul>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">5. Intellectual Property</h2>
            <p>
              All content, designs, logos, and trademarks on this platform are the property of our company or our licensors. You may not copy, reproduce, or use these without prior written permission.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">6. Limitation of Liability</h2>
            <p>
              To the fullest extent permitted by law, we are not liable for any indirect, incidental, or consequential damages arising from your use of our platform.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">7. Termination</h2>
            <p>
              We reserve the right to terminate or suspend your account without notice if you violate these Terms or engage in prohibited conduct. Upon termination, your right to access the platform will immediately cease.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">8. Changes to the Terms</h2>
            <p>
              We may update these Terms from time to time. Any changes will be posted on this page, and it is your responsibility to review them regularly. Your continued use of the platform after changes are made constitutes your acceptance of the revised Terms.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">9. Governing Law</h2>
            <p>
              These Terms are governed by and construed in accordance with the laws of [Your Country/Region], without regard to its conflict of law principles.
            </p>
          </section>
  
          <section>
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">10. Contact Us</h2>
            <p>
              If you have any questions about these Terms, please contact us at:
            </p>
            <p class="font-semibold text-blue-500">support@yourcompany.com</p>
          </section>
        </div>
  
        <!-- Footer -->
        <div class="mt-12 text-center">
          <p class="text-sm text-gray-500">
            Last Updated: January 17, 2025
          </p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TermsOfService',
  };
  </script>
  
  <style scoped>
  /* Add smooth hover effect for links */
  p a {
    color: #4f46e5;
    text-decoration: underline;
    transition: color 0.3s ease;
  }
  
  p a:hover {
    color: #9333ea;
  }
  
  section ul {
    margin-left: 1.5rem;
    list-style-type: disc;
  }
  
  section p {
    line-height: 1.6;
  }
  </style>
  