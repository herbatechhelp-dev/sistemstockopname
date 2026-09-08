<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Uom;
use App\Models\Item;
use App\Models\Location;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@siso.com',
            'password' => Hash::make('super123'),
            'role' => 'superadmin',
            'full_name' => 'Super Administrator',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Admin
        User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@siso.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'full_name' => 'Admin Gudang Utama',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        // Team Leaders
        $tl1 = User::create([
            'name' => 'TL Andi',
            'email' => 'tl.andi@siso.com',
            'password' => Hash::make('tl123'),
            'role' => 'team_leader',
            'full_name' => 'Andi Team Leader',
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $tl2 = User::create([
            'name' => 'TL Budi',
            'email' => 'tl.budi@siso.com',
            'password' => Hash::make('tl123'),
            'role' => 'team_leader',
            'full_name' => 'Budi Team Leader',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        // Petugas SO
        $petugas = [];
        $names = ['Rina', 'Doni', 'Sari', 'Eko', 'Fitri', 'Agus'];
        foreach ($names as $i => $name) {
            $petugas[] = User::create([
                'name' => "Petugas {$name}",
                'email' => strtolower("petugas.{$name}@siso.com"),
                'password' => Hash::make('petugas123'),
                'role' => 'petugas_so',
                'full_name' => "{$name} Petugas SO",
                'phone' => '08123456789' . ($i + 4),
                'is_active' => true,
            ]);
        }

        // Categories
        $categories = [
            ['name' => 'Raw Material', 'code' => 'RM', 'description' => 'Bahan baku mentah'],
            ['name' => 'Finish Good', 'code' => 'FG', 'description' => 'Barang jadi siap jual'],
            ['name' => 'Packaging', 'code' => 'PKG', 'description' => 'Material kemasan'],
            ['name' => 'Spare Part', 'code' => 'SP', 'description' => 'Suku cadang mesin'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // UoMs
        $uoms = [
            ['name' => 'Pieces', 'abbreviation' => 'pcs'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Liter', 'abbreviation' => 'ltr'],
            ['name' => 'Meter', 'abbreviation' => 'm'],
            ['name' => 'Roll', 'abbreviation' => 'roll'],
        ];
        foreach ($uoms as $uom) {
            Uom::create($uom);
        }

        // Items
        $items = [
            ['sku' => 'RM-001', 'name' => 'Tepung Terigu', 'category_id' => 1, 'uom_id' => 2],
            ['sku' => 'RM-002', 'name' => 'Gula Pasir', 'category_id' => 1, 'uom_id' => 2],
            ['sku' => 'RM-003', 'name' => 'Minyak Goreng', 'category_id' => 1, 'uom_id' => 4],
            ['sku' => 'FG-001', 'name' => 'Roti Tawar', 'category_id' => 2, 'uom_id' => 1],
            ['sku' => 'FG-002', 'name' => 'Kue Kering', 'category_id' => 2, 'uom_id' => 3],
            ['sku' => 'FG-003', 'name' => 'Biskuit Coklat', 'category_id' => 2, 'uom_id' => 3],
            ['sku' => 'PKG-001', 'name' => 'Karton Box Kecil', 'category_id' => 3, 'uom_id' => 1],
            ['sku' => 'PKG-002', 'name' => 'Plastik Wrap', 'category_id' => 3, 'uom_id' => 6],
            ['sku' => 'PKG-003', 'name' => 'Label Sticker', 'category_id' => 3, 'uom_id' => 1],
            ['sku' => 'SP-001', 'name' => 'Belt Conveyor', 'category_id' => 4, 'uom_id' => 5],
            ['sku' => 'SP-002', 'name' => 'Bearing 6205', 'category_id' => 4, 'uom_id' => 1],
            ['sku' => 'SP-003', 'name' => 'Filter Oli', 'category_id' => 4, 'uom_id' => 1],
        ];
        foreach ($items as $item) {
            Item::create($item);
        }

        // Locations
        $locations = [
            ['name' => 'Gudang Utama - Blok A - Rak 01', 'warehouse' => 'Gudang Utama', 'block' => 'A', 'rack' => '01', 'row' => '1'],
            ['name' => 'Gudang Utama - Blok A - Rak 02', 'warehouse' => 'Gudang Utama', 'block' => 'A', 'rack' => '02', 'row' => '1'],
            ['name' => 'Gudang Utama - Blok A - Rak 03', 'warehouse' => 'Gudang Utama', 'block' => 'A', 'rack' => '03', 'row' => '1'],
            ['name' => 'Gudang Utama - Blok B - Rak 01', 'warehouse' => 'Gudang Utama', 'block' => 'B', 'rack' => '01', 'row' => '1'],
            ['name' => 'Gudang Utama - Blok B - Rak 02', 'warehouse' => 'Gudang Utama', 'block' => 'B', 'rack' => '02', 'row' => '1'],
            ['name' => 'Gudang Utama - Blok B - Rak 03', 'warehouse' => 'Gudang Utama', 'block' => 'B', 'rack' => '03', 'row' => '1'],
            ['name' => 'Gudang B - Blok A - Rak 01', 'warehouse' => 'Gudang B', 'block' => 'A', 'rack' => '01', 'row' => '1'],
            ['name' => 'Gudang B - Blok A - Rak 02', 'warehouse' => 'Gudang B', 'block' => 'A', 'rack' => '02', 'row' => '1'],
            ['name' => 'Gudang B - Blok B - Rak 01', 'warehouse' => 'Gudang B', 'block' => 'B', 'rack' => '01', 'row' => '1'],
            ['name' => 'Gudang B - Blok B - Rak 02', 'warehouse' => 'Gudang B', 'block' => 'B', 'rack' => '02', 'row' => '1'],
        ];
        foreach ($locations as $loc) {
            Location::create($loc);
        }

        // System Settings
        SystemSetting::create([
            'key' => 'variance_tolerance_percentage',
            'value' => '1',
            'description' => 'Batas toleransi selisih dalam persen (%)',
        ]);
        SystemSetting::create([
            'key' => 'company_name',
            'value' => 'PT. HBT Indonesia',
            'description' => 'Nama perusahaan',
        ]);

        // Seed multi-sesi demo untuk opsi A (TL & petugas multi-assign)
        $this->call(MultiSessionSeeder::class);
        $this->call(ImprovementTestingSeeder::class);
    }
}
