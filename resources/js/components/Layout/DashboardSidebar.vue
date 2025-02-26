<script setup>
    import { ref } from 'vue'
    import { storeToRefs } from 'pinia'
    import { useRoute, useRouter } from "vue-router";

    import { useSidebarStore } from '@/store/useSidebarStore'

    const router = useRouter();
    const emit = defineEmits(['selectContent'])
    
    const sidebarStore = useSidebarStore()
    const { sideBarOpen, sidebarCollapsed, selectedContent, profile,
            dashboard, users, courses, payments, liveSssions,
            messaging, } = storeToRefs(sidebarStore);

    const mainItems = [
        {
            label: 'Dashboard',
            route: dashboard.value,
            description: 'Overview of revenue, users, courses, and system health.',
            icon: 'home'
        },
        {
            label: 'Users Management',
            route: users.value,
            description: 'Manage students, instructors, and admins.',
            icon: 'user-group'
        },
        {
            label: 'Course Management',
            route: courses.value,
            description: 'Manage courses and books.',
            icon: 'book-open'
        },
        {
            label: 'Payments & Revenue',
            route: payments.value,
            description: 'Transactions, earnings, payouts.',
            icon: 'dollar-sign'
        },
        {
            label: 'Live Sessions',
            route: liveSssions.value,
            description: 'Track, join, schedule live classes.',
            icon: 'video'
        },
        {
            label: 'Messaging & Notifications',
            route: messaging.value,
            description: 'Send & manage messages.',
            icon: 'envelope'
        },
        {
            label: 'System Reports & Analytics',
            route: 'reports',
            description: 'View performance metrics.',
            icon: 'chart-simple'
        },
        {
            label: 'Settings & Customization',
            route: 'settings',
            description: 'Configure platform branding & security.',
            icon: 'gear'
        }
    ]

    function selectContent(changeTab) {
        router.push({
            name: 'instructor',
            query: {
              currentTab:changeTab,
            }
        });

        selectedContent.value = changeTab;
    }
</script>

<template>
  <!-- Sidebar container: slides in/out on mobile -->
  <aside
    :class="[
    'fixed top-0 left-0 h-screen z-50 transition-transform duration-300 ease-in-out md:relative md:translate-x-0',
      sideBarOpen ? 'translate-x-0' : '-translate-x-full'
    ]" >
    <!-- Sidebar panel: width changes when sidebarCollapsed -->
    <div
      :class="[
        sidebarCollapsed ? 'w-16' : 'w-64',
        'bg-gray-50 border-r border-lime-300 h-full relative flex flex-col transition-all duration-300'
      ]" >
      <!-- Header with logo and mobile close button (desktop: no close icon) -->
      <div class="flex items-center justify-between h-20 px-4 border-b border-lime-300">
        <!-- Logo: Full Logo (if not sidebarCollapsed) -->
        <div v-if="!sidebarCollapsed" class="flex items-center animate-fadeIn">
          <img 
            src="images/logo.jpg" 
            alt="Full Logo" 
            class="h-12 w-auto object-contain"
          />
          <span class="ml-2 font-semibold text-xl text-lime-500">UniqueEnglish</span>
        </div>
        
        <!-- Logo: Abbreviated Logo (if sidebarCollapsed) -->
        <div v-else class="flex items-center animate-fadeIn">
          <img 
            src="images/logo.jpg" 
            alt="Logo Abbreviation" 
            class="h-10 w-auto object-contain"
          />
        </div>
        
        <!-- Mobile Close Icon (only visible on mobile) -->
        <button 
          @click="sidebarStore.toggleSidebar()" 
          class="text-lime-500 hover:text-lime-600 md:hidden transition-colors"
          title="Close Sidebar"
        >
          <span class="material-icons animate-spinIn">close</span>
        </button>
      </div>

      <!-- Scrollable menu content -->
      <div class="flex-1 h-screen overflow-y-auto scrollbar">
        <!-- MAIN Section -->
        <div class="px-4 py-2">
          <p v-if="!sidebarCollapsed" class="text-sm font-semibold mb-2 text-lime-600 animate-fadeIn">MAIN</p>
          <ul>
            <li
              v-for="(item, index) in mainItems"
              :key="index"
              @click="selectContent(item?.route)"
              class="flex items-center  space-x-2 p-2 hover:bg-lime-100 rounded-lg cursor-pointer transition-all duration-200"
              :title="item.description" >
              <i 
                :class="{
                  ['fa-' + item.icon]: true
                }"
                class="fa-solid w-6 text-lime-500 transition-colors duration-200 text-xl"></i>
              <span v-if="!sidebarCollapsed" class="text-gray-800 animate-fadeIn">{{ item.label }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Collapse/Expand Button at bottom right -->
      <button
        @click=" sidebarStore.toggleCollapse()"
        class="absolute bottom-2 right-2 p-2 rounded-full bg-lime-300 hover:bg-lime-200 transition-colors duration-200" >
        <i 
          :class="sidebarCollapsed ? 'rotate-0' : 'rotate-180'"
          class="fa-solid fa-angle-left text-2xl w-6 h-6 text-lime-900 transform transition-transform duration-300 "></i>
      </button>
    </div>
  </aside>
</template>



<!-- Import Material Icons font and add custom animations -->
<style>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes spinIn {
  from {
    transform: rotate(-90deg);
    opacity: 0;
  }
  to {
    transform: rotate(0deg);
    opacity: 1;
  }
}
.animate-spinIn {
  animation: spinIn 0.5s ease-in-out;
}
</style>
