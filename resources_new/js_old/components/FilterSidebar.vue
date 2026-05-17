<!--<template>-->
<!--    <div class="sidebar py-20 px-20 rounded-4 bg-white bravo_filter"-->
<!--         style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);">-->

<!--        &lt;!&ndash; Search Timer &ndash;&gt;-->
<!--        <div class="sidebar__item pb-15 -no-border">-->
<!--            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3">-->
<!--                <h5 class="mb-0 text-14 fw-600">Flight Search</h5>-->
<!--                <div class="d-flex align-items-center" :class="getTimerClass()">-->
<!--                    <i class="fa-regular fa-clock me-2"></i>-->
<!--                    <span class="text-14 fw-500">{{ timerDisplay }}</span>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        &lt;!&ndash; Price Filter &ndash;&gt;-->
<!--        <div class="sidebar__item pb-15 g-filter-item">-->
<!--            <h5 class="text-18 fw-500 mb-10">Price Range</h5>-->
<!--            <div class="mb-3">-->
<!--                <span class="text-15 fw-500">৳{{ formatPrice(localPriceRange[0]) }} - ৳{{ formatPrice(localPriceRange[1]) }}</span>-->
<!--            </div>-->
<!--            <input-->
<!--                type="range"-->
<!--                class="form-range"-->
<!--                :min="minPrice"-->
<!--                :max="maxPrice"-->
<!--                v-model.number="localPriceRange[1]"-->
<!--                @input="emitFilters"-->
<!--                style="width: 100%;">-->
<!--        </div>-->

<!--        &lt;!&ndash; Airlines Filter &ndash;&gt;-->
<!--        <div class="sidebar__item g-filter-item" :class="{ 'has-filter': localAirlines.length > 0 }">-->
<!--            <h5 class="text-18 fw-500 mb-10">Airlines</h5>-->
<!--            <div class="sidebar-checkbox">-->
<!--                <div v-if="airlines.length > 0">-->
<!--                    <div-->
<!--                        v-for="airline in airlines"-->
<!--                        :key="airline.code"-->
<!--                        class="row y-gap-10 items-center justify-between mb-10">-->
<!--                        <div class="col-auto">-->
<!--                            <label class="d-flex align-items-center cursor-pointer">-->
<!--                                <div class="form-checkbox d-flex align-items-center">-->
<!--                                    <input-->
<!--                                        type="checkbox"-->
<!--                                        :value="airline.code"-->
<!--                                        v-model="localAirlines"-->
<!--                                        @change="emitFilters"-->
<!--                                        style="display: none;">-->
<!--                                    <div class="form-checkbox__mark">-->
<!--                                        <div class="form-checkbox__icon icon-check"></div>-->
<!--                                    </div>-->
<!--                                    <div class="text-15 ms-2">{{ airline.name }}</div>-->
<!--                                </div>-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <div class="col-auto">-->
<!--                            <div class="text-15 text-secondary">({{ airline.count }})</div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div v-else class="text-center text-secondary py-3">-->
<!--                    <i class="fa fa-info-circle"></i>-->
<!--                    <p class="text-14 mt-2 mb-0">No airlines available</p>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        &lt;!&ndash; Stops Filter &ndash;&gt;-->
<!--        <div class="sidebar__item g-filter-item" :class="{ 'has-filter': localStops !== 'all' }">-->
<!--            <h5 class="text-18 fw-500 mb-10">Number of Stops</h5>-->
<!--            <div class="sidebar-checkbox">-->
<!--                <div class="row y-gap-10 items-center justify-between mb-10">-->
<!--                    <div class="col-12">-->
<!--                        <label class="d-flex align-items-center cursor-pointer">-->
<!--                            <div class="form-checkbox d-flex align-items-center">-->
<!--                                <input type="radio" value="all" v-model="localStops" @change="emitFilters">-->
<!--                                <div class="form-checkbox__mark"><div class="form-checkbox__icon"></div></div>-->
<!--                                <div class="text-15 ms-2 d-flex align-items-center">-->
<!--                                    <span>All Stops</span>-->
<!--                                    <span class="text-secondary ms-auto ps-2">({{ stopsCount.all }})</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div v-if="stopsCount.direct > 0" class="row y-gap-10 items-center justify-between mb-10">-->
<!--                    <div class="col-12">-->
<!--                        <label class="d-flex align-items-center cursor-pointer">-->
<!--                            <div class="form-checkbox d-flex align-items-center">-->
<!--                                <input type="radio" value="0" v-model="localStops" @change="emitFilters">-->
<!--                                <div class="form-checkbox__mark"><div class="form-checkbox__icon"></div></div>-->
<!--                                <div class="text-15 ms-2 d-flex align-items-center">-->
<!--                                    <span>Non-Stop</span>-->
<!--                                    <i class="fa fa-plane text-primary ms-1" style="font-size: 12px;"></i>-->
<!--                                    <span class="text-secondary ms-auto ps-2">({{ stopsCount.direct }})</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div v-if="stopsCount.one_stop > 0" class="row y-gap-10 items-center justify-between mb-10">-->
<!--                    <div class="col-12">-->
<!--                        <label class="d-flex align-items-center cursor-pointer">-->
<!--                            <div class="form-checkbox d-flex align-items-center">-->
<!--                                <input type="radio" value="1" v-model="localStops" @change="emitFilters">-->
<!--                                <div class="form-checkbox__mark"><div class="form-checkbox__icon"></div></div>-->
<!--                                <div class="text-15 ms-2 d-flex align-items-center">-->
<!--                                    <span>1 Stop</span>-->
<!--                                    <i class="fa fa-clock text-warning ms-1" style="font-size: 12px;"></i>-->
<!--                                    <span class="text-secondary ms-auto ps-2">({{ stopsCount.one_stop }})</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div v-if="stopsCount.two_plus > 0" class="row y-gap-10 items-center justify-between mb-10">-->
<!--                    <div class="col-12">-->
<!--                        <label class="d-flex align-items-center cursor-pointer">-->
<!--                            <div class="form-checkbox d-flex align-items-center">-->
<!--                                <input type="radio" value="2" v-model="localStops" @change="emitFilters">-->
<!--                                <div class="form-checkbox__mark"><div class="form-checkbox__icon"></div></div>-->
<!--                                <div class="text-15 ms-2 d-flex align-items-center">-->
<!--                                    <span>2+ Stops</span>-->
<!--                                    <i class="fa fa-route text-info ms-1" style="font-size: 12px;"></i>-->
<!--                                    <span class="text-secondary ms-auto ps-2">({{ stopsCount.two_plus }})</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        &lt;!&ndash; ✅ NEW: Refundable Filter &ndash;&gt;-->
<!--        <div class="sidebar__item g-filter-item" :class="{ 'has-filter': localRefundable !== 'all' }">-->
<!--            <h5 class="text-18 fw-500 mb-10">Fare Type</h5>-->
<!--            <div class="sidebar-checkbox">-->

<!--                &lt;!&ndash; All &ndash;&gt;-->
<!--                <div class="refund-option mb-10" @click="setRefundable('all')">-->
<!--                    <div class="refund-radio-wrap" :class="{ 'selected': localRefundable === 'all' }">-->
<!--                        <div class="refund-radio-dot"></div>-->
<!--                    </div>-->
<!--                    <div class="refund-label-wrap">-->
<!--                        <div class="refund-label-title">All Fares</div>-->
<!--                        <div class="refund-label-sub">Show all flight types</div>-->
<!--                    </div>-->
<!--                    <span class="refund-count">({{ refundableCount.all }})</span>-->
<!--                </div>-->

<!--                &lt;!&ndash; Refundable &ndash;&gt;-->
<!--                <div class="refund-option mb-10" @click="setRefundable('refundable')">-->
<!--                    <div class="refund-radio-wrap refund-green" :class="{ 'selected': localRefundable === 'refundable' }">-->
<!--                        <div class="refund-radio-dot"></div>-->
<!--                    </div>-->
<!--                    <div class="refund-label-wrap">-->
<!--                        <div class="refund-label-title">-->
<!--                            <i class="fa fa-check-circle text-success me-1" style="font-size:13px;"></i>-->
<!--                            Refundable-->
<!--                        </div>-->
<!--                        <div class="refund-label-sub">Cancellation allowed</div>-->
<!--                    </div>-->
<!--                    <span class="refund-count">({{ refundableCount.refundable }})</span>-->
<!--                </div>-->

<!--                &lt;!&ndash; Non-Refundable &ndash;&gt;-->
<!--                <div class="refund-option" @click="setRefundable('non_refundable')">-->
<!--                    <div class="refund-radio-wrap refund-red" :class="{ 'selected': localRefundable === 'non_refundable' }">-->
<!--                        <div class="refund-radio-dot"></div>-->
<!--                    </div>-->
<!--                    <div class="refund-label-wrap">-->
<!--                        <div class="refund-label-title">-->
<!--                            <i class="fa fa-times-circle text-danger me-1" style="font-size:13px;"></i>-->
<!--                            Non-Refundable-->
<!--                        </div>-->
<!--                        <div class="refund-label-sub">Lower fare, no refund</div>-->
<!--                    </div>-->
<!--                    <span class="refund-count">({{ refundableCount.non_refundable }})</span>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        &lt;!&ndash; Departure Time Filter &ndash;&gt;-->
<!--        <div class="sidebar__item g-filter-item" :class="{ 'has-filter': localDepartureTime.length > 0 }">-->
<!--            <h5 class="text-18 fw-500 mb-10">Flight Schedules</h5>-->
<!--            <p class="text-14 text-secondary mb-10">Departure Time</p>-->
<!--            <div class="row g-2">-->
<!--                <div class="col-6">-->
<!--                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"-->
<!--                         :class="{ 'active': isTimeSlotActive('early_morning') }"-->
<!--                         @click="toggleTimeSlot('early_morning')">-->
<!--                        <i class="fa-solid fa-moon text-secondary"></i>-->
<!--                        <p class="text-12 mt-1 mb-0">00-06</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="col-6">-->
<!--                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"-->
<!--                         :class="{ 'active': isTimeSlotActive('morning') }"-->
<!--                         @click="toggleTimeSlot('morning')">-->
<!--                        <i class="fa-solid fa-cloud-sun text-warning"></i>-->
<!--                        <p class="text-12 mt-1 mb-0">06-12</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="col-6">-->
<!--                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"-->
<!--                         :class="{ 'active': isTimeSlotActive('afternoon') }"-->
<!--                         @click="toggleTimeSlot('afternoon')">-->
<!--                        <i class="fa-solid fa-sun text-warning"></i>-->
<!--                        <p class="text-12 mt-1 mb-0">12-18</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="col-6">-->
<!--                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"-->
<!--                         :class="{ 'active': isTimeSlotActive('evening') }"-->
<!--                         @click="toggleTimeSlot('evening')">-->
<!--                        <i class="fa-solid fa-moon text-primary"></i>-->
<!--                        <p class="text-12 mt-1 mb-0">18-00</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->


<!--        &lt;!&ndash; Clear All Filters &ndash;&gt;-->
<!--        <div class="bravo-clear-filter">-->
<!--            <button type="button"-->
<!--                    class="btn btn-danger w-100 d-flex align-items-center justify-content-center"-->
<!--                    @click="resetFilters">-->
<!--                <i class="fa fa-redo me-2"></i>-->
<!--                <span class="fw-500">Clear All Filters</span>-->
<!--            </button>-->
<!--        </div>-->

<!--        &lt;!&ndash; Timer Expired Modal &ndash;&gt;-->
<!--        <div v-if="showTimerModal" class="modal-overlay" @click.self="handleTimerNo">-->
<!--            <div class="modal-dialog">-->
<!--                <div class="modal-content">-->
<!--                    <div class="modal-header">-->
<!--                        <h5 class="modal-title">-->
<!--                            <i class="fa fa-exclamation-triangle text-warning mr-2"></i>-->
<!--                            Search Session Expired-->
<!--                        </h5>-->
<!--                    </div>-->
<!--                    <div class="modal-body">-->
<!--                        <p>Your flight search session has expired after 15 minutes.</p>-->
<!--                        <p>Would you like to refresh and search again?</p>-->
<!--                    </div>-->
<!--                    <div class="modal-footer">-->
<!--                        <button type="button" class="btn btn-secondary" @click="handleTimerNo">-->
<!--                            <i class="fa fa-home mr-1"></i> Go to Home-->
<!--                        </button>-->
<!--                        <button type="button" class="btn btn-primary" @click="handleTimerYes">-->
<!--                            <i class="fa fa-sync mr-1"></i> Refresh Search-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->
<!--export default {-->
<!--    name: 'FilterSidebar',-->
<!--    props: {-->
<!--        flights: { type: Array, required: true }-->
<!--    },-->
<!--    data() {-->
<!--        return {-->
<!--            localPriceRange: [0, 200000],-->
<!--            localAirlines: [],-->
<!--            localStops: 'all',-->
<!--            localDepartureTime: [],-->
<!--            localRefundable: 'all',   // ✅ new-->
<!--            timeLeft: 15 * 60,-->
<!--            timerInterval: null,-->
<!--            showTimerModal: false-->
<!--        }-->
<!--    },-->
<!--    computed: {-->
<!--        minPrice() {-->
<!--            const prices = this.flights.map(f => f.price?.total || 0);-->
<!--            return prices.length > 0 ? Math.min(...prices) : 0;-->
<!--        },-->
<!--        maxPrice() {-->
<!--            const prices = this.flights.map(f => f.price?.total || 0);-->
<!--            return prices.length > 0 ? Math.max(...prices) : 200000;-->
<!--        },-->
<!--        timerDisplay() {-->
<!--            const m = Math.floor(this.timeLeft / 60);-->
<!--            const s = this.timeLeft % 60;-->
<!--            return m + 'm ' + (s < 10 ? '0' : '') + s + 's';-->
<!--        },-->
<!--        airlines() {-->
<!--            const airlineMap = {};-->
<!--            this.flights.forEach(flight => {-->
<!--                const code = flight.validating_carrier;-->
<!--                if (!airlineMap[code]) {-->
<!--                    const seg = flight.legs?.[0]?.segments?.[0];-->
<!--                    airlineMap[code] = {-->
<!--                        code,-->
<!--                        name: seg?.carrier_name || this.getAirlineName(code),-->
<!--                        count: 0-->
<!--                    };-->
<!--                }-->
<!--                airlineMap[code].count++;-->
<!--            });-->
<!--            return Object.values(airlineMap).sort((a, b) => b.count - a.count);-->
<!--        },-->
<!--        stopsCount() {-->
<!--            const counts = { all: 0, direct: 0, one_stop: 0, two_plus: 0 };-->
<!--            this.flights.forEach(flight => {-->
<!--                const legs = flight.legs || [];-->
<!--                const maxStops = Math.max(...legs.map(leg => leg.stops).concat([0]));-->
<!--                counts.all++;-->
<!--                if (maxStops === 0) counts.direct++;-->
<!--                else if (maxStops === 1) counts.one_stop++;-->
<!--                else counts.two_plus++;-->
<!--            });-->
<!--            return counts;-->
<!--        },-->
<!--        // ✅ Refundable counts-->
<!--        refundableCount() {-->
<!--            const counts = { all: 0, refundable: 0, non_refundable: 0 };-->
<!--            this.flights.forEach(f => {-->
<!--                counts.all++;-->
<!--                if (f.refundable === true) counts.refundable++;-->
<!--                else counts.non_refundable++;-->
<!--            });-->
<!--            return counts;-->
<!--        }-->
<!--    },-->
<!--    watch: {-->
<!--        flights: {-->
<!--            handler(newFlights) {-->
<!--                if (newFlights.length > 0) {-->
<!--                    this.localPriceRange = [this.minPrice, this.maxPrice];-->
<!--                }-->
<!--            },-->
<!--            immediate: true-->
<!--        }-->
<!--    },-->
<!--    methods: {-->
<!--        formatPrice(price) {-->
<!--            return parseInt(price).toLocaleString();-->
<!--        },-->
<!--        getAirlineName(code) {-->
<!--            const airlines = {-->
<!--                'BS': 'US-Bangla Airlines', 'BG': 'Biman Bangladesh',-->
<!--                'GF': 'Gulf Air', 'EK': 'Emirates', 'QR': 'Qatar Airways',-->
<!--                'SQ': 'Singapore Airlines', 'TK': 'Turkish Airlines',-->
<!--                'SV': 'Saudi Arabian Airlines', 'AI': 'Air India',-->
<!--                'UL': 'SriLankan Airlines', 'KU': 'Kuwait Airways',-->
<!--                'WY': 'Oman Air', 'FZ': 'Flydubai', 'H9': 'Himalaya Airlines',-->
<!--                'CZ': 'China Southern', 'MH': 'Malaysia Airlines',-->
<!--                'MS': 'EgyptAir', 'ET': 'Ethiopian Airlines'-->
<!--            };-->
<!--            return airlines[code] || code;-->
<!--        },-->
<!--        isTimeSlotActive(slot) {-->
<!--            return this.localDepartureTime.indexOf(slot) > -1;-->
<!--        },-->
<!--        toggleTimeSlot(slot) {-->
<!--            const index = this.localDepartureTime.indexOf(slot);-->
<!--            if (index > -1) this.localDepartureTime.splice(index, 1);-->
<!--            else this.localDepartureTime.push(slot);-->
<!--            this.emitFilters();-->
<!--        },-->
<!--        // ✅ Refundable setter-->
<!--        setRefundable(value) {-->
<!--            this.localRefundable = value;-->
<!--            this.emitFilters();-->
<!--        },-->
<!--        emitFilters() {-->
<!--            this.$emit('filter-change', {-->
<!--                priceRange: this.localPriceRange,-->
<!--                airlines: this.localAirlines,-->
<!--                stops: this.localStops,-->
<!--                departureTime: this.localDepartureTime,-->
<!--                refundable: this.localRefundable   // ✅-->
<!--            });-->
<!--        },-->
<!--        resetFilters() {-->
<!--            this.localPriceRange = [this.minPrice, this.maxPrice];-->
<!--            this.localAirlines = [];-->
<!--            this.localStops = 'all';-->
<!--            this.localDepartureTime = [];-->
<!--            this.localRefundable = 'all';   // ✅-->
<!--            this.emitFilters();-->
<!--        },-->
<!--        getTimerClass() {-->
<!--            if (this.timeLeft <= 60) return 'text-danger';-->
<!--            if (this.timeLeft <= 120) return 'text-warning';-->
<!--            return '';-->
<!--        },-->
<!--        startTimer() {-->
<!--            this.timerInterval = setInterval(() => {-->
<!--                if (this.timeLeft > 0) this.timeLeft&#45;&#45;;-->
<!--                else {-->
<!--                    clearInterval(this.timerInterval);-->
<!--                    this.showTimerModal = true;-->
<!--                }-->
<!--            }, 1000);-->
<!--        },-->
<!--        handleTimerYes() { this.showTimerModal = false; window.location.reload(); },-->
<!--        handleTimerNo() { this.showTimerModal = false; window.location.href = '/'; }-->
<!--    },-->
<!--    mounted() {-->
<!--        this.localPriceRange = [this.minPrice, this.maxPrice];-->
<!--        this.startTimer();-->
<!--    },-->
<!--    beforeDestroy() {-->
<!--        if (this.timerInterval) clearInterval(this.timerInterval);-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->
<!--.has-filter {-->
<!--    border-left: 3px solid #1d4ed8;-->
<!--}-->
<!--.has-filter .text-18 {-->
<!--    color: #1d4ed8;-->
<!--    font-weight: 600;-->
<!--}-->

<!--.time-slot-filter.active {-->
<!--    background-color: #fee2e2 !important;-->
<!--    border-color: #dc2626 !important;-->
<!--    font-weight: 600;-->
<!--}-->

<!--/* ================================-->
<!--   ✅ REFUNDABLE FILTER STYLES-->
<!--   ================================ */-->
<!--.refund-option {-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    gap: 10px;-->
<!--    padding: 10px 12px;-->
<!--    border: 1.5px solid #e5e7eb;-->
<!--    border-radius: 10px;-->
<!--    cursor: pointer;-->
<!--    transition: all 0.2s ease;-->
<!--    background: #fafafa;-->
<!--}-->
<!--.refund-option:hover {-->
<!--    border-color: #93c5fd;-->
<!--    background: #eff6ff;-->
<!--}-->
<!--.refund-option:has(.selected) {-->
<!--    border-color: #2563eb;-->
<!--    background: #eff6ff;-->
<!--}-->

<!--/* Radio circle */-->
<!--.refund-radio-wrap {-->
<!--    width: 20px;-->
<!--    height: 20px;-->
<!--    border-radius: 50%;-->
<!--    border: 2px solid #d1d5db;-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    flex-shrink: 0;-->
<!--    transition: all 0.2s ease;-->
<!--    background: white;-->
<!--}-->
<!--.refund-radio-wrap.refund-green { border-color: #16a34a; }-->
<!--.refund-radio-wrap.refund-red   { border-color: #dc2626; }-->

<!--.refund-radio-wrap.selected {-->
<!--    border-color: #1d4ed8;-->
<!--    background: #1d4ed8;-->
<!--}-->
<!--.refund-radio-wrap.refund-green.selected { border-color: #16a34a; background: #16a34a; }-->
<!--.refund-radio-wrap.refund-red.selected   { border-color: #dc2626; background: #dc2626; }-->

<!--.refund-radio-dot {-->
<!--    width: 8px;-->
<!--    height: 8px;-->
<!--    border-radius: 50%;-->
<!--    background: white;-->
<!--    opacity: 0;-->
<!--    transition: opacity 0.15s ease;-->
<!--}-->
<!--.refund-radio-wrap.selected .refund-radio-dot { opacity: 1; }-->

<!--.refund-label-wrap { flex: 1; }-->
<!--.refund-label-title {-->
<!--    font-size: 13px;-->
<!--    font-weight: 600;-->
<!--    color: #1f2937;-->
<!--    line-height: 1.3;-->
<!--}-->
<!--.refund-label-sub {-->
<!--    font-size: 11px;-->
<!--    color: #6b7280;-->
<!--    margin-top: 1px;-->
<!--}-->

<!--.refund-count {-->
<!--    font-size: 12px;-->
<!--    color: #6b7280;-->
<!--    flex-shrink: 0;-->
<!--}-->

<!--/* selected state — border highlight on the whole option */-->
<!--.refund-option:has(.refund-radio-wrap.selected) {-->
<!--    border-color: #2563eb;-->
<!--    background: #eff6ff;-->
<!--}-->

<!--/* Timer */-->
<!--.text-warning { color: #f59e0b !important; }-->
<!--.text-danger  { color: #ef4444 !important; }-->

<!--/* Timer Modal */-->
<!--.modal-overlay {-->
<!--    position: fixed;-->
<!--    inset: 0;-->
<!--    background: rgba(0,0,0,0.5);-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    z-index: 9999;-->
<!--}-->
<!--.modal-dialog {-->
<!--    background: white;-->
<!--    border-radius: 8px;-->
<!--    max-width: 500px;-->
<!--    width: 90%;-->
<!--    box-shadow: 0 4px 6px rgba(0,0,0,0.1);-->
<!--}-->
<!--.modal-header { padding: 20px; border-bottom: 1px solid #e5e7eb; }-->
<!--.modal-title  { margin: 0; font-size: 18px; font-weight: 600; }-->
<!--.modal-body   { padding: 20px; }-->
<!--.modal-body p { margin-bottom: 10px; }-->
<!--.modal-footer {-->
<!--    padding: 20px;-->
<!--    border-top: 1px solid #e5e7eb;-->
<!--    display: flex;-->
<!--    justify-content: flex-end;-->
<!--    gap: 10px;-->
<!--}-->

<!--.btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; transition: all 0.3s; }-->
<!--.btn-secondary { background: #6c757d; color: white; }-->
<!--.btn-secondary:hover { background: #5a6268; }-->
<!--.btn-primary { background: #3b82f6; color: white; }-->
<!--.btn-primary:hover { background: #2563eb; }-->

<!--/* Checkbox / Radio */-->
<!--.form-checkbox input[type="radio"],-->
<!--.form-checkbox input[type="checkbox"] { display: none; }-->

<!--.form-checkbox input[type="radio"] + .form-checkbox__mark {-->
<!--    border-radius: 50% !important;-->
<!--    width: 18px; height: 18px;-->
<!--    border: 2px solid #ddd;-->
<!--    background: white;-->
<!--    position: relative;-->
<!--}-->
<!--.form-checkbox input[type="radio"] + .form-checkbox__mark .form-checkbox__icon {-->
<!--    border-radius: 50%;-->
<!--    width: 8px; height: 8px;-->
<!--    background: white;-->
<!--    position: absolute;-->
<!--    top: 50%; left: 50%;-->
<!--    transform: translate(-50%,-50%) scale(0);-->
<!--    opacity: 0;-->
<!--    transition: all 0.2s ease;-->
<!--}-->
<!--.form-checkbox input[type="radio"]:checked + .form-checkbox__mark { background: #1d4ed8; border-color: #1d4ed8; }-->
<!--.form-checkbox input[type="radio"]:checked + .form-checkbox__mark .form-checkbox__icon { opacity: 1; transform: translate(-50%,-50%) scale(1); }-->

<!--.form-checkbox input[type="checkbox"] + .form-checkbox__mark {-->
<!--    width: 18px; height: 18px;-->
<!--    border: 2px solid #ddd;-->
<!--    background: white;-->
<!--    border-radius: 4px;-->
<!--    position: relative;-->
<!--}-->
<!--.form-checkbox input[type="checkbox"]:checked + .form-checkbox__mark { background: #1d4ed8; border-color: #1d4ed8; }-->

<!--.bravo-clear-filter {-->
<!--    position: sticky;-->
<!--    bottom: 0;-->
<!--    background: white;-->
<!--    padding: 15px;-->
<!--    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);-->
<!--    z-index: 10;-->
<!--    margin-top: 20px;-->
<!--    border-radius: 8px;-->
<!--}-->
<!--.bravo-clear-filter button:hover {-->
<!--    background-color: #dc2626 !important;-->
<!--    transform: translateY(-2px);-->
<!--    box-shadow: 0 4px 12px rgba(220,38,38,0.3);-->
<!--}-->
<!--</style>-->


<template>
    <div class="fsb-wrap">

        <!-- Header + Timer -->
        <div class="fsb-header">
            <div class="fsb-header-title">
                <i class="fa fa-sliders"></i>
                Filters
                <span v-if="activeFilterCount > 0" class="fsb-badge">{{ activeFilterCount }}</span>
            </div>
            <div class="fsb-timer" :class="timerClass">
                <i class="fa fa-clock"></i>
                {{ timerDisplay }}
            </div>
        </div>

        <!-- Clear all -->
        <div v-if="activeFilterCount > 0" class="fsb-clear-row">
            <button class="fsb-clear-btn" @click="resetFilters">
                <i class="fa fa-rotate-left"></i> Clear All Filters
            </button>
        </div>

        <!-- ══ PRICE RANGE ══ -->
        <div class="fsb-section">
            <div class="fsb-section-head" @click="toggleSection('price')">
                <span><i class="fa fa-tag fsb-icon-blue"></i> Price Range</span>
                <i class="fa" :class="sections.price ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
            <div v-show="sections.price" class="fsb-section-body">

                <!-- Min / Max display -->
                <div class="fsb-price-row">
                    <div class="fsb-price-box">
                        <label>Min</label>
                        <div class="fsb-price-val">৳{{ formatPriceK(localPriceRange[0]) }}</div>
                    </div>
                    <div class="fsb-price-sep">—</div>
                    <div class="fsb-price-box">
                        <label>Max</label>
                        <div class="fsb-price-val">৳{{ formatPriceK(localPriceRange[1]) }}</div>
                    </div>
                </div>

                <!-- Dual range slider -->
                <div class="fsb-dual-range">
                    <div class="fsb-dr-track">
                        <div class="fsb-dr-fill"
                             :style="{ left: priceLeftPct + '%', width: priceWidthPct + '%' }">
                        </div>
                    </div>
                    <input type="range" class="fsb-range fsb-range-lo"
                           :min="minPrice" :max="maxPrice" :step="priceStep"
                           :value="localPriceRange[0]"
                           @input="onMinInput" @change="emitFilters">
                    <input type="range" class="fsb-range fsb-range-hi"
                           :min="minPrice" :max="maxPrice" :step="priceStep"
                           :value="localPriceRange[1]"
                           @input="onMaxInput" @change="emitFilters">
                </div>

                <!-- Quick preset buttons -->
                <div class="fsb-presets">
                    <button v-for="p in pricePresets" :key="p.label"
                            class="fsb-preset"
                            :class="{ active: isPresetActive(p) }"
                            @click="applyPreset(p)">
                        {{ p.label }}
                    </button>
                </div>

                <div class="fsb-range-labels">
                    <span>৳{{ formatPriceK(minPrice) }}</span>
                    <span>৳{{ formatPriceK(maxPrice) }}</span>
                </div>
            </div>
        </div>

        <!-- ══ STOPS ══ -->
        <div class="fsb-section" :class="{ 'fsb-active': localStops !== 'all' }">
            <div class="fsb-section-head" @click="toggleSection('stops')">
                <span><i class="fa fa-map-pin fsb-icon-blue"></i> Stops</span>
                <div class="fsb-head-right">
                    <span v-if="localStops !== 'all'" class="fsb-active-tag">{{ stopLabel }}</span>
                    <i class="fa" :class="sections.stops ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </div>
            <div v-show="sections.stops" class="fsb-section-body">
                <div class="fsb-stop-grid">
                    <button v-for="s in stopOptions" :key="s.value"
                            class="fsb-stop-card"
                            :class="{ active: localStops === s.value }"
                            @click="setStop(s.value)">
                        <i :class="s.icon" class="fsb-sc-icon"></i>
                        <span class="fsb-sc-label">{{ s.label }}</span>
                        <span class="fsb-sc-count">{{ getStopCount(s.value) }}</span>
                        <div v-if="localStops === s.value" class="fsb-sc-tick"><i class="fa fa-check"></i></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ DEPARTURE TIME ══ -->
        <div class="fsb-section" :class="{ 'fsb-active': localDepartureTime.length > 0 }">
            <div class="fsb-section-head" @click="toggleSection('time')">
                <span><i class="fa fa-clock fsb-icon-blue"></i> Departure Time</span>
                <div class="fsb-head-right">
                    <span v-if="localDepartureTime.length" class="fsb-active-tag">{{ localDepartureTime.length }} selected</span>
                    <i class="fa" :class="sections.time ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </div>
            <div v-show="sections.time" class="fsb-section-body">
                <div class="fsb-time-grid">
                    <button v-for="t in timeSlots" :key="t.value"
                            class="fsb-time-card"
                            :class="{ active: isTimeActive(t.value) }"
                            @click="toggleTime(t.value)">
                        <i :class="t.icon" class="fsb-tc-icon"></i>
                        <span class="fsb-tc-label">{{ t.label }}</span>
                        <span class="fsb-tc-range">{{ t.range }}</span>
                        <div v-if="isTimeActive(t.value)" class="fsb-sc-tick"><i class="fa fa-check"></i></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ FARE TYPE ══ -->
        <div class="fsb-section" :class="{ 'fsb-active': localRefundable !== 'all' }">
            <div class="fsb-section-head" @click="toggleSection('fare')">
                <span><i class="fa fa-shield-alt fsb-icon-blue"></i> Fare Type</span>
                <div class="fsb-head-right">
                    <span v-if="localRefundable !== 'all'" class="fsb-active-tag">{{ fareLabel }}</span>
                    <i class="fa" :class="sections.fare ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </div>
            <div v-show="sections.fare" class="fsb-section-body">
                <div class="fsb-fare-list">
                    <button v-for="f in fareOptions" :key="f.value"
                            class="fsb-fare-row"
                            :class="[{ active: localRefundable === f.value }, f.cls]"
                            @click="setFare(f.value)">
                        <div class="fsb-fr-icon" :class="f.cls"><i :class="f.icon"></i></div>
                        <div class="fsb-fr-info">
                            <div class="fsb-fr-name">{{ f.label }}</div>
                            <div class="fsb-fr-sub">{{ f.sub }}</div>
                        </div>
                        <span class="fsb-count">{{ getFareCount(f.value) }}</span>
                        <i v-if="localRefundable === f.value" class="fa fa-check-circle fsb-fr-tick"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ AIRLINES ══ -->
        <div class="fsb-section" :class="{ 'fsb-active': localAirlines.length > 0 }">
            <div class="fsb-section-head" @click="toggleSection('airlines')">
                <span><i class="fa fa-plane fsb-icon-blue"></i> Airlines</span>
                <div class="fsb-head-right">
                    <span v-if="localAirlines.length" class="fsb-active-tag">{{ localAirlines.length }} selected</span>
                    <i class="fa" :class="sections.airlines ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </div>
            <div v-show="sections.airlines" class="fsb-section-body">
                <div v-if="airlines.length === 0" class="fsb-empty">No airlines available</div>
                <div v-else class="fsb-airline-list">
                    <label v-for="a in airlines" :key="a.code"
                           class="fsb-airline-row"
                           :class="{ active: localAirlines.includes(a.code) }">
                        <div class="fsb-checkbox" :class="{ checked: localAirlines.includes(a.code) }">
                            <i v-if="localAirlines.includes(a.code)" class="fa fa-check"></i>
                        </div>
                        <input type="checkbox" :value="a.code" v-model="localAirlines" @change="emitFilters" style="display:none">
                        <span class="fsb-airline-name">{{ a.name }}</span>
                        <span class="fsb-count">{{ a.count }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Apply button -->
        <div class="fsb-apply-row">
            <button class="fsb-apply-btn" @click="$emit('apply')">
                <i class="fa fa-check"></i>
                Show {{ filteredCount }} Flights
            </button>
        </div>

        <!-- Timer expired modal -->
        <div v-if="showTimerModal" class="fsb-timer-modal" @click.self="handleTimerNo">
            <div class="fsb-timer-dialog">
                <div class="fsb-td-icon"><i class="fa fa-clock"></i></div>
                <h3>Session Expired</h3>
                <p>Your 15-minute search session has expired. Search again for fresh results.</p>
                <div class="fsb-td-btns">
                    <button @click="handleTimerNo" class="fsb-td-sec">Go Home</button>
                    <button @click="handleTimerYes" class="fsb-td-pri">Refresh</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: 'FilterSidebar',
    props: {
        flights:        { type: Array,  required: true },
        filteredCount:  { type: Number, default: 0 },
        initialFilters: { type: Object, default: null }
    },
    emits: ['filter-change', 'apply'],

    data() {
        return {
            localPriceRange:    [0, 200000],
            localAirlines:      [],
            localStops:         'all',
            localDepartureTime: [],
            localRefundable:    'all',

            sections: { price: true, stops: true, time: true, fare: true, airlines: false },

            timeLeft:       15 * 60,
            timerInterval:  null,
            showTimerModal: false,

            stopOptions: [
                { value: 'all', label: 'Any',      icon: 'fa fa-globe' },
                { value: '0',   label: 'Direct',   icon: 'fa fa-bolt' },
                { value: '1',   label: '1 Stop',   icon: 'fa fa-dot-circle' },
                { value: '2',   label: '2+ Stops', icon: 'fa fa-ellipsis-h' }
            ],
            timeSlots: [
                { value: 'early_morning', label: 'Early',     range: '00–06', icon: 'fa fa-moon' },
                { value: 'morning',       label: 'Morning',   range: '06–12', icon: 'fa fa-cloud-sun' },
                { value: 'afternoon',     label: 'Afternoon', range: '12–18', icon: 'fa fa-sun' },
                { value: 'evening',       label: 'Evening',   range: '18–00', icon: 'fa fa-star' }
            ],
            fareOptions: [
                { value: 'all',            label: 'All Fares',      sub: 'Show everything',       icon: 'fa fa-globe',        cls: 'fare-all'   },
                { value: 'refundable',     label: 'Refundable',     sub: 'Cancellation allowed',  icon: 'fa fa-check-circle', cls: 'fare-ref'   },
                { value: 'non_refundable', label: 'Non-Refundable', sub: 'Lower price, no refund',icon: 'fa fa-times-circle', cls: 'fare-noref' }
            ]
        };
    },

    computed: {
        minPrice() {
            const p = this.flights.map(f => f.price?.total || 0);
            return p.length ? Math.min(...p) : 0;
        },
        maxPrice() {
            const p = this.flights.map(f => f.price?.total || 0);
            return p.length ? Math.max(...p) : 200000;
        },
        priceStep() {
            const r = this.maxPrice - this.minPrice;
            return r > 100000 ? 1000 : r > 10000 ? 500 : 100;
        },
        priceLeftPct() {
            const r = this.maxPrice - this.minPrice;
            return r ? ((this.localPriceRange[0] - this.minPrice) / r) * 100 : 0;
        },
        priceWidthPct() {
            const r = this.maxPrice - this.minPrice;
            return r ? ((this.localPriceRange[1] - this.localPriceRange[0]) / r) * 100 : 100;
        },
        pricePresets() {
            const mn = this.minPrice, mx = this.maxPrice;
            return [
                { label: 'Budget',  min: mn, max: Math.round(mn + (mx - mn) * .3)  },
                { label: 'Mid',     min: mn, max: Math.round(mn + (mx - mn) * .6)  },
                { label: 'Premium', min: mn, max: Math.round(mn + (mx - mn) * .85) },
                { label: 'All',     min: mn, max: mx }
            ];
        },
        timerDisplay() {
            const m = Math.floor(this.timeLeft / 60), s = this.timeLeft % 60;
            return m + ':' + (s < 10 ? '0' : '') + s;
        },
        timerClass() {
            if (this.timeLeft <= 60)  return 'danger';
            if (this.timeLeft <= 120) return 'warn';
            return '';
        },
        airlines() {
            const map = {};
            this.flights.forEach(f => {
                const code = f.validating_carrier;
                if (!map[code]) {
                    const seg = f.legs?.[0]?.segments?.[0];
                    map[code] = { code, name: seg?.carrier_name || code, count: 0 };
                }
                map[code].count++;
            });
            return Object.values(map).sort((a, b) => b.count - a.count);
        },
        stopsCount() {
            const c = { all: 0, '0': 0, '1': 0, '2': 0 };
            this.flights.forEach(f => {
                const max = Math.max(...(f.legs?.map(l => l.stops) || [0]));
                c.all++;
                if (max === 0) c['0']++;
                else if (max === 1) c['1']++;
                else c['2']++;
            });
            return c;
        },
        refundableCount() {
            const c = { all: 0, refundable: 0, non_refundable: 0 };
            this.flights.forEach(f => {
                c.all++;
                if (f.refundable) c.refundable++;
                else c.non_refundable++;
            });
            return c;
        },
        activeFilterCount() {
            let n = 0;
            if (this.localStops !== 'all') n++;
            if (this.localDepartureTime.length) n++;
            if (this.localRefundable !== 'all') n++;
            if (this.localAirlines.length) n++;
            if (this.localPriceRange[0] > this.minPrice || this.localPriceRange[1] < this.maxPrice) n++;
            return n;
        },
        stopLabel() { return this.stopOptions.find(s => s.value === this.localStops)?.label || ''; },
        fareLabel() { return this.fareOptions.find(f => f.value === this.localRefundable)?.label || ''; }
    },

    watch: {
        flights: {
            handler() {
                this.localPriceRange = [this.minPrice, this.maxPrice];
            },
            immediate: true
        },
        // sync from parent (e.g. when chip clears a filter)
        initialFilters: {
            handler(v) {
                if (!v) return;
                if (v.stops         !== undefined) this.localStops         = v.stops;
                if (v.priceRange    !== undefined) this.localPriceRange    = [...v.priceRange];
                if (v.departureTime !== undefined) this.localDepartureTime = [...v.departureTime];
                if (v.airlines      !== undefined) this.localAirlines      = [...v.airlines];
                if (v.refundable    !== undefined) this.localRefundable    = v.refundable;
            },
            deep: true
        }
    },

    methods: {
        toggleSection(k) { this.sections[k] = !this.sections[k]; },

        // ── Price ──
        formatPriceK(p) {
            if (!p) return '0';
            const v = parseInt(p);
            return v >= 1000 ? (v / 1000).toFixed(v % 1000 === 0 ? 0 : 1) + 'K' : v.toString();
        },
        onMinInput(e) {
            const v = parseInt(e.target.value);
            this.$set(this.localPriceRange, 0, Math.min(v, this.localPriceRange[1] - this.priceStep));
        },
        onMaxInput(e) {
            const v = parseInt(e.target.value);
            this.$set(this.localPriceRange, 1, Math.max(v, this.localPriceRange[0] + this.priceStep));
        },
        isPresetActive(p) {
            return this.localPriceRange[0] === p.min && this.localPriceRange[1] === p.max;
        },
        applyPreset(p) {
            this.$set(this.localPriceRange, 0, p.min);
            this.$set(this.localPriceRange, 1, p.max);
            this.emitFilters();
        },

        // ── Stops / Time / Fare ──
        setStop(v)  { this.localStops = v; this.emitFilters(); },
        setFare(v)  { this.localRefundable = v; this.emitFilters(); },
        isTimeActive(v) { return this.localDepartureTime.includes(v); },
        toggleTime(v) {
            const i = this.localDepartureTime.indexOf(v);
            if (i > -1) this.localDepartureTime.splice(i, 1);
            else this.localDepartureTime.push(v);
            this.emitFilters();
        },
        getStopCount(v) { return this.stopsCount[v] || 0; },
        getFareCount(v) {
            if (v === 'all') return this.refundableCount.all;
            return this.refundableCount[v] || 0;
        },

        // ── Emit ──
        emitFilters() {
            this.$emit('filter-change', {
                priceRange:    [...this.localPriceRange],
                airlines:      [...this.localAirlines],
                stops:         this.localStops,
                departureTime: [...this.localDepartureTime],
                refundable:    this.localRefundable
            });
        },

        resetFilters() {
            this.localPriceRange    = [this.minPrice, this.maxPrice];
            this.localAirlines      = [];
            this.localStops         = 'all';
            this.localDepartureTime = [];
            this.localRefundable    = 'all';
            this.emitFilters();
        },

        // ── Timer ──
        startTimer() {
            this.timerInterval = setInterval(() => {
                if (this.timeLeft > 0) this.timeLeft--;
                else { clearInterval(this.timerInterval); this.showTimerModal = true; }
            }, 1000);
        },
        handleTimerYes() { this.showTimerModal = false; window.location.reload(); },
        handleTimerNo()  { this.showTimerModal = false; window.location.href = '/'; }
    },

    mounted() {
        this.localPriceRange = [this.minPrice, this.maxPrice];
        this.startTimer();
    },
    beforeDestroy() { clearInterval(this.timerInterval); }
};
</script>

<style scoped>
/* ═══════════════════════════════
   TOKENS
═══════════════════════════════ */
.fsb-wrap {
    --fsb-blue:   #1d4ed8;
    --fsb-blue2:  #1e3a8a;
    --fsb-green:  #16a34a;
    --fsb-red:    #dc2626;
    --fsb-orange: #d97706;
    --fsb-border: #e5e7eb;
    --fsb-bg:     #f9fafb;
    --fsb-text:   #111827;
    --fsb-muted:  #6b7280;
    --fsb-radius: 12px;

    background: #fff;
    border-radius: var(--fsb-radius);
    border: 1px solid var(--fsb-border);
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 14px;
    color: var(--fsb-text);
    position: relative;
    /* ✅ NO overflow:hidden — clear filter & sticky apply both need to be visible */
}

/* ── Header ── */
.fsb-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px;
    background: var(--fsb-blue2);
    color: #fff;
    border-radius: var(--fsb-radius) var(--fsb-radius) 0 0;
}
.fsb-header-title { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; }
.fsb-badge { background: #fff; color: var(--fsb-blue); font-size: 11px; font-weight: 800; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.fsb-timer { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; background: rgba(255,255,255,.15); padding: 4px 10px; border-radius: 20px; }
.fsb-timer.warn   { background: #fef3c7; color: #92400e; }
.fsb-timer.danger { background: #fee2e2; color: var(--fsb-red); animation: fsb-pulse .8s ease-in-out infinite; }
@keyframes fsb-pulse { 0%,100%{opacity:1} 50%{opacity:.6} }

/* ── Clear row ── */
.fsb-clear-row { padding: 8px 16px; background: #fff7ed; border-bottom: 1px solid #fed7aa; }
.fsb-clear-btn {
    width: 100%; padding: 7px;
    background: transparent; border: 1.5px solid var(--fsb-orange); color: var(--fsb-orange);
    border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px; transition: all .2s;
}
.fsb-clear-btn:hover { background: var(--fsb-orange); color: #fff; }

/* ── Section ── */
.fsb-section { border-bottom: 1px solid var(--fsb-border); }
.fsb-section.fsb-active > .fsb-section-head { border-left: 3px solid var(--fsb-blue); }
.fsb-section-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; cursor: pointer; font-weight: 600; font-size: 13px;
    transition: background .15s; user-select: none;
}
.fsb-section-head:hover { background: var(--fsb-bg); }
.fsb-section-head > span { display: flex; align-items: center; gap: 8px; }
.fsb-icon-blue  { color: var(--fsb-blue); font-size: 13px; }
.fsb-head-right { display: flex; align-items: center; gap: 8px; }
.fsb-active-tag { font-size: 10px; font-weight: 700; background: #eff6ff; color: var(--fsb-blue); padding: 2px 8px; border-radius: 10px; }
.fsb-section-body { padding: 0 16px 14px; }

/* ═══════════════════════════════
   PRICE RANGE — dual slider
═══════════════════════════════ */
.fsb-price-row {
    display: flex; align-items: center; gap: 8px;
    justify-content: space-between; margin-bottom: 16px;
}
.fsb-price-box {
    flex: 1; text-align: center;
    background: var(--fsb-bg); border: 1.5px solid var(--fsb-border);
    border-radius: 10px; padding: 8px 6px;
}
.fsb-price-box label {
    display: block; font-size: 10px; font-weight: 700; color: var(--fsb-muted);
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px;
}
.fsb-price-val  { font-size: 16px; font-weight: 800; color: var(--fsb-blue); }
.fsb-price-sep  { font-size: 16px; color: var(--fsb-muted); flex-shrink: 0; }

/* Dual range track */
.fsb-dual-range {
    position: relative; height: 36px; margin: 0 0 12px;
}
.fsb-dr-track {
    position: absolute; top: 50%; left: 0; right: 0;
    height: 6px; background: #e5e7eb; border-radius: 3px;
    transform: translateY(-50%); pointer-events: none;
}
.fsb-dr-fill {
    position: absolute; height: 100%;
    background: var(--fsb-blue); border-radius: 3px;
    transition: left .04s, width .04s;
}
/* Both inputs overlap exactly */
.fsb-range {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 100%; height: 6px;
    -webkit-appearance: none; background: transparent;
    outline: none; pointer-events: none; margin: 0; padding: 0;
}
.fsb-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--fsb-blue);
    border: 3px solid #fff;
    box-shadow: 0 2px 6px rgba(29,78,216,.35);
    cursor: pointer; pointer-events: all;
    transition: transform .15s;
}
.fsb-range::-webkit-slider-thumb:active { transform: scale(1.2); }
.fsb-range::-moz-range-thumb {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--fsb-blue); border: 3px solid #fff;
    box-shadow: 0 2px 6px rgba(29,78,216,.35);
    cursor: pointer; pointer-events: all;
}
.fsb-range-lo { z-index: 3; }
.fsb-range-hi { z-index: 4; }

/* Presets */
.fsb-presets { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
.fsb-preset {
    padding: 4px 10px; border: 1.5px solid var(--fsb-border);
    border-radius: 16px; background: var(--fsb-bg);
    font-size: 11px; font-weight: 600; color: #374151;
    cursor: pointer; transition: all .15s;
}
.fsb-preset.active { border-color: var(--fsb-blue); background: #eff6ff; color: var(--fsb-blue); }
.fsb-preset:hover:not(.active) { border-color: #93c5fd; }
.fsb-range-labels { display: flex; justify-content: space-between; font-size: 10px; color: var(--fsb-muted); }

/* ═══════════════════════════════
   STOPS — card grid
═══════════════════════════════ */
.fsb-stop-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
.fsb-stop-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 12px 6px;
    border: 2px solid var(--fsb-border); border-radius: 12px;
    background: var(--fsb-bg); cursor: pointer;
    transition: all .18s; position: relative;
    -webkit-tap-highlight-color: transparent;
}
.fsb-stop-card:hover:not(.active) { border-color: #93c5fd; background: #f0f9ff; }
.fsb-stop-card.active {
    border-color: var(--fsb-blue); background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(29,78,216,.1);
}
.fsb-sc-icon  { font-size: 18px; color: var(--fsb-muted); }
.fsb-stop-card.active .fsb-sc-icon { color: var(--fsb-blue); }
.fsb-sc-label { font-size: 12px; font-weight: 700; color: var(--fsb-text); }
.fsb-stop-card.active .fsb-sc-label { color: var(--fsb-blue); }
.fsb-sc-count { font-size: 10px; color: var(--fsb-muted); }
.fsb-sc-tick  {
    position: absolute; top: 5px; right: 5px;
    width: 16px; height: 16px; border-radius: 50%;
    background: var(--fsb-blue); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 8px;
}

/* ═══════════════════════════════
   TIME — card grid
═══════════════════════════════ */
.fsb-time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
.fsb-time-card {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 12px 6px;
    border: 2px solid var(--fsb-border); border-radius: 12px;
    background: var(--fsb-bg); cursor: pointer;
    transition: all .18s; position: relative;
    -webkit-tap-highlight-color: transparent;
}
.fsb-time-card:hover:not(.active) { border-color: #93c5fd; }
.fsb-time-card.active { border-color: var(--fsb-blue); background: #eff6ff; box-shadow: 0 0 0 3px rgba(29,78,216,.1); }
.fsb-tc-icon  { font-size: 18px; color: var(--fsb-muted); }
.fsb-time-card.active .fsb-tc-icon { color: var(--fsb-blue); }
.fsb-tc-label { font-size: 12px; font-weight: 700; color: var(--fsb-text); }
.fsb-time-card.active .fsb-tc-label { color: var(--fsb-blue); }
.fsb-tc-range { font-size: 10px; color: var(--fsb-muted); }

/* ═══════════════════════════════
   FARE TYPE
═══════════════════════════════ */
.fsb-fare-list { display: flex; flex-direction: column; gap: 7px; }
.fsb-fare-row {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 12px;
    border: 2px solid var(--fsb-border); border-radius: 12px;
    background: var(--fsb-bg); cursor: pointer;
    transition: all .18s; text-align: left;
}
.fsb-fare-row:hover:not(.active) { border-color: #93c5fd; background: #f0f9ff; }
.fsb-fare-row.active.fare-ref    { border-color: var(--fsb-green); background: #f0fdf4; }
.fsb-fare-row.active.fare-noref  { border-color: var(--fsb-red);   background: #fef2f2; }
.fsb-fare-row.active.fare-all    { border-color: var(--fsb-blue);  background: #eff6ff; }
.fsb-fr-icon {
    width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.fare-all  .fsb-fr-icon { background: #eff6ff; color: var(--fsb-blue);  }
.fare-ref  .fsb-fr-icon { background: #dcfce7; color: var(--fsb-green); }
.fare-noref .fsb-fr-icon { background: #fee2e2; color: var(--fsb-red);  }
.fsb-fr-info  { flex: 1; }
.fsb-fr-name  { font-size: 12px; font-weight: 700; color: var(--fsb-text); }
.fsb-fr-sub   { font-size: 10px; color: var(--fsb-muted); margin-top: 1px; }
.fsb-fr-tick  { font-size: 16px; color: var(--fsb-blue); flex-shrink: 0; }
.fsb-fare-row.active.fare-ref  .fsb-fr-tick { color: var(--fsb-green); }
.fsb-fare-row.active.fare-noref .fsb-fr-tick { color: var(--fsb-red); }

/* ═══════════════════════════════
   AIRLINES
═══════════════════════════════ */
.fsb-empty { padding: 12px 0; text-align: center; font-size: 12px; color: var(--fsb-muted); }
.fsb-airline-list { display: flex; flex-direction: column; gap: 3px; max-height: 220px; overflow-y: auto; }
.fsb-airline-list::-webkit-scrollbar { width: 3px; }
.fsb-airline-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.fsb-airline-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 8px; cursor: pointer;
    transition: background .15s; border: 1.5px solid transparent;
}
.fsb-airline-row:hover  { background: var(--fsb-bg); }
.fsb-airline-row.active { background: #eff6ff; border-color: #bfdbfe; }
.fsb-checkbox {
    width: 18px; height: 18px; border-radius: 4px; border: 2px solid #d1d5db;
    background: #fff; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .15s; font-size: 10px; color: #fff;
}
.fsb-checkbox.checked { background: var(--fsb-blue); border-color: var(--fsb-blue); }
.fsb-airline-name { flex: 1; font-size: 12px; font-weight: 500; }
.fsb-count {
    font-size: 11px; color: var(--fsb-muted);
    background: #f3f4f6; padding: 1px 6px; border-radius: 10px; font-weight: 600;
}

/* ═══════════════════════════════
   APPLY BUTTON — sticky bottom
   z-index: 20 ensures it stays above
   flight list sort bar (z-index: 200
   is for the sort bar itself relative
   to viewport, not this component)
═══════════════════════════════ */
.fsb-apply-row {
    padding: 12px 16px;
    background: #fff;
    border-top: 1px solid var(--fsb-border);
    position: sticky;
    bottom: 0;
    z-index: 20;
    border-radius: 0 0 var(--fsb-radius) var(--fsb-radius);
    box-shadow: 0 -3px 10px rgba(0,0,0,.07);
}
.fsb-apply-btn {
    width: 100%; padding: 11px;
    background: var(--fsb-blue); color: #fff;
    border: none; border-radius: 10px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: background .2s;
}
.fsb-apply-btn:hover { background: var(--fsb-blue2); }

/* ═══════════════════════════════
   TIMER MODAL
═══════════════════════════════ */
.fsb-timer-modal {
    position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 16px;
}
.fsb-timer-dialog {
    background: #fff; border-radius: 16px; padding: 24px 20px;
    max-width: 340px; width: 100%; text-align: center;
    box-shadow: 0 16px 40px rgba(0,0,0,.2);
}
.fsb-td-icon { font-size: 40px; color: var(--fsb-orange); margin-bottom: 12px; }
.fsb-timer-dialog h3 { font-size: 18px; font-weight: 700; margin: 0 0 8px; }
.fsb-timer-dialog p  { font-size: 13px; color: var(--fsb-muted); margin: 0 0 18px; }
.fsb-td-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.fsb-td-sec { padding: 10px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; }
.fsb-td-pri { padding: 10px; background: var(--fsb-blue); color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
.fsb-td-pri:hover { background: var(--fsb-blue2); }
</style>
