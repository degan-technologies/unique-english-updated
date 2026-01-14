import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                kanit: ["Kanit", "sans-serif"],
                noto: ["Noto Sans SC", "sans-serif"],
                roboto: ["Roboto", "sans-serif"],
            },
            colors: {
                lime: {
                    50: "#fff7ed",
                    100: "#ffedd5",
                    200: "#fed7aa",
                    300: "#f97316",
                    400: "#f97316",
                    500: "#f97316",
                    600: "#ea580c",
                    700: "#f97316",
                    800: "#ea580c",
                    900: "#f97316",
                },
            },
        },
    },
    plugins: [require("@tailwindcss/typography")],
};
