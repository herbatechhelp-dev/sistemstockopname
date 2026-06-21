<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('key')->get();
        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($data['settings'] as $key => $value) {
            $old = SystemSetting::get($key);
            SystemSetting::set($key, $value);
            AuditLog::log('update_setting', SystemSetting::class, null, [$key => $old], [$key => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
