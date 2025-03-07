<template>
  <aside class="w-full md:w-80 bg-white border-t md:border-l md:border-t-0 overflow-auto">
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Activity Feed</h2>

      <!-- Activity Filter -->
      <div class="mb-4">
        <select v-model="activityFilter" class="w-full border-gray-300 rounded focus:outline-none">
          <option value="">All Activities</option>
          <option value="login">Logins</option>
          <option value="enroll">Course Enrollments</option>
          <option value="complete">Completions</option>
        </select>
      </div>

      <!-- Activity List -->
      <ul class="space-y-4">
        <li v-for="(activity, index) in filteredActivityFeed" :key="index" class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <span class="material-icons text-lime-700">info</span>
          </div>
          <div>
            <p class="text-sm text-gray-800">
              <strong>{{ getUserFullName(activity.user) }}</strong> {{ activity.description }}
            </p>
            <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
          </div>
        </li>
      </ul>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// State variables
const activityFeed = ref([])
const activityFilter = ref('')

// Fetch activities from backend
const fetchActivities = async () => {
  try {
    const response = await axios.get('/api/courses/activities') // Adjust API URL as needed
    activityFeed.value = response.data
  } catch (error) {
    console.error('Error fetching activities:', error)
  }
}

// Computed property to filter activity feed
const filteredActivityFeed = computed(() => {
  if (!activityFilter.value) return activityFeed.value
  return activityFeed.value.filter(activity => activity.type === activityFilter.value)
})

// Helper function to get user's full name
const getUserFullName = (user) => {
  return user ? `${user.first_name} ${user.middle_name}` : 'Unknown User'
}

// Helper function to format date
const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

// Fetch data on component mount
onMounted(fetchActivities)
</script>
