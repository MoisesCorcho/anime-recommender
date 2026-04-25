<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates an HNSW index on users.preference_vector using cosine distance,
     * matching the <=> operator used by orderByVectorDistance() in the AI SDK.
     * m=16 and ef_construction=64 are the pgvector recommended defaults.
     */
    public function up(): void
    {
        DB::statement(
            'CREATE INDEX IF NOT EXISTS users_preference_vector_hnsw_idx
             ON users
             USING hnsw (preference_vector vector_cosine_ops)
             WITH (m = 16, ef_construction = 64)',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_preference_vector_hnsw_idx');
    }
};
