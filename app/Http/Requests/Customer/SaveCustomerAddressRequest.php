<?php

namespace App\Http\Requests\Customer;

use App\Models\Village;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = PhoneNumber::normalize($this->input('recipient_phone'));
        if ($phone !== null) {
            $this->merge(['recipient_phone' => $phone]);
        }

        $villageCode = $this->input('village_code');
        if (! $this->filled('postal_code') && is_string($villageCode)) {
            $village = Village::query()->find($villageCode);

            if ($village && ! $village->isOther() && $village->postal_code !== null) {
                $this->merge(['postal_code' => $village->postal_code]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:20', Rule::in(['Rumah', 'Kantor', 'Kos', 'Lainnya'])],
            'recipient_name' => ['required', 'string', 'max:50'],
            'recipient_phone' => ['required', 'string', 'max:15', 'regex:/^628\d{7,12}$/D'],
            'province_code' => ['required', 'string', 'size:2', 'exists:provinces,code'],
            'regency_code' => ['required', 'string', 'size:4', 'exists:regencies,code'],
            'district_code' => ['required', 'string', 'size:6', 'exists:districts,code'],
            'village_code' => ['required', 'string', 'regex:/^(?:\d{10}|\d{6}LNY)$/D', 'exists:villages,code'],
            'manual_village_name' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{5}$/D'],
            'street_address' => ['required', 'string', 'min:10', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'make_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (['province_code', 'regency_code', 'district_code', 'village_code'] as $field) {
                    if ($validator->errors()->has($field)) {
                        return;
                    }
                }

                $village = Village::query()->with('district.regency')->find($this->input('village_code'));

                if (
                    ! $village
                    || $village->district_code !== $this->input('district_code')
                    || $village->district->regency_code !== $this->input('regency_code')
                    || $village->district->regency->province_code !== $this->input('province_code')
                ) {
                    $validator->errors()->add('village_code', 'Kelurahan harus sesuai dengan kecamatan, kota/kabupaten, dan provinsi yang dipilih.');

                    return;
                }

                $manualVillage = $village->isOther();
                if ($manualVillage && ! $this->filled('manual_village_name')) {
                    $validator->errors()->add('manual_village_name', 'Nama kelurahan wajib diisi untuk pilihan Lainnya.');
                }

                if (! $this->filled('postal_code')) {
                    if ($manualVillage || $village->postal_code === null) {
                        $validator->errors()->add('postal_code', 'Kode pos wajib diisi manual untuk pilihan ini.');
                    } else {
                        $this->merge(['postal_code' => $village->postal_code]);
                    }
                }

                if (
                    $manualVillage
                    && $this->filled('manual_village_name')
                    && mb_strlen($this->input('street_address')."\nKelurahan manual: ".$this->input('manual_village_name')) > 255
                ) {
                    $validator->errors()->add('street_address', 'Gabungan alamat dan nama kelurahan tidak boleh lebih dari 255 karakter.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Label alamat wajib dipilih.',
            'label.in' => 'Label alamat tidak valid.',
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_phone.required' => 'Nomor HP penerima wajib diisi.',
            'recipient_phone.regex' => 'Nomor HP harus menggunakan format nomor Indonesia yang valid.',
            'province_code.required' => 'Provinsi wajib dipilih.',
            'regency_code.required' => 'Kota/kabupaten wajib dipilih.',
            'district_code.required' => 'Kecamatan wajib dipilih.',
            'village_code.required' => 'Kelurahan/desa wajib dipilih.',
            'village_code.regex' => 'Kode kelurahan/desa tidak valid.',
            'village_code.exists' => 'Kelurahan/desa yang dipilih tidak ditemukan.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
            'street_address.required' => 'Alamat lengkap wajib diisi.',
            'street_address.min' => 'Alamat lengkap minimal 10 karakter.',
            'landmark.max' => 'Patokan maksimal 150 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'recipient_name' => 'nama penerima',
            'recipient_phone' => 'nomor HP penerima',
            'province_code' => 'provinsi',
            'regency_code' => 'kota/kabupaten',
            'district_code' => 'kecamatan',
            'village_code' => 'kelurahan/desa',
            'manual_village_name' => 'nama kelurahan',
            'postal_code' => 'kode pos',
            'street_address' => 'alamat lengkap',
            'landmark' => 'patokan',
        ];
    }
}
