<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed permissions and system roles with RBAC capabilities.
     */
    public function run(): void
    {
        // 1. Seed Permissions
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

        // 2. Seed Roles
        Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Unrestricted enterprise control over all property mandates, users, legal approvals, and financial escrows.',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        Role::firstOrCreate(
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

        Role::firstOrCreate(
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

        Role::firstOrCreate(
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

        Role::firstOrCreate(
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

        Role::firstOrCreate(
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
    }
}
