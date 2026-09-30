<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $plans = Plan::query()->active()->ordered()->get();
        $deviceCount = $user->devices()->count();

        return view('billing.index', compact('user', 'plans', 'deviceCount'));
    }
}
