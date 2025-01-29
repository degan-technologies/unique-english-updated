    <script>
    export default {
      data() {
        return {
          instructorProfile: {
            name: "John Doe",
            coursesCreated: 15,
            liveSessions: 10,
            averageRating: 4.9,
          },
          students: [
            { name: "Amelia Collier", progress: 95 },
            { name: "Estelle Baldwin", progress: 75 },
            { name: "Amanda Wood", progress: 60 },
            { name: "Lilly Tano", progress: 85 },
          ],
          courses: [],
          placeholderCourses: [
            {
              id: 1,
              title: "React Development",
              description: "Learn to build dynamic web apps using React.js.",
              thumbnail: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-4.0.3&q=80&w=400",
              progress: 50,
            },
            {
              id: 2,
              title: "Python for Beginners",
              description: "Master Python programming from scratch.",
              thumbnail: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-4.0.3&q=80&w=400",
              progress: 70,
            },
            {
              id: 3,
              title: "UI/UX Design Essentials",
              description: "Design modern interfaces with Figma and Adobe XD.",
              thumbnail: "https://images.unsplash.com/photo-1518770660439-4636190af475?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-4.0.3&q=80&w=400",
              progress: 30,
            },
          ],
          liveSession: {
            topic: "Advanced JavaScript Techniques",
            scheduledAt: "2024-02-22 10:00 AM",
          },
          newLiveSession: {
            topic: "",
            scheduledAt: "",
          },
          pastSessions: [
            { id: 1, topic: "React Basics", date: "2024-01-15" },
            { id: 2, topic: "Vue.js Workshop", date: "2024-01-10" },
            { id: 3, topic: "Node.js APIs", date: "2024-01-05" },
          ],
        };
      },
      computed: {
        populatedCourses() {
          return this.courses.length > 0 ? this.courses : this.placeholderCourses;
        },
        countdown() {
          const now = new Date();
          const sessionTime = new Date(this.liveSession.scheduledAt);
          const diffMs = sessionTime - now;
          const hours = Math.floor(diffMs / (1000 * 60 * 60));
          const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
          return `${hours} hours and ${minutes} minutes`;
        },
      },
      methods: {
        addNewCourse() {
          alert("Redirecting to add new course page...");
        },
        startLiveSession() {
          alert("Starting the live session...");
        },
        scheduleLiveSession() {
          alert(`Scheduled a new session: ${this.newLiveSession.topic}`);
          this.newLiveSession = { topic: "", scheduledAt: "" };
        },
      },
    };
    </script>
      <template>
      <div class="flex flex-col md:flex-row h-screen font-sans">

      <!-- Main Content -->
      <div class="flex-1 flex flex-col bg-gray-100 p-5 overflow-y-auto">
      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto">
        <!-- Welcome and Progress Section -->
        <div class="flex flex-col md:flex-row justify-between gap-4 mb-5">
          <!-- Welcome Card -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden w-full md:w-1/2">
            <img
              class="w-full h-32 object-cover"
              src="https://images.unsplash.com/photo-1573497491208-6b1acb260507?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDIwfHx3ZWxjb218ZW58MHx8fHwxNjk0MDMwMjk4&ixlib=rb-4.0.3&q=80&w=600"
              alt="Welcome Back"
            />
            <div class="p-4">
              <h2 class="text-xl font-bold">Welcome Back, Tiana!</h2>
              <p>Your students completed <span class="text-green-500 font-bold">80%</span> of the tasks. <strong>Great job!</strong> Keep it up!</p>
              <button class="mt-2 bg-blue-500 text-white rounded-lg px-4 py-2">View Progress</button>
            </div>
          </div>

          <!-- Student Progress Card -->
          <div class="bg-white rounded-lg shadow-md w-full md:w-1/2 p-4">
            <h3 class="text-lg font-bold">My Students</h3>
            <div class="space-y-2">
              <div class="bg-gray-100 p-3 rounded">
                <div class="flex justify-between">
                  <span class="font-semibold">Amelia Collier</span>
                  <span class="text-green-500">95%</span>
                </div>
                <div class="bg-gray-300 rounded h-2">
                  <div class="bg-green-500 h-full" :style="{ width: '95%' }"></div>
                </div>
              </div>
              <div class="bg-gray-100 p-3 rounded">
                <div class="flex justify-between">
                  <span class="font-semibold">Estelle Baldwin</span>
                  <span class="text-green-500">75%</span>
                </div>
                <div class="bg-gray-300 rounded h-2">
                  <div class="bg-green-500 h-full" :style="{ width: '75%' }"></div>
                </div>
              </div>
              <div class="bg-gray-100 p-3 rounded">
                <div class="flex justify-between">
                  <span class="font-semibold">Amanda Wood</span>
                  <span class="text-green-500">60%</span>
                </div>
                <div class="bg-gray-300 rounded h-2">
                  <div class="bg-green-500 h-full" :style="{ width: '60%' }"></div>
                </div>
              </div>
              <div class="bg-gray-100 p-3 rounded">
                <div class="flex justify-between">
                  <span class="font-semibold">Lilly Tano</span>
                  <span class="text-green-500">85%</span>
                </div>
                <div class="bg-gray-300 rounded h-2">
                  <div class="bg-green-500 h-full" :style="{ width: '85%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 mb-5">
          <div class="flex items-center">
            <div class="w-16 h-16 rounded-full border-4 border-green-500 flex items-center justify-center">
              <div class="text-lg font-bold">84%</div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-bold">Working Hours</h3>
              <p>You’ve completed <span class="text-green-500 font-bold">84%</span> of your allocated working hours this week.</p>
            </div>
          </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4">
          <div class="bg-white rounded-lg shadow-md flex-1 p-4">
            <h3 class="text-lg font-bold mb-3">Lessons</h3>
            <ul class="space-y-2">
              <li>
                <div class="bg-gray-100 p-3 rounded">
                  <h4 class="font-semibold">Common English</h4>
                  <p>Thu 13 PM - 15 PM</p>
                </div>
              </li>
              <li>
                <div class="bg-gray-100 p-3 rounded">
                  <h4 class="font-semibold">Speaking Club</h4>
                  <p>Thu 15 PM - 17 PM</p>
                </div>
              </li>
            </ul>
          </div>

          <aside class="bg-white rounded-lg shadow-md w-full md:w-1/4 p-4">
            <div class="mb-5">
              <h3 class="text-lg font-bold mb-3">January 2023</h3>
              <div class="grid grid-cols-7 gap-1">
                <div class="font-semibold text-center">Sun</div>
                <div class="font-semibold text-center">Mon</div>
                <div class="font-semibold text-center">Tue</div>
                <div class="font-semibold text-center">Wed</div>
                <div class="font-semibold text-center">Thu</div>
                <div class="font-semibold text-center">Fri</div>
                <div class="font-semibold text-center">Sat</div>
                <div class="col-span-2"></div> 
                <div class="text-center">1</div>
                <div class="text-center">2</div>
                <div class="text-center">3</div>
                <div class="text-center">4</div>
                <div class="text-center">5</div>
                <div class="text-center">6</div>
                <div class="text-center">7</div>
                <div class="text-center">8</div>
                <div class="text-center">9</div>
                <div class="text-center">10</div>
                <div class="text-center">11</div>
                <div class="text-center">12</div>
                <div class="text-center">13</div>
                <div class="text-center">14</div>
                <div class="text-center">15</div>
                <div class="text-center">16</div>
                <div class="text-center">17</div>
                <div class="text-center">18</div>
                <div class="text-center">19</div>
                <div class="text-center">20</div>
                <div class="text-center">21</div>
                <div class="text-center">22</div>
                <div class="text-center">23</div>
                <div class="text-center">24</div>
                <div class="text-center">25</div>
                <div class="text-center">26</div>
                <div class="text-center">27</div>
                <div class="text-center">28</div>
                <div class="text-center">29</div>
                <div class="text-center">30</div>
                <div class="text-center">31</div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-bold mb-3">Upcoming Lessons</h3>
              <ul class="space-y-2">
                <li>
                  <div class="bg-gray-100 p-3 rounded">
                    <h4 class="font-semibold">A1 Common English</h4>
                    <p>Cambridge advanced.pdf - Only view - 48 members - 28 MB</p>
                  </div>
                </li>
                <li>
                  <div class="bg-gray-100 p- 3 rounded">
                    <h4 class="font-semibold">B1 Business English</h4>
                    <p>English Dictionary.wav - Edit available - 30 members - 60 MB</p>
                  </div>
                </li>
                <li>
                  <div class="bg-gray-100 p-3 rounded">
                    <h4 class="font-semibold">C2 Spanish Grammar</h4>
                    <p>Easy Learning Book.zip - Only view - 68 members - 48 MB</p>
                  </div>
                </li>
              </ul>
            </div>
          </aside>
        </div>

        <section class="flex flex-col md:flex-row gap-4 mt-5">
          <div class="bg-white rounded-lg shadow-md p-4 flex-1">
            <h3 class="text-lg font-bold">Total Courses</h3>
            <p class="text-2xl font-bold">{{ instructorProfile.coursesCreated }}</p>
          </div>

          <div class="bg-white rounded-lg shadow-md p-4 flex-1">
            <h3 class="text-lg font-bold">Live Sessions</h3>
            <p class="text-2xl font-bold">{{ instructorProfile.liveSessions }}</p>
          </div>

          <div class="bg-white rounded-lg shadow-md p-4 flex-1">
            <h3 class="text-lg font-bold">Average Rating</h3>
            <div class="flex items-center">
              <span v-for="star in 5" :key="star" :class="{'text-yellow-500': instructorProfile.averageRating >= star, 'text-gray-300': instructorProfile.averageRating < star}">&#9733;</span>
            </div>
          </div>
        </section>

        <section class="mt-5">
          <h2 class="text-2xl font-bold mb-4">Manage Courses</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              class="bg-white rounded-lg shadow-md overflow-hidden"
              v-for="course in populatedCourses"
              :key="course.id"
            >
              <div class="relative">
                <img
                  :src="course.thumbnail"
                  alt="Course Thumbnail"
                  class="w-full h-32 object-cover"
                />
                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                  <button class="bg-blue-500 text-white rounded-lg px-4 py-2">View Details</button>
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-semibold">{{ course.title }}</h3>
                <p class="text-gray-600">{{ course.description }}</p>
                <div class="mt-2">
                  <div class="bg-gray-300 rounded h-2">
                    <div class="bg-blue-500 h-full" :style="{ width: course.progress + '%' }"></div>
                  </div>
                  <p class="text-sm text-gray-500">{{ course.progress }}% Complete</p>
                </div>
              </div>
            </div>

            <div class="bg-gray-100 rounded-lg shadow-md flex items-center justify-center h-32 cursor-pointer">
              <button @click="addNewCourse" class="bg-blue-500 text-white rounded-lg px-4 py-2">+ Add New Course</button>
            </div>
          </div>
        </section>

        <section class="mt-5">
          <h2 class="text-2xl font-bold mb-4">Live Sessions</h2>
          <div class="flex flex-col gap-4">
            <div class="bg-white rounded-lg shadow-md flex">
              <img
                src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDJ8fGxlYXJuaW5nfGVufDB8fHx8MTY1ODc3NTM0OA&ixlib=rb-1.2.1&q=80&w=400"
                alt="Live Session Background"
                class="w-1/3 h-auto object-cover rounded-l-lg"
              />
              <div class="p-4 flex-1">
                <h3 class="text-lg font-bold">English Grammar Workshop</h3>
                <p class="text-sm text-gray-600">Time : Thu, Jan 25th, 3:00 PM - 4:30 PM</p>
                <p class="text-gray-700">Join us for an interactive session to enhance your grammar skills. Don't miss the opportunity to learn and improve with peers!</p>
                <button class="bg-green-500 text-white rounded-lg px-4 py-2 mt-2">Join Live Session</button>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow-md flex flex-col md:flex-row">
              <div class="p-4 flex-1">
                <h3 class="text-lg font-bold">Schedule a New Live Session</h3>
                <p class="text-gray-700">Plan your next live session with ease. Set the topic, date, and time to keep your students on track.</p>
                <form class="space-y-3">
                  <input type="text" placeholder="Session Title" class="border border-gray-300 rounded-lg px-4 py-2 w-full" />
                  <input type="datetime-local" class="border border-gray-300 rounded-lg px-4 py-2 w-full" />
                  <textarea placeholder="Description" class="border border-gray-300 rounded-lg px-4 py-2 w-full" rows="3"></textarea>
                  <button type="submit" class="bg-blue-500 text-white rounded-lg px-4 py-2">Schedule Session</button>
                </form>
              </div>
              <img
                src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfHx8fHx8fHx8MTYxMzQ4NDIzNQ&ixlib=rb-1.2.1&q=80&w=400"
                alt="Schedule Session Background"
                class="w-full md:w-1/3 h-auto object-cover rounded-r-lg"
              />
            </div>

            <div class="bg-white rounded-lg shadow-md flex">
              <img
                src="https://images.unsplash.com/photo-1496307042754-b4aa456c4a2d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfHx8fHx8fHx8MTYxMzQ4NDIzNQ&ixlib=rb-1.2.1&q=80&w=400"
                alt="Past Session Background"
                class="w-1/3 h-auto object-cover rounded-l-lg"
              />
              <div class="p-4 flex-1">
                <h3 class="text-lg font-bold">Past Sessions</h3>
                <ul class="list-disc pl-5">
                  <li class="text-gray-700">English Grammar Basics - Jan 20, 2023 | Duration: 1 hour</li>
                  <li class="text-gray-700">Spanish Vocabulary Workshop - Jan 15, 2023 | Duration: 1.5 hours</li>
                  <li class="text-gray-700">Business English Q&A - Jan 10, 2023 | Duration: 2 hours</li>
                </ul>
              </div>
            </div>
          </div>
        </section>
      </div>
      </div>
      </div>
      </template>

        <style scoped>
        /* You can add any additional styles here if needed */
        </style> 