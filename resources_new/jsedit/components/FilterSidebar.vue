<template>
    <div class="bg-white rounded-lg shadow-sm">
        <!-- Header -->
        <div class="p-4 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <button
                    @click="resetFilters"
                    class="text-sm text-blue-600 hover:text-blue-700">
                    Reset All
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-4 space-y-6">

            <!-- Price Range -->
            <div class="filter-section">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Price Range</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>৳{{ localFilters.priceRange[0].toLocaleString() }}</span>
                        <span>৳{{ localFilters.priceRange[1].toLocaleString() }}</span>
                    </div>
                    <input
                        type="range"
                        v-model.number="localFilters.priceRange[0]"
                        :min="minPrice"
                        :max="maxPrice"
                        @input="emitFilters"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <input
                        type="range"
                        v-model.number="localFilters.priceRange[1]"
                        :min="minPrice"
                        :max="maxPrice"
                        @input="emitFilters"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                </div>
            </div>

            <!-- Airlines -->
            <div class="filter-section">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Airlines</h4>
                <div class="space-y-1 max-h-48 overflow-y-auto pr-2">
                    <label
                        v-for="airline in availableAirlines"
                        :key="airline.code"
                        class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input
                            type="checkbox"
                            :value="airline.code"
                            v-model="localFilters.airlines"
                            @change="emitFilters"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 flex-shrink-0">
                        <span class="text-sm text-gray-700 ml-2 flex-1">{{ airline.name }}</span>
                        <span class="text-xs text-gray-500 ml-2">({{ airline.count }})</span>
                    </label>
                </div>
            </div>

            <!-- Stops -->
            <div class="filter-section">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Stops</h4>
                <div class="space-y-2">
                    <label
                        v-for="stop in stopOptions"
                        :key="stop.value"
                        class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input
                            type="checkbox"
                            :value="stop.value"
                            v-model="localFilters.stops"
                            @change="emitFilters"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 flex-1">{{ stop.label }}</span>
                        <span class="text-xs text-gray-500">({{ stop.count }})</span>
                    </label>
                </div>
            </div>

            <!-- Departure Time -->
            <div class="filter-section">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Departure Time</h4>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="time in timeSlots"
                        :key="time.value"
                        @click="toggleTimeSlot(time.value)"
                        :class="[
                            'p-3 rounded-lg border-2 text-center transition-all',
                            localFilters.departureTime.includes(time.value)
                                ? 'border-blue-600 bg-blue-50 text-blue-700'
                                : 'border-gray-200 hover:border-gray-300'
                        ]">
                        <div class="text-lg mb-1">{{ time.icon }}</div>
                        <div class="text-xs font-medium">{{ time.label }}</div>
                        <div class="text-xs text-gray-500">{{ time.time }}</div>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: 'FilterSidebar',
    props: {
        flights: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            localFilters: {
                priceRange: [0, 200000],
                airlines: [],
                stops: [],
                departureTime: []
            },
            timeSlots: [
                { value: 'morning', label: 'Morning', time: '6AM - 12PM', icon: '🌅' },
                { value: 'afternoon', label: 'Afternoon', time: '12PM - 6PM', icon: '☀️' },
                { value: 'evening', label: 'Evening', time: '6PM - 12AM', icon: '🌆' },
                { value: 'night', label: 'Night', time: '12AM - 6AM', icon: '🌙' }
            ]
        }
    },
    computed: {
        minPrice() {
            const prices = this.flights.map(f => f.price?.total || 0).filter(p => p > 0);
            return prices.length ? Math.min(...prices) : 0;
        },
        maxPrice() {
            const prices = this.flights.map(f => f.price?.total || 0);
            return prices.length ? Math.max(...prices) : 200000;
        },
        availableAirlines() {
            const airlineMap = {};

            this.flights.forEach(flight => {
                const code = flight.validating_carrier;
                if (code) {
                    if (!airlineMap[code]) {
                        airlineMap[code] = {
                            code: code,
                            name: this.getAirlineName(code),
                            count: 0
                        };
                    }
                    airlineMap[code].count++;
                }
            });

            return Object.values(airlineMap).sort((a, b) => b.count - a.count);
        },
        stopOptions() {
            const stopMap = {
                0: { value: 0, label: 'Non-stop', count: 0 },
                1: { value: 1, label: '1 Stop', count: 0 },
                2: { value: 2, label: '2+ Stops', count: 0 }
            };

            this.flights.forEach(flight => {
                const stops = flight.legs?.[0]?.stops || 0;
                const key = stops >= 2 ? 2 : stops;
                if (stopMap[key]) {
                    stopMap[key].count++;
                }
            });

            return Object.values(stopMap).filter(s => s.count > 0);
        }
    },
    methods: {
        emitFilters() {
            this.$emit('filter-change', this.localFilters);
        },
        resetFilters() {
            this.localFilters = {
                priceRange: [this.minPrice, this.maxPrice],
                airlines: [],
                stops: [],
                departureTime: []
            };
            this.emitFilters();
        },
        toggleTimeSlot(slot) {
            const index = this.localFilters.departureTime.indexOf(slot);
            if (index > -1) {
                this.localFilters.departureTime.splice(index, 1);
            } else {
                this.localFilters.departureTime.push(slot);
            }
            this.emitFilters();
        },
        getAirlineName(code) {
            const airlines = {
                'BS': 'US-Bangla Airlines',
                'BG': 'Biman Bangladesh',
                'GF': 'Gulf Air',
                'EK': 'Emirates',
                'QR': 'Qatar Airways',
                'SQ': 'Singapore Airlines',
                'TK': 'Turkish Airlines',
                'SV': 'Saudi Arabian Airlines',
                'AI': 'Air India',
                'UL': 'SriLankan Airlines',
                'KU': 'Kuwait Airways',
                'WY': 'Oman Air',
                'FZ': 'Flydubai',
                'H9': 'Himalaya Airlines',
                'CZ': 'China Southern'
            };
            return airlines[code] || code;
        }
    },
    mounted() {
        // Initialize price range from actual data
        this.localFilters.priceRange = [this.minPrice, this.maxPrice];
    }
}
</script>

<style scoped>
/* Custom range slider styling */
input[type="range"]::-webkit-slider-thumb {
    appearance: none;
    width: 16px;
    height: 16px;
    background: #2563eb;
    cursor: pointer;
    border-radius: 50%;
}

input[type="range"]::-moz-range-thumb {
    width: 16px;
    height: 16px;
    background: #2563eb;
    cursor: pointer;
    border-radius: 50%;
    border: none;
}
</style>
