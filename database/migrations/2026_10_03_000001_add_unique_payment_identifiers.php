<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->unique('external_id', 'payments_external_id_unique');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->unique('external_id', 'subscriptions_external_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropUnique('subscriptions_external_id_unique');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_external_id_unique');
        });
    }
};
