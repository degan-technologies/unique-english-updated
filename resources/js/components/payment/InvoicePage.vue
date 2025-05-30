<script setup>
import Axios from 'axios';
import QRCode from 'qrcode';
import html2pdf from 'html2pdf.js';
import { useRoute } from 'vue-router';
import { ref, onMounted, watchEffect, nextTick } from 'vue';

import Spinner from "@/components/Layout/Spinner.vue";

const route = useRoute();
const transactionRef = ref(route.params.id);
const invoiceData = ref(null);
const qrCodeUrl = ref('');
const isLoading = ref(true);
const errorMessage = ref('');
const printDownload = ref(true);

const company = {
    name: "Unique English",
    address: "CMC Micael, Addis Ababa, Ethiopia",
    email: "support@unique.com",
    phone: "+251 922 68 78 47",
    logo: "/images/logo.jpg",
}

const fetchTransaction = async () => {
    try {
        isLoading.value = true
        const response = await Axios.get(`/api/transaction-info/${transactionRef.value}`)
        invoiceData.value = response.data
    } catch (error) {
        console.error('Error fetching transaction:', error)
        errorMessage.value = error.response?.data?.message ||
            'Failed to load invoice. Please try again later.'
    } finally {
        isLoading.value = false
    }
}

const downloadInvoice = () => { 
    printDownload.value = false;
    nextTick(() => {
        const element = document.querySelector('.invoice-print-container'); // Changed to class selector
        if (!element) {
            console.error('Invoice container not found');
            return;
        }

        const opt = {
            margin: 10,
            filename: `invoice_${invoiceData.value.invoiceNumber}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                logging: true,
                useCORS: true,
                allowTaint: true,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait' 
            }
        };

        html2pdf()
            .set(opt)
            .from(element)
            .save()
            .catch(err => {
                console.error('PDF generation failed:', err);
                errorMessage.value = 'Failed to generate PDF. Please try again.';
            });
    });

    printDownload.value = true;
}

// Generate QR code when invoice data is available
watchEffect(() => {
    if (invoiceData.value) {
        const qrContent = `Invoice: ${invoiceData.value.invoiceNumber}\n` +
            `Amount: $${invoiceData.value.total}\n` +
            `Date: ${invoiceData.value.date}\n` +
            `Customer: ${invoiceData.value.customerName}`

        QRCode.toDataURL(qrContent)
            .then(url => qrCodeUrl.value = url)
            .catch(err => console.error('QR generation failed:', err))
    }
})

onMounted(() => {
    fetchTransaction()
})
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center h-screen">
            <div class="text-center">
                <Spinner />
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="errorMessage" class="flex invoice-containe items-center justify-center h-screen px-4">
            <div  class="text-center max-w-md">
                <div class="text-red-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Unable to Load Invoice</h1>
                <p class="text-gray-600 mb-6">{{ errorMessage }}</p>
                <button @click="fetchTransaction"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Try Again
                </button>
                <p class="mt-4 text-gray-500 text-sm">
                    Need help? Contact us at
                    <a :href="`tel:${company.phone}`" class="text-blue-600 hover:underline">
                        {{ company.phone }}
                    </a>
                </p>
            </div>
        </div>

        <!-- Success State -->
        <div v-else-if="invoiceData" class="container mx-auto p-4 md:p-6">
            <div class=" invoice-print-container max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Header -->
                <div
                    class="flex flex-col md:flex-row justify-between items-center px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-700">
                    <div class="text-center md:text-left mb-4 md:mb-0">
                        <h1 class="text-2xl font-bold text-white">Invoice Receipt</h1>
                        <div class="flex flex-col sm:flex-row sm:gap-4 text-gray-200 text-sm">
                            <p>Invoice #: {{ invoiceData.invoiceNumber }}</p>
                            <p>Date: {{ invoiceData.date }}</p>
                        </div>
                    </div>
                    <img :src="company.logo" :alt="company.name + ' logo'" class="h-16 w-auto object-contain" />
                </div>

                <!-- Company & Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6 border-b">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="font-semibold text-gray-800 mb-2">From:</h2>
                        <p class="font-medium text-gray-900">{{ company.name }}</p>
                        <p class="text-gray-600">{{ company.address }}</p>
                        <p class="text-gray-600">{{ company.email }}</p>
                        <p class="text-gray-600">{{ company.phone }}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="font-semibold text-gray-800 mb-2">To:</h2>
                        <p class="font-medium text-gray-900">{{ invoiceData.customerName }}</p>
                        <p class="text-gray-600">{{ invoiceData.customerEmail }}</p>
                        <p class="text-gray-600">{{ invoiceData.customerPhone || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qty
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in invoiceData.items" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    ${{ item.price?.toFixed(2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    ${{ (item.price * item.quantity)?.toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="bg-gray-50 px-6 py-4">
                    <div class="max-w-md ml-auto space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">${{ invoiceData.subtotal?.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax ({{ (invoiceData.tax / invoiceData.subtotal *
                                100)?.toFixed(0) }}%):</span>
                            <span class="font-medium">${{ invoiceData.tax?.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-lg font-bold text-gray-800">Total:</span>
                            <span class="text-lg font-bold text-gray-800">${{ invoiceData.total?.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code & Actions -->
                <div class="p-6 border-t flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 mb-2">Scan to verify invoice</p>
                        <img :src="qrCodeUrl" alt="Invoice QR Code"
                            class="w-24 h-24 mx-auto border border-gray-200 rounded" v-if="qrCodeUrl" />
                        <div v-else class="w-24 h-24 bg-gray-100 rounded flex items-center justify-center mx-auto">
                            <span class="text-xs text-gray-400">Loading QR...</span>
                        </div>
                    </div>

                    <div v-if="printDownload" class="flex gap-3"> 
                        <button
                             @click="downloadInvoice"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            Download
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-800 text-white p-4 text-center text-sm">
                    <p>Thank you for choosing {{ company.name }}!</p>
                    <p class="mt-1">For questions, contact us at
                        <a :href="`mailto:${company.email}`" class="text-blue-300 hover:underline">{{ company.email
                        }}</a>
                        or call
                        <a :href="`tel:${company.phone}`" class="text-blue-300 hover:underline">{{ company.phone }}</a>
                    </p>
                    <p class="mt-2 text-gray-400">&copy; {{ new Date().getFullYear() }} {{ company.name }}. All rights
                        reserved.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    .container,
    .container * {
        visibility: visible;
    }

    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        box-shadow: none;
    }

    button {
        display: none !important;
    }
}
</style>