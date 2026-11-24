<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [

            // ==========================
            // PIC 1 (ID = 2)
            // ==========================

            ['room_name'=>'Ruang Kepala Sekolah','pic_id'=>2],
            ['room_name'=>'Ruang Wakil Kepala Sekolah','pic_id'=>2],
            ['room_name'=>'Ruang Guru','pic_id'=>2],
            ['room_name'=>'Ruang Tata Usaha','pic_id'=>2],
            ['room_name'=>'Ruang BK','pic_id'=>2],
            ['room_name'=>'Ruang Rapat','pic_id'=>2],
            ['room_name'=>'Ruang Arsip','pic_id'=>2],
            ['room_name'=>'Perpustakaan','pic_id'=>2],
            ['room_name'=>'Ruang Multimedia','pic_id'=>2],
            ['room_name'=>'Laboratorium Komputer 1','pic_id'=>2],
            ['room_name'=>'Laboratorium Komputer 2','pic_id'=>2],
            ['room_name'=>'Laboratorium Bahasa','pic_id'=>2],
            ['room_name'=>'Laboratorium IPA','pic_id'=>2],
            ['room_name'=>'Studio Musik','pic_id'=>2],

            // ==========================
            // PIC 2 (ID = 3)
            // ==========================

            ['room_name'=>'Aula Utama','pic_id'=>3],
            ['room_name'=>'Masjid Sekolah','pic_id'=>3],
            ['room_name'=>'UKS','pic_id'=>3],
            ['room_name'=>'Koperasi Sekolah','pic_id'=>3],
            ['room_name'=>'Gudang Inventaris','pic_id'=>3],
            ['room_name'=>'Ruang OSIS','pic_id'=>3],
            ['room_name'=>'Ruang Pramuka','pic_id'=>3],
            ['room_name'=>'Ruang PMR','pic_id'=>3],
            ['room_name'=>'Ruang Seni','pic_id'=>3],
            ['room_name'=>'Ruang Tari','pic_id'=>3],
            ['room_name'=>'Ruang Band','pic_id'=>3],
            ['room_name'=>'Ruang Podcast','pic_id'=>3],
            ['room_name'=>'Ruang Meeting 1','pic_id'=>3],

            // ==========================
            // PIC 3 (ID = 4)
            // ==========================

            ['room_name'=>'Kelas X-A','pic_id'=>4],
            ['room_name'=>'Kelas X-B','pic_id'=>4],
            ['room_name'=>'Kelas X-C','pic_id'=>4],
            ['room_name'=>'Kelas XI-A','pic_id'=>4],
            ['room_name'=>'Kelas XI-B','pic_id'=>4],
            ['room_name'=>'Kelas XI-C','pic_id'=>4],
            ['room_name'=>'Kelas XII-A','pic_id'=>4],
            ['room_name'=>'Kelas XII-B','pic_id'=>4],
            ['room_name'=>'Kelas XII-C','pic_id'=>4],
            ['room_name'=>'Lapangan Indoor','pic_id'=>4],
            ['room_name'=>'Lapangan Outdoor','pic_id'=>4],
            ['room_name'=>'Green House','pic_id'=>4],
            ['room_name'=>'Ruang Server','pic_id'=>4],

        ];

        foreach ($rooms as $room) {

            Room::create([
                'pic_id' => $room['pic_id'],
                'room_name' => $room['room_name'],
                'facilities' => 'Meja, Kursi, AC, LCD Proyektor',
                'facility_condition' => 'Baik',
                'status' => 'Available',
                'photo' => null,
            ]);
        }
    }
}