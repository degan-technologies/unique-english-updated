<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
      <!-- Header -->
      <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Notifications & Messaging</h1>
          <p class="text-sm text-gray-500">Communicate seamlessly with students and staff</p>
        </div>
        <div class="space-x-3">
          <button
            @click="openNewAnnouncement"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
          >
            New Announcement
          </button>
          <button
            @click="openNewMessage"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
          >
            New Message
          </button>
        </div>
      </header>
  
      <!-- Tabs -->
      <div class="bg-white shadow mt-4 mx-4 rounded">
        <nav class="flex">
          <button
            class="flex-1 py-3 text-center font-medium"
            :class="{
              'border-b-4 border-blue-600 text-blue-600': activeTab === 'announcements',
              'text-gray-600 hover:text-blue-600': activeTab !== 'announcements'
            }"
            @click="activeTab = 'announcements'"
          >
            Announcements
          </button>
          <button
            class="flex-1 py-3 text-center font-medium"
            :class="{
              'border-b-4 border-blue-600 text-blue-600': activeTab === 'chat',
              'text-gray-600 hover:text-blue-600': activeTab !== 'chat'
            }"
            @click="activeTab = 'chat'"
          >
            Chat
          </button>
        </nav>
      </div>
  
      <!-- Main Content -->
      <div class="flex-1 p-4">
        <!-- Announcements Tab -->
        <div v-if="activeTab === 'announcements'" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <!-- Announcements History -->
          <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Scheduled & Past Announcements</h2>
            <ul class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
              <li
                v-for="(announcement, index) in announcements"
                :key="index"
                class="py-3 flex justify-between items-center"
              >
                <div>
                  <p class="font-medium">{{ announcement.title }}</p>
                  <p class="text-sm text-gray-500">{{ announcement.date }}</p>
                </div>
                <span
                  :class="{
                    'bg-green-100 text-green-700': announcement.status === 'Sent',
                    'bg-yellow-100 text-yellow-700': announcement.status === 'Pending',
                    'bg-red-100 text-red-700': announcement.status === 'Failed'
                  }"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ announcement.status }}
                </span>
              </li>
            </ul>
          </div>
  
          <!-- New Announcement Form -->
          <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Create New Announcement</h2>
            <form @submit.prevent="submitAnnouncement">
              <div class="mb-4">
                <label class="block text-gray-700 mb-1">Title</label>
                <input
                  type="text"
                  v-model="newAnnouncement.title"
                  placeholder="Enter announcement title"
                  class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 mb-1">Message</label>
                <!-- For a full WYSIWYG, integrate a rich-text editor here -->
                <textarea
                  v-model="newAnnouncement.message"
                  placeholder="Enter your message"
                  class="w-full border border-gray-300 rounded px-3 py-2 h-32 focus:outline-none focus:ring focus:border-blue-300"
                  required
                ></textarea>
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 mb-1">Schedule Date & Time</label>
                <!-- Replace with a date picker component if needed -->
                <input
                  type="datetime-local"
                  v-model="newAnnouncement.scheduledAt"
                  class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
                  required
                />
              </div>
              <div class="flex items-center space-x-3">
                <button type="button" @click="previewAnnouncement" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                  Preview
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                  Schedule Announcement
                </button>
              </div>
            </form>
          </div>
        </div>
  
        <!-- Chat Tab -->
        <div v-if="activeTab === 'chat'" class="flex h-full">
          <!-- Conversations List -->
          <aside class="w-1/3 bg-white rounded shadow p-4 mr-4 flex flex-col">
            <div class="mb-4">
              <input
                type="text"
                placeholder="Search..."
                v-model="chatSearch"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300"
              />
            </div>
            <ul class="flex-1 overflow-y-auto divide-y divide-gray-200">
              <li
                v-for="(conversation, index) in filteredConversations"
                :key="index"
                @click="selectConversation(conversation)"
                class="flex items-center p-2 cursor-pointer hover:bg-gray-100 rounded"
                :class="{'bg-gray-100': activeConversation && activeConversation.id === conversation.id}"
              >
                <img
                  :src="conversation.avatar"
                  alt="avatar"
                  class="w-10 h-10 rounded-full mr-3 object-cover"
                />
                <div>
                  <p class="font-medium">{{ conversation.name }}</p>
                  <p class="text-sm text-gray-500">{{ conversation.lastMessage }}</p>
                </div>
                <div v-if="conversation.unread" class="ml-auto">
                  <span class="bg-blue-600 text-white text-xs rounded-full px-2 py-0.5">
                    {{ conversation.unread }}
                  </span>
                </div>
              </li>
            </ul>
          </aside>
  
          <!-- Chat Window -->
          <section class="flex-1 bg-white rounded shadow flex flex-col">
            <div class="flex-1 p-4 overflow-y-auto" ref="chatWindow">
              <div v-if="activeConversation">
                <div
                  v-for="(message, index) in activeConversation.messages"
                  :key="index"
                  class="mb-4 flex"
                  :class="{
                    'justify-end': message.from === 'me',
                    'justify-start': message.from !== 'me'
                  }"
                >
                  <div
                    class="max-w-xs p-3 rounded-lg"
                    :class="{
                      'bg-blue-600 text-white': message.from === 'me',
                      'bg-gray-200 text-gray-800': message.from !== 'me'
                    }"
                  >
                    <p>{{ message.text }}</p>
                    <span class="text-xs text-gray-500 block mt-1 text-right">{{ message.time }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="text-center text-gray-500 mt-10">
                Select a conversation to start chatting.
              </div>
            </div>
  
            <!-- Message Input -->
            <div class="p-4 border-t border-gray-200">
              <form @submit.prevent="sendMessage" class="flex items-center">
                <input
                  type="text"
                  v-model="newMessage"
                  placeholder="Type your message..."
                  class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring focus:border-blue-300"
                  required
                />
                <button type="submit" class="ml-3 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                  Send
                </button>
              </form>
            </div>
          </section>
        </div>
      </div>
  
      <!-- Announcement Preview Modal -->
      <div
        v-if="showPreview"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
      >
        <div class="bg-white rounded shadow-lg w-11/12 md:w-1/2 p-6">
          <h3 class="text-xl font-semibold mb-4">Announcement Preview</h3>
          <div class="border p-4 rounded">
            <h4 class="font-bold text-lg mb-2">{{ newAnnouncement.title }}</h4>
            <p class="mb-2">{{ newAnnouncement.message }}</p>
            <p class="text-sm text-gray-500">Scheduled at: {{ newAnnouncement.scheduledAt }}</p>
          </div>
          <div class="mt-4 flex justify-end space-x-3">
            <button @click="showPreview = false" class="px-4 py-2 border rounded hover:bg-gray-100">
              Close
            </button>
            <button @click="confirmAnnouncement" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              Confirm & Schedule
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, nextTick } from 'vue'
  
  // Active tab state: 'announcements' or 'chat'
  const activeTab = ref('announcements')
  
  // ---------------------- Announcements Section ----------------------
  const announcements = ref([
    {
      title: 'System Maintenance',
      date: '2025-02-10 14:00',
      status: 'Pending',
    },
    {
      title: 'New Course Announcement',
      date: '2025-01-28 09:00',
      status: 'Sent',
    },
    // Additional announcements...
  ])
  
  const newAnnouncement = ref({
    title: '',
    message: '',
    scheduledAt: '',
  })
  
  const showPreview = ref(false)
  
  function previewAnnouncement() {
    // Show the preview modal
    showPreview.value = true
  }
  
  function confirmAnnouncement() {
    // Add the new announcement to the list (simulate scheduling)
    announcements.value.unshift({
      title: newAnnouncement.value.title,
      date: newAnnouncement.value.scheduledAt,
      status: 'Pending',
    })
    // Reset form and close modal
    newAnnouncement.value = { title: '', message: '', scheduledAt: '' }
    showPreview.value = false
  }
  
  function submitAnnouncement() {
    // Shortcut: directly confirm without preview
    confirmAnnouncement()
  }
  
  function openNewAnnouncement() {
    activeTab.value = 'announcements'
    // Optionally, scroll to or open the new announcement form
  }
  
  // ---------------------- Chat Section ----------------------
  const chatSearch = ref('')
  const conversations = ref([
    {
      id: 1,
      name: 'John Doe',
      avatar: 'https://i.pravatar.cc/40?img=1',
      lastMessage: 'See you tomorrow!',
      unread: 2,
      messages: [
        { from: 'other', text: 'Hi, are you available for a quick chat?', time: '10:15 AM' },
        { from: 'me', text: 'Sure, what’s up?', time: '10:17 AM' },
        { from: 'other', text: 'Let’s discuss the project.', time: '10:20 AM' },
      ],
    },
    {
      id: 2,
      name: 'Jane Smith',
      avatar: 'https://i.pravatar.cc/40?img=2',
      lastMessage: 'Got it, thanks!',
      unread: 0,
      messages: [
        { from: 'other', text: 'Please check the updated syllabus.', time: '9:00 AM' },
        { from: 'me', text: 'Will do!', time: '9:05 AM' },
      ],
    },
    // More conversations...
  ])
  
  const activeConversation = ref(null)
  const newMessage = ref('')
  
  const filteredConversations = computed(() => {
    if (!chatSearch.value) return conversations.value
    return conversations.value.filter((conv) =>
      conv.name.toLowerCase().includes(chatSearch.value.toLowerCase())
    )
  })
  
  function selectConversation(conversation) {
    activeConversation.value = conversation
    // Mark all messages as read
    conversation.unread = 0
    // Optionally, scroll chat window to the bottom
    nextTick(() => {
      if (chatWindow.value) chatWindow.value.scrollTop = chatWindow.value.scrollHeight
    })
  }
  
  function sendMessage() {
    if (!activeConversation.value) return
    activeConversation.value.messages.push({
      from: 'me',
      text: newMessage.value,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    })
    newMessage.value = ''
    // Auto-scroll to the latest message
    nextTick(() => {
      if (chatWindow.value) chatWindow.value.scrollTop = chatWindow.value.scrollHeight
    })
  }
  
  function openNewMessage() {
    activeTab.value = 'chat'
    // Optionally, trigger a new chat creation modal
  }
  
  // Ref for chat window DOM element
  const chatWindow = ref(null)
  </script>
  
  <style scoped>
  /* Custom scrollbar for chat window */
  ::-webkit-scrollbar {
    width: 6px;
  }
  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  ::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }
  </style>
  