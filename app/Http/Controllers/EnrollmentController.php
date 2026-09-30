<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function show(string $token): View
    {
        $device = Device::where('agent_token', $token)->firstOrFail();

        return view('enroll.show', compact('device'));
    }
}
