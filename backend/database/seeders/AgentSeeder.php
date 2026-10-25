<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AgentSeeder extends Seeder
{
    /**
     * Seed verified advisors and representatives once on initial installation.
     * NEVER overwrites user-edited agents or resurrects deleted agents on subsequent deploys or restarts.
     */
    public function run(): void
    {
        try {
            // If agents have already been seeded or exist in the database, do NOT overwrite or resurrect deleted ones!
            if ((Schema::hasTable('settings') && Setting::getVal('agents_seeded', false)) || Agent::exists()) {
                if (Schema::hasTable('settings') && ! Setting::getVal('agents_seeded', false)) {
                    Setting::setVal('agents_seeded', '1');
                }

                return;
            }
        } catch (\Throwable $e) {
            // Proceed safely
        }

        Agent::create([
            'name' => 'মোঃ আবু হানিফ (Md. Abu Hanif)',
            'email' => 'abu.hanif@gbrel.com',
            'title' => 'Director & Authorized Representative',
            'agency' => 'GBREL Premier Advisory',
            'state' => 'Dhaka North',
            'city' => 'Dhaka',
            'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
            'phone' => '+880 1348-988225',
            'whatsapp' => '+8801348988225',
            'bio' => 'Authorized representative for prime Gulshan-1 mandates, including Lake View and Gulshan-1 15 Katha estates. Specialist in high-value property conveyancing, RAJUK clearance, and escrow transactions.',
            'experience_years' => 16,
            'rating' => 4.98,
            'review_count' => 120,
            'active_listings_count' => 2,
            'specialties' => ['Gulshan-1 Luxury Estates', 'RAJUK Sale Permission', 'Commercial Buildings'],
        ]);

        Agent::create([
            'name' => 'সিরাজুম মুনিরা খন্দকার (Sirajum Munira Khandakar)',
            'email' => 'sirajum.munira@gbrel.com',
            'title' => 'Senior Property Acquisitions Advisor',
            'agency' => 'GBREL Premier Advisory',
            'state' => 'Dhaka North',
            'city' => 'Dhaka',
            'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop',
            'phone' => '+880 1870-862751',
            'whatsapp' => '+8801870862751',
            'bio' => 'Lead representative for Road 92 Gulshan-2 paternal residential mandates. Expert in inheritance mutation vetting, title deed verification, and high-net-worth client advisory.',
            'experience_years' => 11,
            'rating' => 4.92,
            'review_count' => 78,
            'active_listings_count' => 1,
            'specialties' => ['Gulshan-2 Diplomatic Area', 'Paternal Inheritance Properties', 'Direct Seller Representation'],
        ]);

        Agent::create([
            'name' => 'সিয়াম তালুকদার (Siam Talukder)',
            'email' => 'siam.talukder@gbrel.com',
            'title' => 'Commercial Land & Tower Specialist',
            'agency' => 'GBREL Commercial Advisory',
            'state' => 'Dhaka North',
            'city' => 'Dhaka',
            'photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=600&auto=format&fit=crop',
            'phone' => '+880 1863-755006',
            'whatsapp' => '+8801863755006',
            'bio' => 'Commercial specialist representing premier Gulshan-2 multi-katha commercial plots and corner mandates. Expertise in RAJUK commercial clearance, corporate headquarters redevelopment, and bank transactions.',
            'experience_years' => 14,
            'rating' => 4.95,
            'review_count' => 95,
            'active_listings_count' => 1,
            'specialties' => ['Commercial Corner Plots', 'Corporate High-Rise Sites', 'Gulshan-2 Prime Avenues'],
        ]);

        try {
            if (Schema::hasTable('settings')) {
                Setting::setVal('agents_seeded', '1');
            }
        } catch (\Throwable $e) {}
    }
}
