<?php

namespace App\Http\Requests;

use App\Models\Store;
use App\Support\Wilayah;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            // Alamat terstruktur: jalan + 4 tingkat wilayah. `address` diisi otomatis oleh server dari bagian-bagian ini.
            'street' => ['nullable', 'string', 'max:500'],
            'province_code' => ['nullable', 'regex:/^\d{2}$/'],
            'regency_code' => ['nullable', 'regex:/^\d{2}\.\d{2}$/'],
            'district_code' => ['nullable', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
            'village_code' => ['nullable', 'regex:/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/'],
            'address' => ['nullable', 'string'],
            'area' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Alamat wajib terstruktur dan konsisten. Pengecualian: toko lama (alamat teks) yang diedit tanpa
     * menyentuh alamat tetap boleh disimpan; alamat lamanya dibiarkan apa adanya.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $codes = ['province_code', 'regency_code', 'district_code', 'village_code'];
            $complete = filled($this->input('street')) && collect($codes)->every(fn ($k) => filled($this->input($k)));
            $touched = filled($this->input('street')) || collect($codes)->contains(fn ($k) => filled($this->input($k)));

            $existing = $this->isMethod('post') ? null : Store::find($this->route('store'));
            $legacyUntouched = $existing && !$existing->hasStructuredAddress() && !$touched;
            if ($legacyUntouched) {
                return;
            }

            if (!$complete) {
                $v->errors()->add('address', 'Alamat wajib diisi lengkap: pilih provinsi, kabupaten/kota, kecamatan, kelurahan/desa, lalu tulis nama jalan.');
                return;
            }

            $region = Wilayah::resolve($this->input('province_code'), $this->input('regency_code'), $this->input('district_code'), $this->input('village_code'));
            if (!$region) {
                $v->errors()->add('village_code', 'Kombinasi provinsi, kabupaten/kota, kecamatan, dan kelurahan tidak valid.');
            } elseif (Wilayah::cleanStreet($this->input('street'), $region) === '') {
                $v->errors()->add('address', 'Kolom jalan hanya berisi nama wilayah yang sudah dipilih. Tulis nama jalan, nomor, atau patokan.');
            }
        });
    }

    /**
     * Custom message validation.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama toko/warung wajib diisi.',
            'name.max' => 'Nama toko maksimal 255 karakter.',
            'owner_name.required' => 'Nama pemilik toko wajib diisi.',
            'status.required' => 'Status toko wajib dipilih.',
            'status.in' => 'Status toko harus berupa active atau inactive.',
            'latitude.numeric' => 'Latitude harus berupa angka koordinat yang valid.',
            'longitude.numeric' => 'Longitude harus berupa angka koordinat yang valid.',
        ];
    }
}
