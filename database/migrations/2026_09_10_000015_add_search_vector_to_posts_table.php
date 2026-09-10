<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

            DB::statement("
                ALTER TABLE posts ADD COLUMN search_vector tsvector
                GENERATED ALWAYS AS (
                    setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                    setweight(to_tsvector('english', coalesce(seo_description, '')), 'B') ||
                    setweight(to_tsvector('english', coalesce(excerpt, '')), 'C')
                ) STORED;
            ");

            DB::statement('CREATE INDEX idx_posts_search_vector ON posts USING gin (search_vector);');
            DB::statement('CREATE INDEX idx_posts_title_trgm ON posts USING gin (title gin_trgm_ops);');
            DB::statement('CREATE INDEX idx_notices_title_trgm ON notices USING gin (title gin_trgm_ops);');
        } else {
            // SQLite / fallback for local unit tests without pgsql
            Schema::table('posts', function (Blueprint $table) {
                $table->text('search_vector')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_notices_title_trgm;');
            DB::statement('DROP INDEX IF EXISTS idx_posts_title_trgm;');
            DB::statement('DROP INDEX IF EXISTS idx_posts_search_vector;');
            DB::statement('ALTER TABLE posts DROP COLUMN IF EXISTS search_vector;');
        } else {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('search_vector');
            });
        }
    }
};
