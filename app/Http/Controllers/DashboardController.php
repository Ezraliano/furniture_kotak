<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Cukup arahkan ke halaman Dashboard.vue
        return Inertia::render('Dashboard');
    }
}