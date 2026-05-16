<!--<template>-->
<!--    <section class="py-12 bg-gray-50 overflow-hidden">-->
<!--        <div class="container mx-auto px-4 mb-8">-->
<!--            <h2 class="text-3xl font-bold text-gray-800 text-center">Trusted by Airlines Worldwide</h2>-->
<!--        </div>-->

<!--        <div class="relative">-->
<!--            <div class="logo-scroll-wrapper">-->
<!--                <div class="logo-scroll">-->
<!--                    &lt;!&ndash; First set &ndash;&gt;-->
<!--                    <div v-for="partner in partners"-->
<!--                         :key="partner.id"-->
<!--                         class="logo-item">-->
<!--                        <img :src="partner.logo"-->
<!--                             :alt="partner.name"-->
<!--                             class="h-12 object-contain grayscale hover:grayscale-0 transition-all">-->
<!--                    </div>-->

<!--                    &lt;!&ndash; Duplicate for infinite scroll &ndash;&gt;-->
<!--                    <div v-for="partner in partners"-->
<!--                         :key="'dup-' + partner.id"-->
<!--                         class="logo-item">-->
<!--                        <img :src="partner.logo"-->
<!--                             :alt="partner.name"-->
<!--                             class="h-12 object-contain grayscale hover:grayscale-0 transition-all">-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </section>-->
<!--</template>-->

<!--<script>-->
<!--export default {-->
<!--    name: 'PartnersSection',-->
<!--    props: {-->
<!--        partners: {-->
<!--            type: Array,-->
<!--            default: () => []-->
<!--        }-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->
<!--.logo-scroll-wrapper {-->
<!--    display: flex;-->
<!--    overflow: hidden;-->
<!--    user-select: none;-->
<!--}-->

<!--.logo-scroll {-->
<!--    display: flex;-->
<!--    animation: scroll-left 30s linear infinite;-->
<!--    gap: 3rem;-->
<!--}-->

<!--.logo-scroll:hover {-->
<!--    animation-play-state: paused;-->
<!--}-->

<!--.logo-item {-->
<!--    flex-shrink: 0;-->
<!--    padding: 1rem 2rem;-->
<!--    background: white;-->
<!--    border-radius: 12px;-->
<!--    box-shadow: 0 2px 8px rgba(0,0,0,0.1);-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    min-width: 180px;-->
<!--}-->

<!--@keyframes scroll-left {-->
<!--    0% {-->
<!--        transform: translateX(0);-->
<!--    }-->
<!--    100% {-->
<!--        transform: translateX(-50%);-->
<!--    }-->
<!--}-->
<!--</style>-->


<template>
    <section class="py-12 bg-gray-50 overflow-hidden">
        <div class="container mx-auto px-4 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 text-center">Trusted by Airlines Worldwide</h2>
        </div>

        <div class="relative">
            <div class="logo-scroll-wrapper">
                <div class="logo-scroll">
                    <!-- First set -->
                    <div v-for="partner in partners"
                         :key="partner.id"
                         class="logo-item">
                        <img :src="partner.logo"
                             :alt="partner.name"
                             class="h-12 object-contain hover:scale-110 transition-all">
                    </div>

                    <!-- Duplicate for infinite scroll -->
                    <div v-for="partner in partners"
                         :key="'dup-' + partner.id"
                         class="logo-item">
                        <img :src="partner.logo"
                             :alt="partner.name"
                             class="h-12 object-contain hover:scale-110 transition-all">
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'PartnersSection',
    props: {
        partners: {
            type: Array,
            default: () => []
        }
    }
}
</script>

<style scoped>
.logo-scroll-wrapper {
    display: flex;
    overflow: hidden;
    user-select: none;
}

.logo-scroll {
    display: flex;
    animation: scroll-left 30s linear infinite;
    gap: 3rem;
}

.logo-scroll:hover {
    animation-play-state: paused;
}

.logo-item {
    flex-shrink: 0;
    padding: 1rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 180px;
}

@keyframes scroll-left {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>
