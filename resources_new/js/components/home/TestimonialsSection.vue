<template>
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">What Our Customers Say</h2>
                <p class="text-gray-600">Real experiences from real travelers</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div v-for="testimonial in testimonials"
                     :key="testimonial.id"
                     class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mb-2">
                            <i v-for="star in testimonial.rating"
                               :key="star"
                               class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 italic">"{{ testimonial.review }}"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold mr-3"
                             :style="{ backgroundColor: testimonial.avatar_color }">
                            {{ testimonial.initials }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">{{ testimonial.name }}</h4>
                            <p class="text-sm text-gray-500">{{ testimonial.type }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'TestimonialsSection',
    props: {
        testimonials: {
            type: Array,
            default: () => []
        }
    }
}
</script>
