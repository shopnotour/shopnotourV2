@extends ('layouts.app')
@section ('content')
    @if($row->template_id)
{{--        <div class="container py-4">--}}
{{--            <div class="row">--}}
{{--                <div class="col-md-8 mx-auto">--}}
                    <div class="page-template-content" >
                        {!! $row->getProcessedContent() !!}
                    </div>
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="page-template-content">--}}
{{--            <div class="container py-4">--}}

{{--                @php--}}
{{--                    $baseUrl = url('http://127.0.0.1:8000');--}}
{{--                    $today = now()->format('Y-m-d');--}}
{{--                @endphp--}}

{{--                <div class="row">--}}
{{--                    <div class="col-md-8 mx-auto">--}}

{{--                        <!-- Section Heading -->--}}
{{--                        <div class="text-center mb-4">--}}
{{--                            <h2 class="font-weight-bold mb-2">Popular Destinations</h2>--}}
{{--                            <p class="text-muted mb-0">Discover the most visited places around the world</p>--}}
{{--                        </div>--}}

{{--                        <!-- Cards Row -->--}}
{{--                        <div class="row">--}}
{{--                            <!-- Card 1 -->--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ $baseUrl }}/flight?_token=C1NuSVbDnMcG9rY54PQtQRngU6rFv3GwmeZZGFFj&trip_type=oneway&segments[0][from]=61&segments[0][to]=8382&segments[0][departure]={{ $today }}&return_date=&adults=1&children=0&infants=0&travel_class=ECONOMY" class="btn btn-primary">--}}
{{--                                <a href="{{ url('{{$baseUrl}}/flight?_token=C1NuSVbDnMcG9rY54PQtQRngU6rFv3GwmeZZGFFj&trip_type=oneway&segments%5B0%5D%5Bfrom%5D=61&segments%5B0%5D%5Bto%5D=8382&segments%5B0%5D%5Bdeparture%5D=2025-11-13&return_date=&adults=1&children=0&infants=0&travel_class=ECONOMY&adults=1&children=0&infants=0&travel_class=ECONOMY&travelClass=ECONOMY') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=1" class="card-img-top" alt="Santorini">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Santorini, Greece</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <!-- Card 2 -->--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/bali') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=2" class="card-img-top" alt="Bali">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Bali, Indonesia</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <!-- Card 3 -->--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/paris') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=3" class="card-img-top" alt="Paris">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Paris, France</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <!-- Card 4 -->--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/dubai') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=4" class="card-img-top" alt="Dubai">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Dubai, UAE</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/dubai') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=5" class="card-img-top" alt="Dubai">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Dubai, UAE</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/dubai') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=6" class="card-img-top" alt="Dubai">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Dubai, UAE</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/dubai') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=7" class="card-img-top" alt="Dubai">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Dubai, UAE</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 col-sm-6 mb-4">--}}
{{--                                <a href="{{ url('destinations/dubai') }}" class="text-decoration-none text-dark">--}}
{{--                                    <div class="card h-100 shadow-sm border-0">--}}
{{--                                        <img src="https://picsum.photos/350/200?random=8" class="card-img-top" alt="Dubai">--}}
{{--                                        <div class="card-body text-center">--}}
{{--                                            <h5 class="card-title mb-0">Dubai, UAE</h5>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}



    @else
        <div class="container " style="padding-top: 40px;padding-bottom: 40px;">
            <h1>{!! clean($translation->title) !!}</h1>
            <div class="blog-content">
                {!! $translation->content !!}
            </div>
        </div>
    @endif
@endsection
