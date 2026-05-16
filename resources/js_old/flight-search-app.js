import Vue from 'vue/dist/vue.esm.js';
import FlightSearch from './components/FlightSearchPage.vue';
import FlightSearchForm from './components/Flightsearchform.vue';
import FlightHomepageSections from './components/FlightHomepageSections.vue';

console.log('✅ flight-search-app.js executed');

// ── Search Form (home page + search result page দুটোতেই আছে) ──
const searchFormEl = document.getElementById('flight-search-form');
if (searchFormEl) {
    new Vue({
        el: '#flight-search-form',
        components: { FlightSearchForm },
        template: `<FlightSearchForm />`
    });
}

// ── Flight Results (search result page এ আছে, home page এ নেই) ──
const searchAppEl = document.getElementById('flight-search-app');
if (searchAppEl) {
    new Vue({
        el: '#flight-search-app',
        components: { FlightSearch },
        data() {
            return { flightData: null };
        },
        mounted() {
            if (window.flightData) {
                this.flightData = window.flightData;
            }
        },
        template: `
            <div>
                <FlightSearch
                    v-if="flightData"
                    :flight-data="flightData"
                />
                <div v-else class="container py-20 text-center">
                    <div class="text-gray-500">Loading flights...</div>
                </div>
            </div>
        `
    });
}

// ── Homepage Sections (home page এ আছে) ──
const homepageEl = document.getElementById('flight-homepage-sections');
if (homepageEl) {
    new Vue({
        el: '#flight-homepage-sections',
        components: { FlightHomepageSections },
        template: `<FlightHomepageSections />`
    });
}

// ── Bootstrap Modal (optional) ──
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('flightFormBookModal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
        try {
            new bootstrap.Modal(modalEl);
        } catch (e) {
            // ignore if bootstrap not ready
        }
    }
});
