<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        return view('student.dashboard');
    }

    public function menu()
    {
        return view('student.menu');
    }

    public function history()
    {
        return view('student.history');
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function review()
    {
        return view('student.review');
    }
}
