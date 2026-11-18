<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with 6 relational entities.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rutesales.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081198765432',
                'employee_code' => 'ADM-001',
                'is_active' => true,
            ]
        );

        // 2. Akun & Profil Sales
        $salesData = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@rutesales.com',
                'phone' => '081234567891',
                'code' => 'SLS-101',
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'siti@rutesales.com',
                'phone' => '081234567892',
                'code' => 'SLS-102',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'fauzi@rutesales.com',
                'phone' => '081234567893',
                'code' => 'SLS-103',
            ],
        ];

        $salesProfiles = [];
        foreach ($salesData as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'role' => 'sales',
                    'phone' => $item['phone'],
                    'employee_code' => $item['code'],
                    'is_active' => true,
                ]
            );

            $salesProfiles[] = Sales::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'employee_code' => $user->employee_code,
                    'status' => 'active',
                ]
            );
        }

        // 3. Toko & Warung Mitra
        $storesData = [
            [
                'name' => 'Toko Berkah Sejahtera',
                'owner_name' => 'Haji Mansyur',
                'phone' => '081211223344',
                'address' => 'Jl. Malioboro No. 12, Danurejan, Kota Yogyakarta',
                'area' => 'Yogyakarta Pusat',
                'latitude' => -7.7925000,
                'longitude' => 110.3658000,
                'status' => 'active',
            ],
            [
                'name' => 'Warung Kelontong Sumber Rejeki',
                'owner_name' => 'Ibu Sri Wahyuni',
                'phone' => '081322334455',
                'address' => 'Jl. Kaliurang KM 5 No. 45, Caturtunggal, Depok, Sleman',
                'area' => 'Sleman Timur',
                'latitude' => -7.7582000,
                'longitude' => 110.3812000,
                'status' => 'active',
            ],
            [
                'name' => 'Minimarket Barokah Abadi',
                'owner_name' => 'Pak Joko Susilo',
                'phone' => '081533445566',
                'address' => 'Jl. Gejayan No. 88, Condongcatur, Sleman',
                'area' => 'Sleman Timur',
                'latitude' => -7.7691000,
                'longitude' => 110.3923000,
                'status' => 'active',
            ],
            [
                'name' => 'Toko Sembako Makmur Jaya',
                'owner_name' => 'Koh Hendra',
                'phone' => '081744556677',
                'address' => 'Jl. Kusumanegara No. 101, Umbulharjo, Kota Yogyakarta',
                'area' => 'Yogyakarta Timur',
                'latitude' => -7.8034000,
                'longitude' => 110.3891000,
                'status' => 'active',
            ],
            [
                'name' => 'Warung Madura 24 Jam',
                'owner_name' => 'Mas Subhan',
                'phone' => '081855667788',
                'address' => 'Jl. Bantul KM 4 No. 15, Sewon, Bantul',
                'area' => 'Bantul Utara',
                'latitude' => -7.8312000,
                'longitude' => 110.3587000,
                'status' => 'active',
            ],
            [
                'name' => 'Toko Kelontong Podomoro (Tutup Sementara)',
                'owner_name' => 'Pak Slamet',
                'phone' => '081966778899',
                'address' => 'Jl. Wonosari KM 7, Piyungan, Bantul',
                'area' => 'Bantul Timur',
                'latitude' => -7.8350000,
                'longitude' => 110.4350000,
                'status' => 'inactive',
            ],
        ];

        $stores = [];
        foreach ($storesData as $s) {
            $stores[] = Store::firstOrCreate(['name' => $s['name']], $s);
        }

        // 4. Data Rute Penugasan Sales & Titik Kunjungan Multi-Toko
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Rute 1: Budi Santoso (Kemarin - Selesai dengan Pesanan & Checkin)
        $route1 = SalesRoute::create([
            'sales_id' => $salesProfiles[0]->id,
            'route_date' => $yesterday->format('Y-m-d'),
            'name' => 'Rute Harian Wilayah Malioboro & Gejayan',
            'status' => 'completed',
            'notes' => 'Rute kunjungan reguler awal pekan.',
        ]);

        // Toko 1: Sukses + Order
        $visit1 = RouteVisit::create([
            'sales_route_id' => $route1->id,
            'store_id' => $stores[0]->id,
            'sequence' => 1,
            'result' => 'successful',
            'checked_in_at' => $yesterday->copy()->setTime(9, 30),
            'notes' => 'Toko ramai, melakukan pemesanan ulang minyak dan gula.',
            'simulated_latitude' => -7.7925000,
            'simulated_longitude' => 110.3658000,
        ]);
        Order::create([
            'route_visit_id' => $visit1->id,
            'order_number' => 'ORD-' . $yesterday->format('Ymd') . '-0001',
            'total_amount' => 1750000.00,
            'notes' => 'Pesanan reguler 10 karton minyak goreng.',
        ]);

        // Toko 2: Toko Tutup + Failure Reason
        RouteVisit::create([
            'sales_route_id' => $route1->id,
            'store_id' => $stores[1]->id,
            'sequence' => 2,
            'result' => 'store_closed',
            'checked_in_at' => $yesterday->copy()->setTime(11, 15),
            'notes' => 'Toko dalam keadaan rolling door terkunci.',
            'failure_reason' => 'Pemilik sedang menghadiri acara keluarga di luar kota.',
            'simulated_latitude' => -7.7582000,
            'simulated_longitude' => 110.3812000,
        ]);

        // Toko 3: Sukses + Order
        $visit3 = RouteVisit::create([
            'sales_route_id' => $route1->id,
            'store_id' => $stores[2]->id,
            'sequence' => 3,
            'result' => 'successful',
            'checked_in_at' => $yesterday->copy()->setTime(14, 00),
            'notes' => 'Pemilik memesan kopi instan dan sabun cuci.',
            'simulated_latitude' => -7.7691000,
            'simulated_longitude' => 110.3923000,
        ]);
        Order::create([
            'route_visit_id' => $visit3->id,
            'order_number' => 'ORD-' . $yesterday->format('Ymd') . '-0002',
            'total_amount' => 850000.00,
            'notes' => 'Pesanan kopi 5 karton.',
        ]);

        // Rute 2: Siti Rahma (Hari Ini - Sedang Berjalan / Sebagian Check-in)
        $route2 = SalesRoute::create([
            'sales_id' => $salesProfiles[1]->id,
            'route_date' => $today->format('Y-m-d'),
            'name' => 'Rute Area Umbulharjo & Kusumanegara',
            'status' => 'in_progress',
            'notes' => 'Penetrasi produk baru kategori snack.',
        ]);

        // Toko 1: Ditolak + Failure Reason
        RouteVisit::create([
            'sales_route_id' => $route2->id,
            'store_id' => $stores[3]->id,
            'sequence' => 1,
            'result' => 'failed',
            'checked_in_at' => $today->copy()->setTime(10, 15),
            'notes' => 'Toko belum membutuhkan tambahan stok.',
            'failure_reason' => 'Stok gudang masih penuh dan target penjualan belum tercapai.',
            'simulated_latitude' => -7.8034000,
            'simulated_longitude' => 110.3891000,
        ]);

        // Toko 2: Belum Check-in (Planned)
        RouteVisit::create([
            'sales_route_id' => $route2->id,
            'store_id' => $stores[4]->id,
            'sequence' => 2,
            'result' => null,
            'checked_in_at' => null,
            'notes' => 'Kunjungan sore hari pukul 15.00 WIB.',
        ]);

        // Rute 3: Ahmad Fauzi (Besok - Planned)
        $tomorrow = Carbon::tomorrow();
        $route3 = SalesRoute::create([
            'sales_id' => $salesProfiles[2]->id,
            'route_date' => $tomorrow->format('Y-m-d'),
            'name' => 'Rute Wilayah Bantul & Sekitarnya',
            'status' => 'planned',
            'notes' => 'Jadwal kunjungan toko mitra reguler.',
        ]);

        RouteVisit::create([
            'sales_route_id' => $route3->id,
            'store_id' => $stores[0]->id,
            'sequence' => 1,
            'result' => null,
        ]);
        RouteVisit::create([
            'sales_route_id' => $route3->id,
            'store_id' => $stores[3]->id,
            'sequence' => 2,
            'result' => null,
        ]);
    }
}
