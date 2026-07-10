<?php

namespace AHATechnocrats\OmicsLogic\Support;

use Illuminate\Support\Facades\Storage;

class UserProfileAvatar
{
    public static function html(string $name, ?string $image = null, int $size = 36): string
    {
        if ($image) {
            $url = e(Storage::url($image));
            $alt = e($name);

            return '<img src="'.$url.'" alt="'.$alt.'" style="height:'.$size.'px;width:'.$size.'px;flex-shrink:0;border-radius:9999px;object-fit:cover;" />';
        }

        $iconSize = max(12, (int) round($size * 0.39));

        return '<div style="display:flex;height:'.$size.'px;width:'.$size.'px;flex-shrink:0;align-items:center;justify-content:center;border-radius:9999px;background-color:#e5e7eb;color:#6b7280;">'
            .'<i class="fa fa-user" style="font-size:'.$iconSize.'px;"></i>'
            .'</div>';
    }
}
