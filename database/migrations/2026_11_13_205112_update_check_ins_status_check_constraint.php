<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $allowedStatuses = ['valid', 'expired', 'invalid_period', 'pending', 'rejected'];

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('check_ins', function (Blueprint $table) use ($allowedStatuses): void {
                $table->enum('status', $allowedStatuses)->change();
            });

            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE check_ins
            DROP CONSTRAINT IF EXISTS check_ins_status_check,
            ADD CONSTRAINT check_ins_status_check
            CHECK (status IN ('valid', 'expired', 'invalid_period', 'pending', 'rejected'))
        ");
    }

    public function down(): void
    {
        $allowedStatuses = ['valid', 'expired', 'invalid_period'];

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('check_ins', function (Blueprint $table) use ($allowedStatuses): void {
                $table->enum('status', $allowedStatuses)->change();
            });

            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE check_ins
            DROP CONSTRAINT IF EXISTS check_ins_status_check,
            ADD CONSTRAINT check_ins_status_check
            CHECK (status IN ('valid', 'expired', 'invalid_period'))
        ");
    }
};
