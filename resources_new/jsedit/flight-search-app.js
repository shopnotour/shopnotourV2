// import Vue from 'vue/dist/vue.esm.js';
// import {defineComponent} from "vue";
// import FlightSearchPage from "./components/FlightSearchPage.vue";
// import TestComponent from "./components/TestComponent.vue";
//
// export default defineComponent({
//     components: {FlightSearchPage}
// })
//
//
// new Vue({
//     el: '#flight-search-app',
//     components: {
//         'test-component': TestComponent , // ✅ Explicit name
//         'flight_search_page':FlightSearchPage
//     },
//     data() {
//         return {
//             count: 0
//         }
//     },
//     methods: {
//         increment() {
//             this.count++;
//         }
//     },
//     template: `
//        <div>
// <!--           <test-component></test-component>-->
//            <flight_search_page></flight_search_page>
//        </div>
//     `
// });

import Vue from 'vue/dist/vue.esm.js';
import FlightSearch from './components/FlightSearchPage.vue';

new Vue({
    el: '#flight-search-app',
    components: {
        FlightSearch
    },
    data() {
        return {
            flightData: null
        }
    },
    mounted() {
        // Load data from window object (set by Blade)
        if (window.flightData) {
            this.flightData = window.flightData;
        }
    },
    template: `
        <div>
            <FlightSearch v-if="flightData" />
            <div v-else class="container py-20 text-center">
                <div class="text-gray-500">Loading flights...</div>
            </div>
        </div>
    `
});
