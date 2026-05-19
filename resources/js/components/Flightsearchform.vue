<template>
    <div class="fsf-wrap">

        <!-- ══════════════════════════════════
             TRIP TYPE TABS
        ══════════════════════════════════ -->
        <div class="fsf-tabs">
            <button
                v-for="t in tripTypes" :key="t.value"
                type="button"
                class="fsf-tab"
                :class="{ active: tripType === t.value }"
                @click="changeTripType(t.value)">
                <i :class="t.icon"></i>
                <span>{{ t.label }}</span>
            </button>
        </div>

        <!-- ══════════════════════════════════
             MAIN FORM
        ══════════════════════════════════ -->
        <form class="fsf-body" @submit.prevent="handleSearch">

            <!-- ── Segments ── -->
            <div class="fsf-segments">
                <div
                    v-for="(seg, idx) in segments"
                    :key="'seg-' + idx"
                    class="fsf-segment">

                    <!-- Multi-city badge -->
                    <div v-if="tripType === 'multi'" class="fsf-route-badge">
                        <span>Flight {{ idx + 1 }}</span>
                    </div>

                    <!-- Row: airports + dates -->
                    <div class="fsf-row">

                        <!-- FROM -->
                        <!-- ✅ card পুরোটায় click করলে input focus হবে -->
                        <div class="fsf-field fsf-from"
                             :class="{ focused: activeSearch === idx + '-from' }"
                             @click="focusField('from', idx)">
                            <label @click.prevent @mousedown.prevent>
                                <i class="fa fa-plane-departure"></i> From
                            </label>
                            <input
                                type="text"
                                :ref="'from_' + idx"
                                :value="activeSearch === idx + '-from' ? seg.from_search : seg.from_display"
                                @focus="handleFromFocus(idx)"
                                @input="handleFromInput($event, idx)"
                                @blur="handleFromBlur(idx)"
                                placeholder="City or airport"
                                autocomplete="off"
                                class="fsf-input" />
                            <!--                            <span v-if="seg.from_display && activeSearch !== idx + '-from'" class="fsf-code">-->
                            <!--                                {{ seg.from_display.split(' - ')[0] }}-->
                            <!--                            </span>-->

                            <!-- Dropdown -->
                            <div v-if="activeSearch === idx + '-from'" class="fsf-dropdown">
                                <airport-list
                                    :loading="loading"
                                    :results="searchResults[idx + '_from']"
                                    :recents="recentFromAirports"
                                    :show-search="!!seg.from_search"
                                    @select="selectAirport(idx, 'from', $event)" />
                            </div>
                        </div>

                        <!-- SWAP -->
                        <button type="button" class="fsf-swap" @click="swap(idx)" title="Swap">
                            <i class="fa fa-arrow-right-arrow-left"></i>
                        </button>

                        <!-- TO -->
                        <div class="fsf-field fsf-to"
                             :class="{ focused: activeSearch === idx + '-to' }"
                             @click="focusField('to', idx)">
                            <label @click.prevent @mousedown.prevent>
                                <i class="fa fa-plane-arrival"></i> To
                            </label>
                            <input
                                type="text"
                                :ref="'to_' + idx"
                                :value="activeSearch === idx + '-to' ? seg.to_search : seg.to_display"
                                @focus="handleToFocus(idx)"
                                @input="handleToInput($event, idx)"
                                @blur="handleToBlur(idx)"
                                placeholder="City or airport"
                                autocomplete="off"
                                class="fsf-input" />
                            <!--                            <span v-if="seg.to_display && activeSearch !== idx + '-to'" class="fsf-code">-->
                            <!--                                {{ seg.to_display.split(' - ')[0] }}-->
                            <!--                            </span>-->

                            <!-- Dropdown -->
                            <div v-if="activeSearch === idx + '-to'" class="fsf-dropdown">
                                <airport-list
                                    :loading="loading"
                                    :results="searchResults[idx + '_to']"
                                    :recents="recentToAirports"
                                    :show-search="!!seg.to_search"
                                    @select="selectAirport(idx, 'to', $event)" />
                            </div>
                        </div>

                        <!-- DEPARTURE DATE -->
                        <div class="fsf-field fsf-date fsf-date-wrap"
                             :class="{ 'fsf-date-open': pickerOpen && pickerTarget && pickerTarget.type === 'departure' && pickerTarget.idx === idx }"
                             @click.stop="openPicker(idx, 'departure')">
                            <label @click.prevent @mousedown.prevent>
                                <i class="fa fa-calendar-days"></i> Departure
                            </label>
                            <div class="fsf-date-val" v-if="seg.departure">{{ formatDisplayDate(seg.departure) }}</div>
                            <div class="fsf-date-ph" v-else>Select date</div>

                            <!-- inline dropdown picker -->
                            <transition name="fsf-drop">
                                <div v-if="pickerOpen && pickerTarget && pickerTarget.type === 'departure' && pickerTarget.idx === idx"
                                     class="fsf-cal-drop"
                                     @click.stop>
                                    <div class="fsf-cal-inner">
                                        <div class="fsf-cal-nav">
                                            <button type="button" class="fsf-nav-btn" @click.stop="prevMonth"><i class="fa fa-chevron-left"></i></button>
                                            <div class="fsf-cal-my">
                                                <select v-model.number="pickerMonth" @change="clampDay" class="fsf-picker-sel" @click.stop>
                                                    <option v-for="(m, mi) in months" :key="mi" :value="mi">{{ m }}</option>
                                                </select>
                                                <select v-model.number="pickerYear" @change="clampDay" class="fsf-picker-sel" @click.stop>
                                                    <option v-for="y in pickerYears" :key="y" :value="y">{{ y }}</option>
                                                </select>
                                            </div>
                                            <button type="button" class="fsf-nav-btn" @click.stop="nextMonth"><i class="fa fa-chevron-right"></i></button>
                                        </div>
                                        <div class="fsf-cal-wdays">
                                            <span v-for="w in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="w">{{ w }}</span>
                                        </div>
                                        <div class="fsf-cal-days">
                                            <span v-for="n in firstDayOfMonth" :key="'b'+n" class="fsf-day fsf-day-blank"></span>
                                            <button v-for="d in daysInMonth" :key="d" type="button"
                                                    class="fsf-day"
                                                    :class="{ 'fsf-day-today': isToday(d), 'fsf-day-selected': isSelected(d), 'fsf-day-disabled': isDayDisabled(d), 'fsf-day-range': isInRange(d) }"
                                                    :disabled="isDayDisabled(d)"
                                                    @click.stop="selectDay(d)">{{ d }}</button>
                                        </div>
                                        <div class="fsf-cal-foot">
                                            <button type="button" class="fsf-cal-today" @click.stop="selectToday">Today</button>
                                            <button type="button" class="fsf-cal-close" @click.stop="closePicker">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- RETURN DATE (round only) -->
                        <div
                            v-if="tripType === 'round'"
                            class="fsf-field fsf-date fsf-date-wrap"
                            :class="{ disabled: tripType === 'oneway', 'fsf-date-open': pickerOpen && pickerTarget && pickerTarget.type === 'return' }"
                            @click.stop="tripType !== 'oneway' && openPicker(-1, 'return')">
                            <label @click.prevent @mousedown.prevent>
                                <i class="fa fa-calendar-check"></i> Return
                            </label>
                            <div class="fsf-date-val" v-if="returnDate">{{ formatDisplayDate(returnDate) }}</div>
                            <div class="fsf-date-ph" v-else>Select return</div>

                            <!-- inline dropdown picker -->
                            <transition name="fsf-drop">
                                <div v-if="pickerOpen && pickerTarget && pickerTarget.type === 'return'"
                                     class="fsf-cal-drop"
                                     @click.stop>
                                    <div class="fsf-cal-inner">
                                        <div class="fsf-cal-nav">
                                            <button type="button" class="fsf-nav-btn" @click.stop="prevMonth"><i class="fa fa-chevron-left"></i></button>
                                            <div class="fsf-cal-my">
                                                <select v-model.number="pickerMonth" @change="clampDay" class="fsf-picker-sel" @click.stop>
                                                    <option v-for="(m, mi) in months" :key="mi" :value="mi">{{ m }}</option>
                                                </select>
                                                <select v-model.number="pickerYear" @change="clampDay" class="fsf-picker-sel" @click.stop>
                                                    <option v-for="y in pickerYears" :key="y" :value="y">{{ y }}</option>
                                                </select>
                                            </div>
                                            <button type="button" class="fsf-nav-btn" @click.stop="nextMonth"><i class="fa fa-chevron-right"></i></button>
                                        </div>
                                        <div class="fsf-cal-wdays">
                                            <span v-for="w in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="w">{{ w }}</span>
                                        </div>
                                        <div class="fsf-cal-days">
                                            <span v-for="n in firstDayOfMonth" :key="'b'+n" class="fsf-day fsf-day-blank"></span>
                                            <button v-for="d in daysInMonth" :key="d" type="button"
                                                    class="fsf-day"
                                                    :class="{ 'fsf-day-today': isToday(d), 'fsf-day-selected': isSelected(d), 'fsf-day-disabled': isDayDisabled(d), 'fsf-day-range': isInRange(d) }"
                                                    :disabled="isDayDisabled(d)"
                                                    @click.stop="selectDay(d)">{{ d }}</button>
                                        </div>
                                        <div class="fsf-cal-foot">
                                            <button type="button" class="fsf-cal-today" @click.stop="selectToday">Today</button>
                                            <button type="button" class="fsf-cal-close" @click.stop="closePicker">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- REMOVE (multi-city) -->
                        <button
                            v-if="tripType === 'multi'"
                            type="button"
                            class="fsf-remove"
                            :disabled="segments.length <= 1"
                            @click="removeSegment(idx)">
                            <i class="fa fa-xmark"></i>
                        </button>

                    </div><!-- /fsf-row -->
                </div><!-- /fsf-segment -->
            </div><!-- /fsf-segments -->

            <!-- ══════════════════════════════════
                 BOTTOM BAR
            ══════════════════════════════════ -->
            <div class="fsf-bottom">

                <!-- Travelers trigger -->
                <div class="fsf-bottom-left">
                    <div class="fsf-pill-btn" @click="toggleTraveler">
                        <i class="fa fa-users"></i>
                        <span>{{ totalPassengers }} Traveler{{ totalPassengers !== 1 ? 's' : '' }}</span>
                        <span class="fsf-class-badge">{{ travelClassLabel }}</span>
                    </div>

                    <!-- Travelers Dropdown -->
                    <div v-show="openTraveler" class="fsf-traveler-panel" @click.stop>
                        <div class="fsf-traveler-header">Passengers & Class</div>

                        <div class="fsf-pax-row">
                            <div class="fsf-pax-info">
                                <strong>Adults</strong>
                                <small>12+ yrs</small>
                            </div>
                            <div class="fsf-counter">
                                <button type="button" @click="adults > 1 && adults--"><i class="fa fa-minus"></i></button>
                                <span>{{ adults }}</span>
                                <button type="button" @click="adults++"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="fsf-pax-row">
                            <div class="fsf-pax-info">
                                <strong>Children</strong>
                                <small>2–11 yrs</small>
                            </div>
                            <div class="fsf-counter">
                                <button type="button" @click="removeChild"><i class="fa fa-minus"></i></button>
                                <span>{{ children }}</span>
                                <button type="button" @click="addChild"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <div v-if="children > 0" class="fsf-child-ages">
                            <div v-for="(age, i) in childrenAges" :key="'cage-' + i" class="fsf-age-row">
                                <label>Child {{ i + 1 }}</label>
                                <select v-model="childrenAges[i]">
                                    <option value="">Age?</option>
                                    <option v-for="y in 10" :key="y" :value="y + 1">{{ y + 1 }} yrs</option>
                                </select>
                            </div>
                        </div>

                        <div class="fsf-pax-row">
                            <div class="fsf-pax-info">
                                <strong>Infants</strong>
                                <small>Under 2</small>
                            </div>
                            <div class="fsf-counter">
                                <button type="button" @click="infants > 0 && infants--"><i class="fa fa-minus"></i></button>
                                <span>{{ infants }}</span>
                                <button type="button" @click="infants++"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="fsf-class-grid">
                            <button
                                v-for="cl in classes" :key="cl.value"
                                type="button"
                                class="fsf-class-opt"
                                :class="{ active: travelClass === cl.value }"
                                @click="travelClass = cl.value">
                                {{ cl.label }}
                            </button>
                        </div>

                        <button type="button" class="fsf-done-btn" @click="closeTraveler">Done</button>
                    </div>
                </div>

                <div class="fsf-bottom-center">
                    <!-- Add flight (multi) -->
                    <button
                        v-if="tripType === 'multi'"
                        type="button"
                        class="fsf-add-route"
                        @click="addSegment">
                        <i class="fa fa-plus"></i> Add Flight
                    </button>

                    <!-- Advanced Search toggle -->
                    <!--                    <button-->
                    <!--                        type="button"-->
                    <!--                        class="fsf-adv-toggle"-->
                    <!--                        :class="{ active: showAdvanced }"-->
                    <!--                        @click="showAdvanced = !showAdvanced">-->
                    <!--                        <i class="fa fa-sliders"></i>-->
                    <!--                        Advanced-->
                    <!--                        <i class="fa" :class="showAdvanced ? 'fa-chevron-up' : 'fa-chevron-down'"></i>-->
                    <!--                    </button>-->
                </div>

                <!-- Search btn -->
                <div class="fsf-bottom-right">
                    <button type="submit" class="fsf-search-btn" :disabled="isSearching">
                        <template v-if="!isSearching">
                            <i class="fa fa-magnifying-glass"></i>
                            <span>Search</span>
                        </template>
                        <template v-else>
                            <svg class="fsf-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="40" stroke-dashoffset="10"/></svg>
                            <span>Searching…</span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════
                 ADVANCED SEARCH PANEL
            ══════════════════════════════════ -->
            <transition name="fsf-adv">
                <div v-if="showAdvanced" class="fsf-advanced">
                    <div class="fsf-adv-title">
                        <i class="fa fa-sliders"></i> Advanced Filters
                    </div>
                    <div class="fsf-adv-grid">

                        <!-- Stops -->
                        <div class="fsf-adv-group">
                            <label>Max Stops</label>
                            <div class="fsf-stops">
                                <button
                                    v-for="s in stopOptions" :key="s.value"
                                    type="button"
                                    class="fsf-stop-btn"
                                    :class="{ active: advStops === s.value }"
                                    @click="advStops = s.value">
                                    {{ s.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Airline preference -->
                        <div class="fsf-adv-group">
                            <label>Preferred Airline</label>
                            <input
                                type="text"
                                v-model="advAirline"
                                placeholder="e.g. Biman, Emirates"
                                class="fsf-adv-input" />
                        </div>

                        <!-- Departure time -->
                        <div class="fsf-adv-group">
                            <label>Departure Time</label>
                            <div class="fsf-timerange">
                                <button
                                    v-for="t in timeSlots" :key="t.value"
                                    type="button"
                                    class="fsf-time-btn"
                                    :class="{ active: advDepartureTime === t.value }"
                                    @click="advDepartureTime = t.value">
                                    <i :class="t.icon"></i>
                                    {{ t.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Max price -->
                        <div class="fsf-adv-group">
                            <label>Max Budget (BDT)</label>
                            <div class="fsf-budget">
                                <input
                                    type="number"
                                    v-model="advMaxPrice"
                                    placeholder="e.g. 15000"
                                    min="0"
                                    class="fsf-adv-input" />
                            </div>
                        </div>

                        <!-- Baggage -->
                        <div class="fsf-adv-group">
                            <label>Baggage</label>
                            <div class="fsf-checkbox-group">
                                <label class="fsf-check">
                                    <input type="checkbox" v-model="advHandBaggage"> Cabin bag included
                                </label>
                                <label class="fsf-check">
                                    <input type="checkbox" v-model="advCheckedBaggage"> Checked bag included
                                </label>
                            </div>
                        </div>

                        <!-- Refundable -->
                        <div class="fsf-adv-group">
                            <label>Ticket Type</label>
                            <div class="fsf-checkbox-group">
                                <label class="fsf-check">
                                    <input type="checkbox" v-model="advRefundable"> Refundable only
                                </label>
                                <label class="fsf-check">
                                    <input type="checkbox" v-model="advNonStop"> Non-stop only
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </transition>

        </form><!-- /fsf-body -->

    </div><!-- /fsf-wrap -->
</template>

<script>
// ── Airport list sub-component ─────────────────────
const AirportList = {
    name: 'AirportList',
    props: {
        loading:    { type: Boolean, default: false },
        results:    { type: Array, default: () => [] },
        recents:    { type: Array, default: () => [] },
        showSearch: { type: Boolean, default: false }
    },
    emits: ['select'],
    methods: {
        pick(airport) { this.$emit('select', airport); }
    },
    template: `
        <div class="fsf-airport-list">
            <div v-if="loading" class="fsf-al-loading">
                <i class="fa fa-spinner fa-spin"></i> Searching…
            </div>
            <template v-else-if="showSearch">
                <template v-if="results && results.length">
                    <div v-for="a in results" :key="a.id"
                         class="fsf-al-item" @mousedown.prevent="pick(a)">
                        <div class="fsf-al-info">
                            <div class="fsf-al-name">{{ a.name }}</div>
                            <div class="fsf-al-addr" v-if="a.address">{{ a.address }}</div>
                        </div>
                        <div class="fsf-al-code-block">
                            <i class="fa fa-plane"></i>
                            <span class="fsf-al-code">{{ a.code }}</span>
                        </div>
                    </div>
                </template>
                <div v-else class="fsf-al-empty">
                    <i class="fa fa-circle-xmark"></i> No results found
                </div>
            </template>
            <template v-else>
                <div v-if="recents.length" class="fsf-al-section">
                    <i class="fa fa-clock"></i> Recent
                </div>
                <div v-for="a in recents" :key="'r-' + a.id"
                     class="fsf-al-item" @mousedown.prevent="pick(a)">
                    <div class="fsf-al-info">
                        <div class="fsf-al-name">{{ a.name }}</div>
                        <div class="fsf-al-addr" v-if="a.address">{{ a.address }}</div>
                    </div>
                    <div class="fsf-al-code-block">
                        <i class="fa fa-clock"></i>
                        <span class="fsf-al-code">{{ a.code }}</span>
                    </div>
                </div>
                <div v-if="!recents.length" class="fsf-al-empty">
                    <i class="fa fa-magnifying-glass"></i> Type to search airports
                </div>
            </template>
        </div>
    `
};

// ── Main Component ──────────────────────────────────
export default {
    name: 'FlightSearchForm',
    components: { AirportList },

    data() {
        return {
            isSelectingAirport: false,
            isSearching:        false,
            recentFromAirports: [],
            recentToAirports:   [],
            searchTimeout:      null,
            hasSessionData:     false,
            sessionSegments:    [],
            isFormVisible:      false,

            tripTypes: [
                { value: 'oneway', label: 'One Way',    icon: 'fa fa-arrow-right' },
                { value: 'round',  label: 'Round Trip', icon: 'fa fa-arrows-rotate' },
                { value: 'multi',  label: 'Multi City', icon: 'fa fa-code-branch' }
            ],
            classes: [
                { value: 'ECONOMY',  label: 'Economy'  },
                // { value: 'PREMIUM',  label: 'Premium'  },
                { value: 'BUSINESS', label: 'Business' },
                { value: 'FIRST',    label: 'First'    }
            ],
            stopOptions: [
                { value: '',  label: 'Any'    },
                { value: '0', label: 'Direct' },
                { value: '1', label: '1 Stop' },
                { value: '2', label: '2+'     }
            ],
            timeSlots: [
                { value: '',        label: 'Any',       icon: 'fa fa-clock'    },
                { value: 'morning', label: 'Morning',   icon: 'fa fa-sun'      },
                { value: 'noon',    label: 'Afternoon', icon: 'fa fa-cloud-sun'},
                { value: 'night',   label: 'Night',     icon: 'fa fa-moon'     }
            ],

            tripType:   'round',
            segments:   [],
            returnDate: '',

            openTraveler: false,
            adults:       1,
            children:     0,
            childrenAges: [],
            infants:      0,
            travelClass:  'ECONOMY',

            searchResults: {},
            loading:       false,
            activeSearch:  null,

            // Advanced
            showAdvanced:      false,
            advStops:          '',
            advAirline:        '',
            advDepartureTime:  '',
            advMaxPrice:       '',
            advHandBaggage:    false,
            advCheckedBaggage: false,
            advRefundable:     false,
            advNonStop:        false,

            // Custom date picker
            pickerOpen:    false,
            pickerTarget:  null,   // { type: 'departure'|'return', idx: number }
            pickerYear:    new Date().getFullYear(),
            pickerMonth:   new Date().getMonth(),
            months: ['January','February','March','April','May','June',
                'July','August','September','October','November','December']
        };
    },

    computed: {
        totalPassengers() { return this.adults + this.children + this.infants; },
        travelClassLabel() {
            const c = this.classes.find(c => c.value === this.travelClass);
            return c ? c.label : 'Economy';
        },

        // ── Picker computed ──
        pickerLabel() {
            if (!this.pickerTarget) return '';
            return this.pickerTarget.type === 'return' ? 'Select Return Date' : 'Select Departure Date';
        },
        pickerYears() {
            const y = new Date().getFullYear();
            const res = [];
            for (let i = y; i <= y + 2; i++) res.push(i);
            return res;
        },
        daysInMonth() {
            return new Date(this.pickerYear, this.pickerMonth + 1, 0).getDate();
        },
        firstDayOfMonth() {
            return new Date(this.pickerYear, this.pickerMonth, 1).getDay();
        },
        pickerMinDate() {
            if (!this.pickerTarget) return new Date();
            const t = this.pickerTarget;
            if (t.type === 'return') {
                const dep = this.segments[0]?.departure;
                return dep ? new Date(dep) : new Date();
            }
            if (t.type === 'departure' && t.idx > 0) {
                const prev = this.segments[t.idx - 1]?.departure;
                return prev ? new Date(prev) : new Date();
            }
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        }
    },

    // watch: {
    //     segments: {
    //         handler(segs) {
    //             if (this.tripType === 'round' && segs.length && segs[0].departure) {
    //                 const d = new Date(segs[0].departure);
    //                 d.setDate(d.getDate() + 1);
    //                 this.returnDate = d.toISOString().split('T')[0];
    //             }
    //         },
    //         deep: true
    //     }
    // },

    watch: {
        segments: {
            handler(segs) {
                if (
                    this.tripType === 'round' &&
                    segs.length &&
                    segs[0].departure
                ) {
                    const depDate = new Date(segs[0].departure);

                    // Only update return date if empty or invalid
                    if (
                        !this.returnDate ||
                        new Date(this.returnDate) <= depDate
                    ) {
                        const nextDay = new Date(depDate);
                        nextDay.setDate(nextDay.getDate() + 1);

                        this.returnDate = nextDay
                            .toISOString()
                            .split('T')[0];
                    }
                }
            },
            deep: true
        }
    },

    methods: {
        // ── Trip type ──
        changeTripType(t) {
            const old = this.tripType;
            this.tripType = t;

            if (t === 'multi' && old !== 'multi' && this.segments.length === 1)
                this.segments.push(this.createEmptySegment());
            if (t !== 'multi' && old === 'multi')
                this.segments = [this.segments[0]];
            if (t === 'round' && this.segments[0]?.departure) {
                const d = new Date(this.segments[0].departure);
                d.setDate(d.getDate() + 1);
                this.returnDate = d.toISOString().split('T')[0];
            }
            // custom picker ব্যবহার করছি — init দরকার নেই
        },

        // ── Custom Date Picker ──
        openPicker(idx, type) {
            // same picker click করলে toggle করো
            if (this.pickerOpen && this.pickerTarget?.type === type &&
                (type === 'return' || this.pickerTarget?.idx === idx)) {
                this.closePicker();
                return;
            }
            this.pickerTarget = { idx, type };
            let currentDate = null;
            if (type === 'return' && this.returnDate) currentDate = new Date(this.returnDate);
            else if (type === 'departure' && this.segments[idx]?.departure) currentDate = new Date(this.segments[idx].departure);
            if (currentDate) {
                this.pickerYear  = currentDate.getFullYear();
                this.pickerMonth = currentDate.getMonth();
            } else {
                this.pickerYear  = new Date().getFullYear();
                this.pickerMonth = new Date().getMonth();
            }
            this.pickerOpen = true;
        },

        closePicker() {
            this.pickerOpen = false;
            this.pickerTarget = null;
        },

        clampDay() {
            // month/year বদলালে valid range চেক করো
        },

        prevMonth() {
            if (this.pickerMonth === 0) { this.pickerMonth = 11; this.pickerYear--; }
            else this.pickerMonth--;
        },

        nextMonth() {
            if (this.pickerMonth === 11) { this.pickerMonth = 0; this.pickerYear++; }
            else this.pickerMonth++;
        },

        selectDay(d) {
            const dateStr = `${this.pickerYear}-${String(this.pickerMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const t = this.pickerTarget;

            if (!t) return;

            if (t.type === 'return') {

                this.returnDate = dateStr;

            } else {

                this.$set(this.segments, t.idx, {
                    ...this.segments[t.idx],
                    departure: dateStr
                });

                if (t.idx === 0 && this.tripType === 'round') {

                    const depDate = new Date(dateStr);

                    if (
                        !this.returnDate ||
                        new Date(this.returnDate) <= depDate
                    ) {
                        const d2 = new Date(depDate);

                        d2.setDate(d2.getDate() + 1);

                        this.returnDate = d2
                            .toISOString()
                            .split('T')[0];
                    }
                }
            }

            // ✅ AUTO OPEN RETURN DATE
            if (
                t.type === 'departure' &&
                t.idx === 0 &&
                this.tripType === 'round'
            ) {

                this.pickerTarget = {
                    idx: -1,
                    type: 'return'
                };

                if (this.returnDate) {
                    const ret = new Date(this.returnDate);

                    this.pickerYear = ret.getFullYear();
                    this.pickerMonth = ret.getMonth();
                }

                this.pickerOpen = true;

            } else {

                this.closePicker();
            }
        },

        selectToday() {
            const today = new Date();
            const minD = this.pickerMinDate;
            const use = today >= minD ? today : minD;
            this.pickerYear  = use.getFullYear();
            this.pickerMonth = use.getMonth();
            this.selectDay(use.getDate());
        },

        isToday(d) {
            const today = new Date();
            return d === today.getDate() &&
                this.pickerMonth === today.getMonth() &&
                this.pickerYear  === today.getFullYear();
        },

        isSelected(d) {
            const dateStr = `${this.pickerYear}-${String(this.pickerMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const t = this.pickerTarget;
            if (!t) return false;
            if (t.type === 'return') return this.returnDate === dateStr;
            return this.segments[t.idx]?.departure === dateStr;
        },

        isInRange(d) {
            if (this.tripType !== 'round') return false;
            const dateStr = `${this.pickerYear}-${String(this.pickerMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dep = this.segments[0]?.departure;
            const ret = this.returnDate;
            if (!dep || !ret) return false;
            return dateStr > dep && dateStr < ret;
        },

        isDayDisabled(d) {
            const dateStr = `${this.pickerYear}-${String(this.pickerMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const minD = this.pickerMinDate;
            const minStr = `${minD.getFullYear()}-${String(minD.getMonth()+1).padStart(2,'0')}-${String(minD.getDate()).padStart(2,'0')}`;
            // max: 2 years from today
            const maxD = new Date();
            maxD.setFullYear(maxD.getFullYear() + 2);
            const maxStr = maxD.toISOString().split('T')[0];
            return dateStr < minStr || dateStr > maxStr;
        },

        // Date display: "15 Jan 2025"
        formatDisplayDate(dateStr) {
            if (!dateStr) return '';
            try {
                const [y, m, d] = dateStr.split('-');
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return `${parseInt(d)} ${months[parseInt(m) - 1]} ${y}`;
            } catch { return dateStr; }
        },

        // ── Session ──
        async loadSessionData(sd) {
            this.tripType   = sd.trip_type || 'round';
            this.returnDate = sd.return_date || '';
            this.adults     = parseInt(sd.adults) || 1;
            this.children   = parseInt(sd.children) || 0;
            this.infants    = parseInt(sd.infants) || 0;
            this.travelClass = sd.travel_class || 'ECONOMY';
            if (sd.children_ages) this.childrenAges = sd.children_ages;

            this.segments        = [];
            this.sessionSegments = [];
            this.hasSessionData  = true;

            for (const seg of (sd.segments || [])) {
                const [from, to] = await Promise.all([
                    this.fetchAirportById(seg.from),
                    this.fetchAirportById(seg.to)
                ]);
                const s = {
                    from_display: from ? `${from.code} - ${from.name}` : '',
                    from_search: '', from_id: seg.from,
                    to_display: to ? `${to.code} - ${to.name}` : '',
                    to_search: '', to_id: seg.to,
                    departure: seg.departure
                };
                this.segments.push(s);
                this.sessionSegments.push(s);
            }
        },

        async fetchAirportById(id) {
            try {
                const r = await fetch(`/flight/airport/${id}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (r.ok) return await r.json();
            } catch {}
            return null;
        },

        // ── Airport input handlers ──
        focusField(type, idx) {
            this.$nextTick(() => {
                const refs = this.$refs[`${type}_${idx}`];
                const input = Array.isArray(refs) ? refs[0] : refs;
                if (input?.focus) input.focus();
            });
        },
        handleFromFocus(i)          { this.activeSearch = i + '-from'; this.segments[i].from_search = ''; },
        handleFromInput(e, i)       { this.segments[i].from_search = e.target.value; this.searchAirport(e.target.value, i, 'from'); },
        handleFromBlur(i)           { setTimeout(() => { if (!this.isSelectingAirport && this.activeSearch === i + '-from') this.activeSearch = null; }, 200); },
        handleToFocus(i)            { this.activeSearch = i + '-to'; this.segments[i].to_search = ''; },
        handleToInput(e, i)         { this.segments[i].to_search = e.target.value; this.searchAirport(e.target.value, i, 'to'); },
        handleToBlur(i)             { setTimeout(() => { if (!this.isSelectingAirport && this.activeSearch === i + '-to') this.activeSearch = null; }, 200); },

        selectAirport(i, field, airport) {
            this.isSelectingAirport = true;

            const s  = this.segments[i];
            const dn = `${airport.code} - ${airport.name}`;

            if (field === 'from') {
                s.from_display = dn;
                s.from_search  = '';
                s.from_id      = airport.id;

                this.saveRecent('from', airport);

                // ✅ Auto focus TO input after FROM selected
                this.$nextTick(() => {
                    this.focusField('to', i);
                });

            } else {

                s.to_display = dn;
                s.to_search  = '';
                s.to_id      = airport.id;

                this.saveRecent('to', airport);

                // ✅ Automatically open departure calendar
                this.$nextTick(() => {
                    this.openPicker(i, 'departure');
                });
            }

            this.activeSearch = null;

            setTimeout(() => {
                this.isSelectingAirport = false;
            }, 100);
        },

        saveRecent(type, airport) {
            const key = type === 'from' ? 'recentFromAirports' : 'recentToAirports';
            this[key] = [airport, ...this[key].filter(a => a.id !== airport.id)].slice(0, 5);
            localStorage.setItem(key, JSON.stringify(this[key]));
        },

        searchAirport(q, i, field) {
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            if (q.length < 2) { this.$set(this.searchResults, `${i}_${field}`, []); return; }
            this.loading = true;
            this.searchTimeout = setTimeout(() => {
                fetch(`/flight/airport/search?search=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(r => r.json())
                    .then(data => { this.$set(this.searchResults, `${i}_${field}`, data); this.loading = false; })
                    .catch(() => { this.$set(this.searchResults, `${i}_${field}`, []); this.loading = false; });
            }, 400);
        },

        // ── Segment helpers ──
        createDefaultSegment() {
            if (this.hasSessionData && this.sessionSegments.length) return this.sessionSegments[0];
            return {
                from_display: 'DAC - Dhaka', from_search: '', from_id: 61,
                to_display: "CXB - Cox's Bazar", to_search: '', to_id: 62,
                departure: new Date().toISOString().split('T')[0]
            };
        },
        createEmptySegment() {
            return { from_display: '', from_search: '', from_id: '', to_display: '', to_search: '', to_id: '', departure: '' };
        },
        addSegment() {
            this.segments.push(this.createEmptySegment());
            this.$nextTick(() => { this.initDepartureDatePickers(); });
        },
        removeSegment(i) {
            if (this.segments.length <= 1) return;
            this.segments.splice(i, 1);
        },
        swap(i) {
            const s = this.segments[i];
            this.$set(this.segments, i, {
                ...s,
                from_display: s.to_display,
                from_search:  s.to_search,
                from_id:      s.to_id,
                to_display:   s.from_display,
                to_search:    s.from_search,
                to_id:        s.from_id,
            });
        },

        // ── Passengers ──
        addChild()    { this.children++; this.childrenAges.push(''); },
        removeChild() { if (this.children > 0) { this.children--; this.childrenAges.pop(); } },
        toggleTraveler() { this.openTraveler = !this.openTraveler; },
        closeTraveler()  { this.openTraveler = false; },

        // ── Outside click ──
        handleOutsideClick(e) {
            if (this.activeSearch && !e.target.closest('.fsf-field')) this.activeSearch = null;
            if (this.openTraveler && !e.target.closest('.fsf-bottom-left')) this.openTraveler = false;
            if (this.pickerOpen && !e.target.closest('.fsf-date-wrap')) this.closePicker();
        },

        // ── Submit ──
        async handleSearch() {
            for (const seg of this.segments) {
                if (!seg.from_id || !seg.to_id || !seg.departure) {
                    alert('Please fill in all flight details.'); return;
                }
                // ✅ Same airport check
                if (String(seg.from_id) === String(seg.to_id)) {
                    alert('Departure and arrival airport cannot be the same.'); return;
                }
            }

            this.isSearching = true;

            const p = new URLSearchParams();
            p.append('trip_type',    this.tripType);
            p.append('adults',       this.adults);
            p.append('children',     this.children);
            p.append('infants',      this.infants);
            p.append('travel_class', this.travelClass);
            this.childrenAges.forEach((a, i) => { if (a) p.append(`children_ages[${i}]`, a); });
            this.segments.forEach((s, i) => {
                p.append(`segments[${i}][from]`,      s.from_id);
                p.append(`segments[${i}][to]`,        s.to_id);
                p.append(`segments[${i}][departure]`, s.departure);
            });
            if (this.tripType === 'round' && this.returnDate) p.append('return_date', this.returnDate);

            const searchUrl = `${window.flightData?.searchUrl || '/flight'}?${p.toString()}`;
            const isSearchPage = !!document.querySelector('.fsp-wrap');

            if (!isSearchPage) {
                // ✅ Home page — redirect করুন
                window.location.href = searchUrl;
                return;
            }

            // ✅ Search page — streaming করুন
            const fromCode = this.segments[0]?.from_display?.split(' - ')[0] || '';
            const toCode   = this.segments[0]?.to_display?.split(' - ')[0] || '';

            window.dispatchEvent(new CustomEvent('flight-search-started', {
                detail: { route: `${fromCode} → ${toCode}` }
            }));

            history.pushState({}, '', `?${p.toString()}`);
            await this.doStream(searchUrl);
        },

        async doStream(searchUrl) {
            try {
                const res = await fetch(searchUrl, {
                    headers: {
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.status === 422) {
                    const data = await res.json();
                    const errors = Object.values(data.errors || {}).flat();
                    alert(errors.join('\n'));
                    window.dispatchEvent(new CustomEvent('flight-search-done'));
                    return;
                }

                if (!res.ok) throw new Error('Server error: ' + res.status);

                const reader  = res.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let isDone = false;

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        if (!line.startsWith('data: ')) continue;
                        const raw = line.slice(6).trim();

                        if (raw === '[DONE]') {
                            isDone = true;
                            break;
                        }

                        try {
                            const flight = JSON.parse(raw);
                            window.dispatchEvent(new CustomEvent('flight-search-item', { detail: flight }));
                        } catch(e) {}
                    }

                    if (isDone) break;
                }

            } catch(e) {
                console.error('Stream error:', e);
            } finally {
                // ✅ সবসময় এখানে শেষ হবে
                this.isSearching = false;
                window.dispatchEvent(new CustomEvent('flight-search-done'));
            }
        },
    },

    mounted() {
        const hasSession = window.flightData?.searchParams &&
            Object.keys(window.flightData.searchParams).length > 0;

        if (hasSession) {
            this.loadSessionData(window.flightData.searchParams);
        } else {
            this.segments = [this.createDefaultSegment()];
            const d = new Date(); d.setDate(d.getDate() + 1);
            this.returnDate = d.toISOString().split('T')[0];
        }

        const rf = localStorage.getItem('recentFromAirports');
        const rt = localStorage.getItem('recentToAirports');
        if (rf) this.recentFromAirports = JSON.parse(rf);
        if (rt) this.recentToAirports   = JSON.parse(rt);

        document.addEventListener('click', this.handleOutsideClick);

        // ✅ Auto search — page load হলে
        window.addEventListener('trigger-auto-search', async () => {
            if (this.isSearching) return;

            // Segments load হওয়া পর্যন্ত wait
            await new Promise(resolve => {
                const check = () => {
                    if (this.segments.length > 0 && this.segments[0].from_id) resolve();
                    else setTimeout(check, 100);
                };
                check();
            });

            if (this.isSearching) return;

            // ✅ Search page এ এসেছি — stream করুন
            const fromCode = this.segments[0]?.from_display?.split(' - ')[0] || '';
            const toCode   = this.segments[0]?.to_display?.split(' - ')[0] || '';

            window.dispatchEvent(new CustomEvent('flight-search-started', {
                detail: { route: `${fromCode} → ${toCode}` }
            }));

            const p = new URLSearchParams(window.location.search);
            const searchUrl = `${window.flightData?.searchUrl || '/flight'}?${p.toString()}`;

            this.isSearching = true;
            await this.doStream(searchUrl);
        }, { once: true });  // ✅ একবারই fire হবে
    },

    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
        document.body.style.overflow = '';
    }
};
</script>

<style scoped>
/* ════════════════════════════════════════
   TOKENS
════════════════════════════════════════ */
.fsf-wrap {
    --fsf-bg:       #ffffff;
    --fsf-border:   #e2e8f0;
    --fsf-primary:  #0057ff;
    --fsf-primary2: #003fcc;
    --fsf-accent:   #03c5ff;
    --fsf-text:     #0f172a;
    --fsf-muted:    #64748b;
    --fsf-light:    #f8fafc;
    --fsf-radius:   14px;
    --fsf-shadow:   0 2px 12px rgba(0,0,0,.07);
    --fsf-shadow-lg:0 8px 32px rgba(0,87,255,.13);

    background: var(--fsf-bg);
    border-radius: 20px;
    box-shadow: var(--fsf-shadow-lg);
    overflow: visible;          /* ✅ calendar dropdown দেখাতে হবে */
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 14px;
    color: var(--fsf-text);
    position: relative;
}

/* ════════════════════════════════════════
   TABS
════════════════════════════════════════ */
.fsf-tabs {
    display: flex;
    background: var(--fsf-light);
    border-radius: 20px 20px 0 0;
    border-bottom: 1px solid var(--fsf-border);
    overflow: hidden;
}
.fsf-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 8px;
    border: none;
    background: transparent;
    color: var(--fsf-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    letter-spacing: .02em;
}
.fsf-tab i { font-size: 12px; }
.fsf-tab.active {
    background: var(--fsf-bg);
    color: var(--fsf-primary);
    box-shadow: inset 0 -2px 0 var(--fsf-primary);
}
.fsf-tab:hover:not(.active) { background: #eef2ff; color: var(--fsf-primary); }

/* ════════════════════════════════════════
   BODY
════════════════════════════════════════ */
.fsf-body { padding: 16px 16px 0; }

/* ════════════════════════════════════════
   SEGMENTS
════════════════════════════════════════ */
.fsf-segments { display: flex; flex-direction: column; gap: 12px; }

.fsf-segment { }

.fsf-route-badge {
    margin-bottom: 8px;
}
.fsf-route-badge span {
    background: var(--fsf-primary);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 20px;
    letter-spacing: .04em;
}

/* ── Row ── */
.fsf-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr 1fr 1fr;
    gap: 8px;
    align-items: start;
}

/* Round trip: from swap to dep return */
/* One way:    from swap to  dep */
/* Multi:      from swap to  dep  [remove] */

/* ── Field ── */
.fsf-field {
    background: var(--fsf-light);
    border: 1.5px solid var(--fsf-border);
    border-radius: var(--fsf-radius);
    padding: 10px 12px 8px;
    position: relative;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
    min-width: 0;
}
.fsf-field:hover   { border-color: #a5b4fc; }
.fsf-field.focused { border-color: var(--fsf-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.10); background: #fff; }
.fsf-field.disabled { opacity: .45; pointer-events: none; }

.fsf-field label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--fsf-muted);
    margin-bottom: 4px;
    white-space: nowrap;
}
.fsf-field label i { font-size: 10px; color: var(--fsf-primary); }

.fsf-input {
    width: 100%;
    border: none;
    background: transparent;
    font-size: 14px;
    font-weight: 600;
    color: var(--fsf-text);
    outline: none;
    padding: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fsf-input::placeholder { color: #cbd5e1; font-weight: 400; }

.fsf-code {
    position: absolute;
    bottom: 8px;
    right: 10px;
    font-size: 18px;
    font-weight: 800;
    color: #dbeafe;
    pointer-events: none;
    line-height: 1;
}

/* ── Swap ── */
.fsf-swap {
    width: 36px;
    height: 36px;
    margin-top: 22px;
    border-radius: 50%;
    border: 2px solid var(--fsf-border);
    background: var(--fsf-bg);
    color: var(--fsf-primary);
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .2s;
    flex-shrink: 0;
}
.fsf-swap:hover {
    background: var(--fsf-primary);
    border-color: var(--fsf-primary);
    color: #fff;
    transform: rotate(180deg);
}

/* ── Remove ── */
.fsf-remove {
    width: 36px;
    height: 36px;
    margin-top: 22px;
    border-radius: 50%;
    border: 1.5px solid #fca5a5;
    background: #fff5f5;
    color: #ef4444;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .2s;
    flex-shrink: 0;
}
.fsf-remove:hover:not(:disabled) { background: #ef4444; color: #fff; border-color: #ef4444; }
.fsf-remove:disabled { opacity: .3; cursor: not-allowed; }

/* ── Airport dropdown ── */
.fsf-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1.5px solid var(--fsf-border);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,.10);
    z-index: 1000;
    overflow: hidden;
}
.fsf-airport-list { max-height: 220px; overflow-y: auto; }
.fsf-al-section {
    padding: 8px 14px 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--fsf-muted);
    background: var(--fsf-light);
}
.fsf-al-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid #f1f5f9;
}
.fsf-al-item:last-child { border-bottom: none; }
.fsf-al-item:hover { background: #eff6ff; }
/* ── Airport list items ── */
.fsf-al-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid #f1f5f9;
}
.fsf-al-item:last-child { border-bottom: none; }

/* ✅ hover এ item এর bg + সব child এর color বদলায় */
.fsf-al-item:hover { background: #eff6ff; }
.fsf-al-item:hover .fsf-al-code        { color: var(--fsf-primary); }
.fsf-al-item:hover .fsf-al-name        { color: #334155; }
.fsf-al-item:hover .fsf-al-inline-icon { color: var(--fsf-primary); }

/* ✅ code + icon একসাথে inline */
.fsf-al-left {
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 58px;
    flex-shrink: 0;
}
.fsf-al-code {
    font-weight: 800;
    font-size: 15px;
    color: var(--fsf-primary);
    line-height: 1;
}
.fsf-al-inline-icon {
    font-size: 10px;
    color: #cbd5e1;
    transition: color .15s;
}

/* info block */
.fsf-al-info  { flex: 1; min-width: 0; }
.fsf-al-name  { font-size: 12px; color: var(--fsf-muted); transition: color .15s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fsf-al-addr  { font-size: 11px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }

.fsf-al-section {
    padding: 8px 14px 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--fsf-muted);
    background: var(--fsf-light);
    display: flex;
    align-items: center;
    gap: 5px;
}
.fsf-al-loading, .fsf-al-empty {
    padding: 20px;
    text-align: center;
    color: var(--fsf-muted);
    font-size: 13px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.fsf-al-loading i { font-size: 20px; color: var(--fsf-primary); }

/* ════════════════════════════════════════
   BOTTOM BAR
════════════════════════════════════════ */
.fsf-bottom {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 0 16px;
    margin-top: 12px;
    border-top: 1px solid var(--fsf-border);
}
.fsf-bottom-left  { position: relative; }
.fsf-bottom-center { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.fsf-bottom-right { margin-left: auto; }

/* Travelers pill */
.fsf-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 40px;
    background: var(--fsf-light);
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: border-color .2s;
    user-select: none;
}
.fsf-pill-btn:hover { border-color: var(--fsf-primary); }
.fsf-pill-btn i { color: var(--fsf-primary); }
.fsf-class-badge {
    background: var(--fsf-primary);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: .04em;
}

/* Travelers panel */
.fsf-traveler-panel {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 0;
    background: #fff;
    border: 1.5px solid var(--fsf-border);
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,.12);
    padding: 16px;
    width: 290px;
    z-index: 9999;
}
.fsf-traveler-header {
    font-weight: 700;
    font-size: 13px;
    color: var(--fsf-primary);
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--fsf-border);
    letter-spacing: .02em;
}
.fsf-pax-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.fsf-pax-row:last-of-type { border-bottom: none; }
.fsf-pax-info strong { display: block; font-size: 13px; font-weight: 600; }
.fsf-pax-info small  { font-size: 11px; color: var(--fsf-muted); }
.fsf-counter { display: flex; align-items: center; gap: 10px; }
.fsf-counter button {
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 1.5px solid var(--fsf-border);
    background: var(--fsf-light);
    font-size: 11px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.fsf-counter button:hover { border-color: var(--fsf-primary); background: #eff6ff; color: var(--fsf-primary); }
.fsf-counter span { font-weight: 700; font-size: 14px; min-width: 20px; text-align: center; }

.fsf-child-ages { padding: 8px 0; }
.fsf-age-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.fsf-age-row label { font-size: 12px; color: var(--fsf-muted); min-width: 52px; }
.fsf-age-row select {
    flex: 1;
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 12px;
    outline: none;
}

.fsf-class-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin: 12px 0;
}
.fsf-class-opt {
    padding: 7px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    background: var(--fsf-light);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
}
.fsf-class-opt.active {
    border-color: var(--fsf-primary);
    background: #eff6ff;
    color: var(--fsf-primary);
}
.fsf-done-btn {
    width: 100%;
    padding: 9px;
    background: var(--fsf-primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: background .2s;
    margin-top: 4px;
}
.fsf-done-btn:hover { background: var(--fsf-primary2); }

/* Add route / Advanced */
.fsf-add-route {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px dashed var(--fsf-primary);
    border-radius: 40px;
    background: #eff6ff;
    color: var(--fsf-primary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.fsf-add-route:hover { background: var(--fsf-primary); color: #fff; }

.fsf-adv-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 40px;
    background: var(--fsf-light);
    color: var(--fsf-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.fsf-adv-toggle:hover, .fsf-adv-toggle.active {
    border-color: var(--fsf-primary);
    color: var(--fsf-primary);
    background: #eff6ff;
}

/* Search button */
.fsf-search-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    background: var(--fsf-accent);
    color: #1a1a1a;
    border: none;
    border-radius: 40px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 4px 14px rgba(245,158,11,.3);
    letter-spacing: .02em;
    white-space: nowrap;
}
.fsf-search-btn:hover:not(:disabled) {
    background: #1e9cd6;
    box-shadow: 0 6px 20px rgba(245,158,11,.4);
    transform: translateY(-1px);
}
.fsf-search-btn:disabled { opacity: .75; cursor: not-allowed; }
.fsf-spin {
    width: 18px; height: 18px;
    animation: fsf-rotate .8s linear infinite;
}
@keyframes fsf-rotate { to { transform: rotate(360deg); } }

/* ════════════════════════════════════════
   ADVANCED PANEL
════════════════════════════════════════ */
.fsf-advanced {
    border-top: 1px solid var(--fsf-border);
    padding: 16px 0 18px;
}
.fsf-adv-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--fsf-primary);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.fsf-adv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px 20px;
}
.fsf-adv-group label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--fsf-muted);
    margin-bottom: 8px;
}
.fsf-adv-input {
    width: 100%;
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    outline: none;
    background: var(--fsf-light);
    color: var(--fsf-text);
    transition: border-color .2s;
    box-sizing: border-box;
}
.fsf-adv-input:focus { border-color: var(--fsf-primary); background: #fff; }

.fsf-stops, .fsf-timerange {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.fsf-stop-btn, .fsf-time-btn {
    padding: 6px 12px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 20px;
    background: var(--fsf-light);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 4px;
}
.fsf-stop-btn.active, .fsf-time-btn.active {
    border-color: var(--fsf-primary);
    background: #eff6ff;
    color: var(--fsf-primary);
}
.fsf-stop-btn:hover, .fsf-time-btn:hover { border-color: var(--fsf-primary); color: var(--fsf-primary); }

.fsf-checkbox-group { display: flex; flex-direction: column; gap: 8px; }
.fsf-check {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    cursor: pointer;
    color: var(--fsf-text);
}
.fsf-check input[type="checkbox"] {
    width: 15px; height: 15px;
    accent-color: var(--fsf-primary);
    cursor: pointer;
}

/* Transition */
.fsf-adv-enter-active, .fsf-adv-leave-active { transition: all .25s ease; overflow: hidden; }
.fsf-adv-enter, .fsf-adv-leave-to { opacity: 0; max-height: 0; padding: 0; }
.fsf-adv-enter-to, .fsf-adv-leave { opacity: 1; max-height: 400px; }

/* ════════════════════════════════════════
   DATE FIELD DISPLAY
════════════════════════════════════════ */
.fsf-date-val {
    font-size: 14px;
    font-weight: 600;
    color: var(--fsf-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 22px;
    line-height: 22px;
}
.fsf-date-ph {
    font-size: 14px;
    color: #cbd5e1;
    min-height: 22px;
    line-height: 22px;
}

/* ════════════════════════════════════════
   DATE FIELD WRAPPER — relative for dropdown
════════════════════════════════════════ */
.fsf-date-wrap {
    position: relative;
    z-index: auto;
}
.fsf-date-open {
    border-color: var(--fsf-primary) !important;
    box-shadow: 0 0 0 3px rgba(0,87,255,.10) !important;
    background: #fff !important;
    z-index: 500 !important;
}

/* ════════════════════════════════════════
   INLINE CALENDAR DROPDOWN
════════════════════════════════════════ */
.fsf-cal-drop {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 9999;
    /* mobile: full width from left edge */
    width: 300px;
    min-width: 280px;
}

/* On smaller screens push right if near left edge,
   or align to right edge of field */
@media (max-width: 600px) {
    .fsf-cal-drop {
        /* mobile: center relative to viewport */
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        width: 100%;
        min-width: unset;
        z-index: 9999;
    }
}

.fsf-cal-inner {
    background: #fff;
    border: 1.5px solid var(--fsf-border);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.14);
    overflow: hidden;
}

/* Mobile: bottom sheet style */
@media (max-width: 600px) {
    .fsf-cal-inner {
        border-radius: 20px 20px 0 0;
        border-bottom: none;
        box-shadow: 0 -6px 32px rgba(0,0,0,.15);
        padding-bottom: env(safe-area-inset-bottom, 8px);
    }
}

/* Nav row */
.fsf-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 12px 8px;
    gap: 6px;
}
.fsf-nav-btn {
    width: 32px; height: 32px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    background: var(--fsf-light);
    color: #475569;
    font-size: 12px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: all .15s;
}
.fsf-nav-btn:hover { background: var(--fsf-primary); color: #fff; border-color: var(--fsf-primary); }

.fsf-cal-my {
    display: flex;
    gap: 4px;
    flex: 1;
    justify-content: center;
}
.fsf-picker-sel {
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--fsf-text);
    background: var(--fsf-light);
    cursor: pointer;
    outline: none;
    -webkit-appearance: none;
    text-align: center;
    min-width: 0;
}
.fsf-picker-sel:focus { border-color: var(--fsf-primary); }

/* Weekday headers */
.fsf-cal-wdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 0 10px 4px;
}
.fsf-cal-wdays span {
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    padding: 3px 0;
}

/* Day grid */
.fsf-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    padding: 0 10px 8px;
}
.fsf-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 500;
    color: var(--fsf-text);
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    transition: all .1s;
    -webkit-tap-highlight-color: transparent;
    padding: 0;
}
.fsf-day:hover:not(.fsf-day-disabled):not(.fsf-day-selected):not(.fsf-day-blank) {
    background: #eff6ff;
    color: var(--fsf-primary);
}
.fsf-day-blank   { cursor: default; }
.fsf-day-today   { font-weight: 700; color: var(--fsf-primary); border: 1.5px solid var(--fsf-primary); }
.fsf-day-selected { background: var(--fsf-primary) !important; color: #fff !important; font-weight: 700; }
.fsf-day-range   { background: #dbeafe; color: var(--fsf-primary); border-radius: 0; }
.fsf-day-disabled { color: #e2e8f0; cursor: not-allowed; }

/* Footer */
.fsf-cal-foot {
    display: flex;
    gap: 6px;
    padding: 8px 10px 10px;
    border-top: 1px solid var(--fsf-border);
}
.fsf-cal-today {
    flex: 1;
    padding: 8px;
    border: 1.5px solid var(--fsf-border);
    border-radius: 8px;
    background: var(--fsf-light);
    font-size: 12px;
    font-weight: 600;
    color: var(--fsf-text);
    cursor: pointer;
    transition: all .15s;
}
.fsf-cal-today:hover { background: #e2e8f0; }
.fsf-cal-close {
    flex: 2;
    padding: 8px;
    border: none;
    border-radius: 8px;
    background: var(--fsf-primary);
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    transition: background .15s;
}
.fsf-cal-close:hover { background: var(--fsf-primary2); }

/* Dropdown animation */
.fsf-drop-enter-active { transition: opacity .15s ease, transform .15s ease; }
.fsf-drop-leave-active { transition: opacity .1s ease, transform .1s ease; }
.fsf-drop-enter-from, .fsf-drop-leave-to { opacity: 0; transform: translateY(-6px); }
@media (max-width: 600px) {
    .fsf-drop-enter-from, .fsf-drop-leave-to { transform: translateY(20px); }
}

/* ════════════════════════════════════════
   RESPONSIVE — TABLET  (≤900px)
════════════════════════════════════════ */
@media (max-width: 900px) {
    .fsf-row {
        grid-template-columns: 1fr auto 1fr;
        grid-template-rows: auto auto;
        gap: 8px;
    }

    /* Airports: row 1, cols 1-3 */
    .fsf-from { grid-column: 1; grid-row: 1; }
    .fsf-swap { grid-column: 2; grid-row: 1; }
    .fsf-to   { grid-column: 3; grid-row: 1; }

    /* Dates: row 2, full width */
    .fsf-date        { grid-column: 1 / -1; grid-row: 2; }
    .fsf-date:nth-of-type(4) { grid-column: 1 / 3; }
    .fsf-date:nth-of-type(5) { grid-column: 3 / -1; }

    /* Remove: end of row 2 */
    .fsf-remove { grid-column: -1; grid-row: 2; }
}

/* ════════════════════════════════════════
   RESPONSIVE — MOBILE  (≤600px)
════════════════════════════════════════ */
@media (max-width: 600px) {
    .fsf-body    { padding: 12px 12px 0; }
    .fsf-tab     { font-size: 12px; padding: 10px 4px; gap: 4px; }
    .fsf-tab span { display: inline; font-size: 11px; }  /* icon only on very small */

    .fsf-row {
        grid-template-columns: 1fr;
        grid-template-rows: none;
    }

    .fsf-from,
    .fsf-swap,
    .fsf-to,
    .fsf-date,
    .fsf-remove { grid-column: 1 !important; grid-row: auto !important; }

    .fsf-swap {
        width: 32px; height: 32px;
        margin: -4px auto;
        transform: rotate(90deg);
        position: relative;
        z-index: 2;
    }
    .fsf-swap:hover { transform: rotate(270deg); }

    .fsf-remove {
        width: 100%;
        border-radius: 10px;
        height: auto;
        padding: 8px;
        margin-top: 0;
    }

    .fsf-bottom       { gap: 8px; }
    .fsf-bottom-right { margin-left: 0; width: 100%; }
    .fsf-search-btn   { width: 100%; justify-content: center; padding: 12px; }
    .fsf-pill-btn     { font-size: 12px; }
    .fsf-class-badge  { display: none; }

    .fsf-adv-grid { grid-template-columns: 1fr; }

    .fsf-traveler-panel { width: 260px; left: 0; }
}

/* ════════════════════════════════════════
   RESPONSIVE — XS  (≤380px)
════════════════════════════════════════ */
@media (max-width: 380px) {
    .fsf-tabs { flex-direction: row; }
    .fsf-tab  { padding: 9px 4px; font-size: 11px; }
    .fsf-input { font-size: 13px; }
    .fsf-search-btn { font-size: 13px; }
}

.fsf-al-info  { flex: 1; min-width: 0; }
.fsf-al-name  { font-size: 12px; color: var(--fsf-muted); }
.fsf-al-addr  { font-size: 11px; color: #94a3b8;
    white-space: nowrap; overflow: hidden;
    text-overflow: ellipsis; }

/* ═════════════════════════════════════
   SCROLLBAR
════════════════════════════════════════ */
.fsf-airport-list::-webkit-scrollbar { width: 4px; }
.fsf-airport-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
</style>

<style>
/* Airport dropdown — scoped বাইরে, তাই child এ কাজ করবে */
.fsf-airport-list { max-height: 260px; overflow-y: auto; }

.fsf-al-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #e2e8f0;
    transition: background .15s;
}
.fsf-al-item:last-child { border-bottom: none; }
.fsf-al-item:hover { background: #eff6ff; }

/* Left: name + address */
.fsf-al-info { flex: 1; min-width: 0; }
.fsf-al-name {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}
.fsf-al-addr {
    font-size: 12px; /* 11px → 12px */
    color: #64748b;  /* #94a3b8 → একটু গাঢ় */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

/* Right: icon + code */
.fsf-al-code-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
    min-width: 44px;
}
.fsf-al-code-block i {
    font-size: 10px;
    color: #cbd5e1;
}
.fsf-al-code {
    font-size: 18px;
    font-weight: 800;
    color: #0057ff;
    letter-spacing: .02em;
    line-height: 1;
}

.fsf-al-item:hover { background: #dbeafe; } /* #eff6ff → গাঢ় নীল */
.fsf-al-item:hover .fsf-al-code   { color: #1d4ed8; }
.fsf-al-item:hover .fsf-al-code-block i { color: #1d4ed8; }
.fsf-al-item:hover .fsf-al-name   { color: #1d4ed8; }
.fsf-al-item:hover .fsf-al-addr   { color: #3b82f6; } /* hover এ address ও নীল */

/* Section header */
.fsf-al-section {
    padding: 8px 14px 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    background: #f8fafc;
    display: flex;
    align-items: center;
    gap: 5px;
    border-bottom: 1px solid #e2e8f0;
}

.fsf-al-loading, .fsf-al-empty {
    padding: 20px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.fsf-al-loading i { font-size: 20px; color: #0057ff; }

.fsf-airport-list::-webkit-scrollbar { width: 4px; }
.fsf-airport-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
</style>
