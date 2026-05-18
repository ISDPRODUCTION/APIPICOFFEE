<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom customer_id dari orders, lalu drop tabel customers dan settings
     * yang sudah tidak digunakan dalam aplikasi.
     */
    public function up(): void
    {
        // 1. Hapus foreign key & kolom customer_id dari orders (jika masih ada)
        if (Schema::hasColumn('orders', 'customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                // Drop index dulu sebelum foreign key
                $table->dropIndex(['customer_id']);
            });

            Schema::table('orders', function (Blueprint $table) {
                // Drop foreign key constraint
                $table->dropForeign(['customer_id']);
                // Hapus kolom
                $table->dropColumn('customer_id');
            });
        }

        // 2. Drop tabel customers
        Schema::dropIfExists('customers');

        // 3. Drop tabel settings
        Schema::dropIfExists('settings');
    }

    public function down(): void
    {
        // Re-create settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Re-create customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // Re-add customer_id to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->index('customer_id');
        });
    }
};
