<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Uom;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'uom']);
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
        }
        if ($category = $request->get('category_id')) {
            $query->where('category_id', $category);
        }
        $items = $query->orderBy('sku')->paginate(15);
        $categories = Category::orderBy('name')->get();
        return view('admin.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $uoms = Uom::orderBy('name')->get();
        return view('admin.items.form', compact('categories', 'uoms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:items,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'uom_id' => 'required|exists:uoms,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $item = Item::create($data);
        AuditLog::log('create', Item::class, $item->id, null, $data);

        return redirect('/admin/items')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->get();
        $uoms = Uom::orderBy('name')->get();
        return view('admin.items.form', compact('item', 'categories', 'uoms'));
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:items,sku,' . $item->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'uom_id' => 'required|exists:uoms,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $oldValues = $item->toArray();
        $item->update($data);
        AuditLog::log('update', Item::class, $item->id, $oldValues, $data);

        return redirect('/admin/items')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->soEntries()->count() > 0) {
            return back()->with('error', 'Item tidak dapat dihapus karena sudah digunakan dalam data SO.');
        }
        AuditLog::log('delete', Item::class, $item->id, $item->toArray());
        $item->delete();
        return back()->with('success', 'Item berhasil dihapus.');
    }

    // API endpoint for barcode/search lookup (used by field entry)
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $items = Item::with(['category', 'uom'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('sku', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($items);
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        $categories = Category::orderBy('name')->get();
        $uoms = Uom::orderBy('name')->get();
        return view('admin.items.import', compact('categories', 'uoms'));
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Material');

        // Headers with styling
        $headers = ['Nama Item', 'Kategori', 'UoM'];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header row
        $headerRange = 'A1:C1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Example data
        $examples = [
            ['Tepung Terigu', 'Raw Material', 'kg'],
            ['Roti Tawar', 'Finish Good', 'pcs'],
            ['Karton Box', 'Packaging', 'box'],
        ];
        $sheet->fromArray($examples, null, 'A2');

        // Style example rows (light gray)
        $sheet->getStyle('A2:C4')->applyFromArray([
            'font' => ['color' => ['rgb' => '666666']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);

        // --- Reference sheet ---
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');

        $refSheet->setCellValue('A1', 'Kategori Tersedia');
        $refSheet->getStyle('A1')->getFont()->setBold(true);
        $refSheet->setCellValue('A2', 'Nama');
        $refSheet->setCellValue('B2', 'Kode');
        $row = 3;
        foreach (Category::orderBy('name')->get() as $cat) {
            $refSheet->setCellValue("A{$row}", $cat->name);
            $refSheet->setCellValue("B{$row}", $cat->code);
            $row++;
        }

        $refSheet->setCellValue("D1", 'UoM Tersedia');
        $refSheet->getStyle('D1')->getFont()->setBold(true);
        $refSheet->setCellValue('D2', 'Nama');
        $refSheet->setCellValue('E2', 'Singkatan');
        $row = 3;
        foreach (Uom::orderBy('name')->get() as $uom) {
            $refSheet->setCellValue("D{$row}", $uom->name);
            $refSheet->setCellValue("E{$row}", $uom->abbreviation);
            $row++;
        }

        $refSheet->getColumnDimension('A')->setWidth(25);
        $refSheet->getColumnDimension('B')->setWidth(12);
        $refSheet->getColumnDimension('D')->setWidth(25);
        $refSheet->getColumnDimension('E')->setWidth(12);

        // Write to temp file and return
        $writer = new Xlsx($spreadsheet);
        $tempPath = storage_path('app/template_import_item.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath, 'template_import_item.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Process Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return back()->with('error', 'File Excel tidak dapat dibaca: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'File Excel kosong atau hanya berisi header.');
        }

        // Read and map header
        $headerRow = array_shift($rows);
        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_values($headerRow));
        $colMap = $this->mapColumns($header);

        if (!$colMap) {
            return back()->with('error', 'Format kolom tidak dikenali. Gunakan: Nama Item, Kategori, UoM');
        }

        // Pre-load categories and UoMs for matching
        $categories = Category::all()->keyBy(fn($c) => strtolower($c->name));
        $categoriesByCode = Category::all()->keyBy(fn($c) => strtolower($c->code));
        $uoms = Uom::all()->keyBy(fn($u) => strtolower($u->abbreviation));
        $uomsByName = Uom::all()->keyBy(fn($u) => strtolower($u->name));

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;
        $excelRow = 1;

        DB::beginTransaction();

        foreach ($rows as $rowKey => $row) {
            $rowNum++;
            $excelRow++;
            $rowData = array_values($row);

            // Skip empty rows
            if (empty(array_filter($rowData, fn($v) => !empty(trim((string) $v))))) continue;

            $name = trim((string) ($rowData[$colMap['name']] ?? ''));
            $categoryInput = trim((string) ($rowData[$colMap['category']] ?? ''));
            $uomInput = trim((string) ($rowData[$colMap['uom']] ?? ''));

            // Validate required fields
            if (empty($name)) {
                $errors[] = "Baris {$rowNum}: Nama item kosong";
                $skipped++;
                continue;
            }

            // Auto-generate SKU from name
            $prefix = strtoupper(substr($name, 0, 3));
            $lastItem = Item::where('sku', 'like', "{$prefix}-%")->orderBy('sku', 'desc')->first();
            $nextNum = $lastItem ? ((int) substr($lastItem->sku, 4) + 1) : 1;
            $sku = $prefix . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            // Check duplicate SKU
            if (Item::where('sku', $sku)->exists()) {
                $errors[] = "Baris {$rowNum}: SKU '{$sku}' sudah ada";
                $skipped++;
                continue;
            }

            // Match category
            $category = $categories[strtolower($categoryInput)]
                ?? $categoriesByCode[strtolower($categoryInput)]
                ?? null;

            if (!$category) {
                $errors[] = "Baris {$rowNum}: Kategori '{$categoryInput}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Match UoM
            $uom = $uoms[strtolower($uomInput)]
                ?? $uomsByName[strtolower($uomInput)]
                ?? null;

            if (!$uom) {
                $errors[] = "Baris {$rowNum}: UoM '{$uomInput}' tidak ditemukan";
                $skipped++;
                continue;
            }

            Item::create([
                'sku' => $sku,
                'name' => $name,
                'category_id' => $category->id,
                'uom_id' => $uom->id,
                'is_active' => true,
            ]);

            $imported++;
        }

        if ($imported > 0) {
            DB::commit();
            AuditLog::log('import_items', Item::class, null, null, ['imported' => $imported, 'skipped' => $skipped]);
        } else {
            DB::rollBack();
        }

        $message = "Import selesai: {$imported} item berhasil ditambahkan";
        if ($skipped > 0) {
            $message .= ", {$skipped} item dilewati";
        }

        $result = $imported > 0 ? 'success' : 'error';
        if ($skipped > 0 && count($errors) <= 10) {
            $message .= "\n\nDetail error:\n" . implode("\n", $errors);
        }

        return back()->with($result, $message);
    }

    /**
     * Map header columns to known field names
     */
    private function mapColumns(array $header): ?array
    {
        $map = ['name' => null, 'category' => null, 'uom' => null];

        $aliases = [
            'name' => ['nama item', 'nama', 'name', 'item name', 'nama_item', 'nama material', 'material name'],
            'category' => ['kategori', 'category', 'tipe', 'type', 'kategori_id', 'category_id'],
            'uom' => ['uom', 'satuan', 'unit', 'unit of measure', 'uom_id'],
        ];

        foreach ($header as $i => $col) {
            foreach ($aliases as $field => $names) {
                if (in_array($col, $names) && $map[$field] === null) {
                    $map[$field] = $i;
                }
            }
        }

        // Name and category and uom are all required
        if ($map['name'] === null || $map['category'] === null || $map['uom'] === null) return null;

        return $map;
    }
}
