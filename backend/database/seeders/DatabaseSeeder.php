<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Agent;
use App\Models\Viewing;
use App\Models\Lead;
use App\Models\FinancialTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete GBREL dataset.
     */
    public function run(): void
    {
        // 1. Seed Users (RBAC)
        $adminEmail = env('ADMIN_EMAIL', 'admin@gbrel.com');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Chief Admin (GBREL HQ)',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        if ($adminEmail !== 'admin@gbrel.com') {
            User::updateOrCreate(
                ['email' => 'admin@gbrel.com'],
                [
                    'name' => 'Chief Admin (GBREL HQ)',
                    'password' => Hash::make($adminPassword),
                    'email_verified_at' => now(),
                ]
            );
        }

        User::updateOrCreate(
            ['email' => 'agent@gbrel.com'],
            [
                'name' => 'Tanvir Ahmed (Senior Advisor)',
                'password' => Hash::make('agent123'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'buyer@gbrel.com'],
            [
                'name' => 'Shere Ali (VIP Buyer)',
                'password' => Hash::make('buyer123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Seed Agents
        $agent1 = Agent::updateOrCreate(
            ['email' => 'tanvir.ahmed@gbrel.com'],
            [
                'name' => 'Tanvir Ahmed',
                'title' => 'Director of Residential Acquisitions',
                'agency' => 'GBREL Premier Advisory',
                'state' => 'Dhaka North',
                'city' => 'Dhaka',
                'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
                'phone' => '+880 1819-987654',
                'whatsapp' => '+8801819987654',
                'bio' => '14+ years of expertise in high-net-worth real estate transactions across Gulshan, Banani, and Baridhara Diplomatic Zones.',
                'experience_years' => 14,
                'rating' => 4.95,
                'review_count' => 84,
                'active_listings_count' => 12,
                'specialties' => ['Luxury Penthouses', 'Corner Plots', 'NRB Investments']
            ]
        );

        $agent2 = Agent::updateOrCreate(
            ['email' => 'nusrat.jahan@gbrel.com'],
            [
                'name' => 'Nusrat Jahan',
                'title' => 'Senior Coastal & Hospitality Specialist',
                'agency' => 'GBREL Coastal & Commercial',
                'state' => 'Chittagong',
                'city' => 'Chittagong & Cox\'s Bazar',
                'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop',
                'phone' => '+880 1711-889900',
                'whatsapp' => '+8801711889900',
                'bio' => 'Specialist in Cox’s Bazar Marine Drive beachfront resorts, hotel suite fractions, and Agrabad/Khulshi prime commercial acquisitions.',
                'experience_years' => 9,
                'rating' => 4.85,
                'review_count' => 62,
                'active_listings_count' => 9,
                'specialties' => ['Beach Resorts', 'Hotel Suites', 'Commercial Land']
            ]
        );

        // 3. Seed Properties
        Property::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone',
                'slug' => 'lakeview-penthouse-gulshan-2',
                'tagline' => 'Panoramic Lakefront Skyline View with Private Terrace & Infinity Pool',
                'description' => 'An architectural masterpiece located in the heart of Gulshan-2 diplomatic enclave. Designed by internationally acclaimed architects, this penthouse offers an expansive 4,850 sq ft of ultra-luxurious living space with 360-degree views of Gulshan Lake.',
                'address' => 'Road 71, Block NW(H), Gulshan-2, Dhaka-1212',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-2',
                'price' => 78000000,
                'price_unit' => null,
                'listing_type' => 'Sale',
                'property_type' => 'Penthouse',
                'status' => 'Active',
                'bedrooms' => 4,
                'bathrooms' => 5,
                'balconies' => 4,
                'square_footage' => 4850,
                'parking' => 3,
                'floor_number' => 14,
                'total_floors' => 14,
                'facing' => 'South-East',
                'completion_status' => 'Ready',
                'year_built' => 2024,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7925,
                'longitude' => 90.4167,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop'
                ],
                'amenities' => ['Private High-Speed Elevator', 'Lake-Facing Infinity Pool', '24/7 Full Power Generator Backup', 'Italian Marble Flooring'],
                'documents_verified' => ['RAJUK Approved Building Plan', 'Mutation Parch & Updated Khajna', 'Clear Freehold Title Deed']
            ]
        );

        Property::updateOrCreate(
            ['id' => 2],
            [
                'title' => '10 Katha Corner Residential Plot in Purbachal Sector 17',
                'slug' => '10-katha-corner-plot-purbachal-sector-17',
                'tagline' => '100% Boundary Demarcated, 100ft Wide Road Frontage, Ready for Immediate Handover',
                'description' => 'A prestigious 10 Katha south-east corner plot in the prime residential hub of Purbachal New Town (Sector 17, near Lake and Commercial Boulevard).',
                'address' => 'Road 302, Sector 17, Purbachal New Town, Dhaka',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Purbachal New Town',
                'price' => 36000000,
                'price_unit' => 'Total (৳ 36 Lakh / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Plot',
                'status' => 'Active',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'balconies' => 0,
                'land_size' => 10.0,
                'land_unit' => 'Katha',
                'facing' => 'South-East',
                'completion_status' => 'Ready Handover',
                'year_built' => 2023,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.8340,
                'longitude' => 90.5210,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1600&auto=format&fit=crop'
                ],
                'amenities' => ['Corner Plot with Double Road Access', '100ft Avenue Frontage', 'WASA & DESCO Underground Connections Scheduled'],
                'documents_verified' => ['RAJUK Allotment Letter', 'Mutation & Dakhila Cleared', 'CS/RS/BS Khatians Verified']
            ]
        );

        Property::updateOrCreate(
            ['id' => 3],
            [
                'title' => 'Luxury Beachfront Presidential Suite at Marine Drive',
                'slug' => 'luxury-beachfront-presidential-suite-marine-drive',
                'tagline' => 'Direct Oceanfront Views, 14% Guaranteed ROI Yield, 30 Days Free Stay Per Year',
                'description' => 'Experience supreme luxury hospitality ownership at Inani Beach, Marine Drive. Fully furnished 5-star hotel suite with private infinity dip pool.',
                'address' => 'Marine Drive Road, Inani Beach, Cox\'s Bazar',
                'city' => 'Cox\'s Bazar',
                'state' => 'Chittagong',
                'area_name' => 'Marine Drive',
                'price' => 19000000,
                'price_unit' => 'Full Ownership',
                'listing_type' => 'Sale',
                'property_type' => 'Hotel',
                'status' => 'Active',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'balconies' => 2,
                'square_footage' => 1450,
                'parking' => 1,
                'floor_number' => 7,
                'total_floors' => 10,
                'facing' => 'West (Sunset Ocean)',
                'completion_status' => 'Ready',
                'year_built' => 2024,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 21.3120,
                'longitude' => 92.0520,
                'agent_id' => $agent2->id,
                'images' => [
                    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1600&auto=format&fit=crop'
                ],
                'amenities' => ['5-Star Resort Operations', 'Quarterly Revenue Disbursements', 'Helipad Access', 'Private Beach Club'],
                'documents_verified' => ['CDA & Ministry of Tourism Approvals', 'Commercial Freehold Title Deed', 'Sub-Registry Registry Pass']
            ]
        );

        Property::updateOrCreate(
            ['id' => 4],
            [
                'title' => 'Luxury South-Facing Duplex in Dhanmondi Lake Road 8/A',
                'slug' => 'south-facing-duplex-dhanmondi-8a',
                'tagline' => 'Quiet Residential Haven with Private Terrace Garden & Solar System',
                'description' => 'An immaculate south-facing luxury duplex situated in the most desirable and quiet enclave of Dhanmondi (Road 8/A, steps from Dhanmondi Lake). Spanning two levels (7th & 8th floor), this residence includes 4 expansive bedrooms with en-suite walk-in closets, bespoke teak wood fittings, imported modular kitchen, servant quarters, 2 dedicated basement parking bays, and 100% backup generator.',
                'address' => 'Road 8/A, Dhanmondi R/A, Dhaka-1209',
                'city' => 'Dhaka',
                'state' => 'Dhaka South',
                'area_name' => 'Dhanmondi',
                'price' => 42500000,
                'price_unit' => null,
                'listing_type' => 'Sale',
                'property_type' => 'Duplex',
                'status' => 'Active',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'balconies' => 3,
                'square_footage' => 3200,
                'parking' => 2,
                'floor_number' => 7,
                'total_floors' => 9,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 2024,
                'is_featured' => false,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7465,
                'longitude' => 90.3760,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?q=80&w=1200&auto=format&fit=crop'
                ],
                'amenities' => ['Duplex Double Height Living Room', 'Lakeside Walking Distance', '24/7 Full Backup Generator', 'Custom Teak Wood Interior'],
                'documents_verified' => ['RAJUK Approved Structural & Architectural Plan', 'Mutation & Land Tax Certificate Up-to-Date', 'Freehold Land Share Ratio Registered']
            ]
        );

        Property::updateOrCreate(
            ['id' => 5],
            [
                'title' => '5 Katha South-Facing Commercial Plot in Bashundhara Block-M',
                'slug' => '5-katha-commercial-plot-bashundhara-block-m',
                'tagline' => 'Direct 60ft Main Avenue Road Front, 100% Mutation & Ready for Construction',
                'description' => 'An exceptional commercial & high-density residential plot in the rapidly booming Block-M of Bashundhara Residential Area. Located directly on a 60-foot wide central connecting road, just minutes from the Bashundhara Sports Complex and Apollo/Evercare corridor.',
                'address' => 'Road 22, Block M, Bashundhara R/A, Dhaka-1229',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Bashundhara R/A',
                'price' => 24500000,
                'price_unit' => 'Total (৳ 49 Lakh / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Plot',
                'status' => 'Active',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'balconies' => 0,
                'land_size' => 5.0,
                'land_unit' => 'Katha',
                'parking' => 0,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 2024,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => false,
                'latitude' => 23.8180,
                'longitude' => 90.4420,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1524813686514-a57563d77d61?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop'
                ],
                'amenities' => ['60ft Wide Commercial Avenue', 'Complete Underground Gas & Water Network', 'Boundary Demarcated & Registered'],
                'documents_verified' => ['Bashundhara Official Allotment & Handover Deed', 'Mutation Parch & Up-to-date Dakhila Khajna Paid', 'Freehold Clear Title Vetted by Legal Counsel']
            ]
        );

        Property::updateOrCreate(
            ['id' => 6],
            [
                'title' => 'Boutique Eco Resort with 24 Luxury Cottages in Sreemangal',
                'slug' => 'boutique-eco-resort-sreemangal-tea-valley',
                'tagline' => '3.5 Bigha Lush Tea Garden View with Running Profitable Hospitality Business',
                'description' => 'An extraordinary turnkey hospitality estate situated amidst the world-famous lush rolling tea gardens of Sreemangal. Spanning 3.5 Bighas of freehold hilltop property, this resort features 24 fully furnished wooden luxury cottages, a multi-cuisine open-air restaurant, swimming pool, organic fruit orchard, and solar backup power.',
                'address' => 'Bhanugach Road, Sreemangal, Moulvibazar, Sylhet',
                'city' => 'Sylhet / Sreemangal',
                'state' => 'Sylhet',
                'area_name' => 'Sreemangal Tea Valley',
                'price' => 185000000,
                'price_unit' => 'Turnkey Business',
                'listing_type' => 'Sale',
                'property_type' => 'Hotel',
                'status' => 'Active',
                'bedrooms' => 24,
                'bathrooms' => 26,
                'balconies' => 24,
                'land_size' => 3.5,
                'land_unit' => 'Bigha',
                'parking' => 15,
                'completion_status' => 'Ready',
                'year_built' => 2023,
                'is_featured' => true,
                'is_rajuk_approved' => false,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 24.3065,
                'longitude' => 91.7296,
                'agent_id' => 1,
                'images' => [
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1200&auto=format&fit=crop'
                ],
                'amenities' => ['24 Luxury Air-Conditioned Cottages', 'Panoramic Rolling Tea Estate Views', 'Freshwater Swimming Pool', 'Commercial Solar Power & Generator'],
                'documents_verified' => ['Freehold DC Certified Land Record', 'Department of Environment Clearance', 'Trade License & Tourism Hotel Registration'],
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
            ]
        );

        Property::updateOrCreate(
            ['id' => 7],
            [
                'title' => 'Purbachal Green City Executive Land Share Project',
                'slug' => 'purbachal-green-city-executive-land-share',
                'tagline' => 'Co-Ownership Land Share (3.5 Katha Co-Investment Unit) with Pre-Approved RAJUK Foundation',
                'description' => 'An elite co-ownership Land Share opportunity in Purbachal Sector 21. Own a verified share of high-appreciation freehold land alongside a pre-vetted panel of professional investors. Transparent deed registration and direct handover managed by GBREL legal counsel.',
                'address' => 'Road 401, Sector 21, Purbachal New Town, Dhaka',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Purbachal Sector 21',
                'price' => 4200000,
                'price_unit' => 'Per Share (3.5 Katha Base)',
                'listing_type' => 'Sale',
                'property_type' => 'Land Share',
                'status' => 'Active',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'balconies' => 0,
                'land_size' => 3.5,
                'land_unit' => 'Katha',
                'parking' => 2,
                'completion_status' => 'Upcoming Project',
                'year_built' => 2025,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.8390,
                'longitude' => 90.5290,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop'
                ],
                'amenities' => ['Co-Ownership Deed Registration', 'Demarcated Land Share Unit', 'Dedicated Project Management Panel', 'Bank Loan Support Available'],
                'documents_verified' => ['Combined Land Share Title Deed', 'Mutation & Dakhila Cleared', 'RAJUK Allotment Letter Examined'],
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
            ]
        );

        // 4. Seed Viewings
        Viewing::updateOrCreate(
            ['id' => 101],
            [
                'name' => 'Shere Ali',
                'phone' => '+880 1711-234567',
                'email' => 'buyer@gbrel.com',
                'contact_method' => 'WhatsApp',
                'property_id' => 1,
                'property_title' => 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone',
                'scheduled_date' => '2026-09-05',
                'scheduled_time' => '03:00 PM - 04:00 PM',
                'vip_pickup' => true,
                'pickup_location' => 'Gulshan-2 Diplomatic Enclave',
                'assigned_agent' => 'Tanvir Ahmed',
                'status' => 'Confirmed'
            ]
        );

        // 5. Seed Leads
        Lead::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Dr. Farhan Chowdhury',
                'phone' => '+44 7911 123456',
                'email' => 'farhan.chowdhury@nhs.uk',
                'property_title' => 'Lakeview Penthouse at Gulshan-2',
                'lead_type' => 'NRB Investor (UK)',
                'message' => 'Interested in title verification deeds and bank escrow transfer options.',
                'status' => 'Active'
            ]
        );

        // 6. Seed Financial Transactions
        FinancialTransaction::updateOrCreate(
            ['deal_code' => 'TX-901'],
            [
                'property_title' => 'Lakeview Penthouse at Gulshan-2',
                'buyer_name' => 'Dr. Farhan Chowdhury',
                'transacted_value' => 78000000,
                'commission_amount' => 1560000,
                'escrow_bank' => 'BRAC Bank Escrow',
                'status' => 'Settled'
            ]
        );
    }
}
