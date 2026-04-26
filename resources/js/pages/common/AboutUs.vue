 <template>
    <section class="bg-gray-50 py-12 px-4" id="about">
        <div class="block text-center mb-12">
            <h1
                class="mx-auto text-center text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-black font-serif leading-[1.1] tracking-tight max-w-4xl">
                <span class="text-gray-900"> About Us </span><br class="my-4" /> 
            </h1>
        </div>
        <div class="container mx-auto flex flex-col lg:flex-row items-center gap-8">
            <!-- Image Section -->
            <div class="lg:w-1/3 lg:block">
                <img src="/images/mehari.JPG" alt="About Us" class="rounded-lg shadow-md w-full" />
            </div>

            <!-- Text Content -->
            <div class="lg:w-2/3 text-center lg:text-left">
                <p class="text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                    Welcome to our platform! We specialize in offering engaging
                    and effective English learning experiences tailored to all
                    levels from beginners to advanced learners.
                </p>
                <p class="text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                    Whether you're preparing for exams, improving your
                    communication skills, or expanding your vocabulary, our
                    mission is to help you achieve your goals with confidence
                    and ease.
                </p>

                <!-- Expandable Text -->
                <p class="text-gray-600 text-base md:text-lg lg:text-xl py-4 leading-relaxed max-w-2xl">
                    Our platform offers interactive lessons, personalized
                    quizzes, and live sessions with certified instructors. We
                    emphasize practical learning by incorporating real-life
                    scenarios, idioms, and phrases commonly used in everyday
                    conversations. Additionally, you can track your progress,
                    set learning goals, and connect with a vibrant community of
                    language enthusiasts.
                </p> 
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Ensure proper text justification */
p {
    text-align: justify;
    hyphens: auto;
    word-spacing: normal;
    letter-spacing: normal;
}

/* Better line height for readability */
.leading-relaxed {
    line-height: 1.75;
}

/* Smooth transitions for interactive elements */
button {
    transition: all 0.2s ease-in-out;
}

button:hover {
    transform: translateY(-1px);
}
</style>
