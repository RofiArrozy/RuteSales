<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use App\Support\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Toko Makamhaji',
            'owner_name' => 'Pak Budi',
            'street' => 'Jl. Ahmad Yani No. 12, RT 02/RW 05',
            'province_code' => '33',
            'regency_code' => '33.11',
            'district_code' => '33.11.12',
            'village_code' => '33.11.12.2006',
            'latitude' => -7.5663,
            'longitude' => 110.7771,
            'status' => 'active',
        ], $over);
    }

    public function test_wilayah_resolves_consistent_hierarchy_and_composes_address(): void
    {
        $r = Wilayah::resolve('33', '33.11', '33.11.12', '33.11.12.2006');
        $this->assertNotNull($r);
        $this->assertSame('Makamhaji', $r['village']['name']);
        $this->assertSame('57161', $r['village']['postal_code']);

        $text = Wilayah::compose('Jl. Ahmad Yani No. 12', $r);
        $this->assertStringContainsString('Jl. Ahmad Yani No. 12', $text);
        $this->assertStringContainsString('Makamhaji', $text);
        $this->assertStringContainsString('Kartasura', $text);
        $this->assertStringContainsString('Sukoharjo', $text);
        $this->assertStringContainsString('57161', $text);
    }

    public function test_wilayah_rejects_inconsistent_hierarchy(): void
    {
        // Kelurahan Makamhaji bukan bagian dari kecamatan lain / provinsi lain.
        $this->assertNull(Wilayah::resolve('33', '33.11', '33.11.01', '33.11.12.2006'));
        $this->assertNull(Wilayah::resolve('32', '33.11', '33.11.12', '33.11.12.2006'));
        $this->assertNull(Wilayah::resolve('99', '99.99', '99.99.99', '99.99.99.9999'));
    }

    public function test_store_is_saved_with_composed_address_postal_code_and_default_area(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload())
            ->assertRedirect(route('stores.index'));

        $store = Store::where('name', 'Toko Makamhaji')->firstOrFail();
        $this->assertSame('33.11.12.2006', $store->village_code);
        $this->assertSame('57161', $store->postal_code);
        $this->assertSame('Kartasura', $store->area);
        $this->assertStringContainsString('Makamhaji', $store->address);
        $this->assertStringContainsString('Jl. Ahmad Yani', $store->address);
        $this->assertTrue($store->hasStructuredAddress());
    }

    public function test_explicit_area_is_not_overwritten(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload(['area' => 'Cluster Solo Raya']));
        $this->assertSame('Cluster Solo Raya', Store::where('name', 'Toko Makamhaji')->value('area'));
    }

    public function test_mismatched_district_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('stores.store'), $this->payload(['district_code' => '33.11.01']))
            ->assertSessionHasErrors('village_code');
        $this->assertDatabaseMissing('stores', ['name' => 'Toko Makamhaji']);
    }

    public function test_missing_street_or_region_is_rejected_on_create(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload(['street' => '']))
            ->assertSessionHasErrors('address');
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload(['village_code' => '']))
            ->assertSessionHasErrors('address');
        $this->assertDatabaseMissing('stores', ['name' => 'Toko Makamhaji']);
    }

    public function test_malformed_codes_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload(['village_code' => "33'; DROP"]))
            ->assertSessionHasErrors('village_code');
    }

    public function test_legacy_store_can_be_edited_without_touching_address(): void
    {
        $store = Store::factory()->create(['address' => 'Alamat lama teks bebas', 'name' => 'Toko Lama']);

        $this->actingAs($this->admin)->put(route('stores.update', $store), [
            'name' => 'Toko Lama Baru',
            'owner_name' => $store->owner_name,
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $store->refresh();
        $this->assertSame('Toko Lama Baru', $store->name);
        $this->assertSame('Alamat lama teks bebas', $store->address);
        $this->assertNull($store->village_code);
    }

    public function test_legacy_store_can_be_upgraded_to_structured_address(): void
    {
        $store = Store::factory()->create(['address' => 'Alamat lama', 'name' => 'Toko Lama']);

        $this->actingAs($this->admin)->put(route('stores.update', $store), $this->payload(['name' => 'Toko Lama']))
            ->assertSessionHasNoErrors();

        $store->refresh();
        $this->assertSame('33.11.12.2006', $store->village_code);
        $this->assertStringContainsString('Makamhaji', $store->address);
    }

    public function test_structured_store_cannot_lose_its_address_on_update(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload());
        $store = Store::where('name', 'Toko Makamhaji')->firstOrFail();

        $this->actingAs($this->admin)->put(route('stores.update', $store), [
            'name' => 'Toko Makamhaji',
            'owner_name' => 'Pak Budi',
            'status' => 'active',
        ])->assertSessionHasErrors('address');
    }

    public function test_forms_render_address_dropdowns(): void
    {
        $this->actingAs($this->admin)->get(route('stores.create'))
            ->assertOk()
            ->assertSee('name="province_code"', false)
            ->assertSee('name="village_code"', false)
            ->assertSee('name="street"', false)
            ->assertSee('js/rutesales-address.js', false);

        $store = Store::factory()->create(['address' => 'Alamat lama teks bebas']);
        $this->actingAs($this->admin)->get(route('stores.edit', $store))
            ->assertOk()->assertSee('Alamat lama teks bebas');
    }

    public function test_region_data_files_are_served(): void
    {
        $this->assertFileExists(public_path('data/wilayah/provinces.json'));
        $this->assertFileExists(public_path('data/wilayah/33.json'));
    }

    public function test_clean_street_removes_selected_region_segments_but_keeps_street_names(): void
    {
        $r = Wilayah::resolve('33', '33.11', '33.11.12', '33.11.12.2006');

        $this->assertSame(
            'Jalan Belimbing No.10, RT.3/RW.10',
            Wilayah::cleanStreet('Jalan Belimbing No.10, RT.3/RW.10, Makamhaji, Kec. Kartasura, Kabupaten Sukoharjo, Jawa Tengah 57161', $r)
        );
        $this->assertSame('Jalan Makamhaji Raya No. 3', Wilayah::cleanStreet('Jalan Makamhaji Raya No. 3', $r));
        $this->assertSame('Jalan A', Wilayah::cleanStreet('Jalan A, Kartasura, 57161', $r));
        $this->assertSame('', Wilayah::cleanStreet('Makamhaji, Kartasura', $r));
    }

    public function test_pasted_full_address_in_street_is_not_duplicated(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload([
            'street' => 'Jalan Belimbing No.10, Makamhaji, Kec. Kartasura, Kabupaten Sukoharjo, Jawa Tengah 57161',
        ]));

        $store = Store::where('name', 'Toko Makamhaji')->firstOrFail();
        $this->assertSame('Jalan Belimbing No.10', $store->street);
        $this->assertSame(1, substr_count($store->address, 'Makamhaji'));
        $this->assertSame(1, substr_count($store->address, 'Kartasura'));
    }

    public function test_street_containing_only_region_names_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('stores.store'), $this->payload(['street' => 'Makamhaji, Kartasura']))
            ->assertSessionHasErrors('address');
        $this->assertDatabaseMissing('stores', ['name' => 'Toko Makamhaji']);
    }
}
