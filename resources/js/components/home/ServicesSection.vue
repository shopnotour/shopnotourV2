<template>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">We provide comprehensive travel solutions for all your flight booking needs</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="service in services"
                     :key="service.id"
                     class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-blue-500">
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform"
                         :style="getGradientStyle(service)">
                        <i :class="service.icon" class="text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">{{ service.title }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ service.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'ServicesSection',
    props: {
        services: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        getGradientStyle(service) {
            return `background: linear-gradient(to bottom right, ${service.color_from}, ${service.color_to})`;
        }
    }
}
</script>
