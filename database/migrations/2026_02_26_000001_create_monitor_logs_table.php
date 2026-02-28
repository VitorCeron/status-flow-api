<?php

use App\Enums\MonitorStatusEnum;
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
        Schema::create('monitor_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('monitor_id');
            $table->string('status')->default(MonitorStatusEnum::UNKNOWN->value);
            $table->integer('response_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->foreign('monitor_id')
                ->references('id')
                ->on('monitors')
                ->onDelete('cascade');

            $table->index('monitor_id');
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_logs');
    }
};
