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
                            src="/images/mlogo.jpg"
                            alt="Logo"
                            class="h-12 w-12 rounded-full object-cover border-2 border-white mr-3"
                        />
                        <h4 class="text-xl font-bold">Maraki English</h4>
                    </div>
                    <p class="text-sm">
                        Unlock your potential with innovative and engaging
                        English learning experiences.
                    </p>
                    <p class="mt-3 text-sm">
                        Powered by
                        <a
                            href="https://www.degantechnologies.com"
                            target="_blank"
                            class="text-orange-400 hover:underline"
                        >
                            Degan Technologies
                        </a>
                    </p>
                    <p class="mt-1 text-sm">
                        &copy; 2025 Maraki English. All rights reserved.
                    </p>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Contact Us</h3>
                    <div
                        class="flex justify-center md:justify-start items-center mb-3"
                    >
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start md:items-center gap-2">
                                <i
                                    class="fas fa-map-marker-alt text-orange-400 mt-1"
                                ></i>
                                <span>
                                    Megenangna, Metababer Building 4th
                                    floor</span
                                >
                            </li>
                            <li class="flex items-start md:items-center gap-2">
                                <i
                                    class="fas fa-phone-alt text-orange-400 mt-1"
                                ></i>
                                <span>+25193 843 2037 </span>
                            </li>
                            <li class="flex items-start md:items-center gap-2">
                                <i
                                    class="fas fa-envelope text-orange-400 mt-1"
                                ></i>
                                <span>abesho9135@gmail.com</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                    <div
                        class="flex justify-center md:justify-start space-x-4 mb-5"
                    >
                        <a
                            href="https:www.youtube.com/@marakienglishwithabi8159"
                            aria-label="YouTube"
                            class="hover:text-red-600 transition"
                            target="_blank"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'youtube']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="https://www.facebook.com/Marakienglish?mibextid=wwXIfr&mibextid=wwXIfr"
                            aria-label="Facebook"
                            class="hover:text-blue-600 transition"
                            target="_blank"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'facebook']"
                                class="w-6 h-6"
                            />
                        </a>

                        <a
                            href="https://www.tiktok.com/@marakienglish?_t=ZM-8wXGjB534XI&_r=1"
                            aria-label="TikTok"
                            class="hover:text-black transition"
                            target="_blank"
                        >
                            <font-awesome-icon
                                :icon="['fab', 'tiktok']"
                                class="w-6 h-6"
                            />
                        </a>
                        <a
                            href="https:t.me/marakieng"
                            aria-label="Telegram"
                            class="hover:text-blue-500 transition"
                            target="_blank"
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
    faYoutube,
    faFacebook,
    faTiktok,
    faTelegram,
} from "@fortawesome/free-brands-svg-icons";

// Add FontAwesome icons to the library
library.add(faYoutube, faFacebook, faTiktok, faTelegram);
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
