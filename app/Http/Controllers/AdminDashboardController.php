<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function schools()
    {
        return view('admin.schools');
    }

    public function sppg()
    {
        return view('admin.sppg');
    }

    public function students()
    {
        return view('admin.students');
    }

    public function users()
    {
        return view('admin.users');
    }
}
