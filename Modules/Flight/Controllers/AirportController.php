<?php


    namespace Modules\Flight\Controllers;


    use Auth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\AdminController;
    use Modules\Flight\Imports\AirportImportIATA;
    use Modules\Flight\Models\Airport;
    use Modules\Flight\Models\Flight;
    use Modules\Flight\Models\SeatType;
    use Modules\Flight\Resources\AirportResource;
    use Modules\Location\Models\Location;

    class AirportController extends AdminController
    {
        /**
         * @var string
         */
        private $airport;
        /**
         * @var string
         */
        private $location;

        /**
         * @var string
         */

        public function __construct()
        {
            $this->location = Location::class;
            $this->airport = Airport::class;
        }

        public function search(Request $request)
        {
            $q = trim($request->get('search'));

            $airports = Airport::query()
                // code exact match সবার আগে
                ->orderByRaw("CASE
            WHEN code = ? THEN 1
            WHEN code LIKE ? THEN 2
            ELSE 3
            END", [
                    strtoupper($q),
                    strtoupper($q) . '%',
                ])
                ->where(function($query) use ($q) {
                    $query->where('code', 'LIKE', "%{$q}%")
                        ->orWhere('name', 'LIKE', "%{$q}%")
                        ->orWhere('address', 'LIKE', "%{$q}%");
                })
                ->limit(15)
                ->get(['id', 'name', 'code']);

            return response()->json($airports);
        }
//        public function search(Request $request)
//        {
////            return $request;
//            $q = $request->get('search');
//            //   $q = $request->get('q');
//            $airports = Airport::query()
//                ->where('name', 'LIKE', "{$q}%")
////                ->orWhere('address', 'LIKE', "%{$q}%")
//                ->orWhere('code', 'LIKE', "{$q}")
//                ->limit(15)
//                ->get(['id', 'name', 'code']);
//
//            return response()->json($airports);
//
//        }
        public function getAirportById($id)
        {
            $airport = Airport::find($id);

            if ($airport) {
                return response()->json([
                    'id' => $airport->id,
                    'code' => $airport->code,
                    'name' => $airport->name,
                    'city' => $airport->city
                ]);
            }

            return response()->json(null, 404);
        }

    }
