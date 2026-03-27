<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedInteger('credit_balance')->default(0)->after('remember_token');
            $table->string('subscription_tier')->default('FREE')->after('credit_balance');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['credit_balance', 'subscription_tier', 'subscription_ends_at']);
        });
    }
};
