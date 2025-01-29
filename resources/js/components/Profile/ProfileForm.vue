        <script>
        import ProfileSettings from './profileSetting.vue';
        import ChangePassword from './ChangePassword.vue';

        export default {
            name: "AccountSettings",
            components: {
            ProfileSettings,
            ChangePassword
            },
            data() {
            return {
                activeTab: 'profile',  // Default tab is 'profile'
            };
            },
            methods: {
            setActiveTab(tab) {
                this.activeTab = tab;
            }
            }
        };
        </script>
        <template>
                <div class="flex flex-col md:flex-row bg-gray-100">
                <section class="mt-5 w-full">
                    <div class="text-xl font-bold mb-5">Account Settings</div>
                    <div class="flex flex-wrap gap-8">
                    <nav class="flex-none w-full md:w-64 bg-gray-50 p-4 rounded-lg shadow">
                        <ul>
                        <li 
                            @click="setActiveTab('profile')" 
                            :class="{'bg-blue-500 text-white': activeTab === 'profile'}" 
                            class="py-2 px-3 cursor-pointer">👤 Profile Settings</li>
                        <li 
                            @click="setActiveTab('password')" 
                            :class="{'bg-blue-500 text-white': activeTab === 'password'}" 
                            class="py-2 px-3 cursor-pointer hover:bg-gray-200">🔒 Password</li>
                        </ul>
                    </nav>
            
                    <div class="flex-1 bg-white p-5 rounded-lg shadow">
                        <ProfileSettings v-if="activeTab === 'profile'" />
                        <ChangePassword v-if="activeTab === 'password'" />
                    </div>
                    </div>
                </section>
            </div>
        </template>
            
            