<!--<template>-->
<!--    <div class="flight-search-wrapper">-->

<!--        &lt;!&ndash; Main content row &ndash;&gt;-->
<!--        <div class="container">-->
<!--            <div class="row">-->

<!--                &lt;!&ndash; Mobile Floating Filter Button &ndash;&gt;-->
<!--                <button-->
<!--                    v-if="isMobile && !isSidebarOpen"-->
<!--                    class="mobile-floating-btn"-->
<!--                    @click="toggleSidebar"-->
<!--                    aria-label="Open Filters">-->
<!--                    <i class="fas fa-sliders-h"></i>-->
<!--                    <span class="fab-label">Filters</span>-->
<!--                </button>-->

<!--                &lt;!&ndash; Backdrop (Mobile Only) &ndash;&gt;-->
<!--                <div-->
<!--                    class="sidebar-backdrop"-->
<!--                    :class="{ 'active': isSidebarOpen && isMobile }"-->
<!--                    @click="closeSidebar">-->
<!--                </div>-->

<!--                &lt;!&ndash; Left: Filters &ndash;&gt;-->
<!--                <div class="col-lg-3">-->
<!--                    <div-->
<!--                        class="sidebar-wrapper"-->
<!--                        :class="{-->
<!--                            'is-fixed': isSidebarFixed && !isMobile,-->
<!--                            'is-open': isSidebarOpen,-->
<!--                            'is-closed': !isSidebarOpen-->
<!--                        }"-->
<!--                        @click.stop>-->

<!--                        <button-->
<!--                            class="sidebar-close-btn"-->
<!--                            @click="closeSidebar"-->
<!--                            aria-label="Close Filters">-->
<!--                        </button>-->

<!--                        <FilterSidebar-->
<!--                            :flights="allFlights"-->
<!--                            @filter-change="handleFilterChange"-->
<!--                        />-->
<!--                    </div>-->
<!--                </div>-->

<!--                &lt;!&ndash; Right: Flight List &ndash;&gt;-->
<!--                <div class="col-lg-9">-->

<!--                    &lt;!&ndash; Loader &ndash;&gt;-->
<!--                    <div v-if="isLoading" class="flight-loader-overlay">-->
<!--                        <div class="flight-loader-container">-->
<!--                            <svg class="flight-svg" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg">-->
<!--                                <defs>-->
<!--                                    <path id="flightCurve" d="M 50,160 Q 200,20 350,160" />-->
<!--                                    <linearGradient id="planeGradient" x1="0%" y1="0%" x2="100%" y2="100%">-->
<!--                                        <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:1" />-->
<!--                                    </linearGradient>-->
<!--                                </defs>-->
<!--                                <use href="#flightCurve" fill="none" stroke="#cbd5e1" stroke-width="4" stroke-dasharray="12,8" stroke-linecap="round"/>-->
<!--                                <circle cx="50" cy="160" r="8" fill="#10b981">-->
<!--                                    <animate attributeName="opacity" values="1;0.5;1" dur="2s" repeatCount="indefinite"/>-->
<!--                                </circle>-->
<!--                                <circle cx="350" cy="160" r="8" fill="#3b82f6">-->
<!--                                    <animate attributeName="opacity" values="1;0.5;1" dur="2s" repeatCount="indefinite"/>-->
<!--                                </circle>-->
<!--                                <g class="animated-plane">-->
<!--                                    <text x="0" y="5" font-size="40" text-anchor="middle" fill="url(#planeGradient)">✈</text>-->
<!--                                    <ellipse cx="0" cy="15" rx="12" ry="3" fill="black" opacity="0.2"/>-->
<!--                                    <animateMotion dur="4s" repeatCount="indefinite" rotate="auto" calcMode="linear">-->
<!--                                        <mpath href="#flightCurve"/>-->
<!--                                    </animateMotion>-->
<!--                                </g>-->
<!--                            </svg>-->
<!--                            <div class="loading-text-bottom">-->
<!--                                <span class="loading-message">Filtering flights</span>-->
<!--                                <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    &lt;!&ndash; Flight List &ndash;&gt;-->
<!--                    <FlightList-->
<!--                        v-else-->
<!--                        :flights="filteredFlights"-->
<!--                        :airline-chips="airlineChips"-->
<!--                        :selected-airline="filters.airlines.length === 1 ? filters.airlines[0] : ''"-->
<!--                        :cheapest-price="getCheapestPrice()"-->
<!--                        @airline-select="onAirlineSelect"-->
<!--                    />-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->
<!--import FilterSidebar from './FilterSidebar.vue';-->
<!--import FlightList from './FlightList.vue';-->

<!--export default {-->
<!--    name: 'FlightSearch',-->
<!--    components: { FilterSidebar, FlightList },-->
<!--    props: {-->
<!--        flightData: { type: Object, required: true }-->
<!--    },-->
<!--    data() {-->
<!--        return {-->
<!--            allFlights: [],-->
<!--            isLoading: false,-->
<!--            isSidebarFixed: false,-->
<!--            shouldStopAtFooter: false,-->
<!--            isSidebarOpen: true,-->
<!--            isMobile: false,-->
<!--            sidebarLeft: 0,-->
<!--            sidebarWidth: 0,-->
<!--            // ✅ Arrow visibility-->
<!--            showLeftArrow: false,-->
<!--            showRightArrow: false,-->
<!--            filters: {-->
<!--                priceRange: [0, 200000],-->
<!--                airlines: [],-->
<!--                stops: 'all',-->
<!--                departureTime: [],-->
<!--                refundable: 'all'-->
<!--            }-->
<!--        }-->
<!--    },-->
<!--    computed: {-->
<!--        filteredFlights() {-->
<!--            let flights = [...this.allFlights];-->

<!--            flights = flights.filter(f => {-->
<!--                const price = f.price?.total || 0;-->
<!--                return price >= this.filters.priceRange[0] && price <= this.filters.priceRange[1];-->
<!--            });-->

<!--            if (this.filters.airlines.length > 0) {-->
<!--                flights = flights.filter(f => this.filters.airlines.includes(f.validating_carrier));-->
<!--            }-->

<!--            if (this.filters.stops !== 'all') {-->
<!--                flights = flights.filter(f => {-->
<!--                    const maxStops = Math.max(...(f.legs?.map(leg => leg.stops) || [0]));-->
<!--                    const stopFilter = parseInt(this.filters.stops);-->
<!--                    if (stopFilter === 0) return maxStops === 0;-->
<!--                    if (stopFilter === 1) return maxStops === 1;-->
<!--                    if (stopFilter === 2) return maxStops >= 2;-->
<!--                    return true;-->
<!--                });-->
<!--            }-->

<!--            if (this.filters.departureTime.length > 0) {-->
<!--                flights = flights.filter(f => {-->
<!--                    const departureTime = f.legs?.[0]?.departure?.time;-->
<!--                    if (!departureTime) return false;-->
<!--                    const hour = parseInt(departureTime.split(':')[0]);-->
<!--                    return this.filters.departureTime.some(slot => {-->
<!--                        if (slot === 'early_morning') return hour >= 0 && hour < 6;-->
<!--                        if (slot === 'morning') return hour >= 6 && hour < 12;-->
<!--                        if (slot === 'afternoon') return hour >= 12 && hour < 18;-->
<!--                        if (slot === 'evening') return hour >= 18;-->
<!--                        return false;-->
<!--                    });-->
<!--                });-->
<!--            }-->

<!--            if (this.filters.refundable === 'refundable') {-->
<!--                flights = flights.filter(f => f.refundable === true);-->
<!--            } else if (this.filters.refundable === 'non_refundable') {-->
<!--                flights = flights.filter(f => f.refundable === false);-->
<!--            }-->

<!--            return flights;-->
<!--        },-->

<!--        airlineChips() {-->
<!--            const map = {};-->
<!--            this.allFlights.forEach(f => {-->
<!--                const code = f.validating_carrier;-->
<!--                if (!map[code]) {-->
<!--                    const seg = f.legs?.[0]?.segments?.[0];-->
<!--                    map[code] = {-->
<!--                        code,-->
<!--                        name: seg?.carrier_name || code,-->
<!--                        shortName: this.getShortName(seg?.carrier_name || code),-->
<!--                        logo: seg?.carrier_images?.thumb || null,-->
<!--                        cheapest: f.price?.total || 0,-->
<!--                        count: 0-->
<!--                    };-->
<!--                }-->
<!--                map[code].count++;-->
<!--                const price = f.price?.total || 0;-->
<!--                if (price < map[code].cheapest) map[code].cheapest = price;-->
<!--            });-->
<!--            return Object.values(map).sort((a, b) => a.cheapest - b.cheapest);-->
<!--        }-->
<!--    },-->
<!--    methods: {-->
<!--        getShortName(name) {-->
<!--            if (!name) return '??';-->
<!--            const words = name.split(' ');-->
<!--            if (words.length === 1) return name.substring(0, 9);-->
<!--            return words.slice(0, 2).join(' ').substring(0, 13);-->
<!--        },-->

<!--        getCheapestPrice() {-->
<!--            if (!this.allFlights.length) return 0;-->
<!--            return Math.min(...this.allFlights.map(f => f.price?.total || 0));-->
<!--        },-->

<!--        formatChipPrice(price) {-->
<!--            if (!price) return '0';-->
<!--            const p = parseInt(price);-->
<!--            if (p >= 1000) return Math.round(p / 1000) + 'K';-->
<!--            return p.toString();-->
<!--        },-->

<!--        clearAirlineFilter() {-->
<!--            this.filters.airlines = [];-->
<!--            this.triggerLoader();-->
<!--        },-->

<!--        toggleAirlineChip(code) {-->
<!--            if (this.filters.airlines.length === 1 && this.filters.airlines[0] === code) {-->
<!--                this.filters.airlines = [];-->
<!--            } else {-->
<!--                this.filters.airlines = [code];-->
<!--            }-->
<!--            this.triggerLoader();-->
<!--        },-->

<!--        // ✅ FlightList থেকে airline select event-->
<!--        onAirlineSelect(code) {-->
<!--            if (!code) {-->
<!--                this.filters.airlines = [];-->
<!--            } else if (this.filters.airlines.length === 1 && this.filters.airlines[0] === code) {-->
<!--                this.filters.airlines = [];-->
<!--            } else {-->
<!--                this.filters.airlines = [code];-->
<!--            }-->
<!--            this.triggerLoader();-->
<!--        },-->

<!--        triggerLoader() {-->
<!--            this.isLoading = true;-->
<!--            setTimeout(() => { this.isLoading = false; }, 600);-->
<!--        },-->

<!--        // ✅ Scroll arrows logic-->
<!--        updateArrows() {-->
<!--            this.$nextTick(() => {-->
<!--                const el = this.$refs.chipsScroll;-->
<!--                if (!el) return;-->
<!--                this.showLeftArrow  = el.scrollLeft > 8;-->
<!--                this.showRightArrow = el.scrollLeft < (el.scrollWidth - el.clientWidth - 8);-->
<!--            });-->
<!--        },-->

<!--        scrollChips(dir) {-->
<!--            const el = this.$refs.chipsScroll;-->
<!--            if (!el) return;-->
<!--            el.scrollBy({ left: dir === 'right' ? 260 : -260, behavior: 'smooth' });-->
<!--            // arrow visibility আবার check-->
<!--            setTimeout(() => this.updateArrows(), 350);-->
<!--        },-->

<!--        checkMobile() {-->
<!--            this.isMobile = window.innerWidth <= 768;-->
<!--            this.isSidebarOpen = !this.isMobile;-->
<!--        },-->

<!--        toggleSidebar() {-->
<!--            this.isSidebarOpen = !this.isSidebarOpen;-->
<!--            if (this.isMobile) {-->
<!--                document.body.style.overflow = this.isSidebarOpen ? 'hidden' : '';-->
<!--            }-->
<!--        },-->

<!--        closeSidebar() {-->
<!--            if (this.isMobile) {-->
<!--                this.isSidebarOpen = false;-->
<!--                document.body.style.overflow = '';-->
<!--            }-->
<!--        },-->

<!--        handleScroll() {-->
<!--            if (this.isMobile) return;-->
<!--            const trigger = document.getElementById('sidebar-trigger-point');-->
<!--            if (!trigger) return;-->
<!--            const triggerTop = trigger.getBoundingClientRect().top;-->
<!--            const shouldBeFixed = triggerTop <= 80;-->

<!--            if (shouldBeFixed && !this.isSidebarFixed) {-->
<!--                const col = document.querySelector('.col-lg-3');-->
<!--                if (col) {-->
<!--                    const rect = col.getBoundingClientRect();-->
<!--                    this.sidebarLeft = rect.left;-->
<!--                    this.sidebarWidth = rect.width;-->
<!--                }-->
<!--            }-->

<!--            const footer = document.querySelector('footer');-->
<!--            if (footer && shouldBeFixed) {-->
<!--                this.shouldStopAtFooter = footer.getBoundingClientRect().top < window.innerHeight;-->
<!--            } else {-->
<!--                this.shouldStopAtFooter = false;-->
<!--            }-->

<!--            this.isSidebarFixed = shouldBeFixed && !this.shouldStopAtFooter;-->

<!--            if (this.isSidebarFixed) {-->
<!--                const el = document.querySelector('.sidebar-wrapper');-->
<!--                if (el) {-->
<!--                    el.style.left = this.sidebarLeft + 'px';-->
<!--                    el.style.width = this.sidebarWidth + 'px';-->
<!--                }-->
<!--            }-->
<!--        },-->

<!--        handleFilterChange(filters) {-->
<!--            this.isLoading = true;-->
<!--            window.scrollTo({ top: 0, behavior: 'smooth' });-->
<!--            setTimeout(() => {-->
<!--                this.filters = { ...this.filters, ...filters };-->
<!--                this.isLoading = false;-->
<!--                if (this.isMobile) this.closeSidebar();-->
<!--            }, 1000);-->
<!--        },-->

<!--        handleResize() {-->
<!--            this.checkMobile();-->
<!--            this.updateArrows();-->
<!--            if (!this.isMobile && this.isSidebarFixed) {-->
<!--                const col = document.querySelector('.col-lg-3');-->
<!--                if (col) {-->
<!--                    const rect = col.getBoundingClientRect();-->
<!--                    const el = document.querySelector('.sidebar-wrapper');-->
<!--                    if (el) {-->
<!--                        el.style.left = rect.left + 'px';-->
<!--                        el.style.width = rect.width + 'px';-->
<!--                    }-->
<!--                }-->
<!--            }-->
<!--        }-->
<!--    },-->

<!--    mounted() {-->
<!--        if (window.flightData && window.flightData.flights) {-->
<!--            // ✅ Search result page-->
<!--            this.allFlights = window.flightData.flights.flights || [];-->
<!--            const prices = this.allFlights.map(f => f.price?.total || 0);-->
<!--            if (prices.length > 0) {-->
<!--                this.filters.priceRange = [Math.min(...prices), Math.max(...prices)];-->
<!--            }-->
<!--        }-->
<!--        // ✅ Home page এ flights নেই — কোনো error নেই-->

<!--        this.checkMobile();-->
<!--        window.addEventListener('scroll', this.handleScroll);-->
<!--        window.addEventListener('resize', this.handleResize);-->
<!--        setTimeout(() => {-->
<!--            this.handleScroll();-->
<!--            this.updateArrows();-->
<!--        }, 200);-->
<!--    },-->

<!--    beforeDestroy() {-->
<!--        window.removeEventListener('scroll', this.handleScroll);-->
<!--        window.removeEventListener('resize', this.handleResize);-->
<!--        document.body.style.overflow = '';-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->
<!--.flight-search-wrapper {-->
<!--    width: 100%;-->
<!--}-->

<!--/* ================================-->
<!--   MOBILE FLOATING BUTTON-->
<!--   ================================ */-->
<!--.mobile-floating-btn {-->
<!--    position: fixed;-->
<!--    bottom: 80px;-->
<!--    left: 16px;-->
<!--    height: 46px;-->
<!--    padding: 0 16px;-->
<!--    background: linear-gradient(135deg, #2563eb, #1d4ed8);-->
<!--    color: white;-->
<!--    border: none;-->
<!--    border-radius: 23px;-->
<!--    box-shadow: 0 4px 16px rgba(37,99,235,0.45);-->
<!--    cursor: pointer;-->
<!--    z-index: 10000;-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    gap: 6px;-->
<!--    font-size: 13px;-->
<!--    font-weight: 600;-->
<!--    transition: all 0.25s ease;-->
<!--}-->
<!--.mobile-floating-btn:hover {-->
<!--    transform: scale(1.05);-->
<!--    box-shadow: 0 6px 20px rgba(37,99,235,0.5);-->
<!--}-->
<!--.fab-label { font-size: 12px; font-weight: 600; }-->

<!--/* ================================-->
<!--   BACKDROP-->
<!--   ================================ */-->
<!--.sidebar-backdrop {-->
<!--    position: fixed;-->
<!--    inset: 0;-->
<!--    background: rgba(0,0,0,0.5);-->
<!--    z-index: 9998;-->
<!--    opacity: 0;-->
<!--    visibility: hidden;-->
<!--    transition: all 0.3s ease;-->
<!--}-->
<!--.sidebar-backdrop.active { opacity: 1; visibility: visible; }-->
<!--@media (min-width: 769px) { .sidebar-backdrop { display: none; } }-->

<!--/* ================================-->
<!--   SIDEBAR — Desktop fixed on scroll-->
<!--   ================================ */-->
<!--.sidebar-wrapper { transition: transform 0.3s ease; }-->

<!--@media (min-width: 992px) {-->
<!--    .sidebar-wrapper.is-fixed {-->
<!--        position: fixed;-->
<!--        top: 130px;-->
<!--        max-height: calc(100vh - 150px);-->
<!--        overflow-y: auto;-->
<!--        z-index: 50;-->
<!--        scrollbar-width: thin;-->
<!--        scrollbar-color: #cbd5e1 #f1f5f9;-->
<!--    }-->
<!--    .sidebar-wrapper.is-fixed::-webkit-scrollbar { width: 5px; }-->
<!--    .sidebar-wrapper.is-fixed::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }-->
<!--    .sidebar-wrapper.is-fixed::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }-->
<!--}-->

<!--@media (min-width: 769px) and (max-width: 991px) {-->
<!--    .sidebar-wrapper.is-fixed { position: static; width: 100%; max-height: none; }-->
<!--}-->

<!--/* ================================-->
<!--   MOBILE: LEFT DRAWER-->
<!--   ================================ */-->
<!--@media (max-width: 768px) {-->
<!--    .sidebar-wrapper {-->
<!--        position: fixed;-->
<!--        top: 0; left: 0;-->
<!--        width: 300px;-->
<!--        height: 100vh;-->
<!--        background: white;-->
<!--        z-index: 9999;-->
<!--        overflow-y: auto;-->
<!--        box-shadow: 4px 0 20px rgba(0,0,0,0.18);-->
<!--        transform: translateX(-100%);-->
<!--        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);-->
<!--    }-->
<!--    .sidebar-wrapper.is-open  { transform: translateX(0); }-->
<!--    .sidebar-wrapper.is-closed { transform: translateX(-100%); }-->
<!--}-->

<!--/* ================================-->
<!--   CLOSE BUTTON (Mobile)-->
<!--   ================================ */-->
<!--.sidebar-close-btn { display: none; }-->
<!--@media (max-width: 768px) {-->
<!--    .sidebar-close-btn {-->
<!--        display: flex;-->
<!--        align-items: center;-->
<!--        justify-content: center;-->
<!--        position: sticky;-->
<!--        top: 0;-->
<!--        width: 100%;-->
<!--        height: 48px;-->
<!--        background: #1d4ed8;-->
<!--        color: white;-->
<!--        border: none;-->
<!--        font-size: 14px;-->
<!--        font-weight: 600;-->
<!--        cursor: pointer;-->
<!--        z-index: 10;-->
<!--    }-->
<!--    .sidebar-close-btn::before { content: '← Close Filters'; color: white; }-->
<!--}-->

<!--/* ================================-->
<!--   LOADER-->
<!--   ================================ */-->
<!--.flight-loader-overlay {-->
<!--    position: fixed;-->
<!--    inset: 0;-->
<!--    background: rgba(255,255,255,0.96);-->
<!--    backdrop-filter: blur(3px);-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    z-index: 9999;-->
<!--}-->
<!--.flight-loader-container {-->
<!--    position: relative;-->
<!--    width: 100%;-->
<!--    max-width: 500px;-->
<!--    height: 250px;-->
<!--    display: flex;-->
<!--    flex-direction: column;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--}-->
<!--.flight-svg { width: 100%; height: 100%; }-->
<!--.loading-text-bottom {-->
<!--    position: absolute;-->
<!--    bottom: 20px;-->
<!--    font-size: 18px;-->
<!--    color: #475569;-->
<!--    font-weight: 600;-->
<!--    text-align: center;-->
<!--}-->
<!--.dot { animation: blink 1.4s infinite; opacity: 0; font-size: 24px; font-weight: bold; }-->
<!--.dot:nth-child(2) { animation-delay: 0.2s; }-->
<!--.dot:nth-child(3) { animation-delay: 0.4s; }-->
<!--.dot:nth-child(4) { animation-delay: 0.6s; }-->
<!--@keyframes blink {-->
<!--    0%, 20% { opacity: 0; }-->
<!--    50%      { opacity: 1; }-->
<!--    100%     { opacity: 0; }-->
<!--}-->
<!--</style>-->


<template>
    <div class="fsp-wrap">

        <!-- ══════════════════════════════════════
             MOBILE FILTER CHIPS BAR  (sticky top)
        ══════════════════════════════════════ -->
        <div class="fsp-chips-bar">
            <div class="fsp-chips-scroll">

                <button class="fsp-chip fsp-chip-all"
                        :class="{ 'has-filter': activeFilterCount > 0 }"
                        @click="openSheet('all')">
                    <i class="fa fa-sliders"></i>
                    <span>Filters</span>
                    <span v-if="activeFilterCount" class="fsp-chip-badge">{{ activeFilterCount }}</span>
                </button>

                <button class="fsp-chip" :class="{ active: filters.stops !== 'all' }" @click="openSheet('stops')">
                    <i class="fa fa-map-pin"></i>
                    <span>{{ filters.stops === 'all' ? 'Stops' : stopLabel(filters.stops) }}</span>
                    <i v-if="filters.stops !== 'all'" class="fa fa-xmark fsp-chip-x" @click.stop="clearFilter('stops')"></i>
                    <i v-else class="fa fa-chevron-down fsp-chip-arr"></i>
                </button>

                <button class="fsp-chip" :class="{ active: isPriceFiltered }" @click="openSheet('price')">
                    <i class="fa fa-tag"></i>
                    <span>{{ isPriceFiltered ? '৳'+formatPriceK(filters.priceRange[0])+'–'+ formatPriceK(filters.priceRange[1]) : 'Price' }}</span>
                    <i v-if="isPriceFiltered" class="fa fa-xmark fsp-chip-x" @click.stop="clearFilter('price')"></i>
                    <i v-else class="fa fa-chevron-down fsp-chip-arr"></i>
                </button>

                <button class="fsp-chip" :class="{ active: filters.departureTime.length }" @click="openSheet('time')">
                    <i class="fa fa-clock"></i>
                    <span>{{ filters.departureTime.length ? filters.departureTime.length+' Time' : 'Time' }}</span>
                    <i v-if="filters.departureTime.length" class="fa fa-xmark fsp-chip-x" @click.stop="clearFilter('time')"></i>
                    <i v-else class="fa fa-chevron-down fsp-chip-arr"></i>
                </button>

                <button class="fsp-chip" :class="{ active: filters.airlines.length }" @click="openSheet('airlines')">
                    <i class="fa fa-plane"></i>
                    <span>{{ filters.airlines.length ? filters.airlines.length+' Airline' : 'Airline' }}</span>
                    <i v-if="filters.airlines.length" class="fa fa-xmark fsp-chip-x" @click.stop="clearFilter('airlines')"></i>
                    <i v-else class="fa fa-chevron-down fsp-chip-arr"></i>
                </button>

                <button class="fsp-chip" :class="{ active: filters.refundable !== 'all' }" @click="openSheet('fare')">
                    <i class="fa fa-shield-alt"></i>
                    <span>{{ filters.refundable === 'all' ? 'Fare' : (filters.refundable === 'refundable' ? 'Refundable' : 'Non-Refund') }}</span>
                    <i v-if="filters.refundable !== 'all'" class="fa fa-xmark fsp-chip-x" @click.stop="clearFilter('fare')"></i>
                    <i v-else class="fa fa-chevron-down fsp-chip-arr"></i>
                </button>

            </div>
            <div class="fsp-count-pill">
                <span>{{ filteredFlights.length }}</span>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             BACKDROP
        ══════════════════════════════════════ -->
        <div class="fsp-backdrop" :class="{ active: !!activeSheet }" @click="closeSheet"></div>

        <!-- ══════════════════════════════════════
             BOTTOM SHEETS
        ══════════════════════════════════════ -->

        <!-- ALL FILTERS -->
        <div class="fsp-sheet" :class="{ open: activeSheet === 'all' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">All Filters</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body">
                <FilterSidebar :flights="allFlights" :filtered-count="filteredFlights.length"
                               :initial-filters="filters" @filter-change="handleFilterChange" @apply="closeSheet"/>
            </div>
        </div>

        <!-- STOPS -->
        <div class="fsp-sheet fsp-sheet-sm" :class="{ open: activeSheet === 'stops' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">Number of Stops</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body fsp-sh-pad">
                <div class="fsp-stops-grid">
                    <button v-for="s in stopOptions" :key="s.value"
                            class="fsp-stop-card" :class="{ active: filters.stops === s.value }"
                            @click="quickSet('stops', s.value)">
                        <div class="fsp-sc-icon"><i :class="s.icon"></i></div>
                        <div class="fsp-sc-label">{{ s.label }}</div>
                        <div class="fsp-sc-count">{{ stopsCount[s.value] || 0 }} flights</div>
                        <div v-if="filters.stops === s.value" class="fsp-sc-tick"><i class="fa fa-check"></i></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- PRICE -->
        <div class="fsp-sheet fsp-sheet-sm" :class="{ open: activeSheet === 'price' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">Price Range</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body fsp-sh-pad">
                <div class="fsp-price-bubbles">
                    <div class="fsp-price-bubble">
                        <label>Min</label>
                        <div class="fsp-pb-val">৳{{ formatPriceK(tempMin) }}</div>
                    </div>
                    <div class="fsp-pb-sep">–</div>
                    <div class="fsp-price-bubble">
                        <label>Max</label>
                        <div class="fsp-pb-val">৳{{ formatPriceK(tempMax) }}</div>
                    </div>
                </div>

                <div class="fsp-dual-range">
                    <div class="fsp-dr-track">
                        <div class="fsp-dr-fill" :style="{ left: tempLeftPct+'%', width: tempWidthPct+'%' }"></div>
                    </div>
                    <input type="range" class="fsp-range fsp-range-lo"
                           :min="priceMin" :max="priceMax" :step="priceStep"
                           :value="tempMin" @input="onTempMin">
                    <input type="range" class="fsp-range fsp-range-hi"
                           :min="priceMin" :max="priceMax" :step="priceStep"
                           :value="tempMax" @input="onTempMax">
                </div>

                <div class="fsp-presets">
                    <button v-for="p in pricePresets" :key="p.label"
                            class="fsp-preset-btn" :class="{ active: tempMin===p.min && tempMax===p.max }"
                            @click="applyPreset(p)">{{ p.label }}</button>
                </div>
                <div class="fsp-range-labels">
                    <span>৳{{ formatPriceK(priceMin) }}</span>
                    <span>৳{{ formatPriceK(priceMax) }}</span>
                </div>
                <button class="fsp-apply-btn" @click="applyPrice">
                    <i class="fa fa-check"></i> Show {{ filteredWithTempPrice }} flights
                </button>
            </div>
        </div>

        <!-- TIME -->
        <div class="fsp-sheet fsp-sheet-sm" :class="{ open: activeSheet === 'time' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">Departure Time</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body fsp-sh-pad">
                <div class="fsp-time-grid">
                    <button v-for="t in timeSlots" :key="t.value"
                            class="fsp-time-card" :class="{ active: filters.departureTime.includes(t.value) }"
                            @click="toggleTime(t.value)">
                        <i :class="t.icon" class="fsp-tc-icon"></i>
                        <span class="fsp-tc-name">{{ t.label }}</span>
                        <span class="fsp-tc-range">{{ t.range }}</span>
                        <div v-if="filters.departureTime.includes(t.value)" class="fsp-sc-tick"><i class="fa fa-check"></i></div>
                    </button>
                </div>
                <button class="fsp-apply-btn" @click="closeSheet">
                    <i class="fa fa-check"></i> Show {{ filteredFlights.length }} flights
                </button>
            </div>
        </div>

        <!-- AIRLINE -->
        <div class="fsp-sheet fsp-sheet-md" :class="{ open: activeSheet === 'airlines' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">Choose Airline</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body fsp-sh-pad">
                <div class="fsp-airline-grid">
                    <button v-for="a in airlineOptions" :key="a.code"
                            class="fsp-airline-card" :class="{ active: filters.airlines.includes(a.code) }"
                            @click="toggleAirline(a.code)">
                        <img v-if="a.logo" :src="a.logo" :alt="a.name" class="fsp-ac-img">
                        <div v-else class="fsp-ac-fb">{{ a.code.substring(0,2) }}</div>
                        <div class="fsp-ac-name">{{ a.shortName }}</div>
                        <div class="fsp-ac-price">from ৳{{ formatPriceK(a.cheapest) }}</div>
                        <div v-if="filters.airlines.includes(a.code)" class="fsp-sc-tick"><i class="fa fa-check"></i></div>
                    </button>
                </div>
                <button class="fsp-apply-btn" @click="closeSheet">
                    <i class="fa fa-check"></i> Show {{ filteredFlights.length }} flights
                </button>
            </div>
        </div>

        <!-- FARE TYPE -->
        <div class="fsp-sheet fsp-sheet-sm" :class="{ open: activeSheet === 'fare' }">
            <div class="fsp-sh-head">
                <div class="fsp-sh-bar"></div>
                <span class="fsp-sh-title">Fare Type</span>
                <button class="fsp-sh-close" @click="closeSheet"><i class="fa fa-times"></i></button>
            </div>
            <div class="fsp-sh-body fsp-sh-pad">
                <div class="fsp-fare-list">
                    <button v-for="f in fareOptions" :key="f.value"
                            class="fsp-fare-row" :class="[{ active: filters.refundable === f.value }, f.cls]"
                            @click="quickSet('refundable', f.value)">
                        <div class="fsp-fr-icon" :class="f.cls"><i :class="f.icon"></i></div>
                        <div class="fsp-fr-info">
                            <div class="fsp-fr-name">{{ f.label }}</div>
                            <div class="fsp-fr-sub">{{ f.sub }}</div>
                        </div>
                        <div class="fsp-fr-count">{{ getFareCount(f.value) }}</div>
                        <i v-if="filters.refundable === f.value" class="fa fa-check-circle fsp-fr-tick"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             MAIN LAYOUT
        ══════════════════════════════════════ -->
        <div class="container">
            <div class="row">
                <!-- Desktop sidebar -->
                <div class="col-lg-3 fsp-desktop-col">
                    <div class="fsp-dsk-sidebar" :class="{ fixed: sidebarFixed }" ref="desktopSidebar">
                        <FilterSidebar :flights="allFlights" :filtered-count="filteredFlights.length"
                                       :initial-filters="filters" @filter-change="handleFilterChange" @apply="()=>{}"/>
                    </div>
                </div>
                <!-- Results -->
                <!--                <div class="col-lg-9 col-12 fsp-results-col">-->
                <!--                    <div v-if="isLoading" class="fsp-loader">-->
                <!--                        <div class="fsp-li">-->
                <!--                            <svg viewBox="0 0 300 150" width="200" height="100">-->
                <!--                                <defs>-->
                <!--                                    <path id="arc2" d="M 40,120 Q 150,20 260,120"/>-->
                <!--                                    <linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="0%">-->
                <!--                                        <stop offset="0%" style="stop-color:#3b82f6"/>-->
                <!--                                        <stop offset="100%" style="stop-color:#1d4ed8"/>-->
                <!--                                    </linearGradient>-->
                <!--                                </defs>-->
                <!--                                <use href="#arc2" fill="none" stroke="#e2e8f0" stroke-width="3" stroke-dasharray="10,6"/>-->
                <!--                                <circle cx="40" cy="120" r="6" fill="#10b981"/>-->
                <!--                                <circle cx="260" cy="120" r="6" fill="#3b82f6"/>-->
                <!--                                <g><text font-size="26" text-anchor="middle" fill="url(#g2)">✈</text>-->
                <!--                                    <animateMotion dur="3s" repeatCount="indefinite" rotate="auto"><mpath href="#arc2"/></animateMotion>-->
                <!--                                </g>-->
                <!--                            </svg>-->
                <!--                            <p>Filtering<span class="fsp-dots"><span>.</span><span>.</span><span>.</span></span></p>-->
                <!--                        </div>-->
                <!--                    </div>-->
                <!--                    <FlightList v-else :flights="filteredFlights" :airline-chips="airlineChips"-->
                <!--                                :selected-airline="filters.airlines.length===1 ? filters.airlines[0] : ''"-->
                <!--                                :cheapest-price="cheapestPrice" @airline-select="onAirlineSelect"/>-->
                <!--                </div>-->

                <div class="col-lg-9 col-12 fsp-results-col">

                    <!-- Searching animation -->
                    <div v-if="isSearching && allFlights.length === 0" class="fsp-loader">
                        <div class="fsp-li">
                            <svg viewBox="0 0 300 150" width="200" height="100">
                                <defs>
                                    <path id="arc2" d="M 40,120 Q 150,20 260,120"/>
                                    <linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#3b82f6"/>
                                        <stop offset="100%" style="stop-color:#1d4ed8"/>
                                    </linearGradient>
                                </defs>
                                <use href="#arc2" fill="none" stroke="#e2e8f0" stroke-width="3" stroke-dasharray="10,6"/>
                                <circle cx="40" cy="120" r="6" fill="#10b981"/>
                                <circle cx="260" cy="120" r="6" fill="#3b82f6"/>
                                <g>
                                    <text font-size="26" text-anchor="middle" fill="url(#g2)">✈</text>
                                    <animateMotion dur="3s" repeatCount="indefinite" rotate="auto">
                                        <mpath href="#arc2"/>
                                    </animateMotion>
                                </g>
                            </svg>
                            <p>{{ searchMessages[msgIndex] }}<span class="fsp-dots"><span>.</span><span>.</span><span>.</span></span></p>
                            <div class="fsp-prog-wrap">
                                <div class="fsp-prog-bar" :style="{ width: searchProgress + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Flights আসতে থাকলে দেখান -->
                    <template v-if="allFlights.length > 0">
                        <div v-if="isSearching" class="fsp-partial-note">
                            <i class="fa fa-spinner fa-spin"></i>
                            {{ allFlights.length }} flights found, searching more...
                        </div>
                        <div v-if="isLoading" class="fsp-loader">
                            <div class="fsp-li">
                                <p>Filtering<span class="fsp-dots"><span>.</span><span>.</span><span>.</span></span></p>
                            </div>
                        </div>
                        <FlightList
                            v-else
                            :flights="filteredFlights"
                            :airline-chips="airlineChips"
                            :selected-airline="filters.airlines.length===1 ? filters.airlines[0] : ''"
                            :cheapest-price="cheapestPrice"
                            @airline-select="onAirlineSelect"
                        />
                    </template>

                    <!-- Search শেষ কিন্তু কিছু নেই -->
                    <div v-else-if="!isSearching && hasSearched && allFlights.length === 0" class="fsp-loader">
                        <div class="fsp-li">
                            <i class="fa fa-plane-slash" style="font-size:48px;color:#d1d5db;margin-bottom:12px"></i>
                            <p>No flights found</p>
                            <p style="font-size:12px;color:#9ca3af">Try different dates or destinations</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FilterSidebar from './FilterSidebar.vue';
import FlightList    from './FlightList.vue';

export default {
    name: 'FlightSearch',
    components: { FilterSidebar, FlightList },
    props: { flightData: { type: Object, required: true } },
    data() {
        return {
            allFlights:       [],
            isLoading:        false,
            isSearching:      false,
            hasSearched:      false,
            sidebarFixed:     false,
            activeSheet:      null,
            tempMin:          0,
            tempMax:          200000,
            footerElement: null,
            resizeObserver: null,
            searchProgress:   0,
            msgIndex:         0,
            searchRouteLabel: '',
            searchMessages: [
                'Connecting to airlines...',
                'Checking available seats...',
                'Comparing fares...',
                'Finding best deals...',
                'Almost there...',
            ],
            _progressTimer: null,
            _msgTimer:      null,

            filters: { priceRange: [0, 200000], airlines: [], stops: 'all', departureTime: [], refundable: 'all' },
            stopOptions: [
                { value: 'all', label: 'Any',     icon: 'fa fa-globe' },
                { value: '0',   label: 'Direct',  icon: 'fa fa-bolt' },
                { value: '1',   label: '1 Stop',  icon: 'fa fa-dot-circle' },
                { value: '2',   label: '2+ Stops',icon: 'fa fa-ellipsis-h' }
            ],
            timeSlots: [
                { value: 'early_morning', label: 'Early',     range: '12AM–6AM', icon: 'fa fa-moon' },
                { value: 'morning',       label: 'Morning',   range: '6AM–12PM', icon: 'fa fa-cloud-sun' },
                { value: 'afternoon',     label: 'Afternoon', range: '12PM–6PM', icon: 'fa fa-sun' },
                { value: 'evening',       label: 'Evening',   range: '6PM–12AM', icon: 'fa fa-star' }
            ],
            fareOptions: [
                { value: 'all',            label: 'All Fares',      sub: 'Show everything',         icon: 'fa fa-globe',        cls: 'fc-all'   },
                { value: 'refundable',     label: 'Refundable',     sub: 'Cancellation allowed',    icon: 'fa fa-check-circle', cls: 'fc-green' },
                { value: 'non_refundable', label: 'Non-Refundable', sub: 'Lower price, no refund',  icon: 'fa fa-times-circle', cls: 'fc-red'   }
            ]
        };
    },
    computed: {
        priceMin() { const p = this.allFlights.map(f => f.price?.total||0); return p.length ? Math.min(...p) : 0; },
        priceMax() { const p = this.allFlights.map(f => f.price?.total||0); return p.length ? Math.max(...p) : 200000; },
        priceStep() { const r = this.priceMax - this.priceMin; return r > 100000 ? 1000 : r > 10000 ? 500 : 100; },
        tempLeftPct()  { const r = this.priceMax - this.priceMin; return r ? ((this.tempMin - this.priceMin) / r) * 100 : 0; },
        tempWidthPct() { const r = this.priceMax - this.priceMin; return r ? ((this.tempMax - this.tempMin) / r) * 100 : 100; },
        isPriceFiltered() { return this.filters.priceRange[0] > this.priceMin || this.filters.priceRange[1] < this.priceMax; },
        pricePresets() {
            const mn = this.priceMin, mx = this.priceMax;
            return [
                { label: 'Budget',  min: mn, max: Math.round(mn + (mx-mn)*.3)  },
                { label: 'Mid',     min: mn, max: Math.round(mn + (mx-mn)*.6)  },
                { label: 'Premium', min: mn, max: Math.round(mn + (mx-mn)*.85) },
                { label: 'All',     min: mn, max: mx                            }
            ];
        },
        stopsCount() {
            const c = { all: 0, '0': 0, '1': 0, '2': 0 };
            this.allFlights.forEach(f => {
                const m = Math.max(...(f.legs?.map(l=>l.stops)||[0]));
                c.all++; if(m===0)c['0']++; else if(m===1)c['1']++; else c['2']++;
            });
            return c;
        },
        airlineOptions() {
            const map = {};
            this.allFlights.forEach(f => {
                const code = f.validating_carrier;
                if (!map[code]) {
                    const seg = f.legs?.[0]?.segments?.[0];
                    const name = seg?.carrier_name || code;
                    map[code] = { code, name, shortName: name.split(' ').slice(0,2).join(' ').substring(0,14),
                        logo: seg?.carrier_images?.thumb||null, cheapest: f.price?.total||0, count: 0 };
                }
                map[code].count++;
                const p = f.price?.total||0;
                if (p < map[code].cheapest) map[code].cheapest = p;
            });
            return Object.values(map).sort((a,b)=>a.cheapest-b.cheapest);
        },
        airlineChips() { return this.airlineOptions; },
        cheapestPrice() { return this.allFlights.length ? Math.min(...this.allFlights.map(f=>f.price?.total||0)) : 0; },
        activeFilterCount() {
            let n = 0;
            if (this.filters.stops !== 'all') n++;
            if (this.isPriceFiltered) n++;
            if (this.filters.departureTime.length) n++;
            if (this.filters.airlines.length) n++;
            if (this.filters.refundable !== 'all') n++;
            return n;
        },
        filteredFlights() {
            let f = [...this.allFlights];
            f = f.filter(fl => { const p=fl.price?.total||0; return p>=this.filters.priceRange[0] && p<=this.filters.priceRange[1]; });
            if (this.filters.airlines.length) f = f.filter(fl => this.filters.airlines.includes(fl.validating_carrier));
            if (this.filters.stops !== 'all') {
                f = f.filter(fl => { const m=Math.max(...(fl.legs?.map(l=>l.stops)||[0])); const n=parseInt(this.filters.stops); return n===2?m>=2:m===n; });
            }
            if (this.filters.departureTime.length) {
                f = f.filter(fl => {
                    const h = parseInt((fl.legs?.[0]?.departure?.time||'').split(':')[0]);
                    return this.filters.departureTime.some(s => {
                        if(s==='early_morning') return h>=0&&h<6;
                        if(s==='morning')       return h>=6&&h<12;
                        if(s==='afternoon')     return h>=12&&h<18;
                        if(s==='evening')       return h>=18;
                    });
                });
            }
            if (this.filters.refundable==='refundable')     f = f.filter(fl=>fl.refundable===true);
            if (this.filters.refundable==='non_refundable') f = f.filter(fl=>fl.refundable===false);
            return f;
        },
        filteredWithTempPrice() {
            return this.allFlights.filter(fl => { const p=fl.price?.total||0; return p>=this.tempMin && p<=this.tempMax; }).length;
        }
    },
    methods: {
        openSheet(name) {
            if (name === 'price') { this.tempMin = this.filters.priceRange[0]; this.tempMax = this.filters.priceRange[1]; }
            this.activeSheet = name;
            document.body.style.overflow = 'hidden';
        },
        closeSheet() { this.activeSheet = null; document.body.style.overflow = ''; },
        quickSet(key, val) { this.filters[key] = val; this.triggerLoader(); this.closeSheet(); },
        stopLabel(v) { return this.stopOptions.find(s=>s.value===v)?.label||v; },
        clearFilter(key) {
            if (key==='stops')    this.filters.stops = 'all';
            if (key==='price')    this.filters.priceRange = [this.priceMin, this.priceMax];
            if (key==='time')     this.filters.departureTime = [];
            if (key==='airlines') this.filters.airlines = [];
            if (key==='fare')     this.filters.refundable = 'all';
            this.triggerLoader();
        },
        toggleTime(v) {
            const i = this.filters.departureTime.indexOf(v);
            if (i>-1) this.filters.departureTime.splice(i,1); else this.filters.departureTime.push(v);
            this.triggerLoader();
        },
        toggleAirline(code) {
            const i = this.filters.airlines.indexOf(code);
            if (i>-1) this.filters.airlines.splice(i,1); else this.filters.airlines.push(code);
            this.triggerLoader();
        },
        onTempMin(e) { const v=parseInt(e.target.value); this.tempMin = Math.min(v, this.tempMax - this.priceStep); },
        onTempMax(e) { const v=parseInt(e.target.value); this.tempMax = Math.max(v, this.tempMin + this.priceStep); },
        applyPrice() { this.filters.priceRange=[this.tempMin,this.tempMax]; this.triggerLoader(); this.closeSheet(); },
        applyPreset(p) { this.tempMin=p.min; this.tempMax=p.max; },
        formatPriceK(p) { if(!p) return '0'; const v=parseInt(p); return v>=1000?(v/1000).toFixed(v%1000===0?0:1)+'K':v.toString(); },
        getFareCount(v) {
            if (v==='all') return this.allFlights.length;
            if (v==='refundable') return this.allFlights.filter(f=>f.refundable===true).length;
            return this.allFlights.filter(f=>f.refundable===false).length;
        },
        onAirlineSelect(code) {
            if (!code) { this.filters.airlines=[]; }
            else {
                const i=this.filters.airlines.indexOf(code);
                if(i>-1) this.filters.airlines.splice(i,1); else this.filters.airlines=[code];
            }
            this.triggerLoader();
        },
        triggerLoader() { this.isLoading=true; setTimeout(()=>{ this.isLoading=false; },400); },
        handleFilterChange(f) { this.isLoading=true; setTimeout(()=>{ this.filters={...this.filters,...f}; this.isLoading=false; },400); },
        handleScroll() {
            const sidebar = this.$refs.desktopSidebar;
            if (!sidebar) return;

            const col = sidebar.closest('.col-lg-3');
            const footer = document.querySelector('.footer-brand-section');

            if (!col || !footer) return;

            // Desktop only
            if (window.innerWidth < 992) {
                sidebar.removeAttribute('style');
                this.sidebarFixed = false;
                return;
            }

            const scrollTop = window.pageYOffset;
            const sidebarTopGap = 100; // header spacing
            const sidebarHeight = sidebar.offsetHeight;

            // Parent column positions
            const colTop = col.offsetTop;

            // Footer position
            const footerTop = footer.offsetTop;

            // Point where sidebar should stop
            const stopPoint = footerTop - sidebarHeight - sidebarTopGap - 20;

            // Sidebar width
            const colRect = col.getBoundingClientRect();

            // BEFORE sidebar fixed
            if (scrollTop < colTop - sidebarTopGap) {

                sidebar.style.position = '';
                sidebar.style.top = '';
                sidebar.style.left = '';
                sidebar.style.width = '';
                sidebar.style.bottom = '';
                sidebar.style.maxHeight = '';
                sidebar.style.overflowY = '';

                this.sidebarFixed = false;

            }

            // FIXED sidebar
            else if (scrollTop < stopPoint) {

                sidebar.style.position = 'fixed';
                sidebar.style.top = sidebarTopGap + 'px';
                sidebar.style.left = colRect.left + 'px';
                sidebar.style.width = colRect.width + 'px';
                sidebar.style.maxHeight = `calc(100vh - ${sidebarTopGap + 20}px)`;
                sidebar.style.overflowY = 'auto';

                this.sidebarFixed = true;

            }

            // FOOTER reached → absolute
            else {

                sidebar.style.position = 'absolute';
                sidebar.style.top = (stopPoint - colTop + sidebarTopGap) + 'px';
                sidebar.style.left = '15px';
                sidebar.style.width = `calc(100% - 30px)`;
                sidebar.style.maxHeight = `calc(100vh - ${sidebarTopGap + 20}px)`;
                sidebar.style.overflowY = 'auto';

                this.sidebarFixed = false;
            }
        },

        // Handle window resize to recalculate sidebar position
        handleResize() {
            if (!this.sidebarFixed) return;

            const sidebar = this.$refs.desktopSidebar;
            const col = sidebar?.closest?.('.col-lg-3');
            if (col && sidebar) {
                const colRect = col.getBoundingClientRect();
                sidebar.style.left = colRect.left + 'px';
                sidebar.style.width = colRect.width + 'px';
            }
        },

        startSearchAnimation() {
            this.searchProgress = 0;
            this.msgIndex = 0;
            this._progressTimer = setInterval(() => {
                if (this.searchProgress < 90) {
                    const inc = this.searchProgress < 40 ? 2 :
                        this.searchProgress < 70 ? 1 : 0.3;
                    this.searchProgress += inc;
                }
            }, 200);
            this._msgTimer = setInterval(() => {
                this.msgIndex = (this.msgIndex + 1) % this.searchMessages.length;
            }, 2000);
        },

        stopSearchAnimation() {
            clearInterval(this._progressTimer);
            clearInterval(this._msgTimer);
            this.searchProgress = 100;
            // ✅ একসাথে set করুন, delay ছাড়া
            this.isSearching = false;
            this.hasSearched = true;
        },
    },
    mounted() {
        if (window.flightData?.flights) {
            this.allFlights = window.flightData.flights.flights ||
                window.flightData.flights || [];
            const prices = this.allFlights.map(f => f.price?.total || 0);
            if (prices.length) {
                this.filters.priceRange = [Math.min(...prices), Math.max(...prices)];
                this.tempMin = Math.min(...prices);
                this.tempMax = Math.max(...prices);
            }
        }

        // ✅ Check URL for airline_codes parameter
        const urlParams = new URLSearchParams(window.location.search);
        const airlineCode = urlParams.get('airline_codes');
        if (airlineCode) {
            this.filters.airlines = [airlineCode];
        }

        this.$nextTick(() => {
            window.addEventListener('scroll', this.handleScroll);
            window.addEventListener('resize', this.handleResize);
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992 && this.activeSheet) this.closeSheet();
        });

        // ── Search শুরু ──
        window.addEventListener('flight-search-started', (e) => {
            this.allFlights      = [];
            this.isSearching     = true;
            this.hasSearched     = false;
            this.searchRouteLabel = e.detail?.route || '';
            this.startSearchAnimation();
            this.$nextTick(() => {
                document.getElementById('flight-search-app')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // ── একটা একটা flight আসছে ──
        window.addEventListener('flight-search-item', (e) => {
            const flight = e.detail;
            const price  = parseFloat(flight.price?.total || 0);
            const idx    = this.allFlights.findIndex(f =>
                parseFloat(f.price?.total || 0) > price
            );
            if (idx === -1) {
                this.allFlights.push(flight);
            } else {
                this.allFlights.splice(idx, 0, flight);
            }

            const prices = this.allFlights.map(f => f.price?.total || 0);
            this.tempMin = Math.min(...prices);
            this.tempMax = Math.max(...prices);
            this.filters.priceRange = [this.tempMin, this.tempMax];
        });

        // ── সব শেষ ──
        window.addEventListener('flight-search-done', () => {
            this.stopSearchAnimation();
            // this.hasSearched           = true;
            this.filters.airlines      = [];
            this.filters.stops         = 'all';
            this.filters.departureTime = [];
            this.filters.refundable    = 'all';

            const section = document.getElementById('searchFormSection');
            section?.classList.remove('active');
            const chevron = document.getElementById('chevronIcon');
            const btnText = document.querySelector('#searchToggleBtn span');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            if (btnText) btnText.textContent = 'Search Flights';
        });
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.handleScroll);
        window.removeEventListener('resize', this.handleResize);

        // Clean up observer
        if (this.footerObserver) {
            this.footerObserver.disconnect();
        }
        window.removeEventListener('flight-search-results', () => {});
        document.body.style.overflow = '';
    }
};
</script>

<style scoped>
.fsp-wrap {
    --fsp-blue: #1d4ed8; --fsp-accent2: #1e3a8a;
    --fsp-green: #16a34a; --fsp-red: #dc2626; --fsp-orange: #d97706; --fsp-accent: #03c5ff;
    --fsp-border: #e5e7eb; --fsp-bg: #f8fafc; --fsp-text: #111827; --fsp-muted: #6b7280; 
    font-family: 'Segoe UI', system-ui, sans-serif;
    width: 100%; padding-bottom: 24px;
}


.fsp-partial-note {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    color: #1d4ed8;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fsp-prog-wrap {
    width: 200px; height: 4px;
    background: #e5e7eb; border-radius: 2px;
    margin: 8px auto 0; overflow: hidden;
}
.fsp-prog-bar {
    height: 100%; background: #1d4ed8;
    border-radius: 2px; transition: width .2s ease;
}
/* ── CHIPS BAR ── */
.fsp-chips-bar {
    display: none;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--fsp-border);
    position: sticky; top: 0; z-index: 300;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
}
.fsp-chips-scroll {
    display: flex; gap: 6px; overflow-x: auto;
    padding: 10px 12px; scrollbar-width: none; flex: 1;
    -webkit-overflow-scrolling: touch;
}
.fsp-chips-scroll::-webkit-scrollbar { display: none; }

.fsp-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px;
    background: #f3f4f6; border: 1.5px solid var(--fsp-border);
    border-radius: 20px; font-size: 12px; font-weight: 600; color: #374151;
    white-space: nowrap; cursor: pointer; flex-shrink: 0;
    transition: all .18s; -webkit-tap-highlight-color: transparent;
}
.fsp-chip i { font-size: 11px; }
.fsp-chip:active { transform: scale(.95); }
.fsp-chip.active { background: var(--fsp-accent); border-color: var(--fsp-accent); color: #fff; box-shadow: 0 2px 8px rgba(29,78,216,.3); }
.fsp-chip-all { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
.fsp-chip-all.has-filter { background: var(--fsp-green); border-color: var(--fsp-green); }
.fsp-chip-badge { background:#ef4444; color:#fff; font-size:9px; font-weight:800; min-width:16px; height:16px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; padding:0 3px; }
.fsp-chip-x   { opacity:.75; font-size:10px !important; }
.fsp-chip-arr { font-size:9px !important; opacity:.55; }

.fsp-count-pill {
    flex-shrink: 0; padding: 0 12px;
    border-left: 1px solid var(--fsp-border);
    display: flex; align-items: center; height: 100%;
}
.fsp-count-pill span {
    background: #eff6ff; color: var(--fsp-accent);
    font-size: 12px; font-weight: 800;
    padding: 4px 10px; border-radius: 12px; white-space: nowrap;
}

/* ── BACKDROP ── */
.fsp-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 400; opacity: 0; visibility: hidden;
    transition: opacity .3s, visibility .3s; display: none;
}
.fsp-backdrop.active { opacity: 1; visibility: visible; }

/* ── SHEETS ── */
.fsp-sheet {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #fff; border-radius: 20px 20px 0 0;
    z-index: 500; transform: translateY(100%);
    transition: transform .32s cubic-bezier(.32,0,.14,1);
    display: none; flex-direction: column;
    max-height: 88vh; overflow: hidden;
    box-shadow: 0 -8px 40px rgba(0,0,0,.18);
}
.fsp-sheet.open { transform: translateY(0); }
.fsp-sheet-sm { max-height: 74vh; }
.fsp-sheet-md { max-height: 82vh; }

.fsp-sh-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px 10px; border-bottom: 1px solid var(--fsp-border);
    flex-shrink: 0; position: relative;
}
.fsp-sh-bar {
    position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
    width: 36px; height: 4px; background: #d1d5db; border-radius: 2px;
}
.fsp-sh-title { font-size: 15px; font-weight: 700; color: var(--fsp-text); padding-top: 8px; }
.fsp-sh-close {
    width: 30px; height: 30px; background: #f3f4f6; border: none;
    border-radius: 50%; color: #6b7280; font-size: 13px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; margin-top: 8px;
    -webkit-tap-highlight-color: transparent;
}
.fsp-sh-body { flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; }
.fsp-sh-pad  { padding: 16px; }

/* STOPS grid */
.fsp-stops-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 4px; }
.fsp-stop-card {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 18px 8px; border: 2px solid var(--fsp-border);
    border-radius: 14px; background: var(--fsp-bg);
    cursor: pointer; transition: all .2s; position: relative;
    -webkit-tap-highlight-color: transparent;
}
.fsp-stop-card.active { border-color: var(--fsp-accent); background: #eff6ff; box-shadow: 0 0 0 3px rgba(29,78,216,.1); }
.fsp-sc-icon { font-size: 22px; color: var(--fsp-muted); }
.fsp-stop-card.active .fsp-sc-icon { color: var(--fsp-accent); }
.fsp-sc-label { font-size: 13px; font-weight: 700; color: var(--fsp-text); }
.fsp-stop-card.active .fsp-sc-label { color: var(--fsp-accent); }
.fsp-sc-count { font-size: 11px; color: var(--fsp-muted); }
.fsp-sc-tick {
    position: absolute; top: 6px; right: 6px;
    width: 18px; height: 18px; background: var(--fsp-accent);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 9px; color: #fff;
}

/* PRICE range */
.fsp-price-bubbles { display: flex; align-items: center; gap: 10px; justify-content: center; margin-bottom: 20px; }
.fsp-price-bubble { text-align: center; flex: 1; background: var(--fsp-bg); border: 1.5px solid var(--fsp-border); border-radius: 12px; padding: 10px; }
.fsp-price-bubble label { display: block; font-size: 10px; font-weight: 700; color: var(--fsp-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.fsp-pb-val { font-size: 20px; font-weight: 800; color: var(--fsp-accent); }
.fsp-pb-sep { font-size: 20px; color: var(--fsp-muted); }

.fsp-dual-range { position: relative; height: 40px; margin: 4px 0 14px; }
.fsp-dr-track { position: absolute; top: 50%; left: 0; right: 0; height: 6px; background: #e5e7eb; border-radius: 3px; transform: translateY(-50%); }
.fsp-dr-fill  { position: absolute; height: 100%; background: var(--fsp-accent); border-radius: 3px; }
.fsp-range { position: absolute; top: 50%; transform: translateY(-50%); width: 100%; height: 6px; -webkit-appearance: none; background: transparent; outline: none; pointer-events: none; margin: 0; }
.fsp-range::-webkit-slider-thumb { -webkit-appearance: none; width: 26px; height: 26px; border-radius: 50%; background: var(--fsp-accent); border: 3px solid #fff; box-shadow: 0 2px 8px rgba(29,78,216,.35); cursor: pointer; pointer-events: all; transition: transform .15s; }
.fsp-range::-webkit-slider-thumb:active { transform: scale(1.2); }
.fsp-range::-moz-range-thumb { width: 26px; height: 26px; border-radius: 50%; background: var(--fsp-accent); border: 3px solid #fff; box-shadow: 0 2px 8px rgba(29,78,216,.35); cursor: pointer; pointer-events: all; }
.fsp-range-lo { z-index: 3; }
.fsp-range-hi { z-index: 4; }

.fsp-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
.fsp-preset-btn { padding: 5px 12px; border: 1.5px solid var(--fsp-border); border-radius: 20px; background: var(--fsp-bg); font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; color: #374151; }
.fsp-preset-btn.active { border-color: var(--fsp-accent); background: #eff6ff; color: var(--fsp-accent); }
.fsp-range-labels { display: flex; justify-content: space-between; font-size: 10px; color: var(--fsp-muted); margin-bottom: 16px; }

/* TIME grid */
.fsp-time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.fsp-time-card {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    padding: 18px 8px; border: 2px solid var(--fsp-border);
    border-radius: 14px; background: var(--fsp-bg);
    cursor: pointer; transition: all .2s; position: relative;
    -webkit-tap-highlight-color: transparent;
}
.fsp-time-card.active { border-color: var(--fsp-accent); background: #eff6ff; }
.fsp-tc-icon  { font-size: 24px; color: var(--fsp-muted); }
.fsp-tc-name  { font-size: 13px; font-weight: 700; color: var(--fsp-text); }
.fsp-tc-range { font-size: 10px; color: var(--fsp-muted); }
.fsp-time-card.active .fsp-tc-icon { color: var(--fsp-accent); }
.fsp-time-card.active .fsp-tc-name { color: var(--fsp-accent); }

/* AIRLINE grid */
.fsp-airline-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.fsp-airline-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 14px 8px; border: 2px solid var(--fsp-border);
    border-radius: 14px; background: var(--fsp-bg);
    cursor: pointer; transition: all .2s; position: relative; text-align: center;
    -webkit-tap-highlight-color: transparent;
}
.fsp-airline-card.active { border-color: var(--fsp-accent); background: #eff6ff; box-shadow: 0 0 0 3px rgba(29,78,216,.1); }
.fsp-ac-img { width: 48px; height: 32px; object-fit: contain; border-radius: 6px; border: 1px solid var(--fsp-border); background: #fff; padding: 3px; }
.fsp-ac-fb  { width: 48px; height: 32px; background: #e5e7eb; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #374151; }
.fsp-ac-name  { font-size: 12px; font-weight: 700; color: var(--fsp-text); line-height: 1.2; }
.fsp-ac-price { font-size: 11px; font-weight: 700; color: var(--fsp-accent); }

/* FARE list */
.fsp-fare-list { display: flex; flex-direction: column; gap: 10px; }
.fsp-fare-row {
    display: flex; align-items: center; gap: 12px;
    padding: 14px; border: 2px solid var(--fsp-border);
    border-radius: 14px; background: var(--fsp-bg);
    cursor: pointer; transition: all .2s; text-align: left;
    -webkit-tap-highlight-color: transparent;
}
.fsp-fare-row.active.fc-green { border-color: var(--fsp-green); background: #f0fdf4; }
.fsp-fare-row.active.fc-red   { border-color: var(--fsp-red);   background: #fef2f2; }
.fsp-fare-row.active.fc-all   { border-color: var(--fsp-accent);  background: #eff6ff; }
.fsp-fr-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.fc-green .fsp-fr-icon { background: #dcfce7; color: var(--fsp-green); }
.fc-red   .fsp-fr-icon { background: #fee2e2; color: var(--fsp-red);   }
.fc-all   .fsp-fr-icon { background: #eff6ff; color: var(--fsp-accent);  }
.fsp-fr-info { flex: 1; }
.fsp-fr-name  { font-size: 13px; font-weight: 700; color: var(--fsp-text); }
.fsp-fr-sub   { font-size: 11px; color: var(--fsp-muted); margin-top: 2px; }
.fsp-fr-count { font-size: 11px; color: var(--fsp-muted); white-space: nowrap; }
.fsp-fr-tick  { font-size: 18px; color: var(--fsp-accent); flex-shrink: 0; }
.fsp-fare-row.active.fc-green .fsp-fr-tick { color: var(--fsp-green); }
.fsp-fare-row.active.fc-red   .fsp-fr-tick { color: var(--fsp-red);   }

/* Shared apply */
.fsp-apply-btn {
    width: 100%; padding: 14px;
    background: var(--fsp-accent); color: #fff; border: none;
    border-radius: 12px; font-size: 14px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .2s; -webkit-tap-highlight-color: transparent;
}
.fsp-apply-btn:active { background: var(--fsp-accent); transform: scale(.99); }

/* Desktop sidebar */
/* Add smooth transition for sidebar */
.fsp-dsk-sidebar {
    transition: none !important;
}

/* Improved desktop sidebar fixed state */
.fsp-dsk-sidebar.fixed {
    position: fixed;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    z-index: 50;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* Custom scrollbar for better appearance */
.fsp-dsk-sidebar.fixed::-webkit-scrollbar {
    width: 4px;
}

.fsp-dsk-sidebar.fixed::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.fsp-dsk-sidebar.fixed::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.fsp-dsk-sidebar.fixed::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.fsp-desktop-col {
    position: relative;
}

/* Loader */
.fsp-loader { display: flex; align-items: center; justify-content: center; min-height: 240px; background: rgba(255,255,255,.92); border-radius: 12px; border: 1px solid var(--fsp-border); }
.fsp-li { text-align: center; }
.fsp-li p { font-size: 14px; color: #475569; font-weight: 600; margin-top: 8px; }
.fsp-dots span { animation: fsp-blink 1.4s infinite; opacity: 0; font-size: 18px; font-weight: 700; }
.fsp-dots span:nth-child(2) { animation-delay: .2s; }
.fsp-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes fsp-blink { 0%,20%{opacity:0} 50%{opacity:1} 100%{opacity:0} }

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .fsp-chips-bar { display: flex; }
    .fsp-backdrop  { display: block; }
    .fsp-sheet     { display: flex; }
    .fsp-desktop-col { display: none !important; }
    .fsp-results-col { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; padding-bottom: 16px; }
}
@media (min-width: 992px) {
    .fsp-chips-bar   { display: none !important; }
    .fsp-backdrop    { display: none !important; }
    .fsp-sheet       { display: none !important; }
    .fsp-desktop-col { display: block !important; }
}
@media (max-width: 400px) {
    .fsp-stops-grid  { grid-template-columns: 1fr 1fr; }
    .fsp-airline-grid { grid-template-columns: 1fr 1fr; }
    .fsp-chip { font-size: 11px; padding: 6px 10px; }
}
</style>
