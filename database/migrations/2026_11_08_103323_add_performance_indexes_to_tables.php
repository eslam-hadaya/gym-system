<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement(
            "CREATE INDEX IF NOT EXISTS idx_subscriptions_status_lookup ON subscriptions (member_id, payment_status, end_date)",
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS idx_checkins_lookup ON check_ins (member_id, status, created_at)",
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS idx_members_created_at ON members (created_at DESC)",
        );
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_subscriptions_status_lookup");
        DB::statement("DROP INDEX IF EXISTS idx_checkins_lookup");
        DB::statement("DROP INDEX IF EXISTS idx_members_created_at");
    }
};
