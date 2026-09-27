<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TrackingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_saves_and_clears_tracking_ids_and_they_are_public(): void
    {
        $this->signInAs('admin');
        $this->postJson('/api/settings', ['meta_pixel_id' => '1234567890123456', 'gtm_container_id' => 'GTM-ABC1234', 'ga4_measurement_id' => 'G-XYZ123ABC4'])->assertOk();

        $this->getJson('/api/settings')
            ->assertJsonPath('data.meta_pixel_id', '1234567890123456')
            ->assertJsonPath('data.gtm_container_id', 'GTM-ABC1234')
            ->assertJsonPath('data.ga4_measurement_id', 'G-XYZ123ABC4');

        $this->postJson('/api/settings', ['gtm_container_id' => ''])->assertOk();
        $this->getJson('/api/settings')->assertJsonPath('data.gtm_container_id', '');
    }

    public function test_malformed_ids_are_rejected(): void
    {
        $this->signInAs('admin');
        $this->postJson('/api/settings', ['meta_pixel_id' => '<script>'])->assertUnprocessable();
        $this->postJson('/api/settings', ['gtm_container_id' => 'UA-123'])->assertUnprocessable();
        $this->postJson('/api/settings', ['ga4_measurement_id' => 'G-1"><x'])->assertUnprocessable();
    }
}
