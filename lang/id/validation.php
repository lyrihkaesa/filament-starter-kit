<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':Attribute harus disetujui.',
    'accepted_if' => ':Attribute harus diterima ketika :other adalah :value.',
    'active_url' => ':Attribute bukan URL yang valid.',
    'after' => ':Attribute harus tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, dash, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa array.',
    'ascii' => ':Attribute harus berisi karakter alfanumerik satu byte dan simbol.',
    'before' => ':Attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':Attribute harus memiliki antara :min dan :max item.',
        'file' => ':Attribute harus antara :min dan :max kilobyte.',
        'numeric' => ':Attribute harus antara :min dan :max.',
        'string' => ':Attribute harus antara :min dan :max karakter.',
    ],
    'boolean' => ':Attribute harus benar atau salah.',
    'can' => ':Attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :Attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':Attribute harus tanggal yang valid.',
    'date_equals' => ':Attribute harus tanggal yang sama dengan :date.',
    'date_format' => ':Attribute tidak cocok dengan format :format.',
    'decimal' => ':Attribute harus memiliki :decimal tempat desimal.',
    'declined' => ':Attribute harus ditolak.',
    'declined_if' => ':Attribute harus ditolak ketika :other adalah :value.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus memiliki :digits digit.',
    'digits_between' => ':Attribute harus memiliki antara :min dan :max digit.',
    'dimensions' => ':Attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => ':Attribute memiliki nilai duplikat.',
    'doesnt_end_with' => ':Attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => ':Attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => ':Attribute harus alamat email yang valid.',
    'ends_with' => ':Attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => ':Attribute harus berupa file.',
    'filled' => ':Attribute harus memiliki nilai.',
    'gt' => [
        'array' => ':Attribute harus memiliki lebih dari :value item.',
        'file' => ':Attribute harus lebih besar dari :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string' => ':Attribute harus lebih besar dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus memiliki :value item atau lebih.',
        'file' => ':Attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute harus lebih besar dari atau sama dengan :value karakter.',
    ],
    'hex_color' => ':Attribute harus warna heksadesimal yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute harus ada di :other.',
    'integer' => ':Attribute harus bilangan bulat.',
    'ip' => ':Attribute harus alamat IP yang valid.',
    'ipv4' => ':Attribute harus alamat IPv4 yang valid.',
    'ipv6' => ':Attribute harus alamat IPv6 yang valid.',
    'json' => ':Attribute harus string JSON yang valid.',
    'list' => ':Attribute harus daftar.',
    'lowercase' => ':Attribute harus huruf kecil.',
    'lt' => [
        'array' => ':Attribute harus memiliki kurang dari :value item.',
        'file' => ':Attribute harus kurang dari :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :value item.',
        'file' => ':Attribute harus kurang dari atau sama dengan :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari atau sama dengan :value.',
        'string' => ':Attribute harus kurang dari atau sama dengan :value karakter.',
    ],
    'mac_address' => ':Attribute harus MAC address yang valid.',
    'max' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :max item.',
        'file' => ':Attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => ':Attribute tidak boleh lebih besar dari :max.',
        'string' => ':Attribute tidak boleh lebih besar dari :max karakter.',
    ],
    'max_digits' => ':Attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':Attribute harus berupa file dengan tipe: :values.',
    'mimetypes' => ':Attribute harus berupa file dengan tipe: :values.',
    'min' => [
        'array' => ':Attribute harus memiliki setidaknya :min item.',
        'file' => ':Attribute harus setidaknya :min kilobyte.',
        'numeric' => ':Attribute harus setidaknya :min.',
        'string' => ':Attribute harus setidaknya :min karakter.',
    ],
    'min_digits' => ':Attribute harus memiliki setidaknya :min digit.',
    'missing' => ':Attribute harus tidak ada.',
    'missing_if' => ':Attribute harus tidak ada ketika :other adalah :value.',
    'missing_unless' => ':Attribute harus tidak ada kecuali :other adalah :value.',
    'missing_with' => ':Attribute harus tidak ada ketika :values hadir.',
    'missing_with_all' => ':Attribute harus tidak ada ketika :values hadir.',
    'multiple_of' => ':Attribute harus kelipatan dari :value.',
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :Attribute tidak valid.',
    'numeric' => ':Attribute harus angka.',
    'password' => [
        'letters' => ':Attribute harus memiliki setidaknya satu huruf.',
        'mixed' => ':Attribute harus memiliki setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':Attribute harus memiliki setidaknya satu angka.',
        'symbols' => ':Attribute harus memiliki setidaknya satu simbol.',
        'uncompromised' => ':Attribute yang diberikan muncul dalam kebocoran data. Harap pilih :Attribute yang berbeda.',
    ],
    'present' => ':Attribute harus hadir.',
    'present_if' => ':Attribute harus hadir ketika :other adalah :value.',
    'present_unless' => ':Attribute harus hadir kecuali :other adalah :value.',
    'present_with' => ':Attribute harus hadir ketika :values hadir.',
    'present_with_all' => ':Attribute harus hadir ketika :values hadir.',
    'prohibited' => ':Attribute dilarang.',
    'prohibited_if' => ':Attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => ':Attribute dilarang kecuali :other ada dalam :values.',
    'prohibits' => ':Attribute melarang :other untuk hadir.',
    'regex' => 'Format :Attribute tidak valid.',
    'required' => ':Attribute harus diisi.',
    'required_array_keys' => ':Attribute harus berisi entri untuk: :values.',
    'required_if' => ':Attribute harus diisi ketika :other adalah :value.',
    'required_if_accepted' => ':Attribute harus diisi ketika :other diterima.',
    'required_if_declined' => ':Attribute harus diisi ketika :other ditolak.',
    'required_unless' => ':Attribute harus diisi kecuali :other ada dalam :values.',
    'required_with' => ':Attribute harus diisi ketika :values hadir.',
    'required_with_all' => ':Attribute harus diisi ketika :values hadir.',
    'required_without' => ':Attribute harus diisi ketika :values tidak ada.',
    'required_without_all' => ':Attribute harus diisi ketika tidak ada :values yang hadir.',
    'same' => ':Attribute dan :other harus cocok.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => ':Attribute harus berukuran :size kilobyte.',
        'numeric' => ':Attribute harus berukuran :size.',
        'string' => ':Attribute harus memiliki panjang :size karakter.',
    ],
    'starts_with' => ':Attribute harus dimulai dengan salah satu dari: :values.',
    'string' => ':Attribute harus berupa string.',
    'timezone' => ':Attribute harus zona waktu yang valid.',
    'unique' => ':Attribute sudah ada.',
    'uploaded' => 'Gagal mengunggah :Attribute.',
    'uppercase' => ':Attribute harus huruf kapital.',
    'url' => ':Attribute harus URL yang valid.',
    'ulid' => ':Attribute harus ULID yang valid.',
    'uuid' => ':Attribute harus UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
