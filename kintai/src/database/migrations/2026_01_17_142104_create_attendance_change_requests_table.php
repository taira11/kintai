<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceChangeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->cascadeOnDelete();
            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status', 20);
            $table->string('target_field', 30);
            $table->text('before_value')->nullable();
            $table->text('after_value')->nullable();
            $table->text('reason');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_change_requests');
    }
}
