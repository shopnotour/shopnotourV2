<template>
    <div>
<!--        <staticService />-->
<!--        <ServicesSection :services="services" />-->
        <TruthBanner :partners="partners" />
        <PartnersSection :partners="partners" />
        <WhyChooseUsSection :features="features" />
<!--        <PopularDestinationsSection :destinations="destinations" />-->
<!--        <TestimonialsSection :testimonials="testimonials" />-->
    </div>
</template>

<script>
import ServicesSection from './home/ServicesSection.vue';
import PartnersSection from './home/PartnersSection.vue';
import WhyChooseUsSection from './home/WhyChooseUsSection.vue';
import PopularDestinationsSection from './home/PopularDestinationsSection.vue';
import TestimonialsSection from './home/TestimonialsSection.vue';
import staticService from "@/components/home/staticService.vue";
import TruthBanner from "@/components/home/BannerSection.vue";

export default {
    name: 'FlightHomepageSections',
    components: {
        TruthBanner,
        ServicesSection,
        PartnersSection,
        WhyChooseUsSection,
        PopularDestinationsSection,
        staticService,
        TestimonialsSection
    },
    data() {
        return {
            services: [],
            partners: [],
            features: [],
            destinations: [],
            testimonials: []
        }
    },
    mounted() {
        if (window.flightHomepageData) {
            this.services = window.flightHomepageData.services || [];
            this.partners = window.flightHomepageData.partners || [];
            this.features = window.flightHomepageData.features || [];
            this.destinations = window.flightHomepageData.destinations || [];
            this.testimonials = window.flightHomepageData.testimonials || [];
        }
    }
}
</script>
