<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SPPGDashboardController extends Controller
{
    public function index()
    {
        return view('sppg.dashboard');
    }

    public function menu()
    {
        return view('sppg.menu');
    }

    public function selections()
    {
        return view('sppg.selections');
    }

    public function review()
    {
        return view('sppg.review');
    }
}
