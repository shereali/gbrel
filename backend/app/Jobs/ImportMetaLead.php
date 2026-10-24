<?php

namespace App\Jobs;

use App\Support\MetaLeadImporter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Fetches a lead Meta announced on the leadgen webhook and saves it in the CRM.
 *
 * There is no queue worker on the server, so the webhook uses dispatchAfterResponse(): Meta gets its 200 at once
 * and the Graph API call runs right after. A failure is only logged; `php artisan meta:import-leads` recovers it.
 */
class ImportMetaLead
{
    use Dispatchable;

    public function __construct(public string $leadgenId) {}

    public function handle(): void
    {
        try {
            MetaLeadImporter::importById($this->leadgenId);
        } catch (\Throwable $e) {
            Log::error('Meta lead import failed', ['leadgen_id' => $this->leadgenId, 'error' => $e->getMessage()]);
        }
    }
}
