<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {

        $menus = Menu::select(['id', 'name', 'description', 'category', 'calories'])
            ->orderBy('id', 'asc')
            ->get();

        return view('index', compact('menus')); 
    }
}
