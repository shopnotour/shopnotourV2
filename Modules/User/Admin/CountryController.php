<?php

namespace Modules\User\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $rows = Country::orderBy('name')->get();

        return view('User::admin.country.index', compact('rows'));
    }

    public function create()
    {
        return view('User::admin.country.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|size:2|unique:countries,code',
            'code3'            => 'nullable|string|size:3|unique:countries,code3',
            'capital'          => 'nullable|string|max:100',
            'phone_code'       => 'nullable|string|max:10',
            'flag_emoji'       => 'nullable|string|max:10',
            'passport_min'     => 'nullable|integer|min:1|max:30',
            'passport_max'     => 'nullable|integer|min:1|max:30|gte:passport_min',
            'passport_pattern' => 'nullable|string|max:255',
            'passport_hint'    => 'nullable|string|max:500',
            'is_active'        => 'boolean',
        ]);

        $validated['code']  = strtoupper($validated['code']);
        $validated['code3'] = isset($validated['code3']) ? strtoupper($validated['code3']) : null;

        Country::create($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', __('Country created successfully.'));
    }

    public function edit(Country $country)
    {
        return view('User::admin.country.form', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|size:2|unique:countries,code,' . $country->id,
            'code3'            => 'nullable|string|size:3|unique:countries,code3,' . $country->id,
            'capital'          => 'nullable|string|max:100',
            'phone_code'       => 'nullable|string|max:10',
            'flag_emoji'       => 'nullable|string|max:10',
            'passport_min'     => 'nullable|integer|min:1|max:30',
            'passport_max'     => 'nullable|integer|min:1|max:30|gte:passport_min',
            'passport_pattern' => 'nullable|string|max:255',
            'passport_hint'    => 'nullable|string|max:500',
            'is_active'        => 'boolean',
        ]);

        $validated['code']  = strtoupper($validated['code']);
        $validated['code3'] = isset($validated['code3']) ? strtoupper($validated['code3']) : null;

        $country->update($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', __('Country updated successfully.'));
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('admin.countries.index')
            ->with('success', __('Country deleted successfully.'));
    }

    // ── Quick toggle active status (optional AJAX endpoint) ──
    public function toggleStatus(Country $country)
    {
        $country->update(['is_active' => !$country->is_active]);

        return response()->json([
            'status'    => true,
            'is_active' => $country->is_active,
            'message'   => 'Status updated.',
        ]);
    }
}


