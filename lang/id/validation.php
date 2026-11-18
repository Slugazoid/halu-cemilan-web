<?php

/*
 * Pesan validasi Bahasa Indonesia. Kunci yang belum diterjemahkan otomatis
 * memakai bahasa Inggris bawaan Laravel (fallback_locale = en).
 */
return [
    'accepted' => ':Attribute harus disetujui.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa array.',
    'between' => [
        'array' => ':Attribute harus memiliki :min sampai :max item.',
        'file' => ':Attribute harus berukuran :min sampai :max kilobita.',
        'numeric' => ':Attribute harus bernilai antara :min dan :max.',
        'string' => ':Attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa berkas.',
    'gt' => [
        'numeric' => ':Attribute harus lebih besar dari :value.',
    ],
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':Attribute tidak boleh lebih dari :max item.',
        'file' => ':Attribute tidak boleh lebih besar dari :max kilobita.',
        'numeric' => ':Attribute tidak boleh lebih besar dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => ':Attribute harus memiliki minimal :min item.',
        'file' => ':Attribute harus berukuran minimal :min kilobita.',
        'numeric' => ':Attribute harus bernilai minimal :min.',
        'string' => ':Attribute harus terdiri dari minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',
    'url' => 'Format :attribute tidak valid.',

    'attributes' => [
        'name' => 'nama',
        'phone' => 'nomor HP',
        'password' => 'kata sandi',
        'address' => 'alamat',
        'notes' => 'catatan',
        'price' => 'harga',
        'quantity' => 'jumlah',
        'whatsapp_number' => 'nomor WhatsApp',
    ],
];
