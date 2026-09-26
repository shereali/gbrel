<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Brochure;
use App\Models\FinancialTransaction;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete GBREL dataset.
     */
    public function run(): void
    {
        // 0. Seed Permissions
        $permissionsData = [
            // Properties
            ['name' => 'View Properties', 'slug' => 'properties.view', 'module' => 'Properties', 'description' => 'View property catalog and specifications'],
            ['name' => 'Create Property', 'slug' => 'properties.create', 'module' => 'Properties', 'description' => 'Publish new property mandates and land shares'],
            ['name' => 'Edit Property', 'slug' => 'properties.edit', 'module' => 'Properties', 'description' => 'Update property details, pricing, and media'],
            ['name' => 'Delete Property', 'slug' => 'properties.delete', 'module' => 'Properties', 'description' => 'Delist and permanently remove property records'],
            ['name' => 'Feature Property', 'slug' => 'properties.feature', 'module' => 'Properties', 'description' => 'Toggle showcase status on live homepage'],
            ['name' => 'Review Owner Listings', 'slug' => 'listings.review', 'module' => 'Properties', 'description' => 'Review property owner submissions, verify documents, approve and publish'],
            ['name' => 'Verify RAJUK / CDA Plan', 'slug' => 'properties.verify_rajuk', 'module' => 'Properties', 'description' => 'Audit and certify statutory municipal approvals'],

            // Brochures & Media
            ['name' => 'View Brochures', 'slug' => 'brochures.view', 'module' => 'Brochures', 'description' => 'Access and download project brochures and architectural decks'],
            ['name' => 'Upload Brochure', 'slug' => 'brochures.upload', 'module' => 'Brochures', 'description' => 'Upload and link PDF/DOC marketing brochures to mandates'],
            ['name' => 'Delete Brochure', 'slug' => 'brochures.delete', 'module' => 'Brochures', 'description' => 'Remove brochure documents from storage and vault'],

            // Leads & CRM
            ['name' => 'View Leads', 'slug' => 'leads.view', 'module' => 'Leads CRM', 'description' => 'View incoming inquiries and buyer information'],
            ['name' => 'Manage Lead Stages', 'slug' => 'leads.manage', 'module' => 'Leads CRM', 'description' => 'Update lead status, schedule calls, record notes'],
            ['name' => 'Delete Lead', 'slug' => 'leads.delete', 'module' => 'Leads CRM', 'description' => 'Remove lead records from CRM database'],

            // VIP Viewings
            ['name' => 'View Site Viewings', 'slug' => 'viewings.view', 'module' => 'Site Viewings', 'description' => 'Access site viewing schedule and client roster'],
            ['name' => 'Manage Viewings', 'slug' => 'viewings.manage', 'module' => 'Site Viewings', 'description' => 'Confirm, reschedule, or assign agents and VIP pickup'],

            // Advisors
            ['name' => 'Manage Advisors', 'slug' => 'agents.manage', 'module' => 'Advisors', 'description' => 'Create, edit, and assign real estate brokers'],

            // Financials & Escrow
            ['name' => 'View Financials', 'slug' => 'financials.view', 'module' => 'Financials & Escrow', 'description' => 'Inspect escrow balances and commission tallies'],
            ['name' => 'Manage Escrow Settlements', 'slug' => 'financials.manage', 'module' => 'Financials & Escrow', 'description' => 'Settle transactions and verify bank guarantees'],

            // Users & RBAC
            ['name' => 'View Users & RBAC', 'slug' => 'users.view', 'module' => 'User Management', 'description' => 'View staff directory and role assignments'],
            ['name' => 'Manage Users & Permissions', 'slug' => 'users.manage', 'module' => 'User Management', 'description' => 'Create staff, assign roles, and override custom permissions'],
            ['name' => 'Manage Roles Matrix', 'slug' => 'roles.manage', 'module' => 'User Management', 'description' => 'Configure role capabilities and permission matrix'],

            // System Settings
            ['name' => 'Manage System Settings', 'slug' => 'settings.manage', 'module' => 'System Settings', 'description' => 'Configure mortgage interest rates, hotlines, and license numbers'],
        ];

        foreach ($permissionsData as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 1. Seed Roles
        $roleAdmin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Unrestricted enterprise control over all property mandates, users, legal approvals, and financial escrows.',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        $rolePropertyManager = Role::firstOrCreate(
            ['slug' => 'property_manager'],
            [
                'name' => 'Property & Land Manager',
                'description' => 'Full control over property catalog, Land Share co-ownership projects, brochure vault, and media assets.',
                'permissions' => [
                    'properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'properties.feature', 'listings.review',
                    'brochures.view', 'brochures.upload', 'brochures.delete',
                    'viewings.view', 'leads.view',
                ],
                'is_system' => true,
            ]
        );

        $roleLegal = Role::firstOrCreate(
            ['slug' => 'legal_compliance'],
            [
                'name' => 'Legal & Compliance Officer',
                'description' => 'Vetting RAJUK/CDA municipal plans, vetting CS/RS/BS khatians, and verifying mutation deeds.',
                'permissions' => [
                    'properties.view', 'properties.edit', 'properties.verify_rajuk', 'listings.review',
                    'brochures.view', 'brochures.upload',
                    'leads.view', 'viewings.view',
                ],
                'is_system' => true,
            ]
        );

        $roleAgent = Role::firstOrCreate(
            ['slug' => 'agent'],
            [
                'name' => 'Senior Real Estate Advisor',
                'description' => 'Managing buyer relationships, scheduling VIP physical tours, and coordinating property viewings.',
                'permissions' => [
                    'properties.view', 'brochures.view',
                    'leads.view', 'leads.manage',
                    'viewings.view', 'viewings.manage',
                ],
                'is_system' => true,
            ]
        );

        $roleFinance = Role::firstOrCreate(
            ['slug' => 'finance_auditor'],
            [
                'name' => 'Escrow & Financial Auditor',
                'description' => 'Auditing bank escrow accounts, settling transactions, and reviewing commission distributions.',
                'permissions' => [
                    'financials.view', 'financials.manage',
                    'properties.view', 'leads.view',
                ],
                'is_system' => true,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'owner'],
            [
                'name' => 'Property Owner',
                'description' => 'Submits and updates their own property for GBREL to verify and sell.',
                'permissions' => [],
                'is_system' => true,
            ]
        );
        $roleBuyer = Role::firstOrCreate(
            ['slug' => 'buyer'],
            [
                'name' => 'VIP Client / Investor',
                'description' => 'Public catalog access, project brochure downloads, and self-service viewing requests.',
                'permissions' => [
                    'properties.view', 'brochures.view', 'viewings.manage',
                ],
                'is_system' => true,
            ]
        );

        // 2. Seed Users (RBAC Enabled)
        $adminEmail = env('ADMIN_EMAIL', 'admin@gbrel.com');
        $adminPassword = (string) env('ADMIN_PASSWORD', '');

        // The admin account is created once. Its password is never reset by later deploys.
        if (! User::where('email', $adminEmail)->exists()) {
            if ($adminPassword === '') {
                $adminPassword = Str::random(24);
                Log::warning('ADMIN_PASSWORD is not set. Created '.$adminEmail.' with a random password; reset it before use.');
            }
            User::create([
                'email' => $adminEmail,
                'name' => 'GBREL Admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'role' => 'admin',
                'role_id' => $roleAdmin->id,
                'status' => 'Active',
            ]);
        }

        // Demo staff, agents, listings and leads are only for local development.
        if (! $this->shouldSeedDemoData()) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'manager@gbrel.com'],
            [
                'name' => 'Tariqul Islam (Property Director)',
                'password' => Hash::make('manager123'),
                'email_verified_at' => now(),
                'role' => 'property_manager',
                'role_id' => $rolePropertyManager->id,
                'phone' => '+880 1711-445566',
                'region' => 'Dhaka North',
                'status' => 'Active',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop',
            ]
        );

        User::updateOrCreate(
            ['email' => 'legal@gbrel.com'],
            [
                'name' => 'Barrister Shafiul Alam (Legal Panel Head)',
                'password' => Hash::make('legal123'),
                'email_verified_at' => now(),
                'role' => 'legal_compliance',
                'role_id' => $roleLegal->id,
                'phone' => '+880 1912-334455',
                'region' => 'Dhaka HQ',
                'status' => 'Active',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=200&auto=format&fit=crop',
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@gbrel.com'],
            [
                'name' => 'Tanvir Ahmed (Senior Luxury Advisor)',
                'password' => Hash::make('agent123'),
                'email_verified_at' => now(),
                'role' => 'agent',
                'role_id' => $roleAgent->id,
                'phone' => '+880 1819-987654',
                'region' => 'Dhaka North',
                'status' => 'Active',
                'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop',
            ]
        );

        User::updateOrCreate(
            ['email' => 'buyer@gbrel.com'],
            [
                'name' => 'Shere Ali (VIP Investor)',
                'password' => Hash::make('buyer123'),
                'email_verified_at' => now(),
                'role' => 'buyer',
                'role_id' => $roleBuyer->id,
                'phone' => '+880 1711-234567',
                'region' => 'Dhaka HQ',
                'status' => 'Active',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop',
            ]
        );

        // 2. Clean up dummy listings and related records
        Viewing::query()->delete();
        Lead::query()->delete();
        FinancialTransaction::query()->delete();
        Brochure::query()->delete();
        Property::query()->delete();

        // 3. Seed Official Representatives / Agents from PDFs
        $agent1 = Agent::updateOrCreate(
            ['email' => 'abu.hanif@gbrel.com'],
            [
                'name' => 'মোঃ আবু হানিফ (Md. Abu Hanif)',
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
            ]
        );

        $agent2 = Agent::updateOrCreate(
            ['email' => 'sirajum.munira@gbrel.com'],
            [
                'name' => 'সিরাজুম মুনিরা খন্দকার (Sirajum Munira Khandakar)',
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
            ]
        );

        $agent3 = Agent::updateOrCreate(
            ['email' => 'siam.talukder@gbrel.com'],
            [
                'name' => 'সিয়াম তালুকদার (Siam Talukder)',
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
            ]
        );

        // 4. Seed 4 Verified Properties from PDFs
        Property::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Gulshan-1 Prime 15 Katha Residential Land with 2-Storey Building',
                'slug' => 'gulshan-1-15-katha-residential-land-building',
                'tagline' => '15 Katha Prime Gulshan-1 Land with Structure, 100% Freehold Clear Title & Bank Escrow Ready',
                'description' => 'Prime 15 Katha residential land with an existing 2-storey building located at Road 135/6, Gulshan-1, Dhaka. Features unencumbered freehold ownership acquired through legal inheritance by 5 co-owners, with Ganiur Rahman et al. holding irrevocable power of attorney. 100% updated namjari mutation, online khajna paid through Bangla 1432, documentation and service charges cleared. Immediate possession by owners, 15-day RAJUK sale permission transfer timeline. Bank transaction only.',
                'address' => 'Plot 135/6, Gulshan-1, Dhaka-1212',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-1',
                'price' => 900000000,
                'price_unit' => 'Total (৳ 6.00 Crore / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Land',
                'status' => 'Active',
                'bedrooms' => 6,
                'bathrooms' => 6,
                'balconies' => 4,
                'square_footage' => 5500,
                'land_size' => 15.0,
                'land_unit' => 'Katha',
                'parking' => 6,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 1998,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7785,
                'longitude' => 90.4172,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop',
                ],
                'amenities' => [
                    '15 Katha South-Facing Freehold Land',
                    '2-Storey Existing Structure / Redevelopment Ready',
                    'Wide Gulshan-1 Frontage Road Access',
                    'Independent Boundary Demarcated',
                    'Full Owner Physical Possession',
                    'WASA Water, DESCO Electricity & Gas Connection',
                    'Bank Escrow Transaction Support',
                    'RAJUK Sale Permission Assistance',
                ],
                'documents_verified' => [
                    'মালিকানা দলিল ও ওয়ারিশান সনদপত্র (Inheritance Deed)',
                    '২০২৩ সালের নামজারি ও জমাভাগ খতিয়ান (Updated Mutation)',
                    'বাংলা ১৪৩২ সনের অনলাইন ভূমি উন্নয়ন কর/খাজনা রশিদ',
                    'নিষ্কণ্টক মালিকানা সনদ (Clear Title Certificate)',
                    'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি পরিশোধ রশিদ',
                    'অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নি (গনিউর রাহমান গং)',
                ],
                'buyer_details' => [
                    'landUse' => 'Residential',
                    'cornerPlot' => 'No',
                    'roadWidth' => 40,
                    'buildingDescription' => '১৫ কাঠা জমির উপর ২ তলা পুরাতন দালান/ভবন বিদ্যমান। নতুন বহুতল ভবন নির্মাণের উপযোগী।',
                    'utilities' => 'বিদ্যুৎ, গ্যাস ও ওয়াসা পানি সংযোগ হালনাগাদ রয়েছে।',
                    'priceBasis' => 'Total',
                    'negotiable' => 'Yes',
                    'priceIncludes' => '১৫ কাঠা জমি এবং বিদ্যমান ২ তলা ভবন সহ মোট মূল্য।',
                    'depositPercent' => 25,
                    'agreementDuration' => 'চুক্তির তারিখ হতে ৯০ দিন',
                    'paymentSchedule' => 'বায়না ২৫%, অবশিষ্ট মূল্য রাজউক অনুমতি সাপেক্ষে ব্যাংক পে-অর্ডারে হস্তান্তরকালে।',
                    'paymentMethod' => 'ব্যাংক পে-অর্ডার / একাউন্ট ট্রান্সফার',
                    'buyerCommission' => 2,
                    'sellerCommission' => 2,
                    'registrationCost' => 'সরকারি বিধি মোতাবেক সকল রেজিস্ট্রেশন ফি ক্রেতা বহন করবেন।',
                    'buyerCosts' => 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন ও সংশ্লিষ্ট সরকারি ফি নির্বাহ করবেন।',
                    'sellerCosts' => 'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি পরিশোধ সম্পন্ন।',
                    'transferTimeline' => 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
                    'transferTrigger' => 'রাজউক কর্তৃক বিক্রয় অনুমতি পত্র জারির দিন থেকে।',
                    'ownerCount' => 5,
                    'ownershipSource' => 'Inheritance',
                    'possession' => 'Owner',
                    'bankLoan' => 'None declared',
                    'existingAgreement' => 'None declared',
                    'saleAuthority' => 'গনিউর রাহমান গং (অপ্রত্যাহারযোগ্য পাওয়ার অব অ্যাটর্নি বলে)',
                    'ownershipNotes' => 'ওয়ারিশ সূত্রে ৫ জন মালিক, কোনো বিরোধ নেই, সম্পূর্ণ নিষ্কণ্টক জমি।',
                    'mutationStatus' => 'Available',
                    'taxPaidThrough' => 'বাংলা ১৪৩২ সন',
                    'serviceChargeStatus' => 'জমা দেওয়া আছে',
                    'approvalDetails' => 'রেসিডেন্সিয়াল জোন হিসেবে অনুমোদিত, রাজউক থেকে বিক্রয় অনুমতি প্রক্রিয়া চলমান।',
                    'documentSummary' => 'নিষ্কণ্টক / টোটাল কাগজ-পাতি ১০০% আপডেট।',
                    'sourceDate' => '2026-09-15',
                    'updatedOn' => '2026-09-15',
                ],
            ]
        );

        Property::updateOrCreate(
            ['id' => 2],
            [
                'title' => 'Gulshan-2 Road 92 Prime 17.18 Katha Residential Estate',
                'slug' => 'gulshan-2-road-92-17-katha-residential-estate',
                'tagline' => 'Prestigious Road 92 Mandate, 17.18 Katha Paternal Inheritance Land with 2-Storey Building',
                'description' => 'An exclusive 17.18 Katha south-facing residential property situated on Road 92, Plot 06, Gulshan-2, Dhaka. Jointly owned by 2 brothers through clear paternal inheritance with direct self-possession. 100% dispute-free title with complete updated documentation, online khajna paid through Bangla 1432, mutation cleared. Quoted at ৳ 7 Crore per Katha (negotiable). Seller bears sale permission costs and service charges. Handover within 15 working days of RAJUK sale permission. Bank transactions only.',
                'address' => 'Road 92, Plot 06, Gulshan-2, Dhaka-1212',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-2',
                'price' => 1202600000,
                'price_unit' => 'Per land unit (৳ 7.00 Crore / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Land',
                'status' => 'Active',
                'bedrooms' => 8,
                'bathrooms' => 8,
                'balconies' => 6,
                'square_footage' => 6800,
                'land_size' => 17.18,
                'land_unit' => 'Katha',
                'parking' => 8,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 2005,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7962,
                'longitude' => 90.4195,
                'agent_id' => $agent2->id,
                'images' => [
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1200&auto=format&fit=crop',
                ],
                'amenities' => [
                    '17.18 Katha Prime Gulshan-2 Diplomatic Vicinity',
                    'Road 92 Wide Avenue Frontage',
                    '2-Storey Modern Residential Structure',
                    'Direct Self-Possession by 2 Brothers',
                    'Zero Encumbrance or Bank Liability',
                    'Gas, Electricity & Deep WASA Connection',
                    'Pre-vetted Title Deeds & Survey Khatian',
                    'Seller Bears RAJUK Permission & Service Charge',
                ],
                'documents_verified' => [
                    'পৈতৃক সূত্রের মূল মালিকানা দলিল (Paternal Title Deed)',
                    'অনলাইন নামজারি ও ডিসিআর পর্চা (Mutation DCR)',
                    'বাংলা ১৪৩২ সনের হালনাগাদ অনলাইন খাজনা রশিদ',
                    'রাজউক অনুমোদিত আবাসিক প্লট রেকর্ড',
                    'নিষ্কণ্টক মালিকানা ও দাগ খতিয়ান যাচাইকৃত',
                ],
                'buyer_details' => [
                    'landUse' => 'Residential',
                    'cornerPlot' => 'No',
                    'roadWidth' => 50,
                    'buildingDescription' => '১৭.১৮ কাঠা জমিতে ২ তলা আবাসিক ভবন। সুদৃশ্য বাগান ও ড্রাইভওয়ে সহ।',
                    'utilities' => 'গ্যাস, থ্রি-ফেজ বিদ্যুৎ ও ওয়াসা পানির লাইন সার্বক্ষণিক চালু।',
                    'priceBasis' => 'Per land unit',
                    'negotiable' => 'Yes',
                    'priceIncludes' => 'প্রতি কাঠা ৭ কোটি টাকা হিসেবে মোট ১৭.১৮ কাঠা জমি ও বিদ্যমান ভবন।',
                    'depositPercent' => 30,
                    'agreementDuration' => 'চুক্তির তারিখ হতে ৯০-১২০ কার্যদিবস',
                    'paymentSchedule' => 'বায়না ৩০%, অবশিষ্ট মূল্য রাজউক বিক্রয় অনুমতি প্রাপ্তির পর রেজিস্ট্রি সম্পন্নকালে।',
                    'paymentMethod' => 'ব্যাংক ড্রাফট / পে-অর্ডার / আরটিজিএস',
                    'buyerCommission' => 1,
                    'sellerCommission' => 2,
                    'registrationCost' => 'রেজিস্ট্রেশন ফি ও সরকারি কর ক্রেতার নিয়ম অনুযায়ী।',
                    'buyerCosts' => 'রেজিস্ট্রেশন খরচ ও কর ক্রেতা বহন করবেন।',
                    'sellerCosts' => 'বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
                    'transferTimeline' => 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
                    'transferTrigger' => 'রাজউক বিক্রয় অনুমতি পত্র হাতে পাওয়ার পর।',
                    'ownerCount' => 2,
                    'ownershipSource' => 'Inheritance',
                    'possession' => 'Owner',
                    'bankLoan' => 'None declared',
                    'existingAgreement' => 'None declared',
                    'saleAuthority' => 'নিজ (২ জন ভাই যৌথভাবে উপস্থিত থেকে দলিল সম্পাদন করবেন)',
                    'ownershipNotes' => 'পৈতৃক সূত্রে ২ ভাই একক মালিক, কোনো তৃতীয় পক্ষের দাবি বা মামলা নেই।',
                    'mutationStatus' => 'Available',
                    'taxPaidThrough' => 'বাংলা ১৪৩২ সন',
                    'serviceChargeStatus' => 'পরিশোধিত ও জমা আছে',
                    'approvalDetails' => 'গুলশান-২ আবাসিক এলাকা, রাজউক বিক্রয় অনুমতি বিক্রেতা নিজ খরচে নিবেন।',
                    'documentSummary' => 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
                    'sourceDate' => '2026-09-14',
                    'updatedOn' => '2026-09-14',
                ],
            ]
        );

        Property::updateOrCreate(
            ['id' => 3],
            [
                'title' => 'Gulshan-2 Road 48/4B 31 Katha Commercial Corner Plot & Structure',
                'slug' => 'gulshan-2-road-48-31-katha-commercial-corner-plot',
                'tagline' => 'Rare 31 Katha Commercial Approved Corner Plot in Gulshan-2, 100% Clear Title',
                'description' => 'A once-in-a-generation 31 Katha prime commercial approved corner plot situated at Road 48/4B, Gulshan-2, Dhaka. Spanning an enormous corner plot with multi-road access, this property features an existing 2-storey structure and is fully approved for commercial high-rise tower redevelopment. Owned by 3 co-owners through inheritance with completed mutation and Bangla 1432 online khajna paid. Quoted at ৳ 14 Crore per Katha (negotiable). Seller bears sale permission and service charges. Handover within 15 working days of RAJUK sale permission. Bank transactions only.',
                'address' => 'Road 48/4B, Gulshan-2, Dhaka-1212',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-2',
                'price' => 4340000000,
                'price_unit' => 'Per land unit (৳ 14.00 Crore / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Commercial',
                'status' => 'Active',
                'bedrooms' => 10,
                'bathrooms' => 10,
                'balconies' => 6,
                'square_footage' => 12000,
                'land_size' => 31.0,
                'land_unit' => 'Katha',
                'parking' => 20,
                'floor_number' => 1,
                'total_floors' => 2,
                'facing' => 'South-East',
                'completion_status' => 'Ready',
                'year_built' => 2002,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7915,
                'longitude' => 90.4138,
                'agent_id' => $agent3->id,
                'images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1577495508048-b635879837f1?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
                ],
                'amenities' => [
                    '31 Katha Commercial Approved Corner Plot',
                    'Double Road Corner Frontage with High Visibility',
                    'Suitable for 25+ Storey Corporate Headquarters / Luxury Mixed Tower',
                    'Existing 2-Storey Concrete Building',
                    'High-Tension Power Substation & Commercial Utility Line',
                    'Dispute-Free Inheritance Title with Updated Namjari',
                    'Seller Bears RAJUK Permission & Service Charge',
                    'Direct Owner Possession',
                ],
                'documents_verified' => [
                    'মালিকানা দলিল ও ওয়ারিশান সনদ (Inheritance Title & Succession)',
                    'অনুমোদিত নামজারি ও জমাভাগ খতিয়ান (Completed Mutation)',
                    'বাংলা ১৪৩২ সনের অনলাইন খাজনা পরিশোধ রশিদ',
                    'কমার্শিয়াল ব্যবহারের সরকারি অনুমোদন সনদ',
                    'সার্ভিস চার্জ ও ডকুমেন্টেশন ফি জমা রশিদ',
                    'নিষ্কণ্টক ও নির্ভেজাল টাইটেল রিপোর্ট (Clear Title Audit)',
                ],
                'buyer_details' => [
                    'landUse' => 'Commercial',
                    'cornerPlot' => 'Yes',
                    'roadWidth' => 80,
                    'buildingDescription' => '৩১ কাঠা কর্নার প্লটে ২ তলা দালান বিদ্যমান। মাল্টি-স্টোরি কমার্শিয়াল টাওয়ার নির্মাণের জন্য আদর্শ।',
                    'utilities' => 'বাণিজ্যিক বিদ্যুৎ, গ্যাস ও উচ্চ ক্ষমতাসম্পন্ন ওয়াসা লাইন।',
                    'priceBasis' => 'Per land unit',
                    'negotiable' => 'Yes',
                    'priceIncludes' => 'প্রতি কাঠা ১৪ কোটি টাকা হিসেবে মোট ৩১ কাঠা কমার্শিয়াল কর্নার প্লট ও বিদ্যমান স্থাপনা।',
                    'depositPercent' => 30,
                    'agreementDuration' => 'চুক্তির পর ১২০ কার্যদিবস',
                    'paymentSchedule' => 'বায়না ৩০%, অবশিষ্ট ৭০% রাজউক বিক্রয় অনুমতি সম্পন্ন হওয়ার পর রেজিস্ট্রি কালে ব্যাংকের মাধ্যমে।',
                    'paymentMethod' => 'ব্যাংক পে-অর্ডার / আরটিজিএস',
                    'buyerCommission' => 1,
                    'sellerCommission' => 2,
                    'registrationCost' => 'সরকারি কমার্শিয়াল রেজিস্ট্রেশন কর ক্রেতার নিজ দায়িত্বে।',
                    'buyerCosts' => 'রেজিস্ট্রেশন ফি ও অন্যান্য সরকারি শুল্ক ক্রেতা বহন করবেন।',
                    'sellerCosts' => 'বিক্রয় অনুমতি পত্র ও সার্ভিস চার্জ বিক্রেতা বহন করবেন।',
                    'transferTimeline' => 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তির ১৫ কার্যদিবসের মধ্যে।',
                    'transferTrigger' => 'রাজউক বিক্রয় অনুমতি পত্র প্রকাশের পর।',
                    'ownerCount' => 3,
                    'ownershipSource' => 'Inheritance',
                    'possession' => 'Owner',
                    'bankLoan' => 'None declared',
                    'existingAgreement' => 'None declared',
                    'saleAuthority' => 'ওয়ারিশান ৩ জন মালিক যৌথভাবে',
                    'ownershipNotes' => 'ওয়ারিশ সূত্রে নামজারি সম্পন্ন, ৩ জন মালিকের কোনো বিরোধ নেই।',
                    'mutationStatus' => 'Available',
                    'taxPaidThrough' => 'বাংলা ১৪৩২ সন',
                    'serviceChargeStatus' => 'জমা দেওয়া আছে',
                    'approvalDetails' => 'কমার্শিয়াল অনুমোদন প্রাপ্ত, রাজউক বিক্রয় অনুমতি বিক্রেতা সম্পন্ন করে দিবেন।',
                    'documentSummary' => 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
                    'sourceDate' => '2026-09-15',
                    'updatedOn' => '2026-09-15',
                ],
            ]
        );

        Property::updateOrCreate(
            ['id' => 4],
            [
                'title' => '"Lake View" 6-Storey Luxury Edifice on 23 Katha at Road 8, Gulshan-1',
                'slug' => 'lake-view-gulshan-1-road-8-23-katha-building',
                'tagline' => 'Prestigious "Lake View" 23 Katha Estate, 6-Storey Modern Edifice with 28 Dedicated Car Parks',
                'description' => 'The iconic "Lake View" estate located at Road #8, House #10, Gulshan-1, Dhaka. Spread across 23 Katha of prime lakeside land, this distinguished property boasts a 6-storey modern building with 28 reserved basement car parking bays and comprehensive updated utility supply. Jointly owned by 2 owners (Owner Md. Towfiqul Islam), with complete physical possession by the owners. 100% dispute-free title with all updated documents, mutation cleared and online khajna fully paid. Quoted at ৳ 135 Crore (negotiable). Transfer agreement via 300 Taka non-judicial stamp, handover in 15 working days from RAJUK sale permission. Bank transactions only.',
                'address' => 'House 10, Road 8, Gulshan-1, Dhaka-1212',
                'city' => 'Dhaka',
                'state' => 'Dhaka North',
                'area_name' => 'Gulshan-1',
                'price' => 1350000000,
                'price_unit' => 'Total (৳ 5.87 Crore / Katha)',
                'listing_type' => 'Sale',
                'property_type' => 'Duplex',
                'status' => 'Active',
                'bedrooms' => 16,
                'bathrooms' => 18,
                'balconies' => 12,
                'square_footage' => 26000,
                'land_size' => 23.0,
                'land_unit' => 'Katha',
                'parking' => 28,
                'floor_number' => 1,
                'total_floors' => 6,
                'facing' => 'South',
                'completion_status' => 'Ready',
                'year_built' => 2018,
                'is_featured' => true,
                'is_rajuk_approved' => true,
                'is_verified' => true,
                'has_open_house' => true,
                'latitude' => 23.7798,
                'longitude' => 90.4148,
                'agent_id' => $agent1->id,
                'images' => [
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop',
                ],
                'amenities' => [
                    '23 Katha Lakeside Prime Land in Gulshan-1',
                    '6-Storey Modern Completed Architectural Edifice',
                    '28 Dedicated Covered Car Parking Bays',
                    'Independent 500KVA Substation & Full Generator Backup',
                    'High-Speed Dual Passenger Elevators',
                    'Direct Lake View and Expansive Terrace Gardens',
                    'Owner Physical Possession with 24/7 Security Post',
                    'Comprehensive Utility Supply 100% Updated',
                ],
                'documents_verified' => [
                    'মূল নিষ্কণ্টক স্বত্ব দলিল ও বণ্টননামা (Freehold Title Deed)',
                    'হালনাগাদ নামজারি পর্চা ও জমাভাগ রেকর্ড (Updated Mutation)',
                    'অনলাইন ভূমি উন্নয়ন কর ও খাজনা পরিশোধ রশিদ',
                    'রাজউক অনুমোদিত ৬ তলা ভবনের স্থাপত্য ও কাঠামোগত নকশা',
                    'ফায়ার সার্ভিস ও পরিবেশ ছাড়পত্র সনদ',
                    '৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পে কার্যকারী চুক্তিপত্র প্রস্তুত',
                ],
                'buyer_details' => [
                    'landUse' => 'Residential',
                    'cornerPlot' => 'No',
                    'roadWidth' => 60,
                    'buildingDescription' => 'লেক ভিউ: ২৩ কাঠা জমিতে ৬ তলা বিশিষ্ট আধুনিক ভবন। কার পার্কিং সংখ্যা ২৮টি।',
                    'utilities' => 'ইউটিলিটি সরবরাহ সম্পূর্ণ আপডেট—বিদ্যুৎ সাবস্টেশন, গ্যাস সংযোগ ও গভীর নলকূপ ওয়াসা।',
                    'priceBasis' => 'Total',
                    'negotiable' => 'Yes',
                    'priceIncludes' => '২৩ কাঠা জমি, ৬ তলা সম্পূর্ণ ভবন ও ২৮টি কার পার্কিং সহ মোট মূল্য।',
                    'depositPercent' => 30,
                    'agreementDuration' => 'চুক্তির তারিখ হতে ৯০ দিন',
                    'paymentSchedule' => 'বায়না ৩০%, কার্যকারী পেমেন্ট শিডিউল ৩০০ টাকার নন-জুডিশিয়াল স্ট্যাম্পের মাধ্যমে চুক্তি সাপেক্ষে।',
                    'paymentMethod' => 'ব্যাংক ড্রাফট / পে-অর্ডারের মাধ্যমে',
                    'buyerCommission' => 1,
                    'sellerCommission' => 2,
                    'registrationCost' => 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন সম্পন্ন করবেন।',
                    'buyerCosts' => 'ক্রেতা নিজ দায়িত্বে রেজিস্ট্রেশন ও হস্তান্তরের খরচ করবেন।',
                    'sellerCosts' => 'সার্ভিস চার্জ ও রাজউক অনুমতি প্রক্রিয়াকরণ।',
                    'transferTimeline' => 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
                    'transferTrigger' => 'রাজউক অনুমোদন ও যৌথ চুক্তি সম্পাদনের পর।',
                    'ownerCount' => 2,
                    'ownershipSource' => 'Purchase',
                    'possession' => 'Owner',
                    'bankLoan' => 'None declared',
                    'existingAgreement' => 'None declared',
                    'saleAuthority' => 'মালিক মোঃ তওফিকুল ইসলাম (প্রতিনিধি: মোঃ আবু হানিফ)',
                    'ownershipNotes' => 'মালিক ২ জন, কোনো ব্যাংক লোন বা আইনি ঝামেলা নেই, সম্পূর্ণ নিষ্কণ্টক।',
                    'mutationStatus' => 'Available',
                    'taxPaidThrough' => 'হালনাগাদ (নামজারি ও খাজনা পরিশোধ করা আছে)',
                    'serviceChargeStatus' => 'আপডেট আছে',
                    'approvalDetails' => 'রাজউক অনুমোদিত ভবন, বিক্রয়ের অনুমতি প্রাপ্তি ১৫ কার্যদিবসের মধ্যে।',
                    'documentSummary' => 'নিষ্কণ্টক / টোটাল কাগজ-পাতি আপডেট কমপ্লিট।',
                    'sourceDate' => '2026-09-15',
                    'updatedOn' => '2026-09-15',
                ],
            ]
        );

        // 5. Seed Viewings for the 4 Properties
        Viewing::updateOrCreate(
            ['id' => 101],
            [
                'name' => 'Shere Ali',
                'phone' => '+880 1711-234567',
                'email' => 'buyer@gbrel.com',
                'contact_method' => 'WhatsApp',
                'property_id' => 1,
                'property_title' => 'Gulshan-1 Prime 15 Katha Residential Land with 2-Storey Building',
                'scheduled_date' => '2026-09-28',
                'scheduled_time' => '03:00 PM - 04:00 PM',
                'vip_pickup' => true,
                'pickup_location' => 'Gulshan Club, Dhaka',
                'assigned_agent' => 'মোঃ আবু হানিফ (Md. Abu Hanif)',
                'status' => 'Confirmed',
            ]
        );

        // 6. Seed Leads
        Lead::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Dr. Farhan Chowdhury',
                'phone' => '+44 7911 123456',
                'email' => 'farhan.chowdhury@nhs.uk',
                'property_title' => 'Gulshan-2 Road 48/4B 31 Katha Commercial Corner Plot & Structure',
                'lead_type' => 'NRB Commercial Investor (UK)',
                'message' => 'Interested in commercial approval verification deeds and bank escrow transfer options for the 31 Katha corner plot.',
                'status' => 'Active',
            ]
        );

        // 7. Seed Financial Transactions
        FinancialTransaction::updateOrCreate(
            ['deal_code' => 'TX-901'],
            [
                'property_title' => '"Lake View" 6-Storey Luxury Edifice on 23 Katha at Road 8, Gulshan-1',
                'buyer_name' => 'Tariqul Islam',
                'transacted_value' => 1350000000,
                'commission_amount' => 27000000,
                'escrow_bank' => 'Standard Chartered Bank Escrow',
                'status' => 'Settled',
            ]
        );

        // 8. Seed Official Brochures Vault
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
