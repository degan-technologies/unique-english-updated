    <script setup>
    import Axios from "axios";
    import { ref } from "vue";
    import { storeToRefs } from "pinia";
    import { useAppStore } from "@/store/useAppStore";
import AddInstructor from "./AddInstructor.vue";

    const appStore = useAppStore();
    const { frontLang, facebook, google } = storeToRefs(appStore);

    const name = ref("");
    const email = ref("");
    const password = ref("");
    const showPassword = ref(false);
    const loading = ref(false);
    const agreeTerms = ref(false);
    const successMessage = ref("");
    const errorMessage = ref("");

    const togglePassword = () => {
    showPassword.value = !showPassword.value;
    };
    function handleRegister() {
    if (!agreeTerms.value) {
        errorMessage.value = frontLang.value.lang.termsAndConditions;
        setTimeout(() => {
            errorMessage.value = ""; 
        }, 2000);
        return;
    }

    loading.value = true;

    const data = {
        first_name: name.value,
        email: email.value,
        password: password.value,
    };

    Axios.post("/api/registration", data)
        .then((res) => {
            successMessage.value = res.data.message;
            setTimeout(() => {
                successMessage.value = ""; 
            }, 2000);
        })
        .catch((err) => {
            errorMessage.value = err.response.data.message;
            setTimeout(() => {
                errorMessage.value = ""; 
            }, 2000);
        })
        .finally(() => {
            loading.value = false;
        });
}

    </script>

    <template>
        <AddInstructor />
    <div class="flex flex-col md:flex-row justify-center items-center min-h-screen bg-gray-100 p-4 md:space-x-4">
        <div class="flex flex-col md:flex-row md:items-stretch w-full max-w-4xl space-y-4 md:space-y-0">
        <div class="bg-blue-500 text-white p-6 rounded-lg shadow-lg w-full md:w-1/2 flex flex-col justify-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4"> {{ frontLang.lang.wellcomeToPlatform }} </h1>
            <p class="text-lg">{{ frontLang.lang.joinUsAndSignUp }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg w-full md:w-1/2 flex flex-col justify-center">
            <h1 class="text-2xl font-bold text-center mb-4">{{frontLang.lang.createAccount}}</h1>
            <p class="text-gray-600 text-center mb-6">{{frontLang.lang.startjourneywithus}}</p>     
            <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4 mb-6">
            <button class="flex items-center justify-center px-4 py-2 border rounded-lg w-full text-gray-700 hover:bg-gray-100" >
                <img :src="google" alt="Google" class="mr-2" />
                {{frontLang.lang.signupwithgoogle}}
            </button>
            <button class="flex items-center justify-center px-4 py-2 border rounded-lg w-full text-gray-700 hover:bg-gray-100" >
                <img :src="facebook" alt="Facebook" class="mr-2" />
                {{frontLang.lang.signupwithfacebook}}
            </button>
            </div>

            <div class="relative my-6">
            <hr class="border-gray-300" />
            <span
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-gray-500"
            >
                {{frontLang.lang.or}}
            </span>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{frontLang.lang.name}}</label>
                <input
                type="text"
                id="name"
                v-model="name"
                placeholder="Enter your name"
                required
                class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{frontLang.lang.email}}</label>
                <input
                type="email"
                id="email"
                v-model="email"
                placeholder="Enter your email"
                required
                class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{frontLang.lang.password}}</label>
                <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    v-model="password"
                    placeholder="Enter your password"
                    required
                    class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                    type="button"
                    @click="togglePassword"
                    class="absolute right-3 top-3 text-gray-500 focus:outline-none"
                >
                    <span v-if="showPassword">🙈</span>
                    <span v-else>👁️</span>
                </button>
                </div>
            </div>

            <div class="flex items-start space-x-2">
                <input
                type="checkbox"
                id="terms"
                v-model="agreeTerms"
                class="mt-1"
                />
                <label for="terms" class="text-sm text-gray-700">
                {{frontLang.lang.agreeto}}
                <a href="#" class="text-blue-500 hover:underline">{{frontLang.lang.termsAndConditions}}</a>
                </label>
            </div>

            <button
                @click="handleRegister()"
                :disabled="loading"
                class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300"
            >
                <span v-if="loading">{{frontLang.lang.CreatingAccount}}</span>
                <span v-else>{{frontLang.lang.signup}}</span>
            </button>
            </form>

            <div v-if="successMessage" class="mt-4 text-green-500 text-center font-semibold"> {{ successMessage }} </div>
            <div v-if="errorMessage" class="mt-4 text-red-500 text-center font-semibold"> {{ errorMessage }} </div>
            <div class="mt-6 text-center text-sm">
            <p>
                {{ frontLang.lang.haveAccount }}
                <a href="/login" class="text-blue-500 hover:underline">{{frontLang.lang.login}}</a>
            </p>
            </div>
        </div>
        </div>
    </div>
    </template>
