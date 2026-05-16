<?php
namespace Modules\Flight\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Modules\Flight\Models\Gds;
use Modules\Media\Models\MediaFile;

class GdsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Gds::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $imgGdsImage = DB::table('media_files')->where('file_name','like','airline-%')->get()->pluck(['id'])->toArray();
        return [
            'name'=>$this->faker->city,
            'image_id'=>$this->faker->randomElement($imgAirLineImage)
        ];
    }

}
