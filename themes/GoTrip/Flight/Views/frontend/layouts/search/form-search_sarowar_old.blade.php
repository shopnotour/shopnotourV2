<!-- <html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head> -->

<body class="bg-gray-100 p-8">

    <div x-data="multiCityForm()" x-init="init()" class="max-w-7xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        <form action="{{route('flight.search')}}" method="get">
            @csrf
            <!-- Trip Type Selection -->
            <div class="flex gap-6 mb-6 whitespace-nowrap">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="tripType" name="trip_type" value="oneway" class="cursor-pointer">
                    <span>One Way</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="tripType" name="trip_type" value="round" class="cursor-pointer">
                    <span>Round Way</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="tripType" name="trip_type" value="multi" class="cursor-pointer">
                    <span>Multi City</span>
                </label>
            </div>

            <!-- Flight Segments -->
            <div>
                <template x-for="(segment, index) in segments" :key="index">
                    <div class="flex flex-col lg:flex-row items-end gap-4 mb-4">

                        <!-- From & To Section -->
                        <div class="flex-[2] flex w-full relative gap-4">

                            <!-- From Airport -->
                            <div class="flex-1 border rounded-lg px-3 py-4 relative" x-data="{ openSearch: false }">
                                <label class="block text-sm font-medium text-red-700 mb-1">From</label>
                                <input type="text" x-model="segment.from_name" readonly
                                    @click="openSearch = true; searchAirport(segment.from_name, index, 'from');"
                                    placeholder="Select departure airport"
                                    class="border w-full cursor-pointer bg-gray-50 rounded">
                                <input type="hidden" :name="`segments[${index}][from]`" x-model="segment.from_id">

                                <!-- Search Dropdown for From -->
                                <div x-show="openSearch" @click.outside="openSearch = false; segment.tempSearch = '';"
                                    class="absolute left-0 right-0 bg-white border rounded-lg shadow-md mt-1 z-50">
                                    <input type="text" x-model="segment.tempSearch"
                                        @input="searchAirport($event.target.value, index, 'from')"
                                        placeholder="Search airport..." class="border-b p-2 w-full">
                                    <ul class="max-h-60 overflow-y-auto">
                                        <template x-for="airport in searchResults[`${index}_from`]" :key="airport.id">
                                            <li @click="selectAirport(index, 'from', airport); openSearch = false;"
                                                class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                                                <span x-text="airport.name"></span> (<span
                                                    x-text="airport.code"></span>)
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <!-- Swap Button -->
                            <button type="button" @click="swap(index)"
                                class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-full hover:bg-blue-600 shadow-lg z-10 text-lg font-bold">
                                ⇆
                            </button>

                            <!-- To Airport -->
                            <div class="flex-1 border rounded-lg px-3 py-4 relative" x-data="{ openTo: false }">
                                <label class="block text-sm font-medium text-red-700 mb-1">To</label>
                                <input type="text" x-model="segment.to" readonly
                                    @click="openTo = true; searchAirport(segment.to, index, 'to');"
                                    placeholder="Select destination airport"
                                    class="border p-0 w-full cursor-pointer bg-gray-50 rounded">
                                <input type="hidden" :name="`segments[${index}][to]`" x-model="segment.to_id">

                                <!-- Search Dropdown for To -->
                                <div x-show="openTo" @click.outside="openTo = false; segment.tempTo = '';"
                                    class="absolute left-0 right-0 bg-white border rounded-lg shadow-md mt-1 z-50">
                                    <input type="text" x-model="segment.tempTo"
                                        @input="searchAirport($event.target.value, index, 'to')"
                                        placeholder="Search destination..." class="border-b p-2 w-full">
                                    <ul class="max-h-60 overflow-y-auto">
                                        <template x-for="airport in searchResults[`${index}_to`]" :key="airport.id">
                                            <li @click="selectAirport(index, 'to', airport); openTo = false;"
                                                class="p-2 hover:bg-gray-100 cursor-pointer">
                                                <span x-text="airport.name"></span> (<span
                                                    x-text="airport.code"></span>)
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Date Section -->
                        <div class="flex-[2] flex  flex-row gap-4 w-full items-end">

                            <!-- Departure Date -->
                            <div :class="tripType === 'multi' ? 'w-full' : 'w-full sm:w-1/2'"
                                class="border rounded-lg px-3 py-4 cursor-pointer"
                                @click="$el.querySelector('input[type=date]').showPicker()">
                                <label class="block text-sm font-medium text-red-700 mb-1 pointer-events-none">Departure
                                    Date</label>
                                <input type="date" :name="`segments[${index}][departure]`" x-model="segment.departure"
                                    class="border p-0 w-full rounded cursor-pointer">
                            </div>

                            <!-- Return Date (Only for One Way & Round Trip) -->
                            <div x-show="tripType !== 'multi'" class="w-full sm:w-1/2 border rounded-lg px-3 py-4"
                                :class="tripType === 'oneway' ? 'bg-gray-100 cursor-not-allowed' : 'cursor-pointer'"
                                @click="handleReturnDateClick($el)">
                                <label class="block text-sm font-medium text-red-700 mb-1 pointer-events-none">Return
                                    Date</label>
                                <input type="date" name="return_date" x-model="returnDate"
                                    class="border p-0 w-full rounded"
                                    :class="tripType === 'oneway' ? 'bg-gray-100 cursor-not-allowed' : 'cursor-pointer'">
                            </div>

                            <!-- Remove Button (Mobile - with dates) -->
                            <div x-show="tripType === 'multi'" class="flex-shrink-0 lg:hidden w-full md:w-1/3">
                                <button type="button" @click="removeSegment(index)" :disabled="segments.length < 2"
                                    :class="segments.length < 2 ? 'bg-gray-300 cursor-not-allowed' : 'bg-red-500 hover:bg-red-600'"
                                    class="text-white px-3 py-3 rounded-lg font-semibold whitespace-nowrap w-full sm:w-auto">
                                    X
                                </button>
                            </div>
                        </div>

                        <!-- Remove Button (Desktop - separate column) -->
                        <div x-show="tripType === 'multi'" class="hidden lg:flex flex-shrink-0">
                            <button type="button" @click="removeSegment(index)" :disabled="segments.length < 2"
                                :class="segments.length < 2 ? 'bg-gray-300 cursor-not-allowed' : 'bg-red-500 hover:bg-red-600'"
                                class="text-white px-3 py-3 rounded-lg font-semibold">
                                X
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Bottom Controls -->
            <div class="mt-6 flex flex-col md:flex-row items-stretch md:items-end gap-4 border-t pt-6">

                <!-- Mobile: Traveler & Add Button Together -->
                <div class="flex md:hidden gap-4 w-full m-2">

                    <!-- Traveler & Class (Mobile) -->
                    <div class="flex-shrink-0 relative w-1/2">
                        <div @click="openTraveler = !openTraveler"
                            class="cursor-pointer bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-4 py-3 h-full flex flex-col justify-center min-w-[140px]">
                            <p class="text-blue-800 font-semibold text-sm whitespace-nowrap">
                                <span x-text="adults + children + infants"></span> Traveler(s)
                            </p>
                            <p class="text-xs text-blue-600" x-text="travelClass"></p>
                        </div>

                        <!-- Hidden Inputs -->
                        <input type="hidden" name="adults" :value="adults">
                        <input type="hidden" name="children" :value="children">
                        <input type="hidden" name="infants" :value="infants">
                        <input type="hidden" name="travel_class" :value="travelClass">

                        <!-- Traveler Dropdown -->
                        <div x-show="openTraveler"
                            class="absolute bottom-full mb-2 left-0 bg-white border rounded-lg shadow-xl w-80 p-4 z-50"
                            @click.away="openTraveler = false" x-transition>

                            <!-- Adults -->
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="font-medium">Adults</p>
                                    <p class="text-xs text-gray-500">12 years and above</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if(adults>1) adults--"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                    <span class="w-8 text-center font-semibold" x-text="adults"></span>
                                    <button type="button" @click="adults++"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                </div>
                            </div>

                            <!-- Children -->
                            <div class="mb-3">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <p class="font-medium">Children</p>
                                        <p class="text-xs text-gray-500">2–11 years</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="removeChild()"
                                            class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                        <span class="w-8 text-center font-semibold" x-text="children"></span>
                                        <button type="button" @click="addChild()"
                                            class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                    </div>
                                </div>
                                <!-- Child Ages Input -->
                                <template x-if="children > 0">
                                    <div class="mt-2 space-y-2">
                                        <template x-for="(age, index) in childrenAges" :key="index">
                                            <div class="flex items-center gap-2 text-sm">
                                                <label class="text-xs text-gray-600" x-text="'Child ' + (index + 1) + ' age:'"></label>
                                                <select x-model="childrenAges[index]"
                                                    :name="`children_ages[${index}]`"
                                                    class="border rounded px-2 py-1 text-sm flex-1">
                                                    <option value="">Select age</option>
                                                    <option value="2">2 years</option>
                                                    <option value="3">3 years</option>
                                                    <option value="4">4 years</option>
                                                    <option value="5">5 years</option>
                                                    <option value="6">6 years</option>
                                                    <option value="7">7 years</option>
                                                    <option value="8">8 years</option>
                                                    <option value="9">9 years</option>
                                                    <option value="10">10 years</option>
                                                    <option value="11">11 years</option>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Infants -->
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="font-medium">Infants</p>
                                    <p class="text-xs text-gray-500">Below 2 years</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if(infants>0) infants--"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                    <span class="w-8 text-center font-semibold" x-text="infants"></span>
                                    <button type="button" @click="infants++"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                </div>
                            </div>

                            <!-- Class Selection -->
                            <div class="border-t pt-3">
                                <p class="font-medium mb-2">Class</p>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="ECONOMY" x-model="travelClass"
                                            class="cursor-pointer">
                                        <span>Economy</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="BUSINESS" x-model="travelClass"
                                            class="cursor-pointer">
                                        <span>Business</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Done Button -->
                            <div class="flex justify-end mt-4">
                                <button type="button" @click="openTraveler = false"
                                    class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold">
                                    Done
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add Flight Button (Mobile - Multi City) -->
                    <div x-show="tripType === 'multi'" class="flex-shrink-0 w-1/2">
                        <button type="button" @click="addSegment"
                            class="bg-blue-500 hover:bg-blue-600 text-white w-full h-full rounded-lg font-semibold whitespace-nowrap text-sm">
                            + Add Flight
                        </button>
                    </div>
                </div>

                <!-- Desktop: Traveler & Class -->
                <div class="hidden md:flex md:flex-1 md:gap-4 md:justify-between md:pt-0">

                    <div class="relative flex-shrink-0 h-12 mt-4 ml-4 overflow-visible">
                        <div @click="openTraveler = !openTraveler"
                            class="cursor-pointer bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-5 py-3 h-full flex flex-col justify-center min-w-[180px]">
                            <p class="text-blue-800 font-semibold whitespace-nowrap">
                                <span x-text="adults + children + infants"></span> Traveler(s)
                            </p>
                            <p class="text-sm text-blue-600" x-text="travelClass"></p>
                        </div>

                        <!-- Hidden Inputs -->
                        <input type="hidden" name="adults" :value="adults">
                        <input type="hidden" name="children" :value="children">
                        <input type="hidden" name="infants" :value="infants">
                        <input type="hidden" name="travel_class" :value="travelClass">

                        <!-- Traveler Dropdown (Fixed Position) -->
                        <div x-show="openTraveler"
                            class="absolute top-full mt-2 left-0 bg-white border rounded-lg shadow-xl w-80 p-4 z-50"
                            @click.away="openTraveler = false" x-transition>

                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="font-medium">Adults</p>
                                    <p class="text-xs text-gray-500">12 years and above</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if(adults>1) adults--"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                    <span class="w-8 text-center font-semibold" x-text="adults"></span>
                                    <button type="button" @click="adults++"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <p class="font-medium">Children</p>
                                        <p class="text-xs text-gray-500">2–11 years</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="removeChild()"
                                            class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                        <span class="w-8 text-center font-semibold" x-text="children"></span>
                                        <button type="button" @click="addChild()"
                                            class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                    </div>
                                </div>
                                <!-- Child Ages Input -->
                                <template x-if="children > 0">
                                    <div class="mt-2 space-y-2">
                                        <template x-for="(age, index) in childrenAges" :key="index">
                                            <div class="flex items-center gap-2 text-sm">
                                                <label class="text-xs text-gray-600" x-text="'Child ' + (index + 1) + ' age:'"></label>
                                                <select x-model="childrenAges[index]"
                                                    :name="`children_ages[${index}]`"
                                                    class="border rounded px-2 py-1 text-sm flex-1">
                                                    <option value="">Select age</option>
                                                    <option value="2">2 years</option>
                                                    <option value="3">3 years</option>
                                                    <option value="4">4 years</option>
                                                    <option value="5">5 years</option>
                                                    <option value="6">6 years</option>
                                                    <option value="7">7 years</option>
                                                    <option value="8">8 years</option>
                                                    <option value="9">9 years</option>
                                                    <option value="10">10 years</option>
                                                    <option value="11">11 years</option>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="font-medium">Infants</p>
                                    <p class="text-xs text-gray-500">Below 2 years</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if(infants>0) infants--"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">-</button>
                                    <span class="w-8 text-center font-semibold" x-text="infants"></span>
                                    <button type="button" @click="infants++"
                                        class="w-8 h-8 border rounded-full hover:bg-gray-100">+</button>
                                </div>
                            </div>

                            <div class="border-t pt-3">
                                <p class="font-medium mb-2">Class</p>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="ECONOMY" x-model="travelClass"
                                            class="cursor-pointer">
                                        <span>Economy</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="BUSINESS" x-model="travelClass"
                                            class="cursor-pointer">
                                        <span>Business</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end mt-4">
                                <button type="button" @click="openTraveler = false"
                                    class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold">
                                    Done
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add Flight Button (Desktop - Multi City) -->
                    <div x-show="tripType === 'multi'" class="flex-shrink-0 mt-4">
                        <button type="button" @click="addSegment"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                            + Add Flight
                        </button>
                    </div>
                </div>
                <!-- Search Button (Right aligned on desktop) -->
                <div class="flex-shrink-0 w-full md:w-auto md:ml-auto">
                    <button type="submit"
                        class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 w-full lg:w-auto px-6 py-2 rounded-lg font-bold text-lg shadow-md">
                        Search Flights
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Alpine.js Component for Multi-City Flight Search Form
    function multiCityForm() {
        return {
            // State Variables
            tripType: 'oneway',
            segments: [{
                from_name: 'Hazrat Shahjalal International Airport (DAC)',
                from_id: 61,
                to: 'Coxs Bazar Airport (CXB)',
                to_id: '62',
                departure: new Date().toISOString().split('T')[0],
                tempSearch: '',
                tempTo: ''
            }],
            returnDate: '',
            openTraveler: false,
            adults: 1,
            children: 0,
            childrenAges: [], // Array to store ages of each child
            infants: 0,
            travelClass: "ECONOMY",
            searchResults: {},
            loading: false,

            // Initialize Component
            init() {
                const today = new Date();

                // Pre-populate from request data if available
                @if(request()->has('segments'))
                    this.tripType = '{{ request()->input("trip_type", "oneway") }}';

                    @if(request()->input('trip_type') == 'round')
                        this.returnDate = '{{ request()->input("return_date", "") }}';
                    @endif

                    @php
                        $requestSegments = request()->input('segments', []);
                        $segmentAirports = $segment_airports ?? [];
                    @endphp

                    this.segments = [
                        @foreach($requestSegments as $index => $segment)
                        {
                            from_name: '@if(isset($segmentAirports[$index]) && isset($segmentAirports[$index]["from"]) && $segmentAirports[$index]["from"]){{ $segmentAirports[$index]["from"]->name }} ({{ $segmentAirports[$index]["from"]->code }})@endif',
                            from_id: '{{ $segment["from"] ?? "" }}',
                            to: '@if(isset($segmentAirports[$index]) && isset($segmentAirports[$index]["to"]) && $segmentAirports[$index]["to"]){{ $segmentAirports[$index]["to"]->name }} ({{ $segmentAirports[$index]["to"]->code }})@endif',
                            to_id: '{{ $segment["to"] ?? "" }}',
                            departure: '{{ $segment["departure"] ?? "" }}',
                            tempSearch: '',
                            tempTo: ''
                        }{{ $loop->last ? '' : ',' }}
                        @endforeach
                    ];

                    // Pre-populate traveler info
                    @if(request()->has('adults'))
                        this.adults = {{ request()->input('adults', 1) }};
                    @endif
                    @if(request()->has('children'))
                        this.children = {{ request()->input('children', 0) }};
                    @endif
                    @if(request()->has('children_ages'))
                        this.childrenAges = @json(request()->input('children_ages', []));
                    @endif
                    @if(request()->has('infants'))
                        this.infants = {{ request()->input('infants', 0) }};
                    @endif
                    @if(request()->has('travel_class'))
                        this.travelClass = '{{ request()->input("travel_class", "ECONOMY") }}';
                    @endif
                @else
                    // Default initialization
                    this.segments[0].departure = today.toISOString().split('T')[0];
                @endif

                // Watch trip type changes
                this.$watch('tripType', (value) => {
                    this.handleTripTypeChange(value, today);
                });
            },

            // Handle Trip Type Change
            handleTripTypeChange(value, today) {
                const defaultSegment = {
                    from_name: 'Hazrat Shahjalal International Airport (DAC)',
                    from_id: '61',
                    to: 'Coxs Bazar Airport (CXB)',
                    to_id: '62',
                    departure: today.toISOString().split('T')[0],
                    tempSearch: '',
                    tempTo: ''
                };

                if (value === 'oneway') {
                    this.returnDate = '';
                    this.segments = [{ ...defaultSegment }];
                } else if (value === 'round') {
                    this.setReturnDate();
                    this.segments = [{ ...defaultSegment }];
                } else {
                    this.returnDate = '';
                    this.segments = [
                        { ...defaultSegment },
                        { from_name: '', from_id: '', to: '', to_id: '', departure: '', tempSearch: '', tempTo: '' }
                    ];
                }
            },

            // Set Return Date to Tomorrow
            setReturnDate() {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                this.returnDate = tomorrow.toISOString().split('T')[0];
            },

            // Add New Flight Segment
            addSegment() {
                this.segments.push({
                    from_name: '',
                    from_id: '',
                    to: '',
                    to_id: '',
                    departure: '',
                    tempSearch: '',
                    tempTo: ''
                });
            },

            // Remove Flight Segment
            removeSegment(index) {
                if (this.segments.length > 1) {
                    this.segments.splice(index, 1);
                }
            },

            // Swap From and To Airports
            swap(index) {
                const segment = this.segments[index];
                [segment.from_name, segment.to] = [segment.to, segment.from_name];
                [segment.from_id, segment.to_id] = [segment.to_id, segment.from_id];
            },

            // Handle Return Date Click
            handleReturnDateClick(el) {
                if (this.tripType !== 'oneway') {
                    el.querySelector('input[type=date]').showPicker();
                } else {
                    this.tripType = 'round';
                    this.setReturnDate();
                    setTimeout(() => el.querySelector('input[type=date]').showPicker(), 10);
                }
            },

            // Add Child (with age selection)
            addChild() {
                this.children++;
                this.childrenAges.push(''); // Add empty age slot
            },

            // Remove Child
            removeChild() {
                if (this.children > 0) {
                    this.children--;
                    this.childrenAges.pop(); // Remove last age
                }
            },

            // Select Airport from Search Results
            selectAirport(index, field, airport) {
                const segment = this.segments[index];
                if (field === 'from') {
                    segment.from_name = `${airport.name} (${airport.code})`;
                    segment.from_id = airport.id;
                    segment.tempSearch = '';
                } else {
                    segment.to = `${airport.name} (${airport.code})`;
                    segment.to_id = airport.id;
                    segment.tempTo = '';
                }
                this.searchResults[`${index}_${field}`] = [];
            },

            // Search Airports via API
            async searchAirport(query, index, field) {
                if (query.length < 2) {
                    this.searchResults[`${index}_${field}`] = [];
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(
                        `{{ route('flight.airport.search') }}?search=${encodeURIComponent(query)}`,
                        {
                            headers: {
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        }
                    );

                    if (!response.ok) {
                        throw new Error("Failed to fetch airports");
                    }

                    const data = await response.json();
                    console.log(data);
                    this.searchResults[`${index}_${field}`] = data;

                } catch (error) {
                    console.error("Airport fetch error:", error);
                    this.searchResults[`${index}_${field}`] = [];
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>

</body>
<!--</html>-->
