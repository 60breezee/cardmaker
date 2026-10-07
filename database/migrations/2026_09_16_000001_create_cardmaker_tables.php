<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('professional')->index();
            $table->string('thumbnail')->nullable();
            $table->json('configuration');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_premium')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained()->restrictOnDelete();
            $table->uuid('public_identifier')->unique();
            $table->string('name');
            $table->json('data');
            $table->string('status')->default('draft')->index();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('card_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('format', 10);
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan')->default('free');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('status')->default('pending');
            $table->string('provider')->nullable();
            $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event')->index();
            $table->nullableMorphs('subject');
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('card_exports');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('templates');
    }
};
