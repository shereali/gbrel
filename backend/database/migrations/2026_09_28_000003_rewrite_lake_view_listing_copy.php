<?php

use App\Support\LaunchListings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Brand review of /properties/4: the Lake View seller text is rewritten in GBREL's voice. Only fields that still
     * hold the original text are changed.
     */
    public function up(): void
    {
        LaunchListings::rewriteCopy();
    }

    public function down(): void
    {
        // Copy change only; nothing to undo structurally.
    }
};
