<template>
    <div>
        <!-- Header with results count and sorting -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-800">{{ flights.length }}</span>
                    flights found
                </div>

                <!-- Sort Options -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Sort by:</span>
                    <select
                        v-model="sortBy"
                        @change="sortFlights"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="recommended">Recommended</option>
                        <option value="cheapest">Cheapest First</option>
                        <option value="fastest">Fastest First</option>
                        <option value="earliest">Earliest Departure</option>
                        <option value="latest">Latest Departure</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Flight Cards -->
        <div v-if="sortedFlights.length > 0" class="space-y-4">
            <FlightCard
                v-for="flight in sortedFlights"
                :key="flight.id"
                :flight="flight"
            />
        </div>

        <!-- No Results -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No flights found</h3>
            <p class="text-gray-500">Try adjusting your filters to see more results</p>
        </div>
    </div>
</template>

<script>
import FlightCard from './FlightCard.vue';

export default {
    name: 'FlightList',
    components: {
        FlightCard
    },
    props: {
        flights: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            sortBy: 'recommended'
        }
    },
    computed: {
        sortedFlights() {
            let flights = [...this.flights];

            switch(this.sortBy) {
                case 'cheapest':
                    return flights.sort((a, b) =>
                        (a.price?.total || 0) - (b.price?.total || 0)
                    );

                case 'fastest':
                    return flights.sort((a, b) => {
                        const durationA = a.legs?.[0]?.duration || 0;
                        const durationB = b.legs?.[0]?.duration || 0;
                        return durationA - durationB;
                    });

                case 'earliest':
                    return flights.sort((a, b) => {
                        const timeA = a.legs?.[0]?.departure?.time || '';
                        const timeB = b.legs?.[0]?.departure?.time || '';
                        return timeA.localeCompare(timeB);
                    });

                case 'latest':
                    return flights.sort((a, b) => {
                        const timeA = a.legs?.[0]?.departure?.time || '';
                        const timeB = b.legs?.[0]?.departure?.time || '';
                        return timeB.localeCompare(timeA);
                    });

                case 'recommended':
                default:
                    // Recommended = balance of price and duration
                    return flights.sort((a, b) => {
                        const scoreA = (a.price?.total || 0) / 1000 + (a.legs?.[0]?.duration || 0) / 60;
                        const scoreB = (b.price?.total || 0) / 1000 + (b.legs?.[0]?.duration || 0) / 60;
                        return scoreA - scoreB;
                    });
            }
        }
    },
    methods: {
        sortFlights() {
            // Sorting is handled by computed property
            // This method is just for the @change event
        }
    }
}
</script>
