<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_periksa', function (Blueprint $table) {
            $table->foreignId('id_dokter')->after('id')->constrained('users')->cascadeOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->after('id_dokter');
            $table->time('jam_mulai')->after('hari');
            $table->time('jam_selesai')->after('jam_mulai');
            $table->boolean('status')->default(true)->after('jam_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_periksa', function (Blueprint $table) {
            $table->dropForeign(['id_dokter']);
            $table->dropColumn(['id_dokter', 'hari', 'jam_mulai', 'jam_selesai', 'status']);
        });
    }
};
