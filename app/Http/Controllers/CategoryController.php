<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
        }
        $categories = $query->orderBy('name')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:categories,code',
            'description' => 'nullable|string',
            'tolerance_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $category = Category::create($data);
        AuditLog::log('create', Category::class, $category->id, null, $data);

        return redirect('/admin/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:categories,code,' . $category->id,
            'description' => 'nullable|string',
            'tolerance_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $oldValues = $category->toArray();
        $category->update($data);
        AuditLog::log('update', Category::class, $category->id, $oldValues, $data);

        return redirect('/admin/categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->items()->withTrashed()->count() > 0 || $category->items()->count() > 0) {
            // Soft disable instead of hard delete
            $category->update(['tolerance_percentage' => $category->tolerance_percentage]);
            AuditLog::log('soft_delete', Category::class, $category->id, $category->toArray());
            $category->delete();
            return back()->with('success', 'Kategori diarsipkan (soft delete) karena masih terkait item.');
        }
        AuditLog::log('delete', Category::class, $category->id, $category->toArray());
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
