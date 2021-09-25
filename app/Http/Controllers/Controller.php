<?php

namespace App\Http\Controllers;

use App\Concerns\HandleApiResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use HandleApiResponse;
}
