<template>
    <div class="fc-wrap">

        <!-- NDC Warning Banner -->
        <div v-if="flight.is_ndc" class="fc-ndc-banner">
            <div class="fc-ndc-icon"><i class="fa fa-info-circle"></i></div>
            <div class="fc-ndc-text">
                <span class="fc-ndc-badge">NDC Fare</span>
                Booking confirm এর সময় মূল্য পরিবর্তন হতে পারে। Refund policy airline এর fare rules অনুযায়ী প্রযোজ্য।
            </div>
        </div>

        <!-- CARD BODY -->
        <div class="fc-body">
            <div class="fc-legs-col">
                <div v-for="(leg, legIndex) in legs" :key="'leg-' + legIndex">
                    <div v-if="totalLegs > 1" class="fc-leg-badge" :class="leg.leg_type === 'return' ? 'fc-leg-return' : 'fc-leg-out'">
                        <i :class="getLegIcon(leg.leg_type)"></i>{{ getLegLabel(legIndex) }}
                    </div>
                    <div class="fc-main">
                        <div class="fc-left">
                            <div class="fc-airline">
                                <img v-if="getAirlineLogo(leg)" :src="getAirlineLogo(leg)" :alt="getAirlineName(leg)" class="fc-logo">
                                <div v-else class="fc-logo-fb">{{ getAirlineCode(leg) }}</div>
                                <div class="fc-airline-info">
                                    <span class="fc-airline-name">{{ getAirlineName(leg) }}</span>
                                    <div class="fc-airline-meta">
                                        <span v-if="leg.segments && leg.segments[0]" class="fc-fi-item fc-fi-fn"><i class="fa fa-hashtag"></i>{{ leg.segments[0].full_flight_number }}</span>
                                        <span v-if="leg.segments && leg.segments[0] && leg.segments[0].aircraft_name" class="fc-fi-item"><i class="fa fa-plane-up"></i>{{ leg.segments[0].aircraft_name }}</span>
                                        <span v-if="leg.segments && leg.segments[0] && leg.segments[0].fare_info" class="fc-fi-item fc-fi-cabin">
                                            <i class="fa fa-chair"></i>{{ leg.segments[0].fare_info.cabin_name }}<span v-if="leg.segments[0].fare_info.booking_code" class="fc-fi-class"> ({{ leg.segments[0].fare_info.booking_code }})</span>
                                        </span>
                                        <span v-if="leg.segments && leg.segments[0] && leg.segments[0].fare_info && leg.segments[0].fare_info.seats_available !== null && leg.segments[0].fare_info.seats_available <= 9" class="fc-fi-item fc-fi-seats">
                                            <i class="fa fa-fire"></i>{{ leg.segments[0].fare_info.seats_available }} seats left
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="fc-route">
                                <div class="fc-endpoint">
                                    <div class="fc-time">{{ formatTime(leg.departure && leg.departure.time) }}</div>
                                    <div class="fc-time-12">{{ leg.departure && leg.departure.time_12h }}</div>
                                    <div class="fc-code">{{ leg.departure && leg.departure.airport_code }}</div>
                                    <div class="fc-city">{{ leg.departure && (leg.departure.city || leg.departure.airport_name) }}</div>
                                    <div class="fc-date-sm">{{ formatDateShort(leg.departure && leg.departure.date) }}</div>
                                </div>
                                <div class="fc-mid">
                                    <div class="fc-dur">{{ leg.duration_formatted || formatDuration(leg.duration) }}</div>
                                    <div class="fc-line">
                                        <span class="fc-dot fc-dot-dep"></span><span class="fc-dashes"></span>
                                        <span class="fc-plane-icon"><i class="fa fa-plane"></i></span>
                                        <span class="fc-dashes"></span><span class="fc-dot fc-dot-arr"></span>
                                    </div>
                                    <div class="fc-stops-badge" :class="leg.is_direct ? 'fc-direct' : 'fc-stop'">
                                        {{ leg.is_direct ? 'Non-Stop' : leg.stops + ' Stop' + (leg.stops > 1 ? 's' : '') }}
                                    </div>
                                </div>
                                <div class="fc-endpoint fc-endpoint-right">
                                    <div class="fc-time">{{ formatTime(leg.arrival && leg.arrival.time) }}</div>
                                    <div class="fc-time-12">{{ leg.arrival && leg.arrival.time_12h }}</div>
                                    <div class="fc-code">{{ leg.arrival && leg.arrival.airport_code }}</div>
                                    <div class="fc-city">{{ leg.arrival && (leg.arrival.city || leg.arrival.airport_name) }}</div>
                                    <div class="fc-date-sm">
                                        {{ formatDateShort(leg.arrival && leg.arrival.date) }}
                                        <span v-if="leg.arrival && leg.arrival.date_adjustment > 0" class="fc-next-day">+{{ leg.arrival.date_adjustment }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!leg.is_direct && leg.stops_detail && leg.stops_detail.length" class="fc-layovers">
                                <div v-for="(stop, si) in leg.stops_detail" :key="si" class="fc-layover-pill">
                                    <i class="fa fa-clock"></i>{{ stop.airport_code }} · {{ stop.layover_formatted }}
                                    <span v-if="stop.is_overnight" class="fc-overnight">Overnight</span>
                                </div>
                            </div>
                            <div class="fc-tags">
                                <span class="fc-tag fc-meal"><i class="fa fa-utensils"></i> {{ getMealDescription(leg) }}</span>
                                <span v-if="flight.is_ndc" class="fc-tag fc-ndc-ref"><i class="fa fa-info-circle"></i> Fare Conditions Apply</span>
                                <span v-else-if="flight.refundable" class="fc-tag fc-ref"><i class="fa fa-check-circle"></i> Refundable</span>
                                <span v-else class="fc-tag fc-noref"><i class="fa fa-times-circle"></i> Non-Refundable</span>
                                <span v-if="flight.eTicketable" class="fc-tag fc-etix"><i class="fa fa-ticket"></i> E-Ticket</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="legIndex < totalLegs - 1" class="fc-divider"></div>
                </div>
            </div>

            <!-- RIGHT price panel -->
            <div class="fc-right">
                <div v-if="flight.price && flight.price.tax_note" class="fc-approx">{{ flight.price.tax_note }}</div>
                <div class="fc-price-breakdown">
                    <div class="fc-pb-row"><span>Base Fare</span><span class="fc-pb-val">৳{{ formatPrice(flight.price && flight.price.api_base_fare) }}</span></div>
                    <div class="fc-pb-row"><span>+ Tax</span><span class="fc-pb-val">৳{{ formatPrice(flight.price && flight.price.api_tax) }}</span></div>
                    <div class="fc-pb-row fc-pb-subtotal"><span>= Gross Fare</span><span>৳{{ formatPrice(grossFare) }}</span></div>
                    <div v-if="flight.charges_details && +flight.charges_details.ait_amount" class="fc-pb-row fc-pb-charge">
                        <span>+ AIT<span v-if="flight.charges_details.ait_charge_percentage" class="fc-pb-pct"> ({{ flight.charges_details.ait_charge_percentage }}%)</span></span>
                        <span class="fc-pb-val">৳{{ formatPrice(flight.charges_details.ait_amount) }}</span>
                    </div>
                    <div v-if="flight.charges_details && +flight.charges_details.service_charge" class="fc-pb-row fc-pb-charge">
                        <span>+ Service Charge</span><span class="fc-pb-val">৳{{ formatPrice(flight.charges_details.service_charge) }}</span>
                    </div>
                    <div v-if="hasCharges" class="fc-pb-row fc-pb-subtotal"><span>= Subtotal</span><span>৳{{ formatPrice(subtotalBeforeDiscount) }}</span></div>
                    <div v-if="segmentDiscount > 0 && isLoggedIn" class="fc-pb-row fc-pb-disc">
                        <span><i class="fa fa-tag"></i> Segment Disc.<span v-if="flight.charges_details.segment_discount_label" class="fc-pb-pct"> ({{ flight.charges_details.segment_discount_label }})</span></span>
                        <span>-৳{{ formatPrice(segmentDiscount) }}</span>
                    </div>
                    <div
                        v-if="discountVisible && otherDiscount > 0 && canShowDiscount"
                        class="fc-pb-row fc-pb-disc"
                    >
                        <span><i class="fa fa-tag"></i> Discount<span v-if="flight.charges_details.flight_discount_label" class="fc-pb-pct"> ({{ flight.charges_details.flight_discount_label }})</span></span>
                        <span>-৳{{ formatPrice(otherDiscount) }}</span>
                    </div>
                </div>
                <div class="fc-total">
                    <span class="fc-total-label" style="display: inline-flex; align-items: center; gap: 8px;">
                        You Pay

                        <span
                            v-if="canShowDiscount"
                            @click="discountVisible = !discountVisible"
                            style="cursor: pointer;"
                        >
                            <i :class="discountVisible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" style="color:red;"></i>
                        </span>
                    </span>
                    <span class="fc-total-price">
                        ৳{{
                            formatPrice(
                                isLoggedIn
                                    ? (
                                        discountVisible
                                            ? flight.price.total
                                            : subtotalBeforeDiscount
                                    )
                                    : subtotalBeforeDiscount
                            )
                        }}
                    </span>
                    <!-- <span class="fc-total-pax">per person</span> -->
                </div>
                <div class="fc-btns">
                    <button @click="bookFlight" class="fc-book-btn"><i class="fa fa-bolt"></i> Book Now</button>
                    <div class="fc-actions">
                        <button @click="openModal" class="fc-detail-btn"><i class="fa fa-list"></i> Details</button>
                        <button @click="copyFlightDetails" class="fc-copy-btn" :class="{ copied: isCopied }"><i :class="isCopied ? 'fa fa-check' : 'fa fa-copy'"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOBILE PRICE BAR -->
        <div class="fc-mobile-price">
            <div class="fc-mp-left" @click="showPriceSheet = !showPriceSheet">
                <div class="fc-mp-price-row">
                    <span class="fc-mp-price">৳{{ formatPrice(isLoggedIn ? flight.price.total : subtotalBeforeDiscount) }}</span>
                    <span class="fc-mp-pax">/ person</span>
                </div>
                <div class="fc-mp-breakdown-hint"><i class="fa fa-receipt"></i> View breakdown <i class="fa" :class="showPriceSheet ? 'fa-chevron-down' : 'fa-chevron-up'"></i></div>
            </div>
            <div class="fc-mp-right">
                <button @click="openModal" class="fc-mp-detail"><i class="fa fa-list"></i></button>
                <button @click="copyFlightDetails" class="fc-mp-copy" :class="{ copied: isCopied }"><i :class="isCopied ? 'fa fa-check' : 'fa fa-copy'"></i></button>
                <button @click="bookFlight" class="fc-mp-book">Book <i class="fa fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- MOBILE PRICE SHEET -->
        <transition name="fc-sheet">
            <div v-if="showPriceSheet" class="fc-price-sheet">
                <div class="fc-ps-title"><i class="fa fa-receipt"></i> Price Breakdown<button @click="showPriceSheet = false" class="fc-ps-close"><i class="fa fa-times"></i></button></div>
                <div class="fc-ps-row"><span>Base Fare</span><span>৳{{ formatPrice(flight.price && flight.price.api_base_fare) }}</span></div>
                <div class="fc-ps-row"><span>+ Tax</span><span>৳{{ formatPrice(flight.price && flight.price.api_tax) }}</span></div>
                <div class="fc-ps-row fc-ps-sub"><span>= Gross Fare</span><span>৳{{ formatPrice(grossFare) }}</span></div>
                <template v-if="hasCharges">
                    <div v-if="flight.charges_details && +flight.charges_details.ait_amount" class="fc-ps-row fc-ps-charge"><span>+ AIT</span><span>৳{{ formatPrice(flight.charges_details.ait_amount) }}</span></div>
                    <div v-if="flight.charges_details && +flight.charges_details.service_charge" class="fc-ps-row fc-ps-charge"><span>+ Service Charge</span><span>৳{{ formatPrice(flight.charges_details.service_charge) }}</span></div>
                    <div class="fc-ps-row fc-ps-sub"><span>= Subtotal</span><span>৳{{ formatPrice(subtotalBeforeDiscount) }}</span></div>
                </template>
                <div v-if="segmentDiscount > 0 && isLoggedIn" class="fc-pb-row fc-pb-disc"><span><i class="fa fa-tag"></i> Segment Discount</span><span>-৳{{ formatPrice(segmentDiscount) }}</span></div>
                <div v-if="discountVisible && otherDiscount > 0 && canShowDiscount" class="fc-pb-row fc-pb-disc"><span><i class="fa fa-tag"></i> Discount</span><span>-৳{{ formatPrice(otherDiscount) }}</span></div>
                <div class="fc-ps-total"><span>You Pay</span><span>৳{{
                        formatPrice(
                            isLoggedIn
                                ? (
                                    discountVisible
                                        ? flight.price.total
                                        : subtotalBeforeDiscount
                                )
                                : subtotalBeforeDiscount
                        )
                    }}</span>
                </div>
                <button @click="bookFlight" class="fc-ps-book"><i class="fa fa-bolt"></i> Book Now</button>
            </div>
        </transition>

        <!-- FLIGHT DETAILS MODAL -->
        <transition name="fc-modal-fade">

            <div v-if="showModal" class="fc-modal-overlay" @click.self="closeModal">
                <div class="fc-modal">
                    <div class="fc-mh">
                        <div class="fc-mh-info">
                            <img v-if="legs[0] && getAirlineLogo(legs[0])" :src="getAirlineLogo(legs[0])" class="fc-mh-logo">
                            <div>
                                <div class="fc-mh-route">{{ legs[0] && legs[0].departure && legs[0].departure.airport_code }} <i class="fa fa-arrow-right"></i> {{ legs[legs.length-1] && legs[legs.length-1].arrival && legs[legs.length-1].arrival.airport_code }}</div>
                                <div class="fc-mh-sub">
                                    <span v-for="(p,i) in flight.passengers" :key="i" class="fc-mh-pax">{{ p.count }}× {{ p.type_label }}</span>
                                    <span v-if="flight.is_ndc" class="fc-mh-ndc-badge">NDC</span>
                                </div>
                            </div>
                        </div>
                        <div @click="discountVisible = !discountVisible"
                             style="margin-right: 12px; cursor: pointer;">
                            <i :class="discountVisible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </div>
                        <button @click="closeModal" class="fc-mh-close"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="fc-tabs">
                        <button v-for="tab in modalTabs" :key="tab.key" @click="activeModalTab = tab.key" class="fc-tab" :class="{ active: activeModalTab === tab.key }">
                            <i :class="tab.icon"></i><span>{{ tab.label }}</span>
                        </button>
                    </div>
                    <div class="fc-mb">
                        <!-- FLIGHT INFO -->
                        <div v-if="activeModalTab === 'flight'">
                            <div v-for="(leg, li) in legs" :key="'ml-'+li" class="fc-mb-section">
                                <div class="fc-mb-heading"><i :class="getLegIcon(leg.leg_type)"></i>{{ getLegLabel(li) }}: {{ leg.departure && leg.departure.airport_code }} → {{ leg.arrival && leg.arrival.airport_code }}</div>
                                <div v-for="(seg, si) in (leg.segments || [])" :key="'ms-'+li+'-'+si" class="fc-seg-card">
                                    <div class="fc-seg-head">
                                        <img v-if="seg.carrier_images && seg.carrier_images.thumb" :src="seg.carrier_images.thumb" class="fc-seg-logo">
                                        <div class="fc-seg-head-info"><div class="fc-seg-fn">{{ seg.full_flight_number }}</div><div class="fc-seg-carrier">{{ seg.carrier_name }}</div></div>
                                        <div class="fc-seg-badges">
                                            <span class="fc-seg-badge fc-sb-ac"><i class="fa fa-plane-up"></i> {{ seg.aircraft_name || seg.aircraft || 'N/A' }}</span>
                                            <span v-if="seg.fare_info" class="fc-seg-badge fc-sb-cabin">{{ seg.fare_info.cabin_name }}<span v-if="seg.fare_info.booking_code" class="fc-sb-code">· {{ seg.fare_info.booking_code }}</span></span>
                                            <span v-if="seg.fare_info && seg.fare_info.seats_available !== null && seg.fare_info.seats_available !== undefined" class="fc-seg-badge" :class="seg.fare_info.seats_available <= 5 ? 'fc-sb-hot' : 'fc-sb-seats'">
                                                <i :class="seg.fare_info.seats_available <= 5 ? 'fa fa-fire' : 'fa fa-chair'"></i>{{ seg.fare_info.seats_available }} seats
                                            </span>
                                        </div>
                                    </div>
                                    <div class="fc-seg-tl">
                                        <div class="fc-tl-pt">
                                            <div class="fc-tl-dot dep"></div>
                                            <div>
                                                <div class="fc-tl-time">{{ seg.departure && seg.departure.time_12h }}</div>
                                                <div class="fc-tl-code">{{ seg.departure && seg.departure.airport_code }}</div>
                                                <div class="fc-tl-name">{{ seg.departure && (seg.departure.airport_name || seg.departure.city) }}</div>
                                                <div class="fc-tl-date">{{ formatDate(seg.departure && seg.departure.date) }}</div>
                                                <div v-if="seg.departure && seg.departure.terminal" class="fc-tl-terminal">Terminal {{ seg.departure.terminal }}</div>
                                            </div>
                                        </div>
                                        <div class="fc-tl-mid">
                                            <div class="fc-tl-line"></div><div class="fc-tl-dur">{{ seg.duration_formatted }}</div>
                                            <div class="fc-tl-cabin">{{ seg.fare_info && seg.fare_info.cabin_name }}</div>
                                            <div v-if="seg.miles" class="fc-tl-miles"><i class="fa fa-route"></i> {{ seg.miles }} mi</div>
                                        </div>
                                        <div class="fc-tl-pt">
                                            <div class="fc-tl-dot arr"></div>
                                            <div>
                                                <div class="fc-tl-time">{{ seg.arrival && seg.arrival.time_12h }}<span v-if="seg.arrival && seg.arrival.date_adjustment > 0" class="fc-next-day">+{{ seg.arrival.date_adjustment }}</span></div>
                                                <div class="fc-tl-code">{{ seg.arrival && seg.arrival.airport_code }}</div>
                                                <div class="fc-tl-name">{{ seg.arrival && (seg.arrival.airport_name || seg.arrival.city) }}</div>
                                                <div class="fc-tl-date">{{ formatDate(seg.arrival && seg.arrival.date) }}</div>
                                                <div v-if="seg.arrival && seg.arrival.terminal" class="fc-tl-terminal">Terminal {{ seg.arrival.terminal }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fc-seg-meta">
                                        <span v-if="seg.is_codeshare" class="fc-sm-item fc-sm-cs"><i class="fa fa-code-branch"></i> Codeshare — {{ seg.operating_carrier_name }}</span>
                                        <span v-if="seg.meal_description" class="fc-sm-item"><i class="fa fa-utensils"></i> {{ seg.meal_description }}</span>
                                        <span v-if="seg.eTicketable" class="fc-sm-item"><i class="fa fa-ticket"></i> E-Ticket</span>
                                        <span v-if="seg.fare_info && seg.fare_info.fare_basis_code" class="fc-sm-item fc-sm-fbc"><i class="fa fa-barcode"></i> {{ seg.fare_info.fare_basis_code }}</span>
                                    </div>
                                    <div v-if="seg.layover_after" class="fc-layover-bar" :class="{ overnight: seg.layover_after.is_overnight }">
                                        <i class="fa fa-clock"></i> Layover {{ seg.layover_after.formatted }} · {{ seg.layover_after.airport_code }}
                                        <span v-if="seg.layover_after.is_overnight">· Overnight</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FARE -->
                        <div v-if="activeModalTab === 'fare'">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fc-mb-heading">
                                    <i class="fa fa-users"></i> Fare by Passenger
                                </div>
                            </div>
                            <div class="fc-table-wrap">
                                <table class="fc-table">
                                    <thead><tr><th>Type</th><th>Qty</th><th>Base</th><th>Tax</th><th>Total</th></tr></thead>
                                    <tbody>
                                    <tr v-for="(p,i) in (flight.passengers || [])" :key="i">
                                        <td>{{ p.type_label }}</td><td class="center">{{ p.count }}</td>
                                        <td>৳{{ formatPrice(p.base_fare) }}</td><td>৳{{ formatPrice(p.tax_amount) }}</td>
                                        <td><strong>৳{{ formatPrice(p.total_fare * p.count) }}</strong></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fc-mb-heading mt-16"><i class="fa fa-calculator"></i> Price Calculation</div>
                            <div class="fc-calc">
                                <div class="fc-cr fc-cr-base"><span>Base Fare</span><span>৳{{ formatPrice(flight.price && flight.price.api_base_fare) }}</span></div>
                                <div class="fc-cr fc-cr-base"><span>+ Tax</span><span>৳{{ formatPrice(flight.price && flight.price.api_tax) }}</span></div>
                                <div class="fc-cr fc-cr-sub"><span>= Gross Fare</span><span>৳{{ formatPrice(grossFare) }}</span></div>
                                <template v-if="flight.charges_details && +flight.charges_details.ait_amount"><div class="fc-cr fc-cr-add"><span>+ AIT <span v-if="flight.charges_details.ait_charge_percentage" class="fc-pb-pct">({{ flight.charges_details.ait_charge_percentage }}%)</span></span><span>৳{{ formatPrice(flight.charges_details.ait_amount) }}</span></div></template>
                                <template v-if="flight.charges_details && +flight.charges_details.service_charge"><div class="fc-cr fc-cr-add"><span>+ Service Charge</span><span>৳{{ formatPrice(flight.charges_details.service_charge) }}</span></div></template>
                                <div v-if="hasCharges" class="fc-cr fc-cr-sub"><span>= Subtotal</span><span>৳{{ formatPrice(subtotalBeforeDiscount) }}</span></div>
                                <div v-if="segmentDiscount > 0 && isLoggedIn" class="fc-pb-row fc-pb-disc"><span><i class="fa fa-tag"></i> Segment Disc.</span><span>-৳{{ formatPrice(segmentDiscount) }}</span></div>
                                <div v-if="discountVisible && otherDiscount > 0 && canShowDiscount" class="fc-pb-row fc-pb-disc"><span><i class="fa fa-tag"></i> Discount</span><span>-৳{{ formatPrice(otherDiscount) }}</span></div>
                                <div class="fc-cr fc-cr-total">
                                    <span><strong>You Pay</strong></span>
                                    <span>৳{{ formatPrice(finalPrice) }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- BAGGAGE -->
                        <div v-if="activeModalTab === 'baggage'">
                            <div class="fc-mb-heading"><i class="fa fa-suitcase"></i> Baggage Allowance</div>
                            <div v-for="(p, i) in (flight.passengers || [])" :key="i" class="fc-bag-card" style="margin-bottom:12px">
                                <div class="fc-bag-type">{{ p.type_label }} × {{ p.count }}</div>
                                <div v-if="p.baggage_by_segment && p.baggage_by_segment.length">
                                    <div v-for="(seg, si) in p.baggage_by_segment" :key="si" style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px dashed #f1f5f9;font-size:12px">
                                        <span><i class="fa fa-route"></i> {{ seg.departure }} → {{ seg.arrival }}</span>
                                        <span style="font-weight:700;color:#1d4ed8"><i class="fa fa-suitcase"></i> {{ seg.weight ? seg.weight + ' kg' : seg.piece_count ? seg.piece_count + ' pc' : 'Check airline' }}</span>
                                    </div>
                                </div>
                                <div v-else class="fc-bag-val"><i class="fa fa-suitcase"></i> {{ formatBaggage(p.baggage) }}</div>
                            </div>
                            <div class="fc-notice fc-notice-blue mt-12"><i class="fa fa-info-circle"></i> Cabin baggage ~7kg. Confirm with airline.</div>
                        </div>
                        <!-- PENALTY -->
                        <div v-if="activeModalTab === 'penalty'">
                            <div v-if="flight.is_ndc" class="fc-refund-banner fc-refund-ndc">
                                <i class="fa fa-info-circle"></i>
                                <div><strong>NDC Fare — Fare Conditions Apply</strong><p>এই flight এর refund ও cancellation policy airline confirm করার পর জানা যাবে।</p></div>
                            </div>
                            <div v-else class="fc-refund-banner" :class="flight.refundable ? 'green' : 'red'">
                                <i :class="flight.refundable ? 'fa fa-check-circle' : 'fa fa-times-circle'"></i>
                                <div><strong>{{ flight.refundable ? 'Refundable Fare' : 'Non-Refundable Fare' }}</strong><p>{{ flight.refundable ? 'Refund available subject to airline fees.' : 'This fare cannot be refunded.' }}</p></div>
                            </div>
                            <div class="fc-penalty-grid">
                                <div class="fc-pen-card">
                                    <div class="fc-pen-head red"><i class="fa fa-times-circle"></i> Cancellation</div>
                                    <div class="fc-pen-row"><span>Before Departure</span><span class="red">{{ flight.is_ndc ? 'Check Airline' : 'Airline Fee + Tax' }}</span></div>
                                    <div class="fc-pen-row"><span>No-Show</span><span class="red">{{ flight.is_ndc ? 'Check Airline' : '100% Forfeit' }}</span></div>
                                </div>
                                <div class="fc-pen-card">
                                    <div class="fc-pen-head orange"><i class="fa fa-calendar-alt"></i> Date Change</div>
                                    <div class="fc-pen-row"><span>Before Departure</span><span class="orange">{{ flight.is_ndc ? 'Check Airline' : 'Fare Diff + Fee' }}</span></div>
                                    <div class="fc-pen-row"><span>After Departure</span><span class="red">{{ flight.is_ndc ? 'Check Airline' : 'Not Allowed' }}</span></div>
                                </div>
                            </div>
                            <div v-if="flight.last_ticket_date" class="fc-notice fc-notice-red mt-12"><i class="fa fa-clock"></i> Last Ticketing: {{ formatDate(flight.last_ticket_date) }}</div>
                            <div v-if="flight.is_ndc" class="fc-notice fc-notice-blue mt-12"><i class="fa fa-info-circle"></i> NDC fare rules booking confirm হওয়ার পর airline থেকে সরাসরি প্রযোজ্য হবে।</div>
                        </div>
                        <!-- TAX -->
                        <div v-if="activeModalTab === 'tax'">
                            <div class="fc-mb-heading"><i class="fa fa-receipt"></i> Tax Breakdown</div>
                            <div v-if="flight.taxes_breakdown && flight.taxes_breakdown.length" class="fc-table-wrap">
                                <table class="fc-table">
                                    <thead><tr><th>Code</th><th>Description</th><th class="right">BDT</th></tr></thead>
                                    <tbody>
                                    <tr v-for="(tax,i) in flight.taxes_breakdown" :key="i">
                                        <td><span class="fc-tax-code">{{ tax.code }}</span></td><td class="fc-tax-desc">{{ tax.description }}</td>
                                        <td class="right"><strong>৳{{ formatPrice(tax.amount) }}</strong></td>
                                    </tr>
                                    </tbody>
                                    <tfoot><tr class="fc-tbl-total"><td colspan="2"><strong>Total Tax</strong></td><td class="right"><strong>৳{{ formatPrice(flight.price && flight.price.api_tax) }}</strong></td></tr></tfoot>
                                </table>
                            </div>
                            <div v-else class="fc-notice fc-notice-blue"><i class="fa fa-info-circle"></i> Detailed tax breakdown not available.</div>
                        </div>
                    </div>
                    <div class="fc-mf">
                        <div class="fc-mf-price">
                            <span>Total</span>
                            <strong>৳{{ formatPrice(finalPrice) }}</strong>
                        </div>
                        <div class="fc-mf-btns"><button @click="closeModal" class="fc-mf-sec">Close</button><button @click="bookFlight" class="fc-mf-pri"><i class="fa fa-bolt"></i> Book Now</button></div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- PRICE CONFIRMATION MODAL (Air Arabia / Sabre / NDC) -->
        <transition name="fc-modal-fade">
            <div v-if="showAirArabiaModal" class="fc-modal-overlay">
                <div class="fc-modal">
                    <div class="fc-mh" :style="getModalHeaderStyle()">
                        <div class="fc-mh-info">
                            <i class="fa fa-info-circle" style="color:white;font-size:20px;margin-right:10px"></i>
                            <div>
                                <div class="fc-mh-route">
                                    {{ airArabiaModalSource === 'NDC' ? 'NDC Fare Confirmation' : airArabiaModalSource === 'Sabre' ? 'মূল্য নিশ্চিত করুন' : 'Air Arabia মূল্য' }}
                                </div>
                                <div class="fc-mh-sub" style="font-size:11px;opacity:.85">
                                    <span v-for="(p,i) in (airArabiaModalData?.data?.passengers||[])" :key="i" class="fc-mh-pax">{{ p.count }}× {{ p.type_label }}</span>
                                    <span v-if="airArabiaModalSource === 'NDC'" class="fc-mh-ndc-badge">NDC</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fc-tabs">
                        <button @click="airArabiaModalTab='price'" class="fc-tab" :class="{active:airArabiaModalTab==='price'}"><i class="fa fa-calculator"></i><span>Price</span></button>
                        <button @click="airArabiaModalTab='tax'"   class="fc-tab" :class="{active:airArabiaModalTab==='tax'}"><i class="fa fa-receipt"></i><span>Tax</span></button>
                        <button @click="airArabiaModalTab='pax'"   class="fc-tab" :class="{active:airArabiaModalTab==='pax'}"><i class="fa fa-users"></i><span>Passengers</span></button>
                        <button @click="airArabiaModalTab='bundle'" class="fc-tab" :class="{active:airArabiaModalTab==='bundle'}"><i class="fa fa-boxes"></i><span>Bundle</span></button>
                    </div>

                    <div class="fc-mb" v-if="airArabiaModalData">

                        <!-- PRICE TAB -->
                        <div v-if="airArabiaModalTab==='price'">
                            <!-- Notice -->
                            <div class="fc-notice fc-notice-yellow mb-12">
                                <i class="fa fa-info-circle"></i>
                                <span v-if="airArabiaModalSource === 'NDC'">এটি একটি <strong>NDC fare</strong>। Airline confirmation এর সময় মূল্য পরিবর্তন হতে পারে।</span>
                                <span v-else-if="airArabiaModalSource === 'Air Arabia'">Search এ শুধু Base Fare ছিল। Air Arabia confirmed price এ Tax ও fees যোগ হয়েছে।</span>
                                <span v-else-if="airArabiaModalData.data && airArabiaModalData.data.price_changed">
                                    মূল্য পরিবর্তন হয়েছে।
                                    <span v-if="airArabiaModalData.data.price_diff_type === 'increased'" style="color:#dc2626;font-weight:700">৳{{ formatPrice(Math.abs(airArabiaModalData.data.price_diff)) }} বৃদ্ধি পেয়েছে।</span>
                                    <span v-else-if="airArabiaModalData.data.price_diff_type === 'decreased'" style="color:#16a34a;font-weight:700">৳{{ formatPrice(Math.abs(airArabiaModalData.data.price_diff)) }} কমেছে।</span>
                                </span>
                                <span v-else>মূল্য নিশ্চিত করুন।</span>
                            </div>

                            <!-- Price breakdown -->
                            <div class="fc-calc">
                                <div class="fc-cr fc-cr-base"><span>Search Price (Tax ছাড়া)</span><span class="strike">৳{{ formatPrice(airArabiaModalData.old_price) }}</span></div>
                                <div class="fc-cr fc-cr-base"><span>Base Fare</span><span>৳{{ formatPrice(airArabiaModalData.data.price.api_base_fare) }}</span></div>
                                <div class="fc-cr fc-cr-add"><span>+ Tax</span><span>৳{{ formatPrice(airArabiaModalData.data.price.api_tax) }}</span></div>
                                <div class="fc-cr fc-cr-sub"><span>= Gross Fare</span><span>৳{{ formatPrice(airArabiaModalData.data.price.api_subtotal) }}</span></div>
                                <div class="fc-cr fc-cr-add" v-if="airArabiaModalData.data.charges_details && +airArabiaModalData.data.charges_details.ait_amount">
                                    <span>+ AIT ({{ airArabiaModalData.data.charges_details.ait_charge_percentage }}%)</span>
                                    <span>৳{{ formatPrice(airArabiaModalData.data.charges_details.ait_amount) }}</span>
                                </div>
                                <div class="fc-cr fc-cr-add" v-if="airArabiaModalData.data.charges_details && +airArabiaModalData.data.charges_details.service_charge">
                                    <span>+ Service Charge</span><span>৳{{ formatPrice(airArabiaModalData.data.charges_details.service_charge) }}</span>
                                </div>
                                <div class="fc-cr fc-cr-sub" v-if="airArabiaModalData.data.price.subtotal_before_discount">
                                    <span>= Subtotal</span><span>৳{{ formatPrice(airArabiaModalData.data.price.subtotal_before_discount) }}</span>
                                </div>
                                <div class="fc-cr fc-cr-disc" v-if="+airArabiaModalData.data.price.flight_discount > 0">
                                    <span><i class="fa fa-tag"></i> Flight Discount</span><span>-৳{{ formatPrice(airArabiaModalData.data.price.flight_discount) }}</span>
                                </div>
                                <div class="fc-cr fc-cr-disc" v-if="+airArabiaModalData.data.price.segment_discount > 0">
                                    <span><i class="fa fa-ticket"></i> Segment Discount</span><span>-৳{{ formatPrice(airArabiaModalData.data.price.segment_discount) }}</span>
                                </div>
                                <div class="fc-cr fc-cr-total"><span><strong>Confirmed Price</strong></span><span><strong>৳{{ formatPrice(airArabiaModalData.data.price.total) }}</strong></span></div>
                            </div>

                            <!-- NDC Fare Details -->
                            <template v-if="airArabiaModalSource === 'NDC'">
                                <div class="fc-mb-heading mt-12"><i class="fa fa-shield-alt"></i> Fare Policy</div>

                                <!-- Refund status -->
                                <div class="fc-refund-banner"
                                     :class="airArabiaModalData.data.refundable === true ? 'green' : airArabiaModalData.data.refundable === false ? 'red' : 'fc-refund-ndc'">
                                    <i :class="airArabiaModalData.data.refundable === true ? 'fa fa-check-circle' : airArabiaModalData.data.refundable === false ? 'fa fa-times-circle' : 'fa fa-info-circle'"></i>
                                    <div>
                                        <strong>{{ airArabiaModalData.data.refundable === true ? 'Refundable' : airArabiaModalData.data.refundable === false ? 'Non-Refundable' : 'Fare Conditions Apply' }}</strong>
                                        <p v-if="airArabiaModalData.data.fare_info && (airArabiaModalData.data.fare_info.cancel_fee !== null || airArabiaModalData.data.fare_info.change_fee !== null)">
                                            <span v-if="airArabiaModalData.data.fare_info.cancel_fee !== null">Cancel Fee: {{ airArabiaModalData.data.fare_info.cancel_fee ? 'Yes' : 'No' }} · </span>
                                            <span v-if="airArabiaModalData.data.fare_info.change_fee !== null">Change Fee: {{ airArabiaModalData.data.fare_info.change_fee ? 'Yes' : 'No' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Fare Basis / Brand -->
                                <div v-if="airArabiaModalData.data.fare_info && (airArabiaModalData.data.fare_info.fare_basis || airArabiaModalData.data.fare_info.brand_name)" class="fc-calc mt-12">
                                    <div v-if="airArabiaModalData.data.fare_info.fare_basis" class="fc-cr fc-cr-base"><span>Fare Basis</span><span class="fc-sm-fbc">{{ airArabiaModalData.data.fare_info.fare_basis }}</span></div>
                                    <div v-if="airArabiaModalData.data.fare_info.brand_name" class="fc-cr fc-cr-base"><span>Brand</span><span>{{ airArabiaModalData.data.fare_info.brand_name }}</span></div>
                                </div>

                                <!-- Included Services -->
                                <template v-if="airArabiaModalData.data.services && airArabiaModalData.data.services.length">
                                    <div class="fc-mb-heading mt-12"><i class="fa fa-suitcase"></i> Included Services</div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
                                        <span v-for="(svc, i) in airArabiaModalData.data.services" :key="i" class="fc-sm-item">
                                            <i class="fa fa-check" style="color:#16a34a"></i> {{ svc }}
                                        </span>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- TAX TAB -->
                        <div v-if="airArabiaModalTab==='tax'">
                            <div class="fc-mb-heading"><i class="fa fa-receipt"></i> Tax Breakdown (per Adult)</div>
                            <div v-if="airArabiaModalData.data.taxes_breakdown && airArabiaModalData.data.taxes_breakdown.length" class="fc-table-wrap">
                                <table class="fc-table">
                                    <thead><tr><th>Code</th><th>Description</th><th class="right">BDT</th></tr></thead>
                                    <tbody>
                                    <tr v-for="(tax,i) in (airArabiaModalData.data.taxes_breakdown||[])" :key="i">
                                        <td><span class="fc-tax-code">{{ tax.code }}</span></td><td class="fc-tax-desc">{{ tax.description }}</td>
                                        <td class="right"><strong>৳{{ formatPrice(tax.amount) }}</strong></td>
                                    </tr>
                                    </tbody>
                                    <tfoot><tr class="fc-tbl-total"><td colspan="2"><strong>Total Tax / Adult</strong></td><td class="right"><strong>৳{{ formatPrice(airArabiaModalData.data.taxes_breakdown.reduce((s,t)=>s+t.amount,0)) }}</strong></td></tr></tfoot>
                                </table>
                            </div>
                            <div v-else class="fc-notice fc-notice-blue"><i class="fa fa-info-circle"></i> Tax breakdown not available.</div>
                            <div class="fc-notice fc-notice-blue mt-12"><i class="fa fa-info-circle"></i> Total tax (all passengers) = ৳{{ formatPrice(airArabiaModalData.data.price.api_tax) }}</div>
                        </div>

                        <!-- PASSENGERS TAB -->
                        <div v-if="airArabiaModalTab==='pax'">
                            <div class="fc-mb-heading"><i class="fa fa-users"></i> Per Passenger Breakdown</div>
                            <div class="fc-table-wrap">
                                <table class="fc-table">
                                    <thead><tr><th>Type</th><th class="center">Qty</th><th>Base</th><th>Tax</th><th>Total/Pax</th><th>Subtotal</th></tr></thead>
                                    <tbody>
                                    <tr v-for="(p,i) in (airArabiaModalData.data.passengers||[])" :key="i">
                                        <td><strong>{{ p.type_label }}</strong></td><td class="center">{{ p.count }}</td>
                                        <td>৳{{ formatPrice(p.equivalent_amount) }}</td><td>৳{{ formatPrice(p.tax_amount) }}</td>
                                        <td>৳{{ formatPrice(p.total_fare) }}</td><td><strong>৳{{ formatPrice(p.total_fare * p.count) }}</strong></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-for="(bd,i) in (airArabiaModalData.data.passenger_price_breakdown||[])" :key="'pbd-'+i" class="mt-12">
                                <div class="fc-mb-heading"><i class="fa fa-user"></i> {{ bd.passenger_type }} × {{ bd.passenger_count }} — Charges</div>
                                <div class="fc-calc">
                                    <div class="fc-cr fc-cr-base"><span>API Total</span><span>৳{{ formatPrice(bd.per_pax.api_total) }}</span></div>
                                    <div class="fc-cr fc-cr-add" v-if="+bd.per_pax.ait_amount"><span>+ AIT</span><span>৳{{ formatPrice(bd.per_pax.ait_amount) }}</span></div>
                                    <div class="fc-cr fc-cr-add" v-if="+bd.per_pax.service_charge"><span>+ Service Charge</span><span>৳{{ formatPrice(bd.per_pax.service_charge) }}</span></div>
                                    <div class="fc-cr fc-cr-disc" v-if="+bd.per_pax.user_discount"><span><i class="fa fa-tag"></i> Discount</span><span>-৳{{ formatPrice(bd.per_pax.user_discount) }}</span></div>
                                    <div class="fc-cr fc-cr-disc" v-if="+bd.per_pax.user_seg_discount"><span><i class="fa fa-ticket"></i> Segment Discount</span><span>-৳{{ formatPrice(bd.per_pax.user_seg_discount) }}</span></div>
                                    <div class="fc-cr fc-cr-total"><span><strong>Payable / Pax</strong></span><span><strong>৳{{ formatPrice(bd.per_pax.user_payable) }}</strong></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- BUNDLE TAB -->
                        <div v-if="airArabiaModalTab==='bundle'">
                            <div class="fc-notice fc-notice-blue mb-12"><i class="fa fa-info-circle"></i> Bundle add-ons are optional.</div>
                            <div v-if="!airArabiaModalData.data.bundle_options || !airArabiaModalData.data.bundle_options.length" class="fc-notice fc-notice-blue"><i class="fa fa-info-circle"></i> এই fare এ কোনো bundle option নেই।</div>
                            <div v-for="(bg,gi) in (airArabiaModalData.data.bundle_options||[])" :key="'bg-'+gi" class="mb-12">
                                <div class="fc-mb-heading"><i class="fa fa-route"></i> {{ bg.ond }}</div>
                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px;">
                                    <div v-for="(b,bi) in bg.bundles" :key="'b-'+gi+'-'+bi" style="border:1px solid var(--fc-border);border-radius:10px;overflow:hidden;">
                                        <div style="padding:8px 10px;background:var(--fc-bg);border-bottom:1px solid var(--fc-border);"><div style="font-weight:700;font-size:13px;color:var(--fc-blue);">{{ b.name }}</div><div style="font-size:11px;color:var(--fc-muted);">{{ b.booking_class }}</div></div>
                                        <div style="padding:8px 10px;">
                                            <div style="font-size:15px;font-weight:700;color:#059669;margin-bottom:6px;">+৳{{ formatPrice(b.fee_per_pax) }}/pax</div>
                                            <div v-for="(svc,si) in b.services" :key="si" style="font-size:11px;color:#374151;margin-bottom:2px;display:flex;align-items:center;gap:4px;"><i class="fa fa-check" style="color:#16a34a;font-size:10px;"></i>{{ svc }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="fc-mf">
                        <div class="fc-mf-price"><span>Confirmed Price</span><strong>৳{{ airArabiaModalData ? formatPrice(airArabiaModalData.data.price.total) : 0 }}</strong></div>
                        <div class="fc-mf-btns">
                            <button @click="cancelAirArabia" class="fc-mf-sec">বাতিল</button>
                            <button @click="confirmAirArabia" class="fc-mf-pri"><i class="fa fa-check"></i> {{ airArabiaModalSource === 'NDC' ? 'Confirm & Book' : 'বুক করুন' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Error Modal -->
        <transition name="fc-modal-fade">
            <div v-if="showError" class="fc-modal-overlay" @click.self="showError=false" style="align-items:center;justify-content:center;padding:16px">
                <div style="background:#fff;border-radius:16px;max-width:420px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)">
                    <div style="background:#fef2f2;padding:16px 20px;border-bottom:1px solid #fecaca;display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa fa-exclamation-triangle" style="color:#dc2626;font-size:16px"></i></div>
                        <div style="flex:1"><div style="font-size:14px;font-weight:700;color:#991b1b">বুকিং সমস্যা</div><div style="font-size:11px;color:#ef4444;margin-top:2px">Flight booking error</div></div>
                        <button @click="showError=false" style="width:28px;height:28px;border-radius:50%;background:#fee2e2;border:none;cursor:pointer;color:#dc2626;font-size:12px"><i class="fa fa-times"></i></button>
                    </div>
                    <div style="padding:20px"><p style="font-size:13px;color:#374151;line-height:1.6;margin:0">{{ errorMessage }}</p></div>
                    <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;">
                        <button @click="showError=false" style="padding:8px 16px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;font-size:12px;font-weight:600;cursor:pointer;">বন্ধ করুন</button>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script>
export default {
    name: 'FlightCard',
    props: {
        flight: { type: Object, required: true },
        showDiscount: { type: Boolean, default: false }
    },
    data() {
        return {
            isLoggedIn: window.isLoggedIn === true,
            roleId: window.userRoleId,
            discountVisible: this.showDiscount,
            showModal: false,
            showError: false,
            errorMessage: '',
            showPriceSheet: false,
            activeModalTab: 'flight',
            isCopied: false,
            showAirArabiaModal: false,
            airArabiaModalData: null,
            airArabiaModalTab: 'price',
            airArabiaModalSource: null,
            modalTabs: [
                { key: 'flight',  label: 'Flight',  icon: 'fa fa-plane' },
                { key: 'fare',    label: 'Fare',    icon: 'fa fa-receipt' },
                { key: 'baggage', label: 'Baggage', icon: 'fa fa-suitcase' },
                { key: 'penalty', label: 'Policy',  icon: 'fa fa-shield-alt' },
                { key: 'tax',     label: 'Tax',     icon: 'fa fa-file-invoice' },
            ]
        };
    },
    computed: {
        legs()      { return this.flight.legs || []; },
        totalLegs() { return this.legs.length; },
        grossFare() { return (Number(this.flight.price?.api_base_fare) || 0) + (Number(this.flight.price?.api_tax) || 0); },
        hasCharges() { return (Number(this.flight.charges_details?.ait_amount) || 0) + (Number(this.flight.charges_details?.service_charge) || 0) > 0; },
        subtotalBeforeDiscount() { return this.grossFare + (Number(this.flight.charges_details?.ait_amount) || 0) + (Number(this.flight.charges_details?.service_charge) || 0); },
        segmentDiscount() { return Number(this.flight.charges_details?.segment_discount_total) || 0; },
        otherDiscount()   { return Number(this.flight.flight_discount_details?.flight_discount_amount) || 0; },
        canShowDiscount() {
            return this.isLoggedIn && [1, 2].includes(Number(this.roleId));
        },
        finalPrice() {
            if (!this.isLoggedIn) {
                return Number(this.subtotalBeforeDiscount) || 0;
            }

            // Hide discount
            if (!this.discountVisible) {
                return Number(this.subtotalBeforeDiscount) || 0;
            }

            // Show discount
            return (
                (Number(this.subtotalBeforeDiscount) || 0)
                - (Number(this.segmentDiscount) || 0)
                - (Number(this.otherDiscount) || 0)
            );
        },
    },
    methods: {
        getLegLabel(i) {
            const t = this.legs[i]?.leg_type;
            if (t === 'outbound') return 'Outbound';
            if (t === 'return')   return 'Return';
            return this.totalLegs === 1 ? 'Flight' : 'Leg ' + (i + 1);
        },
        getLegIcon(t) {
            if (t === 'outbound') return 'fa fa-arrow-right';
            if (t === 'return')   return 'fa fa-arrow-left';
            return 'fa fa-plane';
        },
        getAirlineLogo(leg) { return leg.segments?.[0]?.carrier_images?.thumb || null; },
        getAirlineName(leg) { return leg.segments?.[0]?.carrier_name || 'Airline'; },
        getAirlineCode(leg) { return (leg.segments?.[0]?.carrier || '??').substring(0, 2); },
        getMealDescription(leg) { return leg.segments?.[0]?.meal_description || 'Meal'; },
        formatTime(dt) { return dt ? dt.split('+')[0].substring(0, 5) : '--:--'; },
        formatDate(d) {
            if (!d) return '';
            const p = d.split('T')[0].split('-');
            if (p.length !== 3) return d;
            const m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return p[2] + ' ' + m[+p[1] - 1] + ' ' + p[0];
        },
        formatDateShort(d) {
            if (!d) return '';
            const p = d.split('-');
            const m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return p[2] + ' ' + m[+p[1] - 1];
        },
        formatDuration(min) { if (!min) return '0h 0m'; return Math.floor(min / 60) + 'h ' + (min % 60) + 'm'; },
        formatPrice(p) { return p ? parseInt(p).toLocaleString() : '0'; },
        formatBaggage(b) {
            if (!b) return 'Check airline';
            if (b.weight) return b.weight + ' ' + (b.unit || 'kg');
            if (b.piece_count) return b.piece_count + ' pc';
            return 'Check airline';
        },

        getModalHeaderStyle() {
            if (this.airArabiaModalSource === 'NDC') return 'background: linear-gradient(135deg,#1e3a8a,#1d4ed8)';
            return 'background: linear-gradient(135deg,#92400e,#d97706)';
        },

        openModal()  { this.showModal = true; this.activeModalTab = 'flight'; document.body.style.overflow = 'hidden'; },
        closeModal() { this.showModal = false; document.body.style.overflow = ''; },
        confirmAirArabia() { window.location.href = this.airArabiaModalData.redirect_url; },
        cancelAirArabia() {
            if (confirm('বাতিল করবেন?')) { this.showAirArabiaModal = false; this.airArabiaModalData = null; }
        },

        copyFlightDetails() {
            const lines = ['✈ FLIGHT DETAILS', '═'.repeat(32)];
            this.legs.forEach((leg, i) => {
                lines.push(`\n🛫 ${this.getLegLabel(i).toUpperCase()}`);
                lines.push(`Airline: ${this.getAirlineName(leg)}`);
                lines.push(`Route: ${leg.departure?.airport_code} → ${leg.arrival?.airport_code}`);
                lines.push(`Depart: ${this.formatDate(leg.departure?.date)}  ${leg.departure?.time_12h}`);
                lines.push(`Arrive: ${this.formatDate(leg.arrival?.date)}  ${leg.arrival?.time_12h}`);
                lines.push(`Duration: ${leg.duration_formatted || this.formatDuration(leg.duration)}`);
                lines.push(`Stops: ${leg.is_direct ? 'Non-Stop' : leg.stops + ' Stop(s)'}`);
            });
            if (this.flight.price) {
                lines.push('\n💰 PRICE', '─'.repeat(24));
                lines.push(`You Pay: ৳${this.formatPrice(this.flight.price.total)}`);
                lines.push(this.flight.is_ndc ? `Fare Type: NDC (Fare conditions apply)` : `Refundable: ${this.flight.refundable ? '✅ Yes' : '❌ No'}`);
            }
            const text = lines.join('\n');
            navigator.clipboard?.writeText(text).then(() => { this.isCopied = true; setTimeout(() => { this.isCopied = false; }, 2500); }).catch(() => this.fallbackCopy(text));
        },
        fallbackCopy(text) {
            const el = Object.assign(document.createElement('textarea'), { value: text, style: 'position:fixed;opacity:0' });
            document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el);
            this.isCopied = true; setTimeout(() => { this.isCopied = false; }, 2500);
        },

        bookFlight(event) {
            if (!window.isLoggedIn) {
                try { new bootstrap.Modal(document.getElementById('login')).show(); }
                catch { document.querySelector('[data-bs-target="#login"]')?.click(); }
                return;
            }
            const btn = event?.currentTarget;
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing…'; }

            fetch('flight/flightToCart', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ flight: this.flight, service: 'flight' })
            })
                .then(r => r.json())
                .then(data => {

                    if (data.success) {
                        window.location.href = data.redirect_url;
                        return;
                    }

                    // ✅ KEY FIX: NDC check করো data.data.is_ndc থেকে
                    if (data.air_arabia || data.sabre_price) {
                        this.airArabiaModalData = data;
                        this.airArabiaModalSource = data.air_arabia ? 'Air Arabia'
                            : (data.data?.is_ndc ? 'NDC' : 'Sabre');
                        this.airArabiaModalTab = 'price';
                        this.showAirArabiaModal = true;
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> Book Now'; }
                        return;
                    }

                    if (data.price_changed) {
                        this.showError = true;
                        this.errorMessage = 'মূল্য পরিবর্তন হয়েছে। পুরনো মূল্য: ৳' + data.old_price + ' → নতুন মূল্য: ৳' + data.new_api_price + '। অনুগ্রহ করে নতুন করে সার্চ করুন।';
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> Book Now'; }
                        return;
                    }

                    this.showError = true;
                    this.errorMessage = data.message || 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না। আবার চেষ্টা করুন।';
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> Book Now'; }
                })
                .catch(() => {
                    alert('Something went wrong!');
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> Book Now'; }
                });
        }

    }
};
</script>

<style scoped>
.fc-wrap {
    --fc-blue: #1d4ed8; --fc-blue2: #1e3a8a; --fc-green: #16a34a; --fc-red: #DBEAFE;
    --fc-orange: #d97706; --fc-border: #e5e7eb; --fc-bg: #f9fafb; --fc-text: #111827;
    --fc-muted: #6b7280; --fc-radius: 12px; --fl-accent:   #03c5ff; --fc-purple2: #DBEAFE;
    background: #fff; border-radius: var(--fc-radius); border: 1px solid var(--fc-border);
    box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 12px; overflow: hidden;
    font-family: 'Segoe UI', system-ui, sans-serif; font-size: 14px; color: var(--fc-text);
}
.fc-ndc-banner { display:flex; align-items:flex-start; gap:8px; padding:8px 14px; background:#eff6ff; border-bottom:1px solid #bfdbfe; font-size:11px; color:#1e3a8a; line-height:1.5; }
.fc-ndc-icon { color:#1d4ed8; font-size:13px; margin-top:1px; flex-shrink:0; }
.fc-ndc-text { flex:1; }
.fc-ndc-badge { display:inline-block; background:#1d4ed8; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:10px; margin-right:5px; }
.fc-mh-ndc-badge { font-size:10px; font-weight:700; background:rgba(255,255,255,.25); border:1px solid rgba(255,255,255,.4); color:#fff; padding:2px 8px; border-radius:10px; }
.fc-leg-badge { display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:700; padding:5px 14px; letter-spacing:.04em; text-transform:uppercase; }
.fc-leg-out    { background:#eff6ff; color:var(--fc-blue); border-bottom:1px solid #dbeafe; }
.fc-leg-return { background:#fffbeb; color:var(--fc-orange); border-bottom:1px solid #fde68a; }
.fc-body { display:flex; align-items:stretch; }
.fc-legs-col { flex:1; min-width:0; }
.fc-main { display:flex; gap:0; padding:16px 16px 12px; }
.fc-left { flex:1; min-width:0; }
.fc-airline { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.fc-logo { width:44px; height:32px; object-fit:contain; border-radius:6px; border:1px solid var(--fc-border); padding:2px; background:#fff; }
.fc-logo-fb { width:44px; height:32px; background:#e5e7eb; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#374151; }
.fc-airline-name { display:block; font-size:13px; font-weight:700; color:var(--fc-text); }
.fc-airline-meta { display:flex; flex-wrap:wrap; align-items:center; gap:4px; margin-top:4px; }
.fc-route { display:flex; align-items:flex-start; gap:8px; margin-bottom:12px; }
.fc-endpoint { flex:0 0 auto; text-align:center; min-width:64px; }
.fc-endpoint-right { text-align:center; }
.fc-time    { font-size:22px; font-weight:800; line-height:1; color:var(--fc-text); }
.fc-time-12 { font-size:11px; color:var(--fc-muted); }
.fc-code    { font-size:14px; font-weight:700; color:var(--fc-blue); margin-top:2px; }
.fc-city    { font-size:11px; color:var(--fc-muted); max-width:80px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.fc-date-sm { font-size:11px; color:var(--fc-muted); margin-top:2px; }
.fc-next-day { color:var(--fc-red); font-weight:700; font-size:11px; }
.fc-mid { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; padding-top:6px; }
.fc-dur { font-size:11px; font-weight:600; color:var(--fc-muted); }
.fc-line { display:flex; align-items:center; width:100%; gap:2px; color:#9ca3af; font-size:12px; }
.fc-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.fc-dot-dep { background:var(--fc-green); }
.fc-dot-arr { background:var(--fc-blue); }
.fc-dashes { flex:1; border-top:2px dashed #d1d5db; }
.fc-plane-icon { font-size:14px; color:var(--fc-blue); }
.fc-stops-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; margin-top:2px; }
.fc-direct { background:#dcfce7; color:var(--fc-green); }
.fc-stop   { background:#fef3c7; color:var(--fc-orange); }
.fc-fi-item { display:inline-flex; align-items:center; gap:3px; font-size:10px; color:#64748b; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:2px 7px; white-space:nowrap; }
.fc-fi-item i { font-size:9px; opacity:.7; }
.fc-fi-fn    { color:var(--fc-blue); border-color:#bfdbfe; background:#eff6ff; font-weight:600; }
.fc-fi-cabin { color:#4b5563; font-weight:600; }
.fc-fi-class { opacity:.7; font-weight:400; }
.fc-fi-seats { color:var(--fc-blue); border-color:#fecaca; background:#fef2f2; font-weight:700; }
.fc-layovers { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
.fc-layover-pill { display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:20px; padding:3px 10px; }
.fc-overnight { font-weight:700; color:var(--fc-red); }
.fc-tags { display:flex; flex-wrap:wrap; gap:6px; }
.fc-tag  { display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:3px 8px; border-radius:20px; }
.fc-meal  { background:#f0fdf4; color:var(--fc-green); }
.fc-ref   { background:#dcfce7; color:var(--fc-green); }
.fc-noref { background:#fee2e2; color:var(--fc-red); }
.fc-etix  { background:#eff6ff; color:var(--fc-blue); }
.fc-ndc-ref { background:#eff6ff; color:#1d4ed8; border:1px dashed #93c5fd; }
.fc-right { flex:0 0 240px; border-left:1.5px solid var(--fc-border); padding:16px 16px 14px; display:flex; flex-direction:column; align-items:stretch; background:#fafbff; }
.fc-price-breakdown { display:flex; flex-direction:column; gap:2px; flex:1; }
.fc-approx { font-size:11px; font-weight:700; background:linear-gradient(90deg,#0a3afa,#f80404,#f1bf09); -webkit-background-clip:text; background-clip:text; color:transparent; background-size:200% auto; animation:fc-grad 4s linear infinite; text-align:center; margin-bottom:6px; }
@keyframes fc-grad { to { background-position:200% center; } }
.fc-pb-row { display:flex; justify-content:space-between; align-items:center; font-size:11px; color:var(--fc-muted); padding:3px 0; gap:4px; }
.fc-pb-val { font-weight:600; color:var(--fc-text); }
.fc-pb-pct { font-size:10px; opacity:.75; }
.fc-pb-charge { color:#92400e; }
.fc-pb-subtotal { font-weight:700; color:var(--fc-text); border-top:1px solid var(--fc-border); border-bottom:1px solid var(--fc-border); padding:4px 0; margin:2px 0; font-size:11.5px; }
.fc-pb-disc { color:var(--fc-green); font-weight:600; }
.fc-total { display:flex; flex-direction:column; align-items:flex-start; border-top:2px solid var(--fc-border); padding-top:10px; margin-top:8px; }
.fc-total-label { font-size:11px; color:var(--fc-muted); }
.fc-total-price { font-size:24px; font-weight:800; color:var(--fc-blue); line-height:1.2; }
.fc-total-pax   { font-size:10px; color:var(--fc-muted); }
.fc-btns { display:flex; flex-direction:column; gap:6px; margin-top:10px; }
.fc-book-btn { width:100%; padding:9px; background:var(--fc-red); color:#000; border: 1px solid #667cb9 ; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; transition:background .2s; }
.fc-book-btn:hover { background:#2f63f3; color:#fff; }
.fc-actions { display:flex; gap:6px; }
.fc-detail-btn { flex:1; padding:7px; background:#eff6ff; color:var(--fc-blue); border:1px solid #bfdbfe; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:4px; }
.fc-detail-btn:hover { background:var(--fc-blue); color:#fff; }
.fc-copy-btn { width:34px; background:#f3f4f6; border:1px solid var(--fc-border); border-radius:8px; color:var(--fc-muted); font-size:13px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; }
.fc-copy-btn:hover { background:#e0f2fe; color:#0284c7; }
.fc-copy-btn.copied { background:#dcfce7; color:var(--fc-green); border-color:#86efac; }
.fc-mobile-price { display:none; }
.fc-divider { border-top:2px dashed var(--fc-border); margin:0 16px; }
.fc-price-sheet { background:#fff; border-top:1.5px solid #dbeafe; padding:0 14px 14px; font-size:13px; }
.fc-ps-title { display:flex; align-items:center; gap:6px; font-size:12px; font-weight:700; color:var(--fc-blue); padding:10px 0 8px; border-bottom:1px solid var(--fc-border); margin-bottom:8px; }
.fc-ps-close { margin-left:auto; width:24px; height:24px; border:none; background:#f3f4f6; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:11px; color:var(--fc-muted); }
.fc-ps-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; font-size:12px; color:var(--fc-muted); border-bottom:1px dashed #f1f5f9; }
.fc-ps-sub { font-weight:700; color:var(--fc-text); background:#f8fafc; margin:2px -14px; padding:5px 14px; border-top:1px solid var(--fc-border); border-bottom:1px solid var(--fc-border); }
.fc-ps-charge { color:#92400e; }
.fc-ps-total { display:flex; justify-content:space-between; align-items:center; background:#eff6ff; margin:8px -14px 10px; padding:10px 14px; font-size:15px; font-weight:800; color:var(--fc-blue); }
.fc-ps-book { width:100%; padding:11px; background:var(--fc-red); color:#000; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; }
.fc-sheet-enter-active,.fc-sheet-leave-active { transition:all .22s ease; overflow:hidden; }
.fc-sheet-enter-from,.fc-sheet-leave-to { opacity:0; max-height:0; }
.fc-sheet-enter-to,.fc-sheet-leave-from { opacity:1; max-height:500px; }
.fc-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:99999; display:flex; align-items:flex-end; padding:0; backdrop-filter:blur(2px); }
@media (min-width:640px) { .fc-modal-overlay { align-items:center; padding:16px; } }
.fc-modal { background:#fff; width:100%; max-width:740px; margin:0 auto; border-radius:20px 20px 0 0; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 -8px 40px rgba(0,0,0,.2); }
@media (min-width:640px) { .fc-modal { border-radius:16px; max-height:88vh; } }
.fc-modal-fade-enter-active,.fc-modal-fade-leave-active { transition:all .3s ease; }
.fc-modal-fade-enter-from,.fc-modal-fade-leave-to { opacity:0; transform:translateY(40px); }
@media (min-width:640px) { .fc-modal-fade-enter-from,.fc-modal-fade-leave-to { transform:scale(.96); } }
.fc-mh { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:linear-gradient(135deg,var(--fc-blue2),var(--fc-blue)); color:#fff; flex-shrink:0; }
.fc-mh-info { display:flex; align-items:center; gap:10px; flex:1; }
.fc-mh-logo { width:36px; height:28px; object-fit:contain; border-radius:6px; background:#fff; padding:2px; }
.fc-mh-route { font-size:17px; font-weight:700; }
.fc-mh-sub { display:flex; flex-wrap:wrap; gap:5px; margin-top:3px; align-items:center; }
.fc-mh-pax { font-size:11px; background:rgba(255,255,255,.2); padding:2px 8px; border-radius:10px; }
.fc-mh-close { width:30px; height:30px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.3); border-radius:50%; color:#fff; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.fc-tabs { display:flex; background:var(--fc-bg); border-bottom:1px solid var(--fc-border); overflow-x:auto; flex-shrink:0; scrollbar-width:none; }
.fc-tabs::-webkit-scrollbar { display:none; }
.fc-tab { flex:1; min-width:64px; display:flex; flex-direction:column; align-items:center; gap:3px; padding:10px 8px; border:none; background:transparent; font-size:10px; font-weight:600; color:var(--fc-muted); cursor:pointer; transition:all .2s; border-bottom:2px solid transparent; white-space:nowrap; }
.fc-tab i { font-size:14px; }
.fc-tab.active { color:var(--fc-blue); border-bottom-color:var(--fc-blue); background:#fff; }
.fc-tab:hover:not(.active) { background:#eff6ff; color:var(--fc-blue); }
.fc-mb { flex:1; min-height:0; overflow-y:auto; overflow-x:hidden; padding:16px; scrollbar-width:thin; scrollbar-color:#cbd5e1 #f1f5f9; }
.fc-mb::-webkit-scrollbar { width:4px; }
.fc-mb::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
.fc-mb-section { margin-bottom:20px; }
.fc-mb-heading { display:flex; align-items:center; gap:6px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#374151; padding-bottom:6px; border-bottom:2px solid var(--fc-border); margin-bottom:12px; }
.mt-16 { margin-top:16px; }
.mt-12 { margin-top:12px; }
.mb-12 { margin-bottom:12px; }
.fc-seg-card { border:1px solid var(--fc-border); border-radius:10px; margin-bottom:10px; overflow:hidden; }
.fc-seg-head { display:flex; align-items:flex-start; gap:10px; padding:10px 12px; background:var(--fc-bg); border-bottom:1px solid var(--fc-border); flex-wrap:wrap; }
.fc-seg-logo { width:36px; height:26px; object-fit:contain; border-radius:4px; border:1px solid var(--fc-border); background:#fff; padding:2px; flex-shrink:0; }
.fc-seg-head-info { flex:1; min-width:0; }
.fc-seg-fn { font-size:13px; font-weight:700; color:var(--fc-blue2); }
.fc-seg-carrier { font-size:11px; color:var(--fc-muted); }
.fc-seg-badges { display:flex; flex-wrap:wrap; gap:4px; align-items:center; margin-left:auto; }
.fc-seg-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:20px; white-space:nowrap; }
.fc-sb-ac    { background:#f3f4f6; color:#374151; }
.fc-sb-cabin { background:#eff6ff; color:var(--fc-blue); }
.fc-sb-code  { font-weight:400; opacity:.75; }
.fc-sb-seats { background:#dcfce7; color:var(--fc-green); }
.fc-sb-hot   { background:#fef2f2; color:var(--fc-red); }
.fc-seg-tl { display:flex; align-items:flex-start; gap:8px; padding:12px; }
.fc-tl-pt { display:flex; gap:8px; flex:1; }
.fc-tl-dot { width:10px; height:10px; border-radius:50%; margin-top:4px; flex-shrink:0; }
.fc-tl-dot.dep { background:var(--fc-green); }
.fc-tl-dot.arr { background:var(--fc-blue); }
.fc-tl-time { font-size:15px; font-weight:700; }
.fc-tl-terminal { font-size:10px; color:var(--fc-muted); background:#f3f4f6; display:inline-block; padding:1px 6px; border-radius:6px; margin-top:2px; }
.fc-tl-miles { font-size:10px; color:var(--fc-muted); margin-top:3px; text-align:center; }
.fc-seg-meta { display:flex; flex-wrap:wrap; gap:6px; padding:8px 12px 10px; border-top:1px solid var(--fc-border); background:#fafbff; }
.fc-sm-item { display:inline-flex; align-items:center; gap:4px; font-size:11px; color:var(--fc-muted); background:#fff; border:1px solid var(--fc-border); border-radius:10px; padding:3px 8px; }
.fc-sm-cs  { color:var(--fc-orange); background:#fffbeb; border-color:#fde68a; }
.fc-sm-fbc { font-family:monospace; font-size:11px; color:var(--fc-blue2); background:#eff6ff; border-color:#bfdbfe; }
.fc-tl-code { font-size:13px; font-weight:600; color:#374151; }
.fc-tl-name { font-size:11px; color:var(--fc-muted); }
.fc-tl-date { font-size:10px; color:#9ca3af; }
.fc-tl-mid { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; padding-top:4px; }
.fc-tl-line { width:100%; height:2px; background:#d1d5db; border-radius:1px; }
.fc-tl-dur  { font-size:11px; font-weight:600; color:#4b5563; }
.fc-tl-cabin { font-size:10px; color:#9ca3af; text-transform:uppercase; }
.fc-layover-bar { margin:0 12px 10px; padding:6px 10px; background:#fffbeb; border-left:3px solid var(--fc-orange); border-radius:0 6px 6px 0; font-size:11px; color:#713f12; display:flex; align-items:center; gap:6px; }
.fc-layover-bar.overnight { background:#fef2f2; border-left-color:var(--fc-red); color:#7f1d1d; }
.fc-calc { border:1px solid var(--fc-border); border-radius:10px; overflow:hidden; }
.fc-cr { display:flex; justify-content:space-between; padding:8px 12px; font-size:12px; border-bottom:1px solid #f3f4f6; }
.fc-cr:last-child { border-bottom:none; }
.fc-cr-base  { background:var(--fc-bg); color:#374151; }
.fc-cr-add   { background:#fffbeb; color:#92400e; }
.fc-cr-sub   { background:#f3f4f6; font-weight:600; }
.fc-cr-disc  { background:#f0fdf4; color:var(--fc-green); }
.fc-cr-total { background:#eff6ff; font-size:13px; color:var(--fc-blue2); border-top:2px solid #bfdbfe; }
.strike { text-decoration:line-through; color:var(--fc-red); }
.fc-table-wrap { overflow-x:auto; border-radius:8px; border:1px solid var(--fc-border); }
.fc-table { width:100%; border-collapse:collapse; font-size:12px; min-width:320px; }
.fc-table th { background:#f3f4f6; padding:7px 10px; text-align:left; font-weight:600; border-bottom:1px solid var(--fc-border); white-space:nowrap; }
.fc-table td { padding:7px 10px; border-bottom:1px solid #f9fafb; }
.fc-table tr:last-child td { border-bottom:none; }
.fc-table .center { text-align:center; }
.fc-table .right  { text-align:right; }
.fc-tbl-total td { background:#eff6ff; font-weight:600; border-top:2px solid #bfdbfe; }
.fc-tax-code { font-family:monospace; background:#f3f4f6; padding:1px 5px; border-radius:3px; font-size:11px; color:var(--fc-blue2); }
.fc-tax-desc { font-size:11px; color:var(--fc-muted); }
.fc-bag-card { border:1px solid var(--fc-border); border-radius:8px; padding:10px; }
.fc-bag-type { font-size:11px; font-weight:600; color:var(--fc-muted); margin-bottom:4px; }
.fc-bag-val  { font-size:13px; font-weight:700; color:var(--fc-blue); }
.fc-refund-banner { display:flex; align-items:flex-start; gap:10px; padding:12px; border-radius:10px; margin-bottom:14px; font-size:12px; }
.fc-refund-banner i { font-size:18px; margin-top:1px; }
.fc-refund-banner p { margin:4px 0 0; font-size:11px; opacity:.8; }
.fc-refund-banner.green { background:#f0fdf4; color:var(--fc-green); border:1px solid #bbf7d0; }
.fc-refund-banner.red   { background:#fef2f2; color:var(--fc-red); border:1px solid #fecaca; }
.fc-refund-ndc { background:#eff6ff; color:#1e3a8a; border:1px solid #bfdbfe; }
.fc-penalty-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.fc-pen-card { border:1px solid var(--fc-border); border-radius:8px; overflow:hidden; }
.fc-pen-head { padding:8px 12px; font-size:11px; font-weight:700; display:flex; align-items:center; gap:6px; }
.fc-pen-head.red    { background:#fef2f2; color:#991b1b; border-bottom:1px solid #fecaca; }
.fc-pen-head.orange { background:#fffbeb; color:#92400e; border-bottom:1px solid #fde68a; }
.fc-pen-row { display:flex; justify-content:space-between; padding:6px 12px; font-size:11px; border-bottom:1px solid #f9fafb; }
.fc-pen-row:last-child { border-bottom:none; }
.fc-pen-row .red    { color:var(--fc-red); font-weight:600; }
.fc-pen-row .orange { color:var(--fc-orange); font-weight:600; }
@media (max-width:420px) { .fc-penalty-grid { grid-template-columns:1fr; } }
.fc-notice { display:flex; align-items:flex-start; gap:8px; padding:9px 12px; border-radius:8px; font-size:12px; }
.fc-notice-blue   { background:#eff6ff; color:var(--fc-blue2); border:1px solid #bfdbfe; }
.fc-notice-red    { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.fc-notice-yellow { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
.fc-mf { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid var(--fc-border); background:var(--fc-bg); flex-shrink:0; }
.fc-mf-price { display:flex; flex-direction:column; }
.fc-mf-price span { font-size:11px; color:var(--fc-muted); }
.fc-mf-price strong { font-size:20px; font-weight:800; color:var(--fc-blue); }
.fc-mf-btns { display:flex; gap:8px; }
.fc-mf-sec { padding:8px 16px; background:#fff; border:1.5px solid var(--fc-border); border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; }
.fc-mf-pri { padding:8px 18px; background:var(--fc-red); color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:5px; }
.fc-mf-pri:hover { background:#b91c1c; }
@media (max-width:900px) { .fc-right { flex:0 0 200px; padding:12px; } .fc-time { font-size:20px; } .fc-total-price { font-size:20px; } }
@media (max-width:640px) {
    .fc-body { flex-direction:column; } .fc-main { padding:12px; gap:0; } .fc-right { display:none !important; }
    .fc-mobile-price { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:linear-gradient(135deg,#eff6ff,#fff); border-top:1px solid #dbeafe; }
    .fc-mp-left { display:flex; flex-direction:column; gap:1px; cursor:pointer; }
    .fc-mp-price-row { display:flex; align-items:baseline; gap:4px; }
    .fc-mp-price { font-size:20px; font-weight:800; color:var(--fc-blue); }
    .fc-mp-pax { font-size:10px; color:var(--fc-muted); }
    .fc-mp-breakdown-hint { font-size:11px; color:var(--fc-blue); display:flex; align-items:center; gap:4px; opacity:.75; }
    .fc-mp-right { display:flex; gap:6px; align-items:center; }
    .fc-mp-detail,.fc-mp-copy { width:34px; height:34px; border-radius:8px; border:1px solid var(--fc-border); background:#fff; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--fc-muted); }
    .fc-mp-detail { color:var(--fc-blue); border-color:#bfdbfe; background:#eff6ff; }
    .fc-mp-copy.copied { background:#dcfce7; color:var(--fc-green); border-color:#86efac; }
    .fc-mp-book { padding:8px 14px; background:var(--fc-red); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:5px; }
    .fc-time { font-size:18px; } .fc-code { font-size:13px; } .fc-city { font-size:10px; max-width:60px; } .fc-endpoint { min-width:52px; } .fc-route { gap:4px; }
    .fc-tag { font-size:10px; padding:2px 6px; }
    .fc-seg-tl { flex-direction:column; gap:10px; } .fc-tl-mid { width:100%; flex-direction:row; gap:8px; }
    .fc-mf { flex-direction:column; gap:10px; } .fc-mf-btns { display:grid; grid-template-columns:1fr 1fr; width:100%; } .fc-mf-price { align-items:center; }
    .fc-penalty-grid { grid-template-columns:1fr; }
}
@media (max-width:380px) {
    .fc-main { padding:10px; } .fc-time { font-size:16px; } .fc-endpoint { min-width:44px; } .fc-tags { gap:4px; }
    .fc-tab span { display:none; } .fc-tab i { font-size:16px; } .fc-tab { padding:10px 6px; }
}
</style>
