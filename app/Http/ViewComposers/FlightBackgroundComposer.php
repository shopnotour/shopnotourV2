<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Modules\Media\Models\Banner;
use Modules\News\Models\News;
use Intervention\Image\Facades\Image as InterventionImage;
use Modules\News\Models\NewsCategory;

class FlightBackgroundComposer
{
    public function compose(View $view)
    {
        $flightBgImages = Cache::remember('flight_bg_images', 3600, function () {

            $banners = Banner::getActive();

            $backgroundImages = [];

            foreach ($banners as $banner) {
                $imageUrl = get_file_url($banner->image_id, 'full');
                if ($imageUrl) {
                    $backgroundImages[] = $imageUrl;
                }
            }

            if (empty($backgroundImages)) {
                return $this->getFallbackImages();
            }

            return $backgroundImages;
        });

        $view->with('flightBgImages', $flightBgImages);
    }

    private function getFallbackImages()
    {
        return [
            'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=600&fit=crop&q=80',
            'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=1920&h=600&fit=crop&q=80',
            'https://images.unsplash.com/photo-1583922178096-356c8888d746?w=1920&h=600&fit=crop&q=80',
        ];
    }

    /**
     * Resize news image to 1920x600
     */
    private function resizeNewsImage($imageId)
    {
        // ✅ Original image URL
        $originalUrl = get_file_url($imageId, 'full');

        if (!$originalUrl) {
            return null;
        }

        // ✅ Resized file path
        $resizedFileName = 'flight-bg-' . $imageId . '.jpg';
        $resizedPath = 'uploads/flight-backgrounds/' . $resizedFileName;
        $fullResizedPath = public_path($resizedPath);

        // ✅ যদি already resize করা থাকে
        if (file_exists($fullResizedPath)) {
            return asset($resizedPath);
        }

        // ✅ Original path
        $originalPath = str_replace(url('/'), '', $originalUrl);
        $fullOriginalPath = public_path($originalPath);

        if (!file_exists($fullOriginalPath)) {
            \Log::warning('Original image not found: ' . $fullOriginalPath);
            return null;
        }

        // ✅ Create directory
        $dir = dirname($fullResizedPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ✅ Resize and save
        try {
            InterventionImage::make($fullOriginalPath)
                ->fit(1920, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($fullResizedPath, 85);

            \Log::info('Flight BG resized: ' . $resizedPath);

            return asset($resizedPath);

        } catch (\Exception $e) {
            \Log::error('Image resize failed for ID ' . $imageId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fallback images if no news images available
     */
//    private function getFallbackImages()
//    {
//        return [
//            'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=600&fit=crop&q=80',
//            'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=1920&h=600&fit=crop&q=80',
//            'https://images.unsplash.com/photo-1583922178096-356c8888d746?w=1920&h=600&fit=crop&q=80',
//        ];
//    }
}
