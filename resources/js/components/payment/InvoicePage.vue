<template>
  <div class="container mx-auto p-6 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-md overflow-hidden">
      <!-- Header -->
      <div class="flex flex-wrap justify-between items-center px-6 py-4 border-b bg-gray-800">
        <div class="w-full sm:w-auto text-center sm:text-left">
          <h1 class="text-2xl font-bold text-gray-800 text-gray-100">Invoice</h1>
          <p class="text-sm text-gray-200">Invoice #: {{ invoiceData.invoiceNumber }}</p>
          <p class="text-sm text-gray-500">Date: {{ invoiceData.date }}</p>
        </div>
        <div class="w-full sm:w-auto flex justify-center">
          <img
            :src="company.logo"
            alt="Company Logo"
            class="w-24 h-auto mt-4 sm:mt-0"
          />
        </div>
      </div>

      <!-- Company & Customer Info -->
      <div class="px-6 py-4">
        <table class="w-full border-collapse border border-gray-200 bg-gray-100 rounded-md text-sm">
          <thead class="bg-gray-800 text-gray-200">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">Company Information</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Customer Information</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <!-- Company Info -->
              <td class="border border-gray-200 px-4 py-2">
                <p>{{ company.name }}</p>
                <p>{{ company.address }}</p>
                <p>{{ company.email }}</p>
                <p>{{ company.phone }}</p>
              </td>

              <!-- Customer Info -->
              <td class="border border-gray-200 px-4 py-2">
                <p>{{ invoiceData.customerName }}</p>
                <p>{{ invoiceData.customerEmail }}</p>
                <p>{{ invoiceData.customerPhone }}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table -->
      <div class="px-6 py-4 overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">Item</th>
              <th class="border border-gray-200 px-4 py-2 text-right">Price</th>
              <th class="border border-gray-200 px-4 py-2 text-center">Quantity</th>
              <th class="border border-gray-200 px-4 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in invoiceData.items" :key="index" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ item.name }}</td>
              <td class="border border-gray-200 px-4 py-2 text-right">${{ item.price.toFixed(2) }}</td>
              <td class="border border-gray-200 px-4 py-2 text-center">{{ item.quantity }}</td>
              <td class="border border-gray-200 px-4 py-2 text-right">${{ (item.price * item.quantity).toFixed(2) }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="3" class="border border-gray-200 px-4 py-2 text-right font-semibold">Subtotal</td>
              <td class="border border-gray-200 px-4 py-2 text-right font-semibold">${{ invoiceData.subtotal.toFixed(2) }}</td>
            </tr>
            <tr>
              <td colspan="3" class="border border-gray-200 px-4 py-2 text-right font-semibold">Tax (10%)</td>
              <td class="border border-gray-200 px-4 py-2 text-right font-semibold">${{ invoiceData.tax.toFixed(2) }}</td>
            </tr>
            <tr>
              <td colspan="3" class="border border-gray-200 px-4 py-2 text-right font-bold">Total</td>
              <td class="border border-gray-200 px-4 py-2 text-right font-bold">${{ invoiceData.total.toFixed(2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- QR Code -->
      <div class="px-6 py-4 flex justify-center">
        <div>
          <p class="text-center text-gray-500 text-sm mb-2">Scan QR to View Invoice</p>
          <img :src="qrCodeUrl" alt="QR Code" class="w-32 h-32 mx-auto" />
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-gray-50 text-center border-t">
        <p class="text-sm text-gray-600">
          Thank you for your purchase! If you have any questions, please contact us at <a href="mailto:{{ company.email }}" class="text-blue-500 hover:underline">{{ company.email }}</a>.
        </p>
        <p class="text-sm text-gray-600 mt-1">© {{ new Date().getFullYear() }} {{ company.name }}. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script>
import QRCode from "qrcode";
import { ref } from "vue";

export default {
  setup() {
    const company = {
      name: "Unique English",
      address: "CMC Micael, Addis Ababa, Ethiopia",
      email: "support@unique.com",
      phone: "+123 456 7890",
      logo: "/images/logo.jpg", // Replace with your logo URL
    };

    // Initialize `invoiceData` with default values
    const invoiceData = ref({
      invoiceNumber: "INV-123456",
      date: new Date().toLocaleDateString(),
      customerName: "Jane Doe",
      customerEmail: "jane.doe@example.com",
      customerPhone: "+987 654 3210",
      items: [
        { name: "Vue.js Mastery", price: 49.99, quantity: 1 },
        { name: "React for Beginners", price: 59.99, quantity: 2 },
      ],
      subtotal: 169.97,
      tax: 16.997,
      total: 186.967,
    });

    const qrCodeUrl = ref("");

    // Generate QR Code
    QRCode.toDataURL(`Invoice: ${invoiceData.value.invoiceNumber}, Total: $${invoiceData.value.total.toFixed(2)}`)
      .then((url) => {
        qrCodeUrl.value = url;
      })
      .catch((err) => {
        console.error(err);
      });

    return {
      company,
      invoiceData,
      qrCodeUrl,
    };
  },
};

</script>

<style>
/* Mobile-first styles (base styles already handle mobile well) */
.table {
  font-size: 0.875rem; /* Small font for mobile */
}

@media (min-width: 640px) {
  /* Tablet styles */
  .table {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  /* Desktop styles */
  .container {
    padding: 2rem;
  }

  .table {
    font-size: 1.125rem;
  }
}
</style>
