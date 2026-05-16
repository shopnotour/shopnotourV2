<template>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Popular Destinations</h2>
                <p class="text-gray-600">Discover amazing places around the world</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="destination in destinations"
                     :key="destination.id"
                     @click="navigateToDestination(destination)"
                     class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img :src="destination.image"
                         :alt="destination.name"
                         class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-6">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-1">{{ destination.name }}</h3>
                            <p class="text-sm opacity-90">Starting from ${{ destination.price }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'PopularDestinationsSection',
    props: {
        destinations: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        navigateToDestination(destination) {
            window.location.href = `/flights?to=${destination.code}`;
        }
    }
}
</script>
