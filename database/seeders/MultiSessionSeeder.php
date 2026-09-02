<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SoSession;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamLocationAllocation;
use App\Models\SessionSnapshot;
use App\Models\SoEntry;
use App\Models\Item;
use App\Models\Location;

class MultiSessionSeeder extends Seeder
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
        $agus = User::where('email', 'petugas.agus@siso.com')->first();

        if (!$admin || !$tlAndi || !$rina) {
            $this->command->warn('DatabaseSeeder belum dijalankan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $items = Item::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();

        // Helper: simulated stock deterministic (same as SessionController)
        $simulatedStock = function (int $itemId, int $locId): int {
            $seed = ($itemId * 73856093) ^ ($locId * 19349663);
            mt_srand($seed);
            if (mt_rand(0, 99) < 25) return 0;
            return mt_rand(10, 500);
        };

        $makeSnapshot = function (SoSession $session, array $locIds) use ($items, $simulatedStock) {
            $created = 0;
            foreach ($items as $item) {
                foreach ($locIds as $locId) {
                    $qty = $simulatedStock($item->id, $locId);
                    if ($qty <= 0) continue;
                    SessionSnapshot::firstOrCreate(
                        ['session_id'=>$session->id,'item_id'=>$item->id,'location_id'=>$locId],
                        ['system_qty'=>$qty]
                    );
                    $created++;
                }
            }
            return $created;
        };

        $makeEntries = function (SoSession $session, Team $team, User $petugas, array $locIds, int $count = 3) use ($items) {
            $locIds = collect($locIds)->shuffle()->take($count)->values();
            foreach ($locIds as $i => $locId) {
                $item = $items->random();
                SoEntry::create([
                    'session_id' => $session->id,
                    'team_id' => $team->id,
                    'petugas_id' => $petugas->id,
                    'item_id' => $item->id,
                    'location_id' => $locId,
                    'uom' => $item->uom->abbreviation,
                    'batch_code' => 'BATCH-'.strtoupper(substr(md5($session->id.'-'.$team->id.'-'.$i),0,6)),
                    'fisik_qty' => rand(5, 200),
                    'keterangan' => rand(0,1) ? 'Hitungan sesuai fisik' : (rand(0,1) ? 'Stok fisik 0, rak kosong' : null),
                    'status' => $i === 0 ? 'pending' : (rand(0,1) ? 'verified' : 'pending'),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder',
                ]);
            }
        };

        // === Sesi 1: SO Gudang Utama - Januari (COMPLETED) ===
        // TL Andi + Rina,Doni | TL Budi + Sari,Eko
        // Seeder membuat 1 sesi completed agar muncul di riwayat
        $sesi1 = SoSession::firstOrCreate(
            ['name' => 'SO Gudang Utama - Januari 2026'],
            ['description' => 'Stock opname Gudang Utama periode Januari', 'status'=>'draft', 'created_by'=>$admin->id]
        );
        if ($sesi1->wasRecentlyCreated) {
            $teamA = Team::create(['name'=>'Tim A - Andi','session_id'=>$sesi1->id,'team_leader_id'=>$tlAndi->id]);
            $teamB = Team::create(['name'=>'Tim B - Budi','session_id'=>$sesi1->id,'team_leader_id'=>$tlBudi->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamA->id,'user_id'=>$rina->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamA->id,'user_id'=>$doni->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamB->id,'user_id'=>$sari->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamB->id,'user_id'=>$eko->id]);

            // Alokasi lokasi anti-collision per sesi
            $locUtamaA = $locations->where('warehouse','Gudang Utama')->where('block','A')->pluck('id')->toArray();
            $locUtamaB = $locations->where('warehouse','Gudang Utama')->where('block','B')->pluck('id')->toArray();
            foreach (array_slice($locUtamaA,0,3) as $lid) TeamLocationAllocation::create(['team_id'=>$teamA->id,'location_id'=>$lid,'status'=>'assigned']);
            foreach (array_slice($locUtamaB,0,3) as $lid) TeamLocationAllocation::create(['team_id'=>$teamB->id,'location_id'=>$lid,'status'=>'assigned']);

            $allLocIds = array_merge(array_slice($locUtamaA,0,3), array_slice($locUtamaB,0,3));
            $makeSnapshot($sesi1, $allLocIds);
            $sesi1->update(['status'=>'active','started_at'=>now()->subDays(5)]);

            $makeEntries($sesi1, $teamA, $rina, array_slice($locUtamaA,0,3), 3);
            $makeEntries($sesi1, $teamA, $doni, array_slice($locUtamaA,0,3), 2);
            $makeEntries($sesi1, $teamB, $sari, array_slice($locUtamaB,0,3), 3);

            // Complete sesi1
            $sesi1->update(['status'=>'completed','ended_at'=>now()->subDays(2)]);
        }

        // === Sesi 2: SO Gudang B - Januari (ACTIVE) ===
        // TL Andi juga pimpin di sini (multi-assign) + Fitri,Agus | TL Budi + Rina (cross)
        $sesi2 = SoSession::firstOrCreate(
            ['name' => 'SO Gudang B - Januari 2026'],
            ['description' => 'Stock opname Gudang B, TL Andi handle 2 sesi paralel','status'=>'draft','created_by'=>$admin->id]
        );
        if ($sesi2->wasRecentlyCreated) {
            $teamC = Team::create(['name'=>'Tim C - Andi (Gudang B)','session_id'=>$sesi2->id,'team_leader_id'=>$tlAndi->id]);
            $teamD = Team::create(['name'=>'Tim D - Budi (Gudang B)','session_id'=>$sesi2->id,'team_leader_id'=>$tlBudi->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamC->id,'user_id'=>$fitri->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamC->id,'user_id'=>$agus->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamD->id,'user_id'=>$rina->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamD->id,'user_id'=>$doni->id]);

            $locBa = $locations->where('warehouse','Gudang B')->where('block','A')->pluck('id')->toArray();
            $locBb = $locations->where('warehouse','Gudang B')->where('block','B')->pluck('id')->toArray();
            foreach (array_slice($locBa,0,2) as $lid) TeamLocationAllocation::create(['team_id'=>$teamC->id,'location_id'=>$lid,'status'=>'assigned']);
            foreach (array_slice($locBb,0,2) as $lid) TeamLocationAllocation::create(['team_id'=>$teamD->id,'location_id'=>$lid,'status'=>'assigned']);

            $allLocIds2 = array_merge(array_slice($locBa,0,2), array_slice($locBb,0,2));
            $makeSnapshot($sesi2, $allLocIds2);
            $sesi2->update(['status'=>'active','started_at'=>now()->subDay()]);

            $makeEntries($sesi2, $teamC, $fitri, array_slice($locBa,0,2), 2);
            $makeEntries($sesi2, $teamD, $rina, array_slice($locBb,0,2), 2);
        }

        // === Sesi 3: SO Gudang Utama - Februari (ACTIVE, draft contoh) ===
        $sesi3 = SoSession::firstOrCreate(
            ['name' => 'SO Gudang Utama - Februari 2026'],
            ['description' => 'Sesi draft untuk demo filter draft di riwayat','status'=>'draft','created_by'=>$admin->id]
        );
        if ($sesi3->wasRecentlyCreated) {
            $teamE = Team::create(['name'=>'Tim E - Andi','session_id'=>$sesi3->id,'team_leader_id'=>$tlAndi->id]);
            TeamMember::firstOrCreate(['team_id'=>$teamE->id,'user_id'=>$sari->id]);
            $loc = $locations->first();
            TeamLocationAllocation::create(['team_id'=>$teamE->id,'location_id'=>$loc->id,'status'=>'assigned']);
        }

        $this->command->info('MultiSessionSeeder selesai: SO Utama Jan (completed), SO Gudang B Jan (active), SO Utama Feb (draft). TL Andi & Rina ter-assign multi-sesi.');
    }
}
