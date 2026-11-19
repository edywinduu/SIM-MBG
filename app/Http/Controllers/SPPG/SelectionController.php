<?php

namespace App\Http\Controllers\SPPG;

use Illuminate\View\View;

class SelectionController extends BaseSppgController
{
    public function index(): View
    {
        return view('sppg.selections');
    }
}
