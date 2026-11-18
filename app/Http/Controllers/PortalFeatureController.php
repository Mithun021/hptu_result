<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PortalFeature;

class PortalFeatureController extends Controller
{
    public function index()
    {
        $settings = PortalFeature::first();

        // Default: all features active if settings record does not exist
        if (!$settings) {
            $settings = new PortalFeature([
                'cutlist' => 1,
                'attendance_sheet' => 1,
                'profile' => 1,
            ]);
        }

        return view('backend.setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Get existing record or create a new one
        $settings = PortalFeature::firstOrNew();

        // Checked = 1, Unchecked = 0
        $settings->cutlist = $request->boolean('cutlist');
        $settings->attendance_sheet = $request->boolean('attendance_sheet');
        $settings->profile = $request->boolean('profile');

        $settings->save();

        return redirect()
            ->route('setting.index')
            ->with('success', 'Portal settings updated successfully.');
    }
}
