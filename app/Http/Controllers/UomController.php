<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class UomController extends Controller
{
    public function index(Request $request)
    {
        $query = Uom::query();
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")->orWhere('abbreviation', 'like', "%{$search}%");
        }
        $uoms = $query->orderBy('name')->paginate(15);
        return view('admin.uoms.index', compact('uoms'));
    }

    public function create()
    {
        return view('admin.uoms.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
        ]);

        $uom = Uom::create($data);
        AuditLog::log('create', Uom::class, $uom->id, null, $data);

        return redirect('/admin/uoms')->with('success', 'UoM berhasil ditambahkan.');
    }

    public function edit(Uom $uom)
    {
        return view('admin.uoms.form', compact('uom'));
    }

    public function update(Request $request, Uom $uom)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
        ]);

        $oldValues = $uom->toArray();
        $uom->update($data);
        AuditLog::log('update', Uom::class, $uom->id, $oldValues, $data);

        return redirect('/admin/uoms')->with('success', 'UoM berhasil diperbarui.');
    }

    public function destroy(Uom $uom)
    {
        if ($uom->items()->count() > 0) {
            return back()->with('error', 'UoM tidak dapat dihapus karena masih digunakan oleh item.');
        }
        AuditLog::log('delete', Uom::class, $uom->id, $uom->toArray());
        $uom->delete();
        return back()->with('success', 'UoM berhasil dihapus.');
    }
}
