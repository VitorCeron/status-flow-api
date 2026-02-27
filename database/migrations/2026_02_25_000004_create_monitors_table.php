<?php

use App\Enums\MonitorMethodEnum;
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
        Schema::create('monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->string('url');
            $table->string('method')->default(MonitorMethodEnum::GET->value);
            $table->integer('interval')->default(60);
            $table->integer('timeout');
            $table->integer('fail_threshold');
            $table->string('notify_email');
            $table->boolean('is_active')->default(true);
            $table->string('status')->default(MonitorStatusEnum::UNKNOWN->value);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
