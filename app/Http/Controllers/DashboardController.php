<?php

namespace App\Http\Controllers;

use App\Models\SoSession;
use App\Models\SoEntry;
use App\Models\SessionSnapshot;
use App\Models\Item;
use App\Models\Location;
use App\Models\Category;
use App\Services\SessionContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $session = SessionContext::adminSelected(
            $request->has('session_id') ? (int) $request->get('session_id') : null
        );

        if (!$session) {
            return view('dashboard.index', [
                'session' => null,
                'stats' => [],
                'entries' => collect(),
                'categories' => collect(),
                'locations' => collect(),
                'allSessions' => collect(),
            ]);
        }

        // Get stats - optimized: eager snapshots to avoid N+1, cache tolerance via model logic handles per-entry
        $totalSnapshots = SessionSnapshot::where('session_id', $session->id)->count();
        $totalEntries = SoEntry::where('session_id', $session->id)->count();
        $verifiedEntries = SoEntry::where('session_id', $session->id)->where('status', 'verified')->count();
        $pendingEntries = SoEntry::where('session_id', $session->id)->where('status', 'pending')->count();

        // Calculate variance stats with eager loading to avoid N+1 (A5)
        $allEntriesForStats = SoEntry::where('session_id', $session->id)
            ->with(['item.category', 'location'])
            ->get();

        // Build snapshot map for fast lookup (avoid per-entry query)
        $snapshotMap = SessionSnapshot::where('session_id', $session->id)->get()
            ->keyBy(fn($s) => $s->item_id.'-'.$s->location_id);

        $matchCount = 0;
        $minusCount = 0;
        $plusCount = 0;
        $unknownCount = 0;
        $entriesWithSnapshot = 0;

        foreach ($allEntriesForStats as $entry) {
            $key = $entry->item_id.'-'.$entry->location_id;
            $snapshot = $snapshotMap->get($key);
            $variance = $snapshot ? (float)$entry->fisik_qty - (float)$snapshot->system_qty : null;
            if ($variance === null) { $unknownCount++; continue; }
            $entriesWithSnapshot++;
            if ($variance == 0) $matchCount++;
            elseif ($variance < 0) $minusCount++;
            else $plusCount++;
        }

        $progress = $totalSnapshots > 0 ? round(($entriesWithSnapshot / $totalSnapshots) * 100) : 0;

        $stats = [
            'total_snapshots' => $totalSnapshots,
            'total_entries' => $totalEntries,
            'entries_with_snapshot' => $entriesWithSnapshot,
            'unknown_count' => $unknownCount,
            'verified_entries' => $verifiedEntries,
            'pending_entries' => $pendingEntries,
            'progress' => $progress,
            'match' => $matchCount,
            'minus' => $minusCount,
            'plus' => $plusCount,
        ];

        // Filter entries
        $query = SoEntry::where('session_id', $session->id)
            ->with(['item.category', 'location', 'petugas']);

        if ($search = $request->get('search')) {
            $esc = addcslashes($search, '%_');
            $query->where(function ($q) use ($esc) {
                $q->whereHas('item', fn($q2) => $q2->where('name', 'like', "%{$esc}%")->orWhere('sku', 'like', "%{$esc}%"))
                  ->orWhereHas('location', fn($q2) => $q2->where('name', 'like', "%{$esc}%"))
                  ->orWhere('batch_code', 'like', "%{$esc}%");
            });
        }
        if ($category = $request->get('category_id')) {
            $query->whereHas('item', fn($q) => $q->where('category_id', $category));
        }
        if ($location = $request->get('location_id')) {
            $query->where('location_id', $location);
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $allSessions = SessionContext::adminOptions();

        return view('dashboard.index', compact('session', 'stats', 'entries', 'categories', 'locations', 'allSessions'));
    }

    public function show(SoEntry $entry)
    {
        $entry->load(['item.category', 'location', 'petugas', 'revisions.changer', 'recountRequests']);
        $snapshot = $entry->getSnapshot();
        return view('dashboard.show', compact('entry', 'snapshot'));
    }

    public function export(Request $request, SoSession $session)
    {
        $query = SoEntry::where('session_id', $session->id)->with(['item.category','location','petugas']);
        if ($filter = $request->get('filter')) {
            if ($filter === 'unacceptable') {
                // filter later after variance calc
            }
        }
        $entries = $query->get();
        $snapshotMap = SessionSnapshot::where('session_id',$session->id)->get()->keyBy(fn($s)=>$s->item_id.'-'.$s->location_id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Variance');
        $headers = ['SKU','Nama Item','Kategori','Lokasi','Batch','Petugas','Fisik','Sistem','Variance','Status','Toleransi%'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
            'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'1E40AF']],
        ]);
        $row=2;
        foreach ($entries as $e) {
            $snap = $snapshotMap->get($e->item_id.'-'.$e->location_id);
            $sys = $snap? $snap->system_qty : 0;
            $var = $snap? ((float)$e->fisik_qty - (float)$sys) : null;
            $sheet->fromArray([
                $e->item->sku, $e->item->name, $e->item->category->name ?? '-', $e->location->name, $e->batch_code, $e->petugas->full_name ?? $e->petugas->name,
                $e->fisik_qty, $snap? $sys : '-', $var===null? '-' : $var, $e->variance_status,
                $e->item->category->tolerance_percentage ?? \App\Models\SystemSetting::where('key','variance_tolerance_percentage')->value('value')
            ], null, "A{$row}");
            $row++;
        }
        foreach(range('A','K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = storage_path('app/export_variance_'.$session->id.'.xlsx');
        $writer->save($tmp);
        return response()->download($tmp, 'variance_'.$session->name.'.xlsx')->deleteFileAfterSend(true);
    }

    public function adjust(Request $request, SoSession $session)
    {
        if ($session->status !== 'completed') {
            return back()->with('error','Adjustment hanya bisa untuk sesi completed.');
        }
        $entries = SoEntry::where('session_id',$session->id)->with(['item','location'])->get();
        $snapshotMap = SessionSnapshot::where('session_id',$session->id)->get()->keyBy(fn($s)=>$s->item_id.'-'.$s->location_id);
        $created=0;
        \Illuminate\Support\Facades\DB::transaction(function() use ($entries, $snapshotMap, $session, &$created){
            foreach ($entries as $e) {
                $snap = $snapshotMap->get($e->item_id.'-'.$e->location_id);
                if (!$snap) continue;
                $var = (float)$e->fisik_qty - (float)$snap->system_qty;
                if ($var==0) continue;
                // avoid duplicate adjustment
                $exists = \App\Models\InventoryAdjustment::where('session_id',$session->id)->where('item_id',$e->item_id)->where('location_id',$e->location_id)->exists();
                if ($exists) continue;
                \App\Models\InventoryAdjustment::create([
                    'session_id'=>$session->id,
                    'item_id'=>$e->item_id,
                    'location_id'=>$e->location_id,
                    'system_qty'=>$snap->system_qty,
                    'fisik_qty'=>$e->fisik_qty,
                    'adjustment_qty'=>$var,
                    'created_by'=>auth()->id(),
                ]);
                $created++;
            }
        });
        \App\Models\AuditLog::log('inventory_adjust', SoSession::class, $session->id, null, ['adjusted'=>$created]);
        return back()->with('success',"Adjustment selesai: {$created} item disesuaikan.");
    }

    public function stats(Request $request)
    {
        $sessionId = $request->get('session_id') ? (int)$request->get('session_id') : (SessionContext::adminSelected()?->id);
        if (!$sessionId) return response()->json([]);
        $session = SoSession::find($sessionId);
        if (!$session) return response()->json([]);

        $entries = SoEntry::where('session_id',$sessionId)->with(['item.category'])->get();
        $snapshotMap = SessionSnapshot::where('session_id',$sessionId)->get()->keyBy(fn($s)=>$s->item_id.'-'.$s->location_id);
        $byCat = [];
        $match=0;$tolerable=0;$unacceptable=0;$unknown=0;
        foreach ($entries as $e) {
            $snap = $snapshotMap->get($e->item_id.'-'.$e->location_id);
            $var = $snap? ((float)$e->fisik_qty - (float)$snap->system_qty) : null;
            $status = 'unknown';
            if ($var===null) { $unknown++; $status='unknown'; }
            else {
                // reuse model logic tolerance
                $status = $e->variance_status;
                if ($status==='match') $match++;
                elseif ($status==='tolerable') $tolerable++;
                else $unacceptable++;
            }
            $cat = $e->item->category->name ?? 'Tanpa Kategori';
            if (!isset($byCat[$cat])) $byCat[$cat]=['match'=>0,'tolerable'=>0,'unacceptable'=>0,'unknown'=>0];
            $byCat[$cat][$status]++;
        }

        // top 10 variance terbesar abs
        $top = $entries->map(function($e) use ($snapshotMap){
            $snap=$snapshotMap->get($e->item_id.'-'.$e->location_id);
            $var=$snap? ((float)$e->fisik_qty-(float)$snap->system_qty):null;
            return ['sku'=>$e->item->sku,'name'=>$e->item->name,'variance'=>$var,'abs'=>abs($var??0)];
        })->filter(fn($x)=>$x['variance']!==null)->sortByDesc('abs')->take(10)->values();

        return response()->json([
            'session'=>$session->name,
            'by_category'=>$byCat,
            'status_counts'=>['match'=>$match,'tolerable'=>$tolerable,'unacceptable'=>$unacceptable,'unknown'=>$unknown],
            'top_variance'=>$top,
        ]);
    }
}
