<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JourneyController extends Controller
{
    public function __invoke(): View
    {
        $steps = Auth::user()->serviceSteps()->get();

        return view('journey.index', compact('steps'));
    }
}
