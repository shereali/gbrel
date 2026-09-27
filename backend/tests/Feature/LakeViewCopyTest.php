<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Support\LaunchListings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class LakeViewCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_original_seller_text_is_rewritten_but_staff_edits_are_kept(): void
    {
        $property = Property::create([
            'title' => 'লেক ভিউ — গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন',
            'slug' => 'lake-view-gulshan-1-road-8-23-katha-building',
            'address' => 'Road 8',
            'area_name' => 'Gulshan-1',
            'price' => 1350000000,
            'buyer_details' => [
                'transferTimeline' => 'রাজউক থেকে বিক্রয়ের অনুমতি প্রাপ্তি ১৫ (পনের) কার্যদিবস।',
                'ownershipNotes' => 'Edited by staff',
                'depositPercent' => 30,
            ],
        ]);

        LaunchListings::rewriteCopy();
        LaunchListings::rewriteCopy();

        $property->refresh();
        $this->assertSame('গুলশান-১, রোড ৮-এ ২৩ কাঠা জমিসহ ৬ তলা ভবন "লেক ভিউ"', $property->title);
        $this->assertStringContainsString('রাজউকের ওপর নির্ভর করে', $property->buyer_details['transferTimeline']);
        $this->assertSame('Edited by staff', $property->buyer_details['ownershipNotes']);
        $this->assertSame(30, $property->buyer_details['depositPercent']);
    }
}
