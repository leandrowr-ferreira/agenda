<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {

    }

    public function client()
    {
        $user = Auth::user();

        return view('private/client/dashboard');
    }
}
