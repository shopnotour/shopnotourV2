<template>
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">

                <!-- Left: Flight Details -->
                <div class="flex-1">
                    <!-- Airline Logo & Name -->
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-sm font-bold text-gray-600">{{ flight.validating_carrier }}</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-800">{{ getAirlineName(flight.validating_carrier) }}</div>
                            <div class="text-xs text-gray-500">{{ flight.validating_carrier }} • {{ getFlightNumber() }}</div>
                        </div>
                    </div>

                    <!-- Flight Route -->
<!--                    <div class="flex items-center space-x-4">-->
<!--                        &lt;!&ndash; Departure &ndash;&gt;-->
<!--                        <div class="text-center">-->
<!--                            <div class="text-2xl font-bold text-gray-800">{{ formatTime(departureTime) }}</div>-->
<!--                            <div class="text-sm font-medium text-gray-600">{{ departureAirport }}</div>-->
<!--                            <div class="text-xs text-gray-500">{{ formatDate(departureTime) }}</div>-->
<!--                        </div>-->

<!--                        &lt;!&ndash; Duration & Stops &ndash;&gt;-->
<!--                        <div class="flex-1 px-4">-->
<!--                            <div class="relative">-->
<!--                                &lt;!&ndash; Line &ndash;&gt;-->
<!--                                <div class="h-0.5 bg-gray-300 relative">-->
<!--                                    &lt;!&ndash; Plane Icon &ndash;&gt;-->
<!--                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2">-->
<!--                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">-->
<!--                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>-->
<!--                                        </svg>-->
<!--                                    </div>-->

<!--                                    &lt;!&ndash; Stop dots &ndash;&gt;-->
<!--                                    <div v-if="stops > 0" class="absolute top-1/2 transform -translate-y-1/2"-->
<!--                                         :style="{ left: '50%' }">-->
<!--                                        <div class="w-2 h-2 bg-orange-400 rounded-full"></div>-->
<!--                                    </div>-->
<!--                                </div>-->

<!--                                &lt;!&ndash; Duration text &ndash;&gt;-->
<!--                                <div class="text-center mt-2">-->
<!--                                    <div class="text-sm text-gray-600">{{ formatDuration(duration) }}</div>-->
<!--                                    <div class="text-xs" :class="stops === 0 ? 'text-green-600 font-medium' : 'text-orange-600'">-->
<!--                                        {{ stops === 0 ? 'Non-stop' : `${stops} stop${stops > 1 ? 's' : ''}` }}-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->

<!--                        &lt;!&ndash; Arrival &ndash;&gt;-->
<!--                        <div class="text-center">-->
<!--                            <div class="text-2xl font-bold text-gray-800">{{ formatTime(arrivalTime) }}</div>-->
<!--                            <div class="text-sm font-medium text-gray-600">{{ arrivalAirport }}</div>-->
<!--                            <div class="text-xs text-gray-500">-->
<!--                                {{ formatDate(arrivalTime) }}-->
<!--                                <span v-if="dateAdjustment > 0" class="text-orange-600">+{{ dateAdjustment }}</span>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <!-- Flight Routes - Loop through all legs -->
                    <div v-for="(leg, legIndex) in legs" :key="legIndex" class="mb-4">
                        <!-- Leg Header -->
                        <div v-if="totalLegs > 1" class="mb-2">
        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
            {{ getLegLabel(legIndex) }}
        </span>
                        </div>

                        <!-- Flight Route -->
                        <div class="flex items-center space-x-4">
                            <!-- Departure -->
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-800">{{ formatTime(leg.departure?.time) }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ leg.departure?.airport }}</div>
                                <div class="text-xs text-gray-500">{{ formatDate(leg.departure?.time) }}</div>
                            </div>

                            <!-- Duration & Stops -->
                            <div class="flex-1 px-4">
                                <div class="relative">
                                    <!-- Line -->
                                    <div class="h-0.5 bg-gray-300 relative">
                                        <!-- Plane Icon -->
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                            </svg>
                                        </div>

                                        <!-- Stop dots -->
                                        <div v-if="leg.stops > 0" class="absolute top-1/2 transform -translate-y-1/2"
                                             :style="{ left: '50%' }">
                                            <div class="w-2 h-2 bg-orange-400 rounded-full"></div>
                                        </div>
                                    </div>

                                    <!-- Duration text -->
                                    <div class="text-center mt-2">
                                        <div class="text-sm text-gray-600">{{ formatDuration(leg.duration) }}</div>
                                        <div class="text-xs" :class="leg.stops === 0 ? 'text-green-600 font-medium' : 'text-orange-600'">
                                            {{ leg.stops === 0 ? 'Non-stop' : `${leg.stops} stop${leg.stops > 1 ? 's' : ''}` }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Arrival -->
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-800">{{ formatTime(leg.arrival?.time) }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ leg.arrival?.airport }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ formatDate(leg.arrival?.time) }}
                                    <span v-if="leg.arrival?.date_adjustment > 0" class="text-orange-600">
                    +{{ leg.arrival.date_adjustment }}
                </span>
                                </div>
                            </div>
                        </div>

                        <!-- Separator between legs -->
                        <div v-if="legIndex < totalLegs - 1" class="my-4 border-t border-gray-200"></div>
                    </div>

                    <!-- Additional Info -->
                    <div class="flex items-center space-x-4 mt-4 text-xs text-gray-500">
                        <div v-if="flight.baggage" class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            {{ formatBaggage(flight.baggage) }}
                        </div>
                        <div v-if="flight.refundable" class="flex items-center text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Refundable
                        </div>
                        <div v-else class="flex items-center text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Non-refundable
                        </div>
                    </div>
                </div>

                <!-- Right: Price & Book Button -->
                <div class="ml-6 text-right border-l pl-6">
                    <div class="mb-2">
                        <div class="text-xs text-gray-500 mb-1">Total Price</div>
                        <div class="text-3xl font-bold text-blue-600">
                            ৳{{ formatPrice(flight.price?.total) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            per person
                        </div>
                    </div>

                    <!-- Price Breakdown (collapsed) -->
                    <button
                        @click="showDetails = !showDetails"
                        class="text-xs text-blue-600 hover:text-blue-700 mb-3">
                        {{ showDetails ? 'Hide' : 'View' }} Details
                    </button>

                    <!-- Book Button -->
                    <button
                        @click="bookFlight"
                        class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                        Book Now
                    </button>
                </div>
            </div>

            <!-- Expandable Details -->
            <transition name="slide">
                <div v-if="showDetails" class="mt-6 pt-6 border-t border-gray-200">
                    <!-- Price Breakdown -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center p-3 bg-gray-50 rounded">
                            <div class="text-xs text-gray-500 mb-1">Base Fare</div>
                            <div class="text-lg font-semibold text-gray-800">৳{{ formatPrice(flight.price?.base) }}</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded">
                            <div class="text-xs text-gray-500 mb-1">Taxes & Fees</div>
                            <div class="text-lg font-semibold text-gray-800">৳{{ formatPrice(flight.price?.tax) }}</div>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded">
                            <div class="text-xs text-blue-600 mb-1">Total</div>
                            <div class="text-lg font-semibold text-blue-600">৳{{ formatPrice(flight.price?.total) }}</div>
                        </div>
                    </div>

                    <!-- Segments Details -->
<!--                    <div class="space-y-3">-->
<!--                        <h4 class="text-sm font-semibold text-gray-700">Flight Details</h4>-->
<!--                        <div-->
<!--                            v-for="(segment, index) in segments"-->
<!--                            :key="index"-->
<!--                            class="p-4 bg-gray-50 rounded-lg">-->
<!--                            <div class="flex items-center justify-between">-->
<!--                                <div class="flex-1">-->
<!--                                    <div class="flex items-center space-x-4 text-sm">-->
<!--                                        <div>-->
<!--                                            <div class="font-semibold">{{ segment.departure?.airport }}</div>-->
<!--                                            <div class="text-gray-500">{{ formatTime(segment.departure?.time) }}</div>-->
<!--                                        </div>-->
<!--                                        <div class="text-gray-400">→</div>-->
<!--                                        <div>-->
<!--                                            <div class="font-semibold">{{ segment.arrival?.airport }}</div>-->
<!--                                            <div class="text-gray-500">{{ formatTime(segment.arrival?.time) }}</div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="text-xs text-gray-500 mt-2">-->
<!--                                        {{ segment.carrier }} {{ segment.flight_number }} •-->
<!--                                        {{ formatDuration(segment.duration) }} •-->
<!--                                        {{ segment.aircraft }}-->
<!--                                        <span v-if="segment.meal_description" class="ml-2">-->
<!--                                            🍴 {{ segment.meal_description }}-->
<!--                                        </span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <!-- All Legs Details -->
                    <div v-for="(leg, legIndex) in legs" :key="legIndex" class="mb-6">
                        <!-- Leg Header in Details -->
                        <div class="mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">
                                {{ getLegLabel(legIndex) }} - Flight Details
                            </h4>
                        </div>

                        <!-- Segments for this leg -->
                        <div class="space-y-3">
                            <div
                                v-for="(segment, segIndex) in leg.segments"
                                :key="segIndex"
                                class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4 text-sm">
                                            <div>
                                                <div class="font-semibold">{{ segment.departure?.airport }}</div>
                                                <div class="text-gray-500">{{ formatTime(segment.departure?.time) }}</div>
                                            </div>
                                            <div class="text-gray-400">→</div>
                                            <div>
                                                <div class="font-semibold">{{ segment.arrival?.airport }}</div>
                                                <div class="text-gray-500">{{ formatTime(segment.arrival?.time) }}</div>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-2">
                                            {{ segment.carrier }} {{ segment.flight_number }} •
                                            {{ formatDuration(segment.duration) }} •
                                            {{ segment.aircraft }}
                                            <span v-if="segment.meal_description" class="ml-2">
                            🍴 {{ segment.meal_description }}
                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FlightCard',
    props: {
        flight: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            showDetails: false
        }
    },
    computed: {
        legs() {
            return this.flight.legs || [];
        },
        totalLegs() {
            return this.legs.length;
        },
        isRoundTrip() {
            return this.totalLegs === 2;
        },
        isMultiCity() {
            return this.totalLegs > 2;
        }
    },
    methods: {
        getLegLabel(index) {
            if (this.totalLegs === 1) return 'Flight';
            if (this.totalLegs === 2) {
                return index === 0 ? 'Outbound' : 'Return';
            }
            return `Leg ${index + 1}`;
        },
        formatTime(datetime) {
            if (!datetime) return '--:--';
            const time = datetime.split('T')[1]?.split('+')[0] || datetime;
            return time.substring(0, 5); // HH:MM
        },
        formatDate(datetime) {
            if (!datetime) return '';
            const date = datetime.split('T')[0];
            const [year, month, day] = date.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${day} ${months[parseInt(month) - 1]}`;
        },
        formatDuration(minutes) {
            if (!minutes) return '0h 0m';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        },
        formatPrice(price) {
            if (!price) return '0';
            return parseInt(price).toLocaleString();
        },
        formatBaggage(baggage) {
            if (baggage.weight) {
                return `${baggage.weight} ${baggage.unit || 'kg'}`;
            } else if (baggage.piece_count) {
                return `${baggage.piece_count} piece${baggage.piece_count > 1 ? 's' : ''}`;
            }
            return 'Check with airline';
        },
        getFlightNumber() {
            const firstSegment = this.segments[0];
            if (firstSegment) {
                return `${firstSegment.carrier}${firstSegment.flight_number}`;
            }
            return '';
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
        },
        bookFlight() {
            // TODO: Navigate to booking page
            console.log('Booking flight:', this.flight.id);
            // You can emit an event or use Vue Router here
            this.$emit('book', this.flight);
        }
    }
}
</script>

<style scoped>
/* Slide transition for details */
.slide-enter-active, .slide-leave-active {
    transition: all 0.3s ease;
    max-height: 500px;
    overflow: hidden;
}

.slide-enter, .slide-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
