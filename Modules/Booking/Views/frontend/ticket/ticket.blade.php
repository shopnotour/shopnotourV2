@extends('Layout::empty')

@push('css')
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            .no-print { display:none; }
        }
    </style>
@endpush
<!-- Print Button - Floating -->
<div class="fixed bottom-8 right-8 no-print space-x-3 z-50">
    <button onclick="window.print()"
            class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all">
        🖨️ Print Ticket
    </button>
    <button onclick="window.close()"
            class="bg-gray-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all">
        ✕ Close
    </button>
</div>
<div id="invoice-print-zone" class="bg-white shadow-xl mx-auto my-10 p-8 rounded-xl border border-gray-300 max-w-4xl">

    <!-- TOP HEADER -->
    <div class="border-b pb-5">

        <!-- Top row: Logo + Title -->
        <div class="flex justify-between items-start">
            <div>
                @if(!empty($logo = setting_item('logo_invoice_id') ?? setting_item('logo_id')))
                    <img class="max-w-[160px]" src="{{ get_file_url($logo,'full') }}" alt="Logo">
                @endif

                <div class="mt-3 text-gray-600 text-sm leading-5">
                    {!! setting_item_with_lang("invoice_company_info") !!}
                </div>
            </div>

            <!-- QR Code Centered -->
            <div class="flex justify-center mt-4">
                <img src="{{ $booking->qr_code ?? 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.$booking->code }}"
                     class="w-24 h-24 rounded-md border shadow-sm">
            </div>

            <div class="text-right">
                <h1 class="text-3xl font-bold text-red-700 uppercase">E-Ticket</h1>

                <p class="text-gray-600 text-sm">PNR:
                    <span class="font-semibold text-red-700">{{ $booking->code }}</span>
                </p>

                @if(!empty($booking->ticket_number))
                    <p class="text-gray-600 text-sm">
                        Ticket: <span class="font-semibold text-red-700">{{ $booking->ticket_number }}</span>
                    </p>
                @endif

                <p class="text-gray-600 text-sm">Issued:
                    <span class="font-semibold">{{ display_date($booking->created_at) }}</span>
                </p>
            </div>
        </div>

    </div>


    <!-- PASSENGER INFO -->
    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Passenger Information</h2>

    <table class="w-full mt-3 text-sm border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border">Name</th>
            <th class="p-2 border">Gender</th>
            <th class="p-2 border">Type</th>
            <th class="p-2 border">Ticket #</th>
        </tr>
        </thead>

        <tbody>
        @foreach($booking['passengers'] as $p)
            <tr class="hover:bg-gray-50">
                <td class="p-2 border">{{ $p->first_name }} {{ $p->last_name }}</td>
                <td class="p-2 border">{{ $p->gender }}</td>
                <td class="p-2 border">{{ $p->traveler_type }}</td>
                <td class="p-2 border">{{ $booking->ticket_number ?? '--' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- FLIGHT INFORMATION CARD VIEW -->
    <h2 class="text-lg font-semibold text-red-700 mb-3 uppercase border-l-4 border-red-700 pl-2">
        Flight Information
    </h2>

    <div class="space-y-3">

        @foreach($booking['routes'] as $route)
            <div class="border rounded-lg p-4 bg-gray-50 shadow-sm">

                <div class="grid grid-cols-3 gap-3 text-sm">

                    <!-- Departure -->
                    <div>
                        <p class="text-gray-500 text-xs">DEPARTURE</p>
                        <p class="text-xl font-bold text-red-700">{{ $route->departure_iata_code }}</p>
                        <p class="text-gray-700">{{ airport_from_code($route->departure_iata_code) }}</p>
                        @if(!empty($route->departure_terminal))
                            <p class="text-gray-600 text-xs">Terminal:
                                <span class="font-semibold text-red-700">{{ $route->departure_terminal }}</span>
                            </p>
                        @endif
                        <p class="text-gray-600">{{ date('d M, h:i A', strtotime($route->departure_at)) }}</p>
                    </div>

                    <!-- Duration -->
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-gray-500 text-xs">DURATION</p>

                            <div class="flex items-center justify-center space-x-2 my-1">
                                <span class="block w-8 h-0.5 bg-gray-400"></span>
                                ✈️
                                <span class="block w-8 h-0.5 bg-gray-400"></span>
                            </div>

                            <p class="font-semibold text-red-700">{{ $route->duration }}</p>
                        </div>
                    </div>

                    <!-- Arrival -->
                    <div class="text-right">
                        <p class="text-gray-500 text-xs">ARRIVAL</p>
                        <p class="text-xl font-bold text-red-700">{{ $route->arrival_iata_code }}</p>
                        <p class="text-gray-700">{{ airport_from_code($route->arrival_iata_code) }}</p>

                        @if(!empty($route->arrival_terminal))
                            <p class="text-gray-600 text-xs">Terminal:
                                <span class="font-semibold text-red-700">{{ $route->arrival_terminal }}</span>
                            </p>
                        @endif

                        <p class="text-gray-600">{{ date('d M, h:i A', strtotime($route->arrival_at)) }}</p>
                    </div>

                </div>

            </div>
        @endforeach

    </div>


    <!-- FLIGHT TABLE VIEW -->
    {{--    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Flight Information</h2>--}}

    {{--    <table class="w-full mt-3 text-sm border border-gray-300">--}}
    {{--        <thead>--}}
    {{--        <tr class="bg-gray-100">--}}
    {{--            <th class="p-2 border">Flight</th>--}}
    {{--            <th class="p-2 border">Terminal</th>--}}
    {{--            <th class="p-2 border">Departure</th>--}}
    {{--            <th class="p-2 border">Arrival</th>--}}
    {{--            <th class="p-2 border">Time</th>--}}
    {{--            <th class="p-2 border">Duration</th>--}}
    {{--        </tr>--}}
    {{--        </thead>--}}

    {{--        <tbody>--}}
    {{--        @foreach($booking['routes'] as $r)--}}
    {{--            <tr class="hover:bg-gray-50">--}}
    {{--                <td class="p-2 border font-semibold text-red-700">{{ airline_from_code($booking->airline) }}</td>--}}
    {{--                <td class="p-2 border text-red-700 font-semibold">T{{ $r->terminal ?? '—' }}</td>--}}
    {{--                <td class="p-2 border">{{ airport_from_code($r->departure_iata_code) }}</td>--}}
    {{--                <td class="p-2 border">{{ airport_from_code($r->arrival_iata_code) }}</td>--}}
    {{--                <td class="p-2 border">--}}
    {{--                    <div class="flex flex-col text-xs">--}}
    {{--                        <span class="font-semibold text-green-700">Dep: {{ date('d M, h:i A', strtotime($r->departure_at)) }}</span>--}}
    {{--                        <span class="font-semibold text-red-700">Arr: {{ date('d M, h:i A', strtotime($r->arrival_at)) }}</span>--}}
    {{--                    </div>--}}
    {{--                </td>--}}
    {{--                <td class="p-2 border">--}}
    {{--                    <div class="flex items-center gap-2">--}}
    {{--                        <span class="text-red-700 font-semibold">{{ $r->duration }}</span>--}}
    {{--                        ✈️--}}
    {{--                    </div>--}}
    {{--                </td>--}}
    {{--            </tr>--}}
    {{--        @endforeach--}}
    {{--        </tbody>--}}
    {{--    </table>--}}


    <!-- FARE SUMMARY -->
    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Fare Summary</h2>

    <table class="w-full mt-3 text-sm border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border">Passenger Type</th>
            <th class="p-2 border">Fare</th>
            <th class="p-2 border">Tax</th>
            <th class="p-2 border">Total</th>
        </tr>
        </thead>

        <tbody>
        @foreach($booking['passengers'] as $p)
            <tr class="hover:bg-gray-50">
                <td class="p-2 border">{{ $p->traveler_type }}</td>
                <td class="p-2 border">{{ $p->base }} {{ $p->currency }}</td>
                <td class="p-2 border">{{ $p->total - $p->base }} {{ $p->currency }}</td>
                <td class="p-2 border font-semibold text-red-700">{{ $p->total }} {{ $p->currency }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- TERMS -->
    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Terms & Conditions</h2>

    <ul class="text-sm text-gray-700 mt-3 leading-6">
        <li>• This ticket is issued as per airline rules and regulations.</li>
        <li>• Refund & date change charges apply based on the airline fare class.</li>
        <li>• Passenger must carry valid passport, visa, and travel documents.</li>
        <li>• Airline may change flight schedule without prior notice.</li>
        <li>• Baggage allowance is based on the specific airline policy.</li>
        <li>• Name correction is limited and may not be allowed by some airlines.</li>
        <li>• No-show penalties apply if passenger fails to report on time.</li>
    </ul>

</div>
