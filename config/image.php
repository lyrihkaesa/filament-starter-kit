<?php

declare(strict_types=1);

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default image driver for Intervention Image.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'gd') === 'imagick' 
        ? ImagickDriver::class 
        : GdDriver::class,

];
