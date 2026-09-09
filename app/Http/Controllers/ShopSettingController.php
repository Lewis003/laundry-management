<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopSettingController extends Controller
{
    /**
     * Show shop configuration and branding settings.
     */
    public function edit(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators / Owners can modify shop settings.');

        $setting = ShopSetting::current();

        return view('settings.index', compact('setting'));
    }

    /**
     * Update shop configuration and branding.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only Administrators / Owners can modify shop settings.');

        $validated = $request->validate([
            'shop_name'             => ['required', 'string', 'max:150'],
            'tagline'               => ['required', 'string', 'max:200'],
            'location'              => ['required', 'string', 'max:255'],
            'phone'                 => ['required', 'string', 'max:50'],
            'email'                 => ['nullable', 'email', 'max:100'],
            'mpesa_till_or_paybill' => ['nullable', 'string', 'max:50'],
            'receipt_footer'        => ['nullable', 'string', 'max:500'],
            'currency'              => ['nullable', 'string', 'max:10'],
            'tax_percent'           => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (empty($validated['currency'])) {
            $validated['currency'] = 'KSh';
        }

        $setting = ShopSetting::current();
        $setting->update($validated);

        return redirect()->route('settings.edit')->with('success', "Shop branding & location settings updated successfully.");
    }
}

