<template>
    <footer class="bg-gray-800 text-gray-200 py-6">
        <div class="container mx-auto px-4">
            <!-- Grid Layout for Desktop and Mobile -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Company Info -->
                <div>
                    <h4 class="text-lg font-bold mb-3">
                        <img
                            src="/images/logo.jpg"
                            alt="Logo"
                            class="h-12 w-12 rounded-full object-cover border-2 border-white"
                        />
                    </h4>
                    <p class="text-sm">
                        Building seamless experiences with cutting-edge web
                        technology.
                    </p>
                    <p class="mt-3 text-sm">
                        &copy; 2025 Unique English. All rights reserved.
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h3 class="text-lg font-bold mb-3">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#home" class="hover:text-gray-400">Home</a>
                        </li>
                        <li>
                            <a href="#about" class="hover:text-gray-400"
                                >About</a
                            >
                        </li>
                        <li>
                            <a href="#services" class="hover:text-gray-400"
                                >Services</a
                            >
                        </li>
                        <li>
                            <a href="#contact" class="hover:text-gray-400"
                                >Contact</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Social Media Links -->
                <div>
                    <h3 class="text-lg font-bold mb-3">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a
                            href="#"
                            class="hover:text-gray-400"
                            aria-label="Twitter"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'twitter']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            class="hover:text-gray-400"
                            aria-label="Facebook"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'facebook']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            class="hover:text-gray-400"
                            aria-label="Instagram"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'instagram']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            class="hover:text-gray-400"
                            aria-label="TikTok"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'tiktok']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            class="hover:text-gray-400"
                            aria-label="Telegram"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'telegram']"
                                class="w-6 h-6"
                            />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
// Import FontAwesome icons
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    faTwitter,
    faFacebook,
    faInstagram,
    faTiktok,
    faTelegram,
} from "@fortawesome/free-brands-svg-icons";

// Add the icons to the library
library.add(faTwitter, faFacebook, faInstagram, faTiktok, faTelegram);
</script>

<style scoped>
/* Add your custom styles here */
</style>
