<?php

namespace Database\Seeders;

use App\Models\Brochure;
use App\Models\FinancialTransaction;
use App\Models\Lead;
use App\Models\Viewing;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run demo data seeders for local and staging environments only.
     */
    public function run(): void
    {
        if (! $this->shouldSeedDemoData()) {
            return;
        }

        // 1. Seed Demo Viewings
        Viewing::updateOrCreate(
            ['id' => 101],
            [
                'name' => 'Shere Ali',
                'phone' => '+880 1711-234567',
                'email' => 'buyer@gbrel.com',
                'contact_method' => 'WhatsApp',
                'property_id' => 1,
                'property_title' => 'গুলশান ১, ১৩৫/৬ — ১৫ কাঠা জমি ও ২ তলা পুরাতন দালান',
                'scheduled_date' => '2026-09-28',
                'scheduled_time' => '03:00 PM - 04:00 PM',
                'vip_pickup' => true,
                'pickup_location' => 'Gulshan Club, Dhaka',
                'assigned_agent' => 'মোঃ আবু হানিফ (Md. Abu Hanif)',
                'status' => 'Confirmed',
            ]
        );


        // 3. Seed Demo Financial Transactions
        FinancialTransaction::updateOrCreate(
            ['deal_code' => 'TX-901'],
            [
                'property_title' => 'লেক ভিউ — গুলশান-১ রোড ৮, বাড়ি ১০ এ ২৩ কাঠা জমিতে ৬ তলা বাণিজ্যিক ভবন',
                'buyer_name' => 'Tariqul Islam',
                'transacted_value' => 1350000000,
                'commission_amount' => 27000000,
                'escrow_bank' => 'Standard Chartered Bank Escrow',
                'status' => 'Settled',
            ]
        );

        // 4. Seed Official Brochures Vault
        Brochure::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Gulshan-1 15 Katha Estate Title & Land Records Vetting Dossier',
                'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'file_name' => 'gulshan_1_15_katha_mandate_dossier.pdf',
                'file_size' => '4.2 MB',
                'file_type' => 'PDF',
                'property_id' => 1,
                'category' => 'Legal Verification Report',
                'download_count' => 64,
                'is_public' => true,
            ]
        );

        Brochure::updateOrCreate(
            ['id' => 2],
            [
                'title' => 'Gulshan-2 Road 92 17.18 Katha Paternal Estate Layout & Details',
                'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'file_name' => 'gulshan_2_road_92_mandate.pdf',
                'file_size' => '5.8 MB',
                'file_type' => 'PDF',
                'property_id' => 2,
                'category' => 'Property Brochure',
                'download_count' => 92,
                'is_public' => true,
            ]
        );

        Brochure::updateOrCreate(
            ['id' => 3],
            [
                'title' => 'Gulshan-2 31 Katha Commercial Corner Plot Master Feasibility Study',
                'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'file_name' => 'gulshan_2_31_katha_commercial_master_deck.pdf',
                'file_size' => '8.6 MB',
                'file_type' => 'PDF',
                'property_id' => 3,
                'category' => 'Commercial Feasibility Deck',
                'download_count' => 140,
                'is_public' => true,
            ]
        );

        Brochure::updateOrCreate(
            ['id' => 4],
            [
                'title' => 'Lake View 23 Katha 6-Storey Edifice Architectural & Parking Specifications',
                'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'file_name' => 'lake_view_gulshan_1_architectural_deck.pdf',
                'file_size' => '11.4 MB',
                'file_type' => 'PDF',
                'property_id' => 4,
                'category' => 'Architectural & Engineering Deck',
                'download_count' => 185,
                'is_public' => true,
            ]
        );
    }

    private function shouldSeedDemoData(): bool
    {
        return app()->environment(['local', 'testing']) || filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN);
    }
}
