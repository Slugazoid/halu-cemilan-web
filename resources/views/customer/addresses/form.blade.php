@extends('customer.layout')

@section('title', $address ? 'Ubah alamat' : 'Tambah alamat')

@section('content')
    <div>
        <a href="{{ route('customer.addresses.index') }}" class="text-sm underline underline-offset-4">Kembali ke alamat</a>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight">{{ $address ? 'Ubah alamat' : 'Tambah alamat' }}</h1>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-destructive/40 bg-red-50 p-3 text-sm text-destructive">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form
        action="{{ $address ? route('customer.addresses.update', $address) : route('customer.addresses.store') }}"
        method="post"
        class="grid gap-5 rounded-xl border border-border bg-white p-4 sm:p-6"
        x-data="regionAddressForm(@js([
            'urls' => [
                'provinces' => route('regions.provinces'),
                'regencies' => route('regions.regencies', ['provinceCode' => '__CODE__']),
                'districts' => route('regions.districts', ['regencyCode' => '__CODE__']),
                'villages' => route('regions.villages', ['districtCode' => '__CODE__']),
            ],
            'initial' => [
                'province_code' => old('province_code', $formValues['province_code'] ?? ''),
                'regency_code' => old('regency_code', $formValues['regency_code'] ?? ''),
                'district_code' => old('district_code', $formValues['district_code'] ?? ''),
                'village_code' => old('village_code', $formValues['village_code'] ?? ''),
                'postal_code' => old('postal_code', $formValues['postal_code'] ?? ''),
                'manual_village_name' => old('manual_village_name', $manualVillageName ?? ''),
                'latitude' => old('latitude', $formValues['latitude'] ?? ''),
                'longitude' => old('longitude', $formValues['longitude'] ?? ''),
            ],
        ]))"
        x-init="init()"
    >
        @csrf
        @if ($address)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-1.5 text-sm font-medium">
                Label alamat
                <select name="label" required class="admin-input">
                    @foreach (['Rumah', 'Kantor', 'Kos', 'Lainnya'] as $label)
                        <option value="{{ $label }}" @selected(old('label', $formValues['label'] ?? '') === $label)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('label') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-1.5 text-sm font-medium">
                Nama penerima
                <input name="recipient_name" value="{{ old('recipient_name', $formValues['recipient_name'] ?? '') }}" maxlength="50" required class="admin-input" autocomplete="name">
                @error('recipient_name') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
        </div>
        <label class="grid gap-1.5 text-sm font-medium">
            Nomor HP penerima
            <input name="recipient_phone" value="{{ old('recipient_phone', $formValues['recipient_phone'] ?? '') }}" maxlength="15" required class="admin-input" inputmode="tel" autocomplete="tel" placeholder="08…, +62…, atau 62…">
            <span class="font-normal text-muted-foreground">Nomor akan disimpan dalam format 628…</span>
            @error('recipient_phone') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
        </label>

        <fieldset class="grid gap-4 border-t border-border pt-5">
            <legend class="px-1 text-sm font-semibold">Wilayah</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1.5 text-sm font-medium">
                    Provinsi
                    <input type="search" x-model="provinceSearch" placeholder="Cari provinsi" class="admin-input" aria-label="Cari provinsi">
                    <select name="province_code" x-model="provinceCode" @change="changeProvince()" required class="admin-input">
                        <option value="">Pilih provinsi</option>
                        <template x-for="item in filtered(provinces, provinceSearch, provinceCode)" :key="item.code">
                            <option :value="item.code" x-text="item.name"></option>
                        </template>
                    </select>
                    @error('province_code') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    Kota/kabupaten
                    <input type="search" x-model="regencySearch" placeholder="Cari kota/kabupaten" class="admin-input" aria-label="Cari kota/kabupaten">
                    <select name="regency_code" x-model="regencyCode" @change="changeRegency()" required :disabled="!provinceCode" class="admin-input">
                        <option value="">Pilih kota/kabupaten</option>
                        <template x-for="item in filtered(regencies, regencySearch, regencyCode)" :key="item.code">
                            <option :value="item.code" x-text="item.name"></option>
                        </template>
                    </select>
                    @error('regency_code') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    Kecamatan
                    <input type="search" x-model="districtSearch" placeholder="Cari kecamatan" class="admin-input" aria-label="Cari kecamatan">
                    <select name="district_code" x-model="districtCode" @change="changeDistrict()" required :disabled="!regencyCode" class="admin-input">
                        <option value="">Pilih kecamatan</option>
                        <template x-for="item in filtered(districts, districtSearch, districtCode)" :key="item.code">
                            <option :value="item.code" x-text="item.name"></option>
                        </template>
                    </select>
                    @error('district_code') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    Kelurahan/desa
                    <input type="search" x-model="villageSearch" placeholder="Cari kelurahan/desa" class="admin-input" aria-label="Cari kelurahan/desa">
                    <select name="village_code" x-model="villageCode" @change="changeVillage()" required :disabled="!districtCode" class="admin-input">
                        <option value="">Pilih kelurahan/desa</option>
                        <template x-for="item in filtered(villages, villageSearch, villageCode)" :key="item.code">
                            <option :value="item.code" x-text="item.name"></option>
                        </template>
                    </select>
                    @error('village_code') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
                </label>
            </div>
            <label x-cloak x-show="isOtherVillage" class="grid gap-1.5 text-sm font-medium">
                Nama kelurahan/desa
                <input name="manual_village_name" x-model="manualVillageName" maxlength="80" :required="isOtherVillage" class="admin-input" placeholder="Isi nama kelurahan/desa">
                <span class="font-normal text-muted-foreground">Nama ini disimpan bersama alamat lengkap.</span>
                @error('manual_village_name') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-1.5 text-sm font-medium">
                Kode pos
                <input name="postal_code" x-model="postalCode" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" :required="needsManualPostalCode" class="admin-input" placeholder="5 angka">
                <span class="font-normal text-muted-foreground" x-text="needsManualPostalCode ? 'Wajib diisi manual untuk pilihan ini.' : 'Terisi otomatis bila tersedia; boleh dikoreksi.'"></span>
                @error('postal_code') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
            <p x-cloak x-show="regionError" role="alert" class="text-sm text-destructive" x-text="regionError"></p>
        </fieldset>

        <fieldset class="grid gap-4 border-t border-border pt-5">
            <legend class="px-1 text-sm font-semibold">Alamat lengkap</legend>
            <label class="grid gap-1.5 text-sm font-medium">
                Jalan, nomor rumah, RT/RW, blok/gedung
                <textarea name="street_address" required minlength="10" maxlength="255" rows="3" class="admin-input">{{ old('street_address', $formValues['street_address'] ?? '') }}</textarea>
                <span class="font-normal text-muted-foreground">Minimal 10 karakter.</span>
                @error('street_address') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-1.5 text-sm font-medium">
                Patokan/catatan kurir (opsional)
                <textarea name="landmark" maxlength="150" rows="2" class="admin-input">{{ old('landmark', $formValues['landmark'] ?? '') }}</textarea>
                @error('landmark') <span class="font-normal text-destructive">{{ $message }}</span> @enderror
            </label>
            @unless ($address)
                <label class="flex min-h-11 items-center gap-3 text-sm">
                    <input type="checkbox" name="make_primary" value="1" @checked(old('make_primary')) class="size-4 accent-amber-700">
                    Jadikan alamat utama
                </label>
            @endunless
        </fieldset>

        <fieldset class="grid gap-3 border-t border-border pt-5">
            <legend class="px-1 text-sm font-semibold">Pin lokasi (opsional)</legend>
            <p class="text-sm text-muted-foreground">Buka peta lalu pilih atau geser pin untuk menyimpan koordinat.</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="admin-button admin-button-outline" @click="toggleMap()" x-text="showMap ? 'Tutup peta' : 'Pilih pin di peta'"></button>
                <button type="button" x-cloak x-show="latitude !== '' || longitude !== ''" class="admin-button admin-button-outline" @click="clearPin()">Hapus pin</button>
            </div>
            <div x-cloak x-show="showMap" x-ref="map" class="h-72 overflow-hidden rounded-lg border border-border" aria-label="Peta pilih lokasi"></div>
            <input type="hidden" name="latitude" x-model="latitude">
            <input type="hidden" name="longitude" x-model="longitude">
            @error('latitude') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
            @error('longitude') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
        </fieldset>

        <button class="admin-button admin-button-primary w-full sm:w-auto">{{ $address ? 'Simpan perubahan' : 'Simpan alamat' }}</button>
    </form>
@endsection
