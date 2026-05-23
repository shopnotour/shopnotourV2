<?php
namespace App\Http\Controllers;

use App\User;
use Modules\Booking\Models\Booking;
use Modules\Flight\Models\Airline;
use Modules\Hotel\Models\Hotel;
use Modules\Location\Models\Location;
use Modules\Location\Models\LocationCategory;
use Modules\Page\Models\Page;
use Modules\News\Models\NewsCategory;
use Modules\News\Models\Tag;
use Modules\News\Models\News;
use Modules\Media\Models\Banner; // Add this import
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    private function getFlightBackgroundImages()
    {
        // Get active banners from your banner system
        $banners = Banner::where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();
        
        $backgroundItems = [];
        
        foreach($banners as $banner) {
            if ($banner->type == 'image' && $banner->image_id) {
                $backgroundItems[] = [
                    'type' => 'image',
                    'url' => get_file_url($banner->image_id, 'full'),
                    'title' => $banner->title,
                    'link' => $banner->link
                ];
            } elseif ($banner->type == 'video' && $banner->video) {
                $backgroundItems[] = [
                    'type' => 'video',
                    'url' => asset('uploads/video/' . $banner->video),
                    'title' => $banner->title,
                    'link' => $banner->link
                ];
            }
        }
        
        // If no banners found, use default images
        if (empty($backgroundItems)) {
            $backgroundItems = [
                [
                    'type' => 'image',
                    'url' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920',
                    'title' => 'Fly with Us',
                    'link' => null
                ],
                [
                    'type' => 'image',
                    'url' => 'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=1920',
                    'title' => 'Explore the World',
                    'link' => null
                ],
                [
                    'type' => 'image',
                    'url' => 'https://images.unsplash.com/photo-1583922178096-356c8888d746?w=1920',
                    'title' => 'Best Deals',
                    'link' => null
                ],
            ];
        }
        
        return $backgroundItems;
    }
    
    public function index()
    {
        // Get banners for slider (both images and videos)
        $flightBgItems = $this->getFlightBackgroundImages();
        
        // ✅ 1. Partners (Airlines with images)
        $partners = Airline::with('airlineImage')
            ->whereNotNull('image_id')
            ->limit(12)
            ->get()
            ->map(function($airline) {
                return [
                    'id' => $airline->id,
                    'name' => $airline->name,
                    'logo' => $airline->image_id ? get_file_url($airline->image_id, 'medium') : asset('images/default-airline.png')
                ];
            });

        $famousDestinations = ['Dubai', 'London', 'Singapore', 'Bangkok', 'Istanbul', 'Paris', 'New York', 'Kuala Lumpur'];

        $destinations = Location::whereNotNull('image_id')
            ->where('status', 'publish')
            ->where(function($query) use ($famousDestinations) {
                foreach($famousDestinations as $destination) {
                    $query->orWhere('name', 'LIKE', "%{$destination}%");
                }
            })
            ->limit(8)
            ->get()
            ->map(function($location) {
                $airportCodes = [
                    'Dubai' => 'DXB',
                    'London' => 'LHR',
                    'Singapore' => 'SIN',
                    'Bangkok' => 'BKK',
                    'Istanbul' => 'IST',
                    'Paris' => 'CDG',
                    'New York' => 'JFK',
                    'Kuala Lumpur' => 'KUL'
                ];

                $code = 'N/A';
                foreach($airportCodes as $city => $airportCode) {
                    if(stripos($location->name, $city) !== false) {
                        $code = $airportCode;
                        break;
                    }
                }

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $code,
                    'price' => rand(299, 999),
                    'image' => get_file_url($location->image_id, 'large')
                ];
            });

        // If no locations found, use fallback
        if($destinations->isEmpty()) {
            $destinations = collect([
                ['id' => 1, 'name' => 'Dubai', 'code' => 'DXB', 'price' => 599, 'image' => asset('images/destinations/dubai.jpg')],
                ['id' => 2, 'name' => 'London', 'code' => 'LHR', 'price' => 799, 'image' => asset('images/destinations/london.jpg')],
                ['id' => 3, 'name' => 'Singapore', 'code' => 'SIN', 'price' => 499, 'image' => asset('images/destinations/singapore.jpg')],
                ['id' => 4, 'name' => 'Bangkok', 'code' => 'BKK', 'price' => 299, 'image' => asset('images/destinations/bangkok.jpg')],
            ]);
        }

        // ✅ 3. Features (Static)
        $features = [
            [
                'id' => 1,
                'icon' => 'fas fa-dollar-sign',
                'title' => 'Best Price Guarantee',
                'description' => 'Find the lowest fares or we\'ll refund the difference',
                'color' => '#3b82f6'
            ],
            [
                'id' => 2,
                'icon' => 'fas fa-clock',
                'title' => 'Instant Confirmation',
                'description' => 'Get your tickets confirmed within minutes',
                'color' => '#10b981'
            ],
            [
                'id' => 3,
                'icon' => 'fas fa-award',
                'title' => 'Trusted by Millions',
                'description' => 'Join millions of happy travelers worldwide',
                'color' => '#8b5cf6'
            ]
        ];

        // ✅ 4. Testimonials (Static or from bookings)
        $testimonials = $this->getTestimonials();

        // ✅ 5. Stats (Optional)
        $stats = [
            'total_airlines' => Airline::count(),
            'total_bookings' => Booking::count(),
            'happy_customers' => Booking::where('status', 'confirmed')->distinct('email')->count('email')
        ];

        return view('Page::frontend.home', compact(
            'flightBgItems',
            'partners',
            'destinations',
            'features',
            'testimonials',
            'stats'
        ));
    }

    /**
     * Get testimonials from recent bookings
     */
    private function getTestimonials()
    {
        $bookings = Booking::where('status', 'confirmed')
            ->whereNotNull('first_name')
            ->latest()
            ->limit(6)
            ->get();

        if ($bookings->isEmpty()) {
            // Static testimonials if no bookings
            return [
                ['id' => 1, 'name' => 'Ahmed Rahman', 'initials' => 'AR', 'rating' => 5, 'review' => 'Excellent service! Quick and easy booking process.', 'type' => 'Business Traveler', 'avatar_color' => '#3b82f6'],
                ['id' => 2, 'name' => 'Fatima Khan', 'initials' => 'FK', 'rating' => 5, 'review' => 'Best prices and great customer support!', 'type' => 'Frequent Flyer', 'avatar_color' => '#10b981'],
                ['id' => 3, 'name' => 'Karim Hossain', 'initials' => 'KH', 'rating' => 5, 'review' => 'Highly recommended for all travelers!', 'type' => 'Family Traveler', 'avatar_color' => '#8b5cf6'],
            ];
        }

        $colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6'];
        $types = ['Business Traveler', 'Frequent Flyer', 'Vacation Planner', 'Family Traveler'];
        $reviews = [
            'Amazing service! Booking was smooth and flight was perfect.',
            'Great experience! Will definitely book again.',
            'Excellent prices and quick confirmation!',
            'Very professional and helpful staff.',
            'Best flight booking platform I\'ve used!',
            'Highly satisfied with the service quality.'
        ];

        return $bookings->map(function($booking, $index) use ($colors, $types, $reviews) {
            return [
                'id' => $booking->id,
                'name' => $booking->first_name . ' ' . ($booking->last_name ?? ''),
                'initials' => strtoupper(substr($booking->first_name, 0, 1) . substr($booking->last_name ?? 'X', 0, 1)),
                'rating' => 5,
                'review' => $reviews[array_rand($reviews)],
                'type' => $types[array_rand($types)],
                'avatar_color' => $colors[$index % count($colors)]
            ];
        })->toArray();
    }
    
    public function indexold()
    {
        $home_page_id = setting_item('home_page_id');
        if($home_page_id && $page = Page::where("id",$home_page_id)->where("status","publish")->first())
        {

            $this->setActiveMenu($page);
            $translation = $page->translate();
            $seo_meta = $page->getSeoMetaWithTranslation(app()->getLocale(), $translation);
            $seo_meta['full_url'] = url("/");
            $seo_meta['is_homepage'] = true;
            $data = [
                'row'=>$page,
                "seo_meta"=> $seo_meta,
                'translation'=>$translation,
                'is_home' => true,
            ];

//            return $data;
//            return view('Page::frontend.home',$data);
            return view('Page::frontend.detail',$data);
        }
        $model_News = News::where("status", "publish");
        $data = [
            'rows'=>$model_News->paginate(5),
            'model_category'    => NewsCategory::where("status", "publish"),
            'model_tag'         => Tag::query(),
            'model_news'        => News::where("status", "publish"),
            'breadcrumbs' => [
                ['name' => __('News'), 'url' => url("/news") ,'class' => 'active'],
            ],
            "seo_meta" => News::getSeoMetaForPageList()
        ];
        return view('News::frontend.index',$data);
    }

    public function checkConnectDatabase(Request $request){
        $connection = $request->input('database_connection');
        config([
            'database' => [
                'default' => $connection."_check",
                'connections' => [
                    $connection."_check" => [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ],
                ],
            ],
        ]);
        try {
            DB::connection()->getPdo();
            $check = DB::table('information_schema.tables')->where("table_schema","performance_schema")->get();
            if(empty($check) and $check->count() == 0){
                return $this->sendSuccess(false , __("Access denied for user!. Please check your configuration."));
            }
            if(DB::connection()->getDatabaseName()){
                return $this->sendSuccess(false , __("Yes! Successfully connected to the DB: ".DB::connection()->getDatabaseName()));
            }else{
                return $this->sendSuccess(false , __("Could not find the database. Please check your configuration."));
            }
        } catch (\Exception $e) {
            return $this->sendError( $e->getMessage() );
        }
    }
}