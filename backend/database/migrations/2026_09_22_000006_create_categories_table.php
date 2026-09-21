<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('image')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(true);
                $table->timestamps();
            });

            $categories = [
                [
                    'name' => 'Land Share',
                    'slug' => 'land-share',
                    'description' => 'Co-ownership and joint venture land share projects in high-growth corridors',
                    'icon' => 'pie-chart',
                    'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 1
                ],
                [
                    'name' => 'Flat',
                    'slug' => 'flat',
                    'description' => 'Luxury multi-family residential apartments and premium condominium flats',
                    'icon' => 'home',
                    'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 2
                ],
                [
                    'name' => 'Plot',
                    'slug' => 'plot',
                    'description' => 'Residential & demarcated boundary plots in planned townships',
                    'icon' => 'grid',
                    'image' => 'https://images.unsplash.com/photo-1524813686514-a57563d77d61?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 3
                ],
                [
                    'name' => 'Land',
                    'slug' => 'land',
                    'description' => 'Freehold commercial, industrial, and development land in Bigha and Katha',
                    'icon' => 'map',
                    'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 4
                ],
                [
                    'name' => 'Hotel',
                    'slug' => 'hotel',
                    'description' => 'Beachfront resort suites, hotels, and luxury hospitality investments',
                    'icon' => 'briefcase',
                    'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 5
                ],
                [
                    'name' => 'Duplex',
                    'slug' => 'duplex',
                    'description' => 'Multi-level duplex homes, private villas, and high-ceiling residences',
                    'icon' => 'layers',
                    'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 6
                ],
                [
                    'name' => 'Commercial',
                    'slug' => 'commercial',
                    'description' => 'Corporate headquarters, commercial towers, and bank floors',
                    'icon' => 'building',
                    'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 7
                ],
                [
                    'name' => 'Penthouse',
                    'slug' => 'penthouse',
                    'description' => 'Top-floor sky penthouses with panoramic city views and private terraces',
                    'icon' => 'award',
                    'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=800&auto=format&fit=crop',
                    'sort_order' => 8
                ]
            ];

            foreach ($categories as $cat) {
                DB::table('categories')->insert([
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'image' => $cat['image'],
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                    'is_featured' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
