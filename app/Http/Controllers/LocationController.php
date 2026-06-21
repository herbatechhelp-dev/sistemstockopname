<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query();
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('warehouse', 'like', "%{$search}%")
                  ->orWhere('block', 'like', "%{$search}%");
        }
        $locations = $query->orderBy('warehouse')->orderBy('block')->orderBy('rack')->paginate(15);
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse' => 'required|string|max:100',
            'block' => 'required|string|max:50',
            'rack' => 'required|string|max:50',
            'row' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $location = Location::create($data);
        AuditLog::log('create', Location::class, $location->id, null, $data);

        return redirect('/admin/locations')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.form', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse' => 'required|string|max:100',
            'block' => 'required|string|max:50',
            'rack' => 'required|string|max:50',
            'row' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $oldValues = $location->toArray();
        $location->update($data);
        AuditLog::log('update', Location::class, $location->id, $oldValues, $data);

        return redirect('/admin/locations')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        if ($location->soEntries()->count() > 0) {
            return back()->with('error', 'Lokasi tidak dapat dihapus karena sudah digunakan dalam data SO.');
        }
        AuditLog::log('delete', Location::class, $location->id, $location->toArray());
        $location->delete();
        return back()->with('success', 'Lokasi berhasil dihapus.');
    }
}
