<template>
    <div class="container">
        <div class="flex gap-6">
            <!-- Left: Filters -->
            <div class="w-1/4">
                <FilterSidebar
                    :flights="allFlights"
                    @filter-change="handleFilterChange"
                />
            </div>

            <!-- Right: Flight List -->
            <div class="w-3/4">
                <FlightList :flights="filteredFlights" />
            </div>
        </div>
    </div>
</template>

<script>
import FilterSidebar from './FilterSidebar.vue';
import FlightList from './FlightList.vue';

export default {
    name: 'FlightSearch',
    components: {
        FilterSidebar,
        FlightList
    },
    data() {
        return {
            allFlights: [],
            filters: {
                priceRange: [0, 200000],
                airlines: [],
                stops: [],
                departureTime: []
            }
        }
    },
    computed: {
        filteredFlights() {
            let flights = this.allFlights;

            // Filter by price
            flights = flights.filter(f => {
                const price = f.price?.total || 0;
                return price >= this.filters.priceRange[0] &&
                    price <= this.filters.priceRange[1];
            });

            // Filter by airlines
            if (this.filters.airlines.length > 0) {
                flights = flights.filter(f =>
                    this.filters.airlines.includes(f.validating_carrier)
                );
            }

            // Filter by stops
            if (this.filters.stops.length > 0) {
                flights = flights.filter(f => {
                    const stops = f.legs?.[0]?.stops || 0;
                    return this.filters.stops.includes(stops);
                });
            }

            return flights;
        }
    },
    methods: {
        handleFilterChange(filters) {
            this.filters = { ...this.filters, ...filters };
        }
    },
    mounted() {
        // Load data from window object
        if (window.flightData) {
            this.allFlights = window.flightData.flights.flights || [];

            // Set initial price range based on actual data
            const prices = this.allFlights.map(f => f.price?.total || 0);
            if (prices.length > 0) {
                this.filters.priceRange = [
                    Math.min(...prices),
                    Math.max(...prices)
                ];
            }
        }
    }
}
</script>
