<!--<template>-->
<!--    <div>-->
<!--        &lt;!&ndash; ① Sort bar — সবার উপরে &ndash;&gt;-->
<!--        <div class="bg-white rounded-lg shadow-sm p-4 mb-3 sort-header-bar">-->
<!--            <div class="flex items-center justify-between">-->
<!--                <div class="text-sm text-gray-600">-->
<!--                    <span class="font-semibold text-gray-800">{{ flights.length }}</span>-->
<!--                    flights found-->
<!--                </div>-->
<!--                <div class="flex items-center space-x-2" style="position:relative;z-index:200;">-->
<!--                    <span class="text-sm text-gray-600">Sort by:</span>-->
<!--                    <select-->
<!--                        v-model="sortBy"-->
<!--                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"-->
<!--                        style="position:relative;z-index:200;">-->
<!--                        <option value="recommended">Recommended</option>-->
<!--                        <option value="cheapest">Cheapest First</option>-->
<!--                        <option value="fastest">Fastest First</option>-->
<!--                        <option value="earliest">Earliest Departure</option>-->
<!--                        <option value="latest">Latest Departure</option>-->
<!--                    </select>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        &lt;!&ndash; ② Airline chips bar — sort bar এর ঠিক নিচে &ndash;&gt;-->
<!--        <div class="airline-chips-bar mb-4" v-if="airlineChips && airlineChips.length > 0">-->
<!--            <button-->
<!--                v-show="showLeftArrow"-->
<!--                class="chips-arrow chips-arrow-left"-->
<!--                @click="scrollChips('left')"-->
<!--                aria-label="Scroll left">-->
<!--                <i class="fas fa-chevron-left"></i>-->
<!--            </button>-->

<!--            <div class="chips-scroll-wrapper" ref="chipsScroll" @scroll="updateArrows">-->
<!--                &lt;!&ndash; All chip &ndash;&gt;-->
<!--                <div-->
<!--                    class="airline-chip"-->
<!--                    :class="{ 'active': !selectedAirline }"-->
<!--                    @click="selectAirline('')">-->
<!--                    <div class="chip-logo all-logo">-->
<!--                        <i class="fas fa-globe"></i>-->
<!--                    </div>-->
<!--                    <span class="chip-name">All</span>-->
<!--                    <span class="chip-price">{{ formatChipPrice(cheapestPrice) }}</span>-->
<!--                </div>-->

<!--                &lt;!&ndash; Per-airline chips &ndash;&gt;-->
<!--                <div-->
<!--                    v-for="airline in airlineChips"-->
<!--                    :key="'chip-' + airline.code"-->
<!--                    class="airline-chip"-->
<!--                    :class="{ 'active': selectedAirline === airline.code }"-->
<!--                    @click="selectAirline(airline.code)">-->
<!--                    <img-->
<!--                        v-if="airline.logo"-->
<!--                        :src="airline.logo"-->
<!--                        :alt="airline.name"-->
<!--                        class="chip-logo-img">-->
<!--                    <div v-else class="chip-logo fallback-logo">-->
<!--                        {{ airline.code.substring(0,2) }}-->
<!--                    </div>-->
<!--                    <span class="chip-name">{{ airline.shortName }}</span>-->
<!--                    <span class="chip-price">{{ formatChipPrice(airline.cheapest) }}</span>-->
<!--                </div>-->
<!--            </div>-->

<!--            <button-->
<!--                v-show="showRightArrow"-->
<!--                class="chips-arrow chips-arrow-right"-->
<!--                @click="scrollChips('right')"-->
<!--                aria-label="Scroll right">-->
<!--                <i class="fas fa-chevron-right"></i>-->
<!--            </button>-->
<!--        </div>-->

<!--        &lt;!&ndash; ③ Flight Cards &ndash;&gt;-->
<!--        <div v-if="sortedFlights.length > 0" class="space-y-4">-->
<!--            <FlightCard-->
<!--                v-for="flight in sortedFlights"-->
<!--                :key="flight.id"-->
<!--                :flight="flight"-->
<!--            />-->
<!--        </div>-->

<!--        &lt;!&ndash; No Results &ndash;&gt;-->
<!--        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">-->
<!--            <div class="text-gray-400 mb-4">-->
<!--                <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
<!--                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01"></path>-->
<!--                </svg>-->
<!--            </div>-->
<!--            <h3 class="text-xl font-semibold text-gray-700 mb-2">No flights found</h3>-->
<!--            <p class="text-gray-500">Try adjusting your filters to see more results</p>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->
<!--import FlightCard from './FlightCard.vue';-->

<!--export default {-->
<!--    name: 'FlightList',-->
<!--    components: { FlightCard },-->
<!--    props: {-->
<!--        flights:         { type: Array,  required: true },-->
<!--        airlineChips:    { type: Array,  default: () => [] },-->
<!--        selectedAirline: { type: String, default: '' },-->
<!--        cheapestPrice:   { type: Number, default: 0 },-->
<!--    },-->
<!--    data() {-->
<!--        return {-->
<!--            sortBy: 'cheapest',-->
<!--            showLeftArrow: false,-->
<!--            showRightArrow: false,-->
<!--        }-->
<!--    },-->
<!--    computed: {-->
<!--        sortedFlights() {-->
<!--            let flights = [...this.flights];-->
<!--            switch(this.sortBy) {-->
<!--                case 'recommended':-->
<!--                    return flights.sort((a, b) => {-->
<!--                        const sA = ((a.price?.total) || 0) / 1000 + ((a.legs?.[0]?.duration) || 0) / 60;-->
<!--                        const sB = ((b.price?.total) || 0) / 1000 + ((b.legs?.[0]?.duration) || 0) / 60;-->
<!--                        return sA - sB;-->
<!--                    });-->
<!--                case 'fastest':-->
<!--                    return flights.sort((a, b) =>-->
<!--                        ((a.legs?.[0]?.duration) || 0) - ((b.legs?.[0]?.duration) || 0)-->
<!--                    );-->
<!--                case 'earliest':-->
<!--                    return flights.sort((a, b) => {-->
<!--                        const tA = a.legs?.[0]?.departure?.time || '';-->
<!--                        const tB = b.legs?.[0]?.departure?.time || '';-->
<!--                        return tA.localeCompare(tB);-->
<!--                    });-->
<!--                case 'latest':-->
<!--                    return flights.sort((a, b) => {-->
<!--                        const tA = a.legs?.[0]?.departure?.time || '';-->
<!--                        const tB = b.legs?.[0]?.departure?.time || '';-->
<!--                        return tB.localeCompare(tA);-->
<!--                    });-->
<!--                case 'cheapest':-->
<!--                default:-->
<!--                    return flights.sort((a, b) =>-->
<!--                        (parseFloat(a.price?.total) || 0) - (parseFloat(b.price?.total) || 0)-->
<!--                    );-->
<!--            }-->
<!--        }-->
<!--    },-->
<!--    methods: {-->
<!--        selectAirline(code) {-->
<!--            this.$emit('airline-select', code);-->
<!--        },-->
<!--        formatChipPrice(price) {-->
<!--            if (!price) return '0';-->
<!--            const p = parseInt(price);-->
<!--            if (p >= 1000) return Math.round(p / 1000) + 'K';-->
<!--            return p.toString();-->
<!--        },-->
<!--        updateArrows() {-->
<!--            this.$nextTick(() => {-->
<!--                const el = this.$refs.chipsScroll;-->
<!--                if (!el) return;-->
<!--                // ✅ scrollWidth সঠিক পেতে layout settle হওয়া দরকার-->
<!--                this.showLeftArrow  = el.scrollLeft > 8;-->
<!--                this.showRightArrow = el.scrollWidth > el.clientWidth + 8;-->
<!--            });-->
<!--        },-->
<!--        scrollChips(dir) {-->
<!--            const el = this.$refs.chipsScroll;-->
<!--            if (!el) return;-->
<!--            el.scrollBy({ left: dir === 'right' ? 260 : -260, behavior: 'smooth' });-->
<!--            setTimeout(() => this.updateArrows(), 350);-->
<!--        },-->
<!--    },-->
<!--    mounted() {-->
<!--        // ✅ Multiple delays — DOM + layout + images load-->
<!--        this.$nextTick(() => this.updateArrows());-->
<!--        setTimeout(() => this.updateArrows(), 100);-->
<!--        setTimeout(() => this.updateArrows(), 500);-->
<!--        setTimeout(() => this.updateArrows(), 1000);-->

<!--        // ✅ ResizeObserver — container size বদলালে re-check-->
<!--        if (window.ResizeObserver) {-->
<!--            this._resizeObs = new ResizeObserver(() => this.updateArrows());-->
<!--            this.$nextTick(() => {-->
<!--                if (this.$refs.chipsScroll) {-->
<!--                    this._resizeObs.observe(this.$refs.chipsScroll);-->
<!--                }-->
<!--            });-->
<!--        }-->
<!--    },-->
<!--    beforeDestroy() {-->
<!--        if (this._resizeObs) this._resizeObs.disconnect();-->
<!--    },-->
<!--    watch: {-->
<!--        airlineChips: {-->
<!--            handler() {-->
<!--                this.$nextTick(() => this.updateArrows());-->
<!--                setTimeout(() => this.updateArrows(), 200);-->
<!--            },-->
<!--            immediate: true-->
<!--        }-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->
<!--/* Sort bar */-->
<!--.sort-header-bar {-->
<!--    position: sticky;-->
<!--    top: 0;-->
<!--    z-index: 150;-->
<!--    background: white;-->
<!--    overflow: visible !important;-->
<!--}-->
<!--.sort-header-bar select {-->
<!--    position: relative;-->
<!--    z-index: 200;-->
<!--}-->

<!--/* ================================-->
<!--   AIRLINE CHIPS BAR-->
<!--   ================================ */-->
<!--.airline-chips-bar {-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    background: #ffffff;-->
<!--    border: 1px solid #e2e8f0;-->
<!--    border-radius: 12px;-->
<!--    box-shadow: 0 1px 6px rgba(0,0,0,0.06);-->
<!--    overflow: hidden;-->
<!--    position: relative;  /* ✅ z-index কাজ করার জন্য */-->
<!--    z-index: 10;         /* ✅ form collapsed div এর উপরে */-->
<!--}-->

<!--/* Scroll arrows */-->
<!--.chips-arrow {-->
<!--    flex-shrink: 0;-->
<!--    width: 36px;-->
<!--    align-self: stretch;-->
<!--    background: white;-->
<!--    border: none;-->
<!--    cursor: pointer;-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    font-size: 13px;-->
<!--    color: #4b5563;-->
<!--    transition: all 0.2s ease;-->
<!--}-->
<!--.chips-arrow-left  { border-right: 1px solid #e5e7eb; }-->
<!--.chips-arrow-right { border-left:  1px solid #e5e7eb; }-->
<!--.chips-arrow:hover { background: #eff6ff; color: #2563eb; }-->

<!--/* Scroll wrapper */-->
<!--.chips-scroll-wrapper {-->
<!--    display: flex;-->
<!--    gap: 8px;-->
<!--    overflow-x: auto;-->
<!--    padding: 10px 12px;-->
<!--    scrollbar-width: none;-->
<!--    -ms-overflow-style: none;-->
<!--    flex: 1;-->
<!--    -webkit-overflow-scrolling: touch;-->
<!--}-->
<!--.chips-scroll-wrapper::-webkit-scrollbar { display: none; }-->

<!--/* Chip */-->
<!--.airline-chip {-->
<!--    display: flex;-->
<!--    flex-direction: column;-->
<!--    align-items: center;-->
<!--    gap: 3px;-->
<!--    padding: 7px 11px 6px;-->
<!--    background: #f9fafb;-->
<!--    border: 1.5px solid #e5e7eb;-->
<!--    border-radius: 12px;-->
<!--    cursor: pointer;-->
<!--    transition: all 0.2s ease;-->
<!--    flex-shrink: 0;-->
<!--    min-width: 74px;-->
<!--    max-width: 94px;-->
<!--    user-select: none;-->
<!--}-->
<!--.airline-chip:hover {-->
<!--    border-color: #3b82f6;-->
<!--    background: #eff6ff;-->
<!--    transform: translateY(-1px);-->
<!--    box-shadow: 0 2px 8px rgba(59,130,246,0.15);-->
<!--}-->
<!--.airline-chip.active {-->
<!--    border-color: #2563eb;-->
<!--    border-width: 2px;-->
<!--    background: #dbeafe;-->
<!--    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);-->
<!--    transform: translateY(-1px);-->
<!--}-->

<!--/* Logo */-->
<!--.chip-logo {-->
<!--    width: 44px;-->
<!--    height: 30px;-->
<!--    border-radius: 6px;-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    font-size: 17px;-->
<!--    overflow: hidden;-->
<!--    flex-shrink: 0;-->
<!--}-->
<!--.all-logo     { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }-->
<!--.fallback-logo { background: #e5e7eb; color: #374151; font-size: 11px; font-weight: 700; }-->
<!--.chip-logo-img {-->
<!--    width: 44px;-->
<!--    height: 30px;-->
<!--    object-fit: contain;-->
<!--    border-radius: 5px;-->
<!--    border: 0.5px solid #e5e7eb;-->
<!--    padding: 2px;-->
<!--    background: white;-->
<!--    display: block;-->
<!--}-->
<!--.chip-name {-->
<!--    font-size: 9px;-->
<!--    color: #374151;-->
<!--    font-weight: 500;-->
<!--    text-align: center;-->
<!--    overflow: hidden;-->
<!--    text-overflow: ellipsis;-->
<!--    white-space: nowrap;-->
<!--    width: 100%;-->
<!--}-->
<!--.chip-price {-->
<!--    font-size: 11px;-->
<!--    font-weight: 700;-->
<!--    color: #1d4ed8;-->
<!--    background: #eff6ff;-->
<!--    padding: 1px 6px;-->
<!--    border-radius: 20px;-->
<!--    white-space: nowrap;-->
<!--}-->
<!--.airline-chip.active .chip-name  { color: #1e40af; font-weight: 600; }-->
<!--.airline-chip.active .chip-price { background: #2563eb; color: white; }-->

<!--/* Mobile */-->
<!--@media (max-width: 768px) {-->
<!--    .airline-chips-bar { border-radius: 10px; }-->
<!--    .chips-arrow { width: 28px; font-size: 11px; }-->
<!--    .chips-scroll-wrapper { padding: 7px 8px; gap: 6px; }-->
<!--    .airline-chip { min-width: 62px; padding: 5px 7px; }-->
<!--    .chip-logo, .chip-logo-img { width: 36px; height: 26px; }-->
<!--    .chip-name  { font-size: 8px; }-->
<!--    .chip-price { font-size: 10px; padding: 1px 5px; }-->
<!--}-->
<!--</style>-->

<template>
    <div class="fl-wrap">

        <!-- Sort + count bar -->
        <div class="fl-sort-bar">
            <div class="fl-count">
                <strong>{{ flights.length }}</strong>
                <span> flights</span>
            </div>
            <div class="fl-sort-select">
                <i class="fa fa-sort"></i>
                <select v-model="sortBy">
                    <option value="cheapest">Cheapest</option>
                    <option value="fastest">Fastest</option>
                    <option value="earliest">Earliest</option>
                    <option value="latest">Latest</option>
                    <option value="recommended">Best</option>
                </select>
            </div>
        </div>

        <!-- Airline chips -->
        <div v-if="airlineChips && airlineChips.length" class="fl-chips-bar">
            <button v-show="showLeft" class="fl-chips-arrow left" @click="scrollChips('left')">
                <i class="fa fa-chevron-left"></i>
            </button>
            <div class="fl-chips-scroll" ref="chipsScroll" @scroll="updateArrows">
                <!-- All -->
                <div class="fl-chip" :class="{ active: !selectedAirline }" @click="selectAirline('')">
                    <div class="fl-chip-logo all"><i class="fa fa-globe"></i></div>
                    <span class="fl-chip-name">All</span>
                    <span class="fl-chip-price">{{ fmt(cheapestPrice) }}</span>
                </div>
                <!-- Per airline -->
                <div
                    v-for="a in airlineChips" :key="a.code"
                    class="fl-chip" :class="{ active: selectedAirline === a.code }"
                    @click="selectAirline(a.code)">
                    <img v-if="a.logo" :src="a.logo" :alt="a.name" class="fl-chip-logo-img">
                    <div v-else class="fl-chip-logo fallback">{{ a.code.substring(0,2) }}</div>
                    <span class="fl-chip-name">{{ a.shortName }}</span>
                    <span class="fl-chip-price">{{ fmt(a.cheapest) }}</span>
                </div>
            </div>
            <button v-show="showRight" class="fl-chips-arrow right" @click="scrollChips('right')">
                <i class="fa fa-chevron-right"></i>
            </button>
        </div>

        <!-- Flight cards -->
        <div v-if="sortedFlights.length" class="fl-list">
            <FlightCard v-for="(f, fi) in sortedFlights" :key="'fl-' + fi + '-' + (f.id || fi)" :flight="f" />
        </div>

        <!-- Empty state -->
        <div v-else class="fl-empty">
            <div class="fl-empty-icon"><i class="fa fa-plane-slash"></i></div>
            <h3>No flights found</h3>
            <p>Try adjusting your filters</p>
        </div>
    </div>
</template>

<script>
import FlightCard from './FlightCard.vue';
export default {
    name: 'FlightList',
    components: { FlightCard },
    props: {
        flights:         { type: Array,  required: true },
        airlineChips:    { type: Array,  default: () => [] },
        selectedAirline: { type: String, default: '' },
        cheapestPrice:   { type: Number, default: 0 }
    },
    data() { return { sortBy: 'cheapest', showLeft: false, showRight: false }; },
    computed: {
        sortedFlights() {
            const f = [...this.flights];
            switch (this.sortBy) {
                case 'fastest':   return f.sort((a,b) => (a.legs?.[0]?.duration||0)-(b.legs?.[0]?.duration||0));
                case 'earliest':  return f.sort((a,b) => (a.legs?.[0]?.departure?.time||'').localeCompare(b.legs?.[0]?.departure?.time||''));
                case 'latest':    return f.sort((a,b) => (b.legs?.[0]?.departure?.time||'').localeCompare(a.legs?.[0]?.departure?.time||''));
                case 'recommended': return f.sort((a,b) => { const sa=(a.price?.total||0)/1000+(a.legs?.[0]?.duration||0)/60, sb=(b.price?.total||0)/1000+(b.legs?.[0]?.duration||0)/60; return sa-sb; });
                default: return f.sort((a,b) => (parseFloat(a.price?.total)||0)-(parseFloat(b.price?.total)||0));
            }
        }
    },
    methods: {
        selectAirline(code) { this.$emit('airline-select', code); },
        fmt(p) { if (!p) return '0'; const v=parseInt(p); return v>=1000 ? Math.round(v/1000)+'K' : v.toString(); },
        updateArrows() {
            this.$nextTick(() => {
                const el = this.$refs.chipsScroll;
                if (!el) return;
                this.showLeft  = el.scrollLeft > 8;
                this.showRight = el.scrollWidth > el.clientWidth + 8;
            });
        },
        scrollChips(dir) {
            const el = this.$refs.chipsScroll;
            if (!el) return;
            el.scrollBy({ left: dir === 'right' ? 240 : -240, behavior: 'smooth' });
            setTimeout(() => this.updateArrows(), 350);
        }
    },
    mounted() {
        this.$nextTick(() => this.updateArrows());
        [100, 500, 1000].forEach(t => setTimeout(() => this.updateArrows(), t));
        if (window.ResizeObserver) {
            this._ro = new ResizeObserver(() => this.updateArrows());
            this.$nextTick(() => { if (this.$refs.chipsScroll) this._ro.observe(this.$refs.chipsScroll); });
        }
    },
    beforeDestroy() { this._ro?.disconnect(); },
    watch: {
        airlineChips() { this.$nextTick(() => this.updateArrows()); setTimeout(() => this.updateArrows(), 200); }
    }
};
</script>

<style scoped>
.fl-wrap {
    --fl-blue:   #1d4ed8;
    --fl-border: #e5e7eb;
    --fl-bg:     #f9fafb;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* Sort bar */
.fl-sort-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border: 1px solid var(--fl-border);
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 10px;
    position: sticky;
    top: 0;
    z-index: 200;   /* ✅ raised above form section overflow */
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.fl-count strong { font-size: 15px; font-weight: 800; color: var(--fl-blue); }
.fl-count span   { font-size: 13px; color: #6b7280; }
.fl-sort-select {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #374151;
}
.fl-sort-select i { color: var(--fl-blue); }
.fl-sort-select select {
    border: 1.5px solid var(--fl-border);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 600;
    outline: none;
    background: var(--fl-bg);
    cursor: pointer;
    color: #111827;
}

/* Chips */
.fl-chips-bar {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid var(--fl-border);
    border-radius: 10px;
    margin-bottom: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    position: relative;
    z-index: 199;   /* ✅ below sort bar, above form collapse overlay */
}
.fl-chips-arrow {
    flex-shrink: 0;
    width: 32px;
    align-self: stretch;
    background: #fff;
    border: none;
    cursor: pointer;
    font-size: 12px;
    color: #4b5563;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.fl-chips-arrow.left  { border-right: 1px solid var(--fl-border); }
.fl-chips-arrow.right { border-left:  1px solid var(--fl-border); }
.fl-chips-arrow:hover { background: #eff6ff; color: var(--fl-blue); }

.fl-chips-scroll {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding: 8px 10px;
    scrollbar-width: none;
    flex: 1;
    -webkit-overflow-scrolling: touch;
}
.fl-chips-scroll::-webkit-scrollbar { display: none; }

.fl-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    padding: 6px 8px;
    border: 1.5px solid var(--fl-border);
    border-radius: 10px;
    cursor: pointer;
    flex-shrink: 0;
    min-width: 68px;
    transition: all .2s;
    background: #fafafa;
    user-select: none;
}
.fl-chip:hover { border-color: var(--fl-blue); background: #eff6ff; transform: translateY(-1px); }
.fl-chip.active { border-color: var(--fl-blue); border-width: 2px; background: #dbeafe; box-shadow: 0 0 0 2px rgba(29,78,216,.15); }

.fl-chip-logo {
    width: 40px; height: 28px;
    border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
}
.fl-chip-logo.all      { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
.fl-chip-logo.fallback { background: #e5e7eb; color: #374151; font-size: 11px; font-weight: 700; }
.fl-chip-logo-img { width: 40px; height: 28px; object-fit: contain; border-radius: 5px; border: 1px solid var(--fl-border); padding: 2px; background: #fff; }
.fl-chip-name  { font-size: 10px; color: #374151; font-weight: 500; text-align: center; max-width: 70px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fl-chip-price { font-size: 11px; font-weight: 700; color: var(--fl-blue); background: #eff6ff; padding: 1px 5px; border-radius: 10px; white-space: nowrap; }
.fl-chip.active .fl-chip-price { background: var(--fl-blue); color: #fff; }

/* List */
.fl-list { display: flex; flex-direction: column; gap: 10px; }

/* Empty */
.fl-empty {
    background: #fff;
    border: 1px solid var(--fl-border);
    border-radius: 12px;
    padding: 48px 20px;
    text-align: center;
}
.fl-empty-icon { font-size: 48px; color: #d1d5db; margin-bottom: 12px; }
.fl-empty h3 { font-size: 16px; font-weight: 700; color: #374151; margin: 0 0 6px; }
.fl-empty p  { font-size: 13px; color: #6b7280; margin: 0; }

/* Responsive */
@media (max-width: 480px) {
    .fl-chip     { min-width: 60px; padding: 5px 6px; }
    .fl-chip-logo, .fl-chip-logo-img { width: 34px; height: 24px; }
    .fl-sort-bar { padding: 8px 12px; }
}
</style>
