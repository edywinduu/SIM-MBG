<?php

namespace App\Http\Controllers\SPPG;

use Illuminate\View\View;

class MenuController extends BaseSppgController
{
    public function index(): View
    {
        return view('sppg.menu');
    }
}
