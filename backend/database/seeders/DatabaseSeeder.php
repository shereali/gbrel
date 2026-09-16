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
