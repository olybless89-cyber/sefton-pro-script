<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App\Models\Task (used by Admin\CrmController and the admin CRM
     * "task" / "mtask" / "viewtask" pages) has no backing table at all,
     * causing a 500 on every admin task-management page.
     */
    public function up()
    {
        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('designation')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('priority')->nullable();
                $table->string('status')->nullable()->default('Pending');
                $table->timestamps();

                $table->index('designation');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
