import Axios from "axios";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useAppStore = defineStore("useAppStore", () => {
    const frontLang = ref({});
    const logoImage = ref("/images/logo.png");
    const facebook = ref("/socialMediaIcons/face.png");
    const lang = ref("en");
    const google = ref("/socialMediaIcons/google.png");
    const commission = ref(commission);
    const profileUpdated = ref(false);
    const exploreCourses = ref(false);
    const selectedComponentId = ref(null);
    //hero section
    const hero = ref({
        title: "",
        description: "",
        logo: null,
        banner: null,
        background_image: null,
        selectedLogo: null,
        selectedbanner: null,
        selectedBackground: null,
    });

    // Fetch front languages
    function fetchFrontLanguages() {
        return Axios.get(`/language/${lang.value}`).then(
            (response) => (frontLang.value = response.data),
        );
    }

    function getHeroSection() {
        return Axios.get("/api/hero-section")
            .then((res) => {
                const heroData = res.data.data;
                hero.value = {
                    title: heroData.title || "",
                    description: heroData.description || "",
                    logo: heroData.logo
                        ? heroData.logo.startsWith("http")
                            ? heroData.logo
                            : `/storage/${heroData.logo}`
                        : null,
                    banner: heroData.banner
                        ? heroData.banner.startsWith("http")
                            ? heroData.banner
                            : `/storage/${heroData.banner}`
                        : null,
                    background_image: heroData.background_image
                        ? heroData.background_image.startsWith("http")
                            ? heroData.background_image
                            : `/storage/${heroData.background_image}`
                        : null,
                    selectedLogo: null,
                    selectedbanner: null,
                    selectedBackground: null,
                };

                // Update logo image for navbar
                if (heroData.logo) {
                    logoImage.value = heroData.logo.startsWith("http")
                        ? heroData.logo
                        : `/storage/${heroData.logo}`;
                }
            })
            .catch((error) => {
                console.error("Error fetching hero section:", error);
                // Initialize with empty values on error
                hero.value = {
                    title: "",
                    description: "",
                    logo: null,
                    banner: null,
                    background_image: null,
                    selectedLogo: null,
                    selectedbanner: null,
                    selectedBackground: null,
                };
                });
    }

    function scrollToSection(sectionId) {
        setTimeout(() => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        }, 100);
    }

    return {
        logoImage,
        lang,
        frontLang,
        fetchFrontLanguages,
        facebook,
        google,
        commission,

        profileUpdated,

        hero,
        getHeroSection,
        exploreCourses,
        selectedComponentId,
        scrollToSection,
    };
});
