<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(): View
    {
        $features = Feature::query()->ordered()->get();

        return view('admin.features.index', compact('features'));
    }

    public function update(Request $request, Feature $feature): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Feature::STATUSES)],
            'is_public' => 'sometimes|boolean',
        ]);

        $previous = $feature->status;

        $feature->update([
            'status' => $data['status'],
            'is_public' => $request->boolean('is_public'),
        ]);

        AuditLog::record('feature.status.updated', $feature, [
            'from' => $previous,
            'to' => $data['status'],
        ]);

        return back()->with('status', "\"{$feature->name}\" is now ".str_replace('_', ' ', $data['status']).'.');
    }
}
