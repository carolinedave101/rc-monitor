<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount(['devices', 'alerts'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['devices' => fn ($query) => $query->latest()])
            ->load(['alerts' => fn ($query) => $query->latest()->limit(10)])
            ->load('serviceSteps');

        return view('admin.users.show', compact('user'));
    }
}
