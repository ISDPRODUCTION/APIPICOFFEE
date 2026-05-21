<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom customer_id dari orders, lalu drop tabel customers dan settings
     * yang sudah tidak digunakan dalam aplikasi.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'customer_id')) {
            $this->dropOrdersCustomerForeignKey();

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }

        Schema::dropIfExists('customers');
        Schema::dropIfExists('settings');
    }

    public function down(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->index('customer_id');
        });
    }

    private function dropOrdersCustomerForeignKey(): void
    {
        $constraints = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'orders'
               AND COLUMN_NAME = 'customer_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        foreach ($constraints as $row) {
            $name = $row->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$name}`");
        }

        // Index terpisah (jika masih ada setelah FK di-drop)
        $indexes = DB::select(
            "SELECT DISTINCT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'orders'
               AND COLUMN_NAME = 'customer_id'
               AND INDEX_NAME != 'PRIMARY'"
        );

        foreach ($indexes as $row) {
            $name = $row->INDEX_NAME;
            try {
                DB::statement("ALTER TABLE `orders` DROP INDEX `{$name}`");
            } catch (\Throwable) {
                // Index mungkin sudah ikut terhapus bersama FK
            }
        }
    }
};
