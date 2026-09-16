<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index(Request $request)
    {
        $gallery = [
            asset('assets/images/about-gallery-1.png'),
            asset('assets/images/about-gallery-4.png'),
            asset('assets/images/about-gallery-3.png'),
            asset('assets/images/about-gallery-2.png'),
        ];

        return view('user.about', compact('gallery'));
    }
}