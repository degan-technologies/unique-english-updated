<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
      <!-- Page Header -->
      <header class="bg-white shadow p-4 flex items-center">
        <h1 class="text-2xl font-semibold text-gray-800 flex-1">
          Platform Settings & Security Management
        </h1>
      </header>
  
      <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar as Tabs -->
        <aside class="w-64 bg-white border-r">
          <div class="p-4">
            <div class="flex flex-col space-y-2">
              <button
                v-for="(tab, index) in tabs"
                :key="index"
                @click="selectedTab = tab.key"
                class="w-full text-left px-4 py-2 rounded border transition-colors
                       focus:outline-none 
                       hover:bg-blue-50
                       "
                :class="{
                  'bg-blue-100 border-blue-300 text-blue-800': selectedTab === tab.key,
                  'bg-white text-gray-700 border-gray-200': selectedTab !== tab.key
                }"
              >
                <div class="flex items-center space-x-2">
                  <span class="text-xl" v-html="tab.icon"></span>
                  <span class="font-medium">{{ tab.label }}</span>
                </div>
              </button>
            </div>
          </div>
        </aside>
  
        <!-- Dynamic Content Area -->
        <main class="flex-1 overflow-y-auto p-6">
          <!-- Platform Settings Panel -->
          <transition name="fade" mode="out-in">
            <div v-if="selectedTab === 'theme'" key="theme">
              <!-- Dashboard Theme & Branding Card -->
              <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center mb-4">
                  <div class="text-3xl text-blue-500 mr-2">
                    🎨
                  </div>
                  <h2 class="text-xl font-semibold">Dashboard Theme & Branding</h2>
                </div>
                <div class="flex flex-col md:flex-row gap-6">
                  <!-- Customization Controls -->
                  <div class="flex-1 space-y-4">
                    <!-- Color Picker -->
                    <div>
                      <label class="block text-gray-700 font-medium mb-1">Primary Color</label>
                      <input
                        type="color"
                        v-model="themeSettings.primaryColor"
                        class="w-16 h-8 border rounded"
                      />
                      <span class="ml-2 text-gray-600">{{ themeSettings.primaryColor }}</span>
                    </div>
                    <!-- Font Selector -->
                    <div>
                      <label class="block text-gray-700 font-medium mb-1">Heading Font</label>
                      <select v-model="themeSettings.headingFont" class="w-full border rounded p-2">
                        <option v-for="font in fonts" :key="font" :value="font">{{ font }}</option>
                      </select>
                    </div>
                    <!-- Logo Upload -->
                    <div>
                      <label class="block text-gray-700 font-medium mb-1">Upload Logo</label>
                      <input type="file" @change="handleLogoUpload" class="border rounded p-1 w-full" />
                      <div v-if="themeSettings.logoUrl" class="mt-2">
                        <img :src="themeSettings.logoUrl" alt="Logo Preview" class="h-16 object-contain" />
                      </div>
                    </div>
                    <!-- Layout Options -->
                    <div>
                      <label class="block text-gray-700 font-medium mb-1">Sidebar Layout</label>
                      <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                          <input
                            type="radio"
                            value="full"
                            v-model="themeSettings.sidebarLayout"
                            class="form-radio"
                          />
                          <span class="ml-2">Full</span>
                        </label>
                        <label class="inline-flex items-center">
                          <input
                            type="radio"
                            value="collapsed"
                            v-model="themeSettings.sidebarLayout"
                            class="form-radio"
                          />
                          <span class="ml-2">Collapsed</span>
                        </label>
                        <label class="inline-flex items-center">
                          <input
                            type="radio"
                            value="mini"
                            v-model="themeSettings.sidebarLayout"
                            class="form-radio"
                          />
                          <span class="ml-2">Mini</span>
                        </label>
                      </div>
                    </div>
                    <!-- Reset Button -->
                    <button
                      @click="resetThemeSettings"
                      class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors"
                    >
                      Reset to Default
                    </button>
                  </div>
                  <!-- Live Preview Panel -->
                  <div class="flex-1 bg-gray-50 rounded-lg shadow-inner p-4">
                    <h3 class="text-lg font-semibold mb-2">Live Preview</h3>
                    <div
                      class="p-4 rounded"
                      :style="{ backgroundColor: themeSettings.primaryColor, fontFamily: themeSettings.headingFont }"
                    >
                      <p class="text-white">Dashboard Header</p>
                      <p class="text-white mt-2">This is a live preview area.</p>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Feature Controls Card -->
              <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center mb-4">
                  <div class="text-3xl text-green-500 mr-2">
                    ⚙️
                  </div>
                  <h2 class="text-xl font-semibold">Platform Feature Controls</h2>
                </div>
                <div class="space-y-4">
                  <div v-for="(feature) in features" :key="feature.name" class="flex items-center justify-between border-b pb-2">
                    <div>
                      <h3 class="font-medium">{{ feature.name }}</h3>
                      <p class="text-sm text-gray-500">{{ feature.description }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" class="sr-only" v-model="feature.enabled" />
                      <div
                        class="w-11 h-6 bg-gray-200 rounded-full transition-colors duration-300"
                        :class="{'bg-green-500': feature.enabled}"
                      ></div>
                      <span
                        class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transform transition-transform duration-300"
                        :class="{'translate-x-5': feature.enabled}"
                      ></span>
                    </label>
                  </div>
                  <!-- Bulk Actions -->
                  <div class="pt-4">
                    <button
                      @click="toggleBulkFeatures(true)"
                      class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors mr-2"
                    >
                      Enable All
                    </button>
                    <button
                      @click="toggleBulkFeatures(false)"
                      class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors"
                    >
                      Disable All
                    </button>
                  </div>
                </div>
              </div>
  
              <!-- Commission Settings Card -->
              <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                  <div class="text-3xl text-purple-500 mr-2">
                    💲
                  </div>
                  <h2 class="text-xl font-semibold">Instructor Commission Settings</h2>
                </div>
                <div class="space-y-4">
                  <div>
                    <label class="block text-gray-700 font-medium mb-1">Commission Percentage: {{ commission }}%</label>
                    <input
                      type="range"
                      min="0"
                      max="100"
                      v-model="commission"
                      class="w-full"
                    />
                    <input
                      type="number"
                      min="0"
                      max="100"
                      v-model="commission"
                      class="mt-2 w-20 border rounded p-1"
                    />
                    <p class="text-sm text-gray-500 mt-1">
                      <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false" class="underline cursor-help">
                        What's this?
                      </span>
                      <span v-if="showTooltip" class="text-xs text-gray-600">This percentage affects instructor payouts.</span>
                    </p>
                  </div>
                  <button
                    @click="saveCommission"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
                  >
                    Save Commission
                  </button>
                </div>
              </div>
            </div>
          </transition>
  
          <!-- Security Management Panel -->
          <transition name="fade" mode="out-in">
            <div v-if="selectedTab === 'security'" key="security">
              <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center mb-4">
                  <div class="text-3xl text-red-500 mr-2">
                    🔒
                  </div>
                  <h2 class="text-xl font-semibold">Security & Access Control</h2>
                </div>
                <!-- Two-Factor Authentication Section -->
                <div class="mb-6">
                  <h3 class="font-medium text-gray-700 mb-2">Two-Factor Authentication (2FA)</h3>
                  <label class="flex items-center">
                    <input type="checkbox" v-model="securitySettings.twoFactorEnabled" class="mr-2" />
                    <span>{{ securitySettings.twoFactorEnabled ? 'Enabled' : 'Disabled' }}</span>
                  </label>
                  <button
                    v-if="securitySettings.twoFactorEnabled"
                    @click="open2FASetup"
                    class="mt-2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors"
                  >
                    Configure 2FA
                  </button>
                </div>
                <!-- Role-Based Access Control -->
                <div class="mb-6">
                  <h3 class="font-medium text-gray-700 mb-2">Role-Based Access Control</h3>
                  <p class="text-sm text-gray-500 mb-2">Drag and drop users between roles:</p>
                  <div class="border rounded p-4">
                    <ul>
                      <li
                        v-for="(user) in users"
                        :key="user.id"
                        class="flex items-center justify-between py-2 border-b last:border-b-0 cursor-move"
                        draggable="true"
                        @dragstart="onDragStart(user)"
                        @dragover.prevent
                        @drop="onDrop(user)"
                      >
                        <span>{{ user.name }}</span>
                        <span class="text-xs text-gray-500">{{ user.role }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- Activity Logs -->
                <div>
                  <h3 class="font-medium text-gray-700 mb-2">Activity Logs</h3>
                  <div class="flex items-center mb-4">
                    <input
                      type="text"
                      v-model="logsSearch"
                      placeholder="Search logs..."
                      class="border rounded p-2 flex-1 mr-2"
                    />
                    <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 transition-colors">
                      Export CSV
                    </button>
                  </div>
                  <div class="overflow-auto max-h-60">
                    <table class="min-w-full text-sm">
                      <thead>
                        <tr class="border-b">
                          <th class="py-2 px-4 text-left">Timestamp</th>
                          <th class="py-2 px-4 text-left">Activity</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(log, index) in filteredLogs" :key="index" class="border-b hover:bg-gray-50">
                          <td class="py-2 px-4">{{ log.timestamp }}</td>
                          <td class="py-2 px-4">{{ log.activity }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </main>
      </div>
  
      <!-- 2FA Setup Modal -->
      <transition name="fade">
        <div
          v-if="show2FAModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
          <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">2FA Setup Wizard</h2>
            <p class="mb-4 text-gray-600">Follow these steps to configure two-factor authentication:</p>
            <ol class="list-decimal list-inside mb-4">
              <li>Scan the QR code with your authenticator app.</li>
              <li>Enter the verification code.</li>
            </ol>
            <div class="mb-4">
              <img src="https://via.placeholder.com/150" alt="QR Code" class="mx-auto" />
            </div>
            <div class="mb-4">
              <input
                type="text"
                v-model="twoFactorCode"
                placeholder="Enter code"
                class="w-full border rounded p-2"
              />
            </div>
            <div class="flex justify-end space-x-2">
              <button @click="close2FASetup" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition-colors">
                Cancel
              </button>
              <button @click="confirm2FASetup" class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-600 transition-colors">
                Confirm
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  
  // Tab definitions for sidebar
  const tabs = [
    { key: 'theme', label: 'Platform Settings', icon: '🎨' },
    { key: 'security', label: 'Security Management', icon: '🔒' },
  ]
  const selectedTab = ref('theme')
  
  // Theme & Branding Settings
  const themeSettings = ref({
    primaryColor: '#3B82F6',
    headingFont: 'Arial, sans-serif',
    logoUrl: '',
    sidebarLayout: 'full',
  })
  const fonts = ['Arial, sans-serif', 'Helvetica, sans-serif', 'Roboto, sans-serif']
  
  function handleLogoUpload(event) {
    const file = event.target.files[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        themeSettings.value.logoUrl = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }
  function resetThemeSettings() {
    themeSettings.value = {
      primaryColor: '#3B82F6',
      headingFont: 'Arial, sans-serif',
      logoUrl: '',
      sidebarLayout: 'full',
    }
  }
  
  // Feature Controls
  const features = ref([
    { name: 'Live Sessions', description: 'Enable live streaming sessions', enabled: true },
    { name: 'Course Pricing', description: 'Control pricing options', enabled: false },
    { name: 'Advanced Analytics', description: 'Access detailed analytics', enabled: true },
  ])
  function toggleBulkFeatures(enable) {
    features.value = features.value.map(f => ({ ...f, enabled: enable }))
  }
  
  // Commission Settings
  const commission = ref(20)
  const showTooltip = ref(false)
  function saveCommission() {
    alert(`Commission set to ${commission.value}%`)
  }
  
  // Security Settings
  const securitySettings = ref({
    twoFactorEnabled: false,
  })
  // 2FA Modal State
  const show2FAModal = ref(false)
  const twoFactorCode = ref('')
  function open2FASetup() {
    show2FAModal.value = true
  }
  function close2FASetup() {
    show2FAModal.value = false
    twoFactorCode.value = ''
  }
  function confirm2FASetup() {
    alert(`2FA code ${twoFactorCode.value} confirmed!`)
    close2FASetup()
  }
  
  // Role-Based Access: Dummy user data and drag-drop stubs
  const users = ref([
    { id: 1, name: 'Alice', role: 'Instructor' },
    { id: 2, name: 'Bob', role: 'Admin' },
    { id: 3, name: 'Charlie', role: 'Instructor' },
  ])
  let draggedUser = null
  function onDragStart(user) {
    draggedUser = user
  }
  function onDrop(targetUser) {
    if (draggedUser && draggedUser !== targetUser) {
      const temp = draggedUser.role
      draggedUser.role = targetUser.role
      targetUser.role = temp
    }
    draggedUser = null
  }
  
  // Activity Logs
  const logsSearch = ref('')
  const logs = ref([
    { timestamp: '2025-01-01 10:00', activity: 'Logged in' },
    { timestamp: '2025-01-01 10:05', activity: 'Changed commission settings' },
    { timestamp: '2025-01-01 10:10', activity: 'Enabled 2FA' },
  ])
  const filteredLogs = computed(() => {
    if (!logsSearch.value) return logs.value
    return logs.value.filter(log => log.activity.toLowerCase().includes(logsSearch.value.toLowerCase()))
  })
  </script>
  
  <style>
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }
  </style>
  