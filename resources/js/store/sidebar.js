import { defineStore } from 'pinia'

export const useSidebarStore = defineStore('sidebar', {
  state: () => ({
    // Open state based on screen size and stored preference
    sideBarOpen: window.innerWidth >= 1024
      ? JSON.parse(localStorage.getItem('sideBarOpen')) || false
      : false,
    // Collapsed state (true = collapsed, false = expanded)
    sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed')) || false
  }),
  getters: {
    isSidebarOpen: (state) => state.sideBarOpen,
    isSidebarCollapsed: (state) => state.sidebarCollapsed
  },
  actions: {
    toggleSidebar() {
      this.sideBarOpen = !this.sideBarOpen
      localStorage.setItem('sideBarOpen', JSON.stringify(this.sideBarOpen))
    },
    checkScreenSize() {
      this.sideBarOpen = window.innerWidth >= 1024
        ? JSON.parse(localStorage.getItem('sideBarOpen')) || false
        : false
    },
    closeSidebarOnMobile() {
      if (window.innerWidth < 1024) {
        this.sideBarOpen = false
        localStorage.setItem('sideBarOpen', JSON.stringify(this.sideBarOpen))
      }
    },
    toggleCollapse() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      localStorage.setItem('sidebarCollapsed', JSON.stringify(this.sidebarCollapsed))
    }
  }
})
