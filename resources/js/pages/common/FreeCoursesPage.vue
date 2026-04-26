<script setup>
import Header from "@/components/Layout/Header.vue";
import Footer from "@/components/Layout/Footer.vue";
import YoutubeEmbed from "@/components/Layout/YoutubeEmbed.vue";
</script>

<template>
    <div class="min-h-screen bg-stone-100 text-neutral-900">
        <Header />

        <main class="pt-28 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-10">
                    <p class="text-xs uppercase tracking-[0.2em] text-orange-500">
                        Free Courses
                    </p>
                    <h1 class="font-serif text-4xl md:text-5xl leading-tight mt-3">
                        Learn English With Free Video Lessons
                    </h1>
                    <p class="text-neutral-600 mt-4 max-w-2xl">
                        Watch practical lessons from our YouTube channel and improve
                        your speaking, grammar, and vocabulary at your own pace.
                    </p>
                </div>

                <YoutubeEmbed />
            </div>
        </main>

        <Footer />
    </div>
</template>
