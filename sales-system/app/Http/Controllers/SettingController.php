<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings form
     */
    public function index()
    {
        $settings = Setting::getSettings();
        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat_toko' => 'required|string',
            'telepon_toko' => 'required|string|max:20',
            'email_toko' => 'nullable|email|max:255',
            'logo_toko' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency' => 'required|string|max:10'
        ]);

        $settings = Setting::getSettings();
        
        $data = $request->only([
            'nama_toko',
            'alamat_toko', 
            'telepon_toko',
            'email_toko',
            'currency'
        ]);

        if ($request->hasFile('logo_toko')) {
            // Delete old logo
            if ($settings->logo_toko && Storage::exists('public/settings/' . $settings->logo_toko)) {
                Storage::delete('public/settings/' . $settings->logo_toko);
            }
            
            $logo = $request->file('logo_toko');
            $logoName = time() . '_logo.' . $logo->getClientOriginalExtension();
            $logo->storeAs('public/settings', $logoName);
            $data['logo_toko'] = $logoName;
        }

        if ($settings->exists) {
            $settings->update($data);
        } else {
            Setting::create($data);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
