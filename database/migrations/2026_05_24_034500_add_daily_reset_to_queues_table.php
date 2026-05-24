<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Architecture:
     *  - id                : surrogate primary key (always unique, internal)
     *  - queue_number      : DISPLAY only (e.g. "M-001"). Resets daily, NOT globally unique.
     *  - queue_date        : The business date this queue belongs to (used for daily reset)
     *  - daily_sequence    : Numeric sequence (1, 2, 3...) within (service_id, queue_date)
     *  - qr_code_hash      : Globally unique random token for tracking/QR (NOT the queue_number)
     *
     * Uniqueness: composite (service_id, queue_date, daily_sequence)
     *  -> guarantees no duplicate sequence per service per day
     *  -> queue_number can repeat across days (today M-001 / tomorrow M-001 — both OK)
     */
    public function up(): void
    {
        // Step 1: Drop the global unique constraint on queue_number (allows daily repetition)
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('queues_queue_number_unique');
        });

        // Step 2: Add new columns for daily-reset architecture
        Schema::table('queues', function (Blueprint $table) {
            $table->date('queue_date')->nullable()->after('queue_number');
            $table->unsignedInteger('daily_sequence')->nullable()->after('queue_date');
        });

        // Step 3: Backfill existing rows so historical data stays consistent
        // Use the date portion of created_at as queue_date and re-derive sequence per (service, date)
        DB::statement("UPDATE queues SET queue_date = DATE(created_at) WHERE queue_date IS NULL");

        $rows = DB::table('queues')
            ->select('id', 'service_id', 'queue_date')
            ->orderBy('service_id')
            ->orderBy('queue_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($r) => $r->service_id . '|' . $r->queue_date);

        foreach ($rows as $group) {
            $seq = 1;
            foreach ($group as $row) {
                DB::table('queues')->where('id', $row->id)->update(['daily_sequence' => $seq]);
                $seq++;
            }
        }

        // Step 4: Make new columns required and add composite unique + indexes
        Schema::table('queues', function (Blueprint $table) {
            $table->date('queue_date')->nullable(false)->change();
            $table->unsignedInteger('daily_sequence')->nullable(false)->change();

            // Prevent duplicate sequence within same service+day (the only true uniqueness rule)
            $table->unique(['service_id', 'queue_date', 'daily_sequence'], 'queues_service_date_seq_unique');

            // Performance indexes for daily filtering / dashboards / reports
            $table->index(['queue_date', 'service_id'], 'queues_date_service_idx');
            $table->index(['queue_date', 'status'], 'queues_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('queues_service_date_seq_unique');
            $table->dropIndex('queues_date_service_idx');
            $table->dropIndex('queues_date_status_idx');
            $table->dropColumn(['queue_date', 'daily_sequence']);
            $table->unique('queue_number');
        });
    }
};
