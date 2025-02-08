import { defineStore } from "pinia";

export const useThemeStore = defineStore("theme", {
  state: () => ({
    theme: localStorage.getItem("theme") || "light",
  }),
  actions: {
    toggleTheme() {
      this.theme = this.theme === "light" ? "dark" : "light";
      
      document.documentElement.classList.toggle("dark", this.theme === "dark");
      localStorage.setItem("theme", this.theme);
    },
    applyTheme() {
      document.documentElement.classList.toggle("dark", this.theme === "dark");
    },
  },
});
