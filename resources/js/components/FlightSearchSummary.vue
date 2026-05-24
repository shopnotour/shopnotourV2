<template>
    <div 
        class="flight-summary-card bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer"
        @click="toggleSearchForm"
    >
        <div class="p-4 md:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 md:gap-3">
                <!-- Route -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-plane-departure text-blue-500"></i>
                        <span class="font-semibold text-gray-800 text-xs md:text-base">{{ summaryData.route || 'Select Route' }}</span>
                    </div>
                </div>

                <!-- Separator -->
                <div class="hidden md:block w-px h-8 bg-gray-200"></div>

                <!-- Trip Type -->
                <div class="flex items-center gap-2 hidden md:flex">
                    <i class="fas fa-route text-green-500"></i>
                    <span class="text-gray-700 text-sm md:text-base">{{ summaryData.tripTypeLabel }}</span>
                </div>

                <!-- Separator -->
                <div class="hidden md:block w-px h-8 bg-gray-200"></div>

                <!-- Dates -->
                <div class="flex items-center gap-2 hidden md:flex">
                    <i class="fas fa-calendar-alt text-purple-500"></i>
                    <span class="text-gray-700 text-xs md:text-base">{{ summaryData.dates || 'Select dates' }}</span>
                </div>

                <!-- Separator -->
                <div class="hidden md:block w-px h-8 bg-gray-200"></div>

                <!-- Travelers & Class -->
                <div class="flex items-center gap-3 hidden md:flex">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-orange-500"></i>
                        <span class="text-gray-700 text-sm md:text-base">{{ summaryData.travelers || '0 Travelers' }}</span>
                    </div>
                    <div class="hidden md:block w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chair text-indigo-500"></i>
                        <span class="text-gray-700 text-sm md:text-base">{{ summaryData.travelClass || 'Economy' }}</span>
                    </div>
                </div>

                <!-- Toggle Button -->
                <div class="ml-auto">
                    <button
                        class="group flex items-center justify-center gap-2 px-1 py-1 md:px-6 md:py-3
                            bg-gradient-to-r from-[#26c8f9] to-[#03c5ff] hover:from-[#03c5ff] hover:to-[#2bc7e6]
                            text-white font-semibold rounded-full shadow-md hover:shadow-lg
                            transition-all duration-300 transform hover:scale-105"
                        @click.stop="toggleSearchForm"
                    >
                        <i class="fas fa-search text-sm md:text-base group-hover:rotate-12 transition-transform duration-300"></i>

                        <span class="text-xs md:text-sm">
                            {{ isFormVisible ? 'Hide Search' : 'Search Flights' }}
                        </span>

                        <i
                            class="fas fa-chevron-down text-xs transition-transform duration-300"
                            :class="{ 'rotate-180': isFormVisible }"
                        ></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FlightSearchSummary',
    data() {
        return {
            summaryData: {
                route: '',
                to: '',
                tripType: 'round',
                tripTypeLabel: 'Round Trip',
                dates: '',
                travelers: '',
                travelClass: 'Economy'
            },
            isFormVisible: false
        };
    },
    mounted() {
        // Listen for summary updates from the search form
        window.addEventListener('message', this.handleSummaryUpdate);
        
        // Also listen for search form toggle state
        document.addEventListener('searchFormToggled', this.handleFormToggle);
        
        // Initial check for form visibility
        this.checkFormVisibility();
    },
    beforeDestroy() {
        window.removeEventListener('message', this.handleSummaryUpdate);
        document.removeEventListener('searchFormToggled', this.handleFormToggle);
    },
    methods: {
        handleSummaryUpdate(event) {
            if (event.data.type === 'flight-search-summary-update') {
                this.summaryData = {
                    ...this.summaryData,
                    ...event.data.summary,
                    tripTypeLabel: this.getTripTypeLabel(event.data.summary.tripType)
                };
            }
        },
        
        getTripTypeLabel(tripType) {
            const labels = {
                'oneway': 'One Way',
                'round': 'Round Trip',
                'multi': 'Multi City'
            };
            return labels[tripType] || 'Round Trip';
        },
        
        handleFormToggle(event) {
            this.isFormVisible = event.detail?.isVisible || false;
        },
        
        checkFormVisibility() {
            const formSection = document.getElementById('searchFormSection');
            if (formSection) {
                this.isFormVisible = formSection.classList.contains('active');
            }
        },
        
        toggleSearchForm() {
            const section = document.getElementById('searchFormSection');

            if (!section) return;

            const isActive = section.classList.toggle('active');

            this.isFormVisible = isActive;

            // Update hidden button UI if exists
            const chevron = document.getElementById('chevronIcon');
            const btnText = document.querySelector('#searchToggleBtn span');

            if (chevron) {
                chevron.style.transform = isActive
                    ? 'rotate(180deg)'
                    : 'rotate(0deg)';
            }

            if (btnText) {
                btnText.textContent = isActive
                    ? 'Hide Search'
                    : 'Search Flights';
            }

            // Dispatch global event
            document.dispatchEvent(
                new CustomEvent('searchFormToggled', {
                    detail: { isVisible: isActive }
                })
            );

            // Close flatpickr when collapsing
            if (!isActive) {
                document.querySelectorAll('.flatpickr-calendar')
                    .forEach(cal => {
                        cal.style.display = 'none';
                    });
            }
        }
    }
};
</script>

<style scoped>
.flight-summary-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e5e7eb;
    will-change: transform;
}

.flight-summary-card:hover {
    transform: translateY(-2px);
    box-shadow:
        0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.02);
}

.rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.3s ease;
}

/* Mobile Improvements */
@media (max-width: 768px) {

    /* Prevent horizontal layout breaking */
    .flight-summary-card .flex.items-center.gap-4 {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
    }

    .flight-summary-card .flex.items-center.gap-4::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }

    /* Improve tap interaction */
    .flight-summary-card {
        border-radius: 12px;
    }

    /* Disable hover animation on touch devices */
    .flight-summary-card:hover {
        transform: none;
        box-shadow: none;
    }
}
</style>