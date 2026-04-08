<?php

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
        Schema::create('anime_user', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->comment('Foreign key to users.id (BigInt). Identifies which user owns this tracking entry. Deleted when the user is deleted.')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('anime_id')
                ->comment('Foreign key to animes.id (ULID, char 26). Identifies which anime is being tracked. Deleted when the anime is deleted.')
                ->constrained('animes')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'anime_id'], 'anime_user_unique');

            $table->string('status')
                ->nullable()
                ->comment('Tracking status for this anime: WATCHING, COMPLETED, ON_HOLD, DROPPED, PLAN_TO_WATCH, BLACKLISTED.');

            $table->boolean('is_favorite')
                ->default(false)
                ->comment('True if the user marked this anime with a heart. Independent of the current tracking status.');

            $table->tinyInteger('score')
                ->nullable()
                ->comment('Personal rating given by the user, integer from 1 to 10. Null means the user has not scored it yet.');

            $table->integer('episodes_watched')
                ->default(0)
                ->comment('Number of episodes the user has watched so far. Starts at 0 when the anime is first tracked.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_user');
    }
};
