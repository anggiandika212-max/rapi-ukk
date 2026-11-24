<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('name');
            $table->enum('type', [
                'nasional',
                'cuti_bersama',
                'sekolah'
            ])->default('nasional');

            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'type',
                'description'
            ]);
        });
    }
};
