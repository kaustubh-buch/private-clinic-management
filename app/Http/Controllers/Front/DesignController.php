<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class DesignController extends Controller
{
    public function pages($page)
    {
        return view('front.'.$page);
    }
}
