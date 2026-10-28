<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'stream', 'class_level', 'madarsa_section', 'title', 'price', 'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'class_level' => 'integer',
    ];

    public static function templates(): array
    {
        $school = [];
        for ($c = 1; $c <= 8; $c++) {
            $count = match($c) { 1 => 4, 2 => 5, 3 => 6, 4 => 6, 5 => 7, 6 => 7, 7 => 8, 8 => 8, default => 4 };
            $subjects = ['Urdu', 'English', 'Maths', 'Islamiyat', 'Science', 'Social Studies', 'Computer', 'Pakistan Studies'];
            for ($i = 0; $i < $count; $i++) {
                $school[] = ['stream' => 'school', 'class_level' => $c, 'madarsa_section' => null, 'title' => $subjects[$i] . ' — Class ' . $c];
            }
        }

        $madarsa = [
            ['Madarsa A' => ['Noorani Qaida', 'Dua-e-Masnoon', 'Kalma aur Namaz ki Kitab', 'Urdu Deeni Qaida']],
            ['Madarsa B' => ['Quran Pak Nazra (Para 1-5)', 'Yassarnal Quran', 'Dua-e-Masnoon', 'Namaz Masail', 'Deeni Taleem Part 1']],
            ['Madarsa C' => ['Quran Pak Nazra (Para 6-30)', 'Tajweed ul Quran', 'Deeni Taleem Part 2', 'Seerat-un-Nabi (SAW)', 'Masnoon Duain', 'Akhlaq o Adab']],
            ['Madarsa D' => ['Quran Pak Hifz / Nazra Revision', 'Tajweed aur Qirat', 'Talimul Islam', 'Fiqh (Namaz, Roza, Zakat)', 'Arabic Nahw', 'Arabic Sarf', 'Hadith Chaalees']],
        ];

        $result = [];
        foreach ($school as $s) { $result[] = $s; }
        foreach ($madarsa as $idx => $group) {
            foreach ($group as $sec => $items) {
                $section = $sec[strlen('Madarsa ')]; // A,B,C,D
                $i = 0;
                foreach ($items as $t) {
                    $result[] = ['stream' => 'madarsa', 'class_level' => null, 'madarsa_section' => $section, 'title' => $t, 'sort_order' => $i++];
                }
            }
        }
        return $result;
    }
}
