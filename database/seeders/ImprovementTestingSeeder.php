<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\SoSession;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamLocationAllocation;
use App\Models\SessionSnapshot;
use App\Models\SoEntry;
use App\Models\RecountRequest;
use App\Models\InventoryAdjustment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk menguji SEMUA perbaikan flow A1-A8 & fitur baru B1-B6
 *
 * Cara pakai:
 *   php artisan db:seed --class=ImprovementTestingSeeder
 *   atau
 *   php artisan migrate:fresh --seed   (akan otomatis via DatabaseSeeder jika diaktifkan)
 *
 * Isi seeder ini disengaja idempotent (firstOrCreate) agar bisa dijalankan berulang.
 *
 * Skenario yang dicakup:
 * - A6/A7: toleransi per kategori + soft delete
 * - A2: snapshot import vs simulated
 * - A3/A5: duplikasi dicegah + variance unknown
 * - A4: alokasi completed saat TL verifikasi
 * - A1: recount double-blind siap dikerjakan petugas
 * - B1: inventory adjustment (sesi completed)
 * - B2: data untuk analytics (beragam status variance)
 */
class ImprovementTestingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@siso.com')->first();
        $tlAndi = User::where('email', 'tl.andi@siso.com')->first();
        $tlBudi = User::where('email', 'tl.budi@siso.com')->first();
        $rina = User::where('email', 'petugas.rina@siso.com')->first();
        $doni = User::where('email', 'petugas.doni@siso.com')->first();
        $sari = User::where('email', 'petugas.sari@siso.com')->first();
        $eko = User::where('email', 'petugas.eko@siso.com')->first();
        $fitri = User::where('email', 'petugas.fitri@siso.com')->first();

        if (!$admin) {
            $this->command->warn('Jalankan DatabaseSeeder dulu.');
            return;
        }

        // -----------------------------------------------------------------
        // A6/A7: Set toleransi per kategori + contoh soft-delete
        // -----------------------------------------------------------------
        $this->command->info('[A6/A7] Set toleransi per kategori...');
        $tolerances = [
            'RM' => 5.00,   // Raw Material longgar
            'FG' => 0.50,   // Finish Good ketat
            'PKG' => null,  // Paket -> pakai global 1%
            'SP' => 1.50,
        ];
        foreach ($tolerances as $code => $val) {
            Category::where('code', $code)->update(['tolerance_percentage' => $val]);
        }
        // Contoh soft-delete: buat kategori dummy lalu arsipkan
        $dummyCat = Category::withTrashed()->firstOrCreate(
            ['code' => 'DUM'],
            ['name' => 'Dummy Arsip', 'description' => 'Kategori untuk uji soft-delete']
        );
        if (!$dummyCat->trashed()) {
            $dummyCat->delete(); // soft-delete
            AuditLog::log('soft_delete', Category::class, $dummyCat->id, $dummyCat->toArray());
            $this->command->info('  -> Kategori DUM di-soft-delete (cek dengan Category::withTrashed())');
        }
        // UoM & Location soft-delete contoh: arsipkan 1 lokasi tidak dipakai
        $arsipLoc = Location::firstOrCreate(
            ['name' => 'Gudang Arsip - Blok Z - Rak 99'],
            ['warehouse' => 'Gudang Arsip', 'block' => 'Z', 'rack' => '99', 'row' => '1', 'is_active' => false]
        );
        if (!$arsipLoc->trashed()) {
            $arsipLoc->delete();
            $this->command->info('  -> Lokasi Arsip di-soft-delete');
        }

        // -----------------------------------------------------------------
        // Ambil master untuk snapshot
        // -----------------------------------------------------------------
        $items = Item::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        // -----------------------------------------------------------------
        // A2: Sesi dengan snapshot IMPORT (untuk uji import + transaction)
        // -----------------------------------------------------------------
        $this->command->info('[A2] Buat sesi IMPORT snapshot...');
        $sesiImport = SoSession::firstOrCreate(
            ['name' => 'TEST Import Snapshot - Gudang Utama'],
            ['description' => 'Sesi untuk uji import snapshot Excel (source=import)', 'status' => 'draft', 'created_by' => $admin->id]
        );
        if ($sesiImport->wasRecentlyCreated || $sesiImport->snapshots()->count() === 0) {
            $teamImp = Team::firstOrCreate(['name' => 'Tim Imp-Andi', 'session_id' => $sesiImport->id], ['team_leader_id' => $tlAndi->id]);
            TeamMember::firstOrCreate(['team_id' => $teamImp->id, 'user_id' => $rina->id]);
            TeamMember::firstOrCreate(['team_id' => $teamImp->id, 'user_id' => $doni->id]);
            $locs = $locations->where('warehouse', 'Gudang Utama')->take(2)->pluck('id')->toArray();
            foreach ($locs as $lid) {
                TeamLocationAllocation::firstOrCreate(['team_id' => $teamImp->id, 'location_id' => $lid], ['status' => 'assigned']);
            }
            // Simulasi hasil import: 4 snapshot dengan source=import
            DB::transaction(function () use ($sesiImport, $locs, $items) {
                $rows = [];
                foreach (array_slice($items->all(), 0, 2) as $it) {
                    foreach ($locs as $lid) {
                        $rows[] = [
                            'session_id' => $sesiImport->id,
                            'item_id' => $it->id,
                            'location_id' => $lid,
                            'system_qty' => rand(50, 150),
                            'source' => 'import',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if ($rows) SessionSnapshot::insert($rows);
            });
            $sesiImport->update(['status' => 'active', 'started_at' => now()->subHours(2)]);
            $this->command->info('  -> Sesi import: '.$sesiImport->snapshots()->where('source','import')->count().' snapshot import');
        }

        // -----------------------------------------------------------------
        // A3/A5/A4/B2: Sesi ACTIVE lengkap (untuk uji duplikasi, unknown, completed alokasi, analytics)
        // -----------------------------------------------------------------
        $this->command->info('[A3/A4/A5/B2] Buat sesi ACTIVE komplit...');
        $sesiActive = SoSession::firstOrCreate(
            ['name' => 'TEST Active Lengkap - Gudang B'],
            ['description' => 'Sesi active untuk uji duplikasi, unknown, alokasi completed, analytics', 'status' => 'draft', 'created_by' => $admin->id]
        );
        if ($sesiActive->wasRecentlyCreated || $sesiActive->snapshots()->count() === 0) {
            $teamB = Team::firstOrCreate(['name' => 'Tim B-Test Budi', 'session_id' => $sesiActive->id], ['team_leader_id' => $tlBudi->id]);
            TeamMember::firstOrCreate(['team_id' => $teamB->id, 'user_id' => $sari->id]);
            TeamMember::firstOrCreate(['team_id' => $teamB->id, 'user_id' => $eko->id]);
            $locsB = $locations->where('warehouse', 'Gudang B')->take(2)->pluck('id')->toArray();
            foreach ($locsB as $lid) {
                TeamLocationAllocation::firstOrCreate(['team_id' => $teamB->id, 'location_id' => $lid], ['status' => 'assigned']);
            }
            // Snapshot simulated 3 titik
            $snapItems = $items->take(3);
            $rows = [];
            foreach ($snapItems as $it) {
                foreach ([$locsB[0]] as $lid) {
                    $rows[] = [
                        'session_id' => $sesiActive->id,
                        'item_id' => $it->id,
                        'location_id' => $lid,
                        'system_qty' => 100, // patokan 100 untuk hitungan toleransi mudah
                        'source' => 'simulated',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            SessionSnapshot::insert($rows);
            $sesiActive->update(['status' => 'active', 'started_at' => now()->subHour()]);
            $this->command->info('  -> Snapshot simulated: '.count($rows).' titik (system_qty=100)');

            // Entries: buat 4 entry dengan varian status untuk analytics B2 & A5
            // 1) match (100)
            // 2) tolerable FG (100 vs 100.3 -> 0.3% <= 0.5% FG)
            // 3) unacceptable (100 vs 150 -> 50%)
            // 4) pending unknown (item tanpa snapshot)
            $firstLoc = $locsB[0];
            $secondLoc = $locsB[1] ?? $firstLoc;
            $itMatch = $snapItems[0];
            $itTolerable = Item::where('category_id', Category::where('code','FG')->first()->id)->first() ?? $snapItems[1];
            $itUnacceptable = $snapItems[2] ?? $items->last();
            $itUnknown = $items->whereNotIn('id', $snapItems->pluck('id'))->first() ?? $items->last();

            // Pastikan itTolerable ada snapshot di firstLoc (jika belum, buat)
            SessionSnapshot::firstOrCreate(
                ['session_id'=>$sesiActive->id,'item_id'=>$itTolerable->id,'location_id'=>$firstLoc],
                ['system_qty'=>100,'source'=>'simulated']
            );

            $entriesData = [
                [
                    'item_id'=>$itMatch->id, 'location_id'=>$firstLoc, 'fisik_qty'=>100, 'batch_code'=>'BATCH-MATCH', 'status'=>'verified', 'petugas_id'=>$sari->id, 'ket'=>'Cocok',
                ],
                [
                    'item_id'=>$itTolerable->id, 'location_id'=>$firstLoc, 'fisik_qty'=>100.40, 'batch_code'=>'BATCH-TOL', 'status'=>'verified', 'petugas_id'=>$sari->id, 'ket'=>'Selisih kecil masih toleransi FG 0.5%',
                ],
                [
                    'item_id'=>$itUnacceptable->id, 'location_id'=>$firstLoc, 'fisik_qty'=>150, 'batch_code'=>'BATCH-UNACC', 'status'=>'pending', 'petugas_id'=>$eko->id, 'ket'=>'Selisih besar',
                ],
                [
                    'item_id'=>$itUnknown->id, 'location_id'=>$secondLoc, 'fisik_qty'=>20, 'batch_code'=>'BATCH-UNKNOWN', 'status'=>'pending', 'petugas_id'=>$eko->id, 'ket'=>'Tanpa snapshot (unknown)',
                ],
            ];
            foreach ($entriesData as $d) {
                // A3: firstOrCreate mencegah duplikasi
                SoEntry::firstOrCreate(
                    ['session_id'=>$sesiActive->id,'location_id'=>$d['location_id'],'item_id'=>$d['item_id'],'batch_code'=>$d['batch_code']],
                    [
                        'team_id'=>$teamB->id,
                        'petugas_id'=>$d['petugas_id'],
                        'uom'=>$d['item_id'] ? Item::find($d['item_id'])->uom->abbreviation : 'pcs',
                        'fisik_qty'=>$d['fisik_qty'],
                        'keterangan'=>$d['ket'],
                        'status'=>$d['status'],
                        'ip_address'=>'127.0.0.1',
                        'user_agent'=>'ImprovementTestingSeeder',
                    ]
                );
            }
            $this->command->info('  -> 4 entries: match, tolerable, unacceptable, unknown');

            // A4: simulasi lokasi completed (semua entry di firstLoc sudah verified kecuali 1 pending, jadi belum completed)
            // Untuk uji completed, buat lokasi extra yang semua verified
            $locCompleted = $locsB[0];
            // Tandai allocation jadi completed jika semua verified (manual untuk demo)
            // Kita biarkan logic di VerificationController yang akan auto-complete, tapi set manual untuk demo:
            // Tidak set completed di sini agar tester bisa coba verifikasi -> auto completed

            $this->command->info('  -> Lokasi '.$firstLoc.' masih assigned (ada pending), verifikasi semua -> akan jadi completed');

            // A8: contoh validasi 2 desimal: entry dengan 2 desimal lolos, 3 desimal ditolak (tidak dibuat)
            // Kita buat 1 entry 2 desimal valid:
            SoEntry::firstOrCreate(
                ['session_id'=>$sesiActive->id,'location_id'=>$firstLoc,'item_id'=>$itMatch->id,'batch_code'=>'BATCH-2DEC'],
                [
                    'team_id'=>$teamB->id,
                    'petugas_id'=>$sari->id,
                    'uom'=>'pcs',
                    'fisik_qty'=>12.50,
                    'keterangan'=>'Uji 2 desimal valid',
                    'status'=>'pending',
                    'ip_address'=>'127.0.0.1',
                    'user_agent'=>'Seeder 2dec',
                ]
            );
        }

        // -----------------------------------------------------------------
        // A1: Recount double-blind (entry unacceptable -> recount pending)
        // -----------------------------------------------------------------
        $this->command->info('[A1] Buat recount double-blind...');
        $targetEntry = SoEntry::where('session_id', $sesiActive->id)->where('batch_code','BATCH-UNACC')->first();
        if ($targetEntry) {
            $teamAsal = $targetEntry->team_id;
            // Cari tim lain di sesi yang sama (untuk uji B1, kita pakai Tim Imp-Andi jika sesi beda tidak boleh, jadi buat tim recount di sesi yang sama)
            $teamLain = Team::where('session_id', $sesiActive->id)->where('id','!=',$teamAsal)->first();
            if (!$teamLain) {
                // buat tim recount dummy di sesi active
                $teamLain = Team::create(['name'=>'Tim Recount - Andi','session_id'=>$sesiActive->id,'team_leader_id'=>$tlAndi->id]);
                TeamMember::firstOrCreate(['team_id'=>$teamLain->id,'user_id'=>$fitri->id]);
                // alokasi lokasi sama agar bisa recount
                TeamLocationAllocation::firstOrCreate(['team_id'=>$teamLain->id,'location_id'=>$targetEntry->location_id], ['status'=>'assigned']);
            }
            $recountPetugas = $fitri->id !== $targetEntry->petugas_id ? $fitri : $rina;
            $recount = RecountRequest::firstOrCreate(
                ['so_entry_id'=>$targetEntry->id, 'status'=>'pending'],
                [
                    'requested_by'=>$admin->id,
                    'assigned_team_id'=>$teamLain->id,
                    'assigned_petugas_id'=>$recountPetugas->id,
                    'notes'=>'Selisih > toleransi, hitung ulang double-blind',
                ]
            );
            $targetEntry->update(['status'=>'recount_requested']);
            $this->command->info('  -> Recount pending: entry '.$targetEntry->id.' (asal petugas '.$targetEntry->petugas_id.') -> assigned '.$recountPetugas->id.' tim '.$teamLain->id);
            $this->command->info('     Uji: login sebagai '.$recountPetugas->email.' -> menu Recount -> Kerjakan');
        }

        // Tambahan: recount completed contoh (untuk uji B3 badge selesai vs pending)
        $matchEntry = SoEntry::where('session_id', $sesiActive->id)->where('batch_code','BATCH-TOL')->first();
        if ($matchEntry && !RecountRequest::where('so_entry_id',$matchEntry->id)->exists()) {
            $teamLain2 = Team::where('session_id',$sesiActive->id)->first();
            $rr2 = RecountRequest::create([
                'so_entry_id'=>$matchEntry->id,
                'requested_by'=>$admin->id,
                'assigned_team_id'=>$teamLain2->id,
                'assigned_petugas_id'=>$doni->id,
                'status'=>'completed',
                'notes'=>'Contoh recount completed',
            ]);
            $this->command->info('  -> Recount completed contoh id '.$rr2->id);
        }

        // -----------------------------------------------------------------
        // B1: Sesi COMPLETED + Inventory Adjustment
        // -----------------------------------------------------------------
        $this->command->info('[B1] Buat sesi COMPLETED + adjustment...');
        $sesiComp = SoSession::firstOrCreate(
            ['name' => 'TEST Completed + Adjustment'],
            ['description' => 'Sesi completed untuk uji export & adjustment', 'status' => 'active', 'created_by' => $admin->id, 'started_at'=>now()->subDays(2)]
        );
        if ($sesiComp->wasRecentlyCreated || $sesiComp->snapshots()->count()===0) {
            $teamComp = Team::firstOrCreate(['name'=>'Tim Comp - Andi','session_id'=>$sesiComp->id], ['team_leader_id'=>$tlAndi->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamComp->id,'user_id'=>$rina->id]);
            $locComp = $locations->first()->id;
            TeamLocationAllocation::firstOrCreate(['team_id'=>$teamComp->id,'location_id'=>$locComp], ['status'=>'completed','completed_at'=>now()]);
            $itComp = $items->first();
            SessionSnapshot::firstOrCreate(['session_id'=>$sesiComp->id,'item_id'=>$itComp->id,'location_id'=>$locComp], ['system_qty'=>100,'source'=>'import']);
            $entryComp = SoEntry::firstOrCreate(
                ['session_id'=>$sesiComp->id,'location_id'=>$locComp,'item_id'=>$itComp->id,'batch_code'=>'BATCH-ADJ'],
                ['team_id'=>$teamComp->id,'petugas_id'=>$rina->id,'uom'=>$itComp->uom->abbreviation,'fisik_qty'=>120,'keterangan'=>'Selisih 20 untuk adjustment','status'=>'verified','ip_address'=>'127.0.0.1','user_agent'=>'Seeder']
            );
            $sesiComp->update(['status'=>'completed','ended_at'=>now()]);
            // Buat adjustment
            InventoryAdjustment::firstOrCreate(
                ['session_id'=>$sesiComp->id,'item_id'=>$itComp->id,'location_id'=>$locComp],
                ['system_qty'=>100,'fisik_qty'=>120,'adjustment_qty'=>20,'created_by'=>$admin->id]
            );
            $this->command->info('  -> Sesi completed id '.$sesiComp->id.' + adjustment 20');
            $this->command->info('     Uji: /admin/sessions/'.$sesiComp->id.'/export & POST adjust (hanya completed)');
        }

        // -----------------------------------------------------------------
        // Ringkasan
        // -----------------------------------------------------------------
        $this->command->info('');
        $this->command->info('=== Ringkasan Uji Manual ===');
        $this->command->info('1. A3 Duplikasi: coba POST /entry item yang sudah ada (BATCH-MATCH di TEST Active Lengkap) -> harus ditolak');
        $this->command->info('2. A8 2 desimal: POST fisik_qty 12.345 -> ditolak, 12.50 -> lolos');
        $this->command->info('3. A4 Alokasi: login TL Budi -> /verification -> Verify All di TEST Active Lengkap -> lokasi should jadi completed');
        $this->command->info('4. A5 Unknown: dashboard TEST Active Lengkap harus ada card Unknown =1');
        $this->command->info('5. A7 Toleransi: FG 0.5% entry 100.40 (0.4%) = Tolerable, RM 5% longgar');
        $this->command->info('6. A2 Import: buka /admin/sessions/'.($sesiImport->id ?? '-').' -> Download Template -> Import');
        $this->command->info('7. A1 Recount: login petugas.fitri@siso.com / petugas.rina@siso.com -> /recount -> Kerjakan');
        $this->command->info('8. B1 Export/Adjust: /dashboard?session_id='.$sesiComp->id.' -> Export & Adjust');
        $this->command->info('9. B2 Analytics: /dashboard?session_id='.$sesiActive->id.' -> lihat chart & Top 10');
        $this->command->info('10. B4 Offline: matikan internet -> submit entry -> lihat antrian di header mobile');
        $this->command->info('11. B5 Barcode: /entry/create -> Pindai Barcode kamera');
        $this->command->info('12. A6 Soft-delete: Category DUM & Lokasi Arsip -> cek admin/categories dengan ?withTrashed (hanya via tinker)');
        $this->command->info('');
        $this->command->info('Akun uji: admin@siso.com/admin123, tl.andi@siso.com/tl123, petugas.rina@siso.com/petugas123');
    }
}
