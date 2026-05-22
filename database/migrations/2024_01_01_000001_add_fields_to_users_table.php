<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->unique()->nullable()->after('name');
            $table->enum('role', ['admin', 'pegawai'])->default('pegawai')->after('nip');
            $table->string('jabatan')->nullable()->after('role');
            $table->string('departemen')->nullable()->after('jabatan');
            $table->string('no_hp')->nullable()->after('departemen');
            $table->string('foto')->nullable()->after('no_hp');
            $table->string('qr_token')->unique()->nullable()->after('foto');
            $table->boolean('is_active')->default(true)->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role', 'jabatan', 'departemen', 'no_hp', 'foto', 'qr_token', 'is_active']);
        });
    }
};
