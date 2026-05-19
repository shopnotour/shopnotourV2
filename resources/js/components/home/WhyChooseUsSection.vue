<template>
    <section class="py-16 bg-gradient-to-br from-blue-50 to-purple-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Why Choose Us?
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Experience hassle-free flight booking with unmatched
                    benefits
                </p>
            </div>

            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto"
            >
                <div
                    v-for="feature in features"
                    :key="feature.id"
                    class="text-center"
                >
                    <div
                        class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg"
                        :style="{ backgroundColor: feature.color }"
                    >
                        <i
                            :class="feature.icon"
                            class="text-white text-3xl"
                        ></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-600">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "WhyChooseUsSection",
    props: {
        features: {
            type: Array,
            default: () => [],
        },
    },
};
</script>

<style scoped>
.from-blue-50 {
    --tw-gradient-from: #67c7c7 var(--tw-gradient-from-position);
    --tw-gradient-to: rgb(239 246 255 / 0) var(--tw-gradient-to-position);
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}
</style>
