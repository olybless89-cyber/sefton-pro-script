<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App\Models\GrantApplication has no migration anywhere in the shipped
     * script, so any grant-related page (dashboard widget, admin
     * admin.grants.* routes, GrantApplicationController) 500s with
     * "Base table or view not found: grant_applications".
     */
    public function up()
    {
        if (Schema::hasTable('grant_applications')) {
            return;
        }

        Schema::create('grant_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('application_type')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('program_funding')->default(false);
            $table->boolean('equipment_funding')->default(false);
            $table->boolean('research_funding')->default(false);
            $table->boolean('community_outreach')->default(false);
            $table->string('legal_name')->nullable();
            $table->string('mailing_address')->nullable();
            $table->string('ein')->nullable();
            $table->date('incorporation_date')->nullable();
            $table->text('mission_statement')->nullable();
            $table->text('service_areas')->nullable();
            $table->text('organization_history')->nullable();
            $table->decimal('requested_amount', 15, 2)->nullable();
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->date('disbursal_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grant_applications');
    }
};
