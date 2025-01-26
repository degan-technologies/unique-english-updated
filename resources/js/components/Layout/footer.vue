<template>
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <!-- Grid Layout -->
            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left"
            >
                <!-- Company Info -->
                <div>
                    <div
                        class="flex justify-center md:justify-start items-center mb-3"
                    >
                        <img
                            src="/images/logo.jpg"
                            alt="Logo"
                            class="h-12 w-12 rounded-full object-cover border-2 border-white mr-3"
                        />
                        <h4 class="text-xl font-bold">Unique English</h4>
                    </div>
                    <p class="text-sm">
                        Unlock your potential with innovative and engaging
                        English learning experiences.
                    </p>
                    <p class="mt-3 text-sm">
                        Developed by
                        <a
                            href="https://www.degantechnologies.com"
                            target="_blank"
                            class="text-lime-400 hover:underline"
                        >
                            Degan Technologies </a
                        >.
                    </p>
                    <p class="mt-1 text-sm">
                        &copy; 2025 Unique English. All rights reserved.
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a
                                href="#home"
                                class="hover:text-lime-400 transition"
                                >Home</a
                            >
                        </li>
                        <li>
                            <a
                                href="#about"
                                class="hover:text-lime-400 transition"
                                >About</a
                            >
                        </li>
                        <li>
                            <a
                                href="#services"
                                class="hover:text-lime-400 transition"
                                >Services</a
                            >
                        </li>
                        <li>
                            <a
                                href="#contact"
                                class="hover:text-lime-400 transition"
                                >Contact</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Social Media Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                    <div class="flex justify-center md:justify-start space-x-4">
                        <a
                            href="#"
                            aria-label="X"
                            class="hover:text-gray-400 transition"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'x-twitter']"
                                class="w-6 h-6 text-gray-300 hover:text-black"
                            />
                        </a>
                        <a
                            href="#"
                            aria-label="Facebook"
                            class="hover:text-blue-600 transition"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'facebook']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            aria-label="Instagram"
                            class="hover:text-pink-500 transition"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'instagram']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            aria-label="TikTok"
                            class="hover:text-black transition"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'tiktok']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="#"
                            aria-label="Telegram"
                            class="hover:text-blue-500 transition"
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
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    faXTwitter,
    faFacebook,
    faInstagram,
    faTiktok,
    faTelegram,
} from "@fortawesome/free-brands-svg-icons";

// Add FontAwesome icons to the library
library.add(faXTwitter, faFacebook, faInstagram, faTiktok, faTelegram);
</script>

<style scoped>
footer {
    /* Center the footer content on smaller devices */
    text-align: center;
}

footer h4,
footer h3 {
    margin-bottom: 8px;
}

/* Styling for the social media icons */
footer a {
    transition: color 0.3s ease;
}

/* Adjust for responsiveness */
@media (min-width: 768px) {
    footer {
        text-align: left;
    }
}
</style>
