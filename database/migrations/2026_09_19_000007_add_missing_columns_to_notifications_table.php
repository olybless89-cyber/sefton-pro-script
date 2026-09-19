<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App\Models\Notification's $fillable list (title, type, icon, link,
     * is_read, data) and every notification-related view/controller
     * reference columns the original migration never created - it only
     * has id, user_id, message, timestamps. This was crashing the
     * dashboard layout itself (unread-count query in the header partial),
     * not just the notifications page.
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable()->after('message');
            }
            if (! Schema::hasColumn('notifications', 'icon')) {
                $table->string('icon')->nullable()->after('type');
            }
            if (! Schema::hasColumn('notifications', 'link')) {
                $table->string('link')->nullable()->after('icon');
            }
            if (! Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('link');
            }
            if (! Schema::hasColumn('notifications', 'data')) {
                $table->text('data')->nullable()->after('is_read');
            }
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            foreach (['title', 'type', 'icon', 'link', 'is_read', 'data'] as $col) {
                if (Schema::hasColumn('notifications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
