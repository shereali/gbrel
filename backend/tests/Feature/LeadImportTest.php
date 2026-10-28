<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;
use ZipArchive;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_leads_from_csv_and_handle_duplicates(): void
    {
        $admin = $this->signInAs('admin', ['name' => 'Admin Importer']);
        $staff = User::create([
            'name' => 'Advisor Karim',
            'email' => 'karim@example.test',
            'password' => bcrypt('secret-password'),
            'role' => 'staff',
        ]);

        $csvContent = implode("\n", [
            'Name,Phone,Email,Project,Status,Budget,Preferred Floor,Notes',
            '"Rahim Uddin",01711223344,rahim@example.com,"River View Duplex",Contacted,"1.5 Cr","5th Floor","Interested in corner plot"',
            '"Karim Mia",1812345678,karim@example.com,"Green Valley",New,"80 Lac","3rd Floor","Needs bank loan"',
            '"Invalid Phone",12345,bad@example.com,"Green Valley",New,"50 Lac","2nd Floor","Too short"',
        ]);

        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->postJson('/api/leads/import', [
            'file' => $file,
            'assigned_to' => $staff->id,
            'duplicate_mode' => 'skip',
        ])->assertOk();

        $response->assertJsonPath('data.created', 2);
        $response->assertJsonPath('data.failed', 1);
        $response->assertJsonPath('data.skipped', 0);

        $rahim = Lead::where('phone', '+8801711223344')->firstOrFail();
        $this->assertSame('Rahim Uddin', $rahim->name);
        $this->assertSame('River View Duplex', $rahim->property_title);
        $this->assertSame('Contacted', $rahim->status);
        $this->assertSame('1.5 Cr', $rahim->budget_range);
        $this->assertSame($staff->id, $rahim->assigned_to);
        $this->assertSame([['question' => 'Preferred Floor', 'answer' => '5th Floor']], $rahim->meta_answers);
        $this->assertStringContainsString('Preferred Floor: 5th Floor', (string) $rahim->message);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $rahim->id,
            'user_id' => $admin->id,
            'type' => 'manual',
        ]);

        // Excel stripped leading zero on 1812345678 -> normalized to +8801812345678
        $this->assertDatabaseHas('leads', [
            'name' => 'Karim Mia',
            'phone' => '+8801812345678',
        ]);

        // Re-importing with duplicate_mode=skip skips both existing numbers
        $file2 = UploadedFile::fake()->createWithContent('leads2.csv', $csvContent);
        $resSkip = $this->postJson('/api/leads/import', [
            'file' => $file2,
            'duplicate_mode' => 'skip',
        ])->assertOk();
        $resSkip->assertJsonPath('data.created', 0);
        $resSkip->assertJsonPath('data.skipped', 2);

        // Re-importing with duplicate_mode=update updates existing leads
        $csvUpdate = implode("\n", [
            'Name,Phone,Status,Budget,Preferred Floor',
            '"Rahim Uddin Updated",01711223344,Qualified,"2.0 Cr","Top Floor"',
        ]);
        $file3 = UploadedFile::fake()->createWithContent('leads_update.csv', $csvUpdate);
        $resUpdate = $this->postJson('/api/leads/import', [
            'file' => $file3,
            'duplicate_mode' => 'update',
        ])->assertOk();
        $resUpdate->assertJsonPath('data.updated', 1);

        $rahim->refresh();
        $this->assertSame('Rahim Uddin Updated', $rahim->name);
        $this->assertSame('Qualified', $rahim->status);
        $this->assertSame('2.0 Cr', $rahim->budget_range);
        $this->assertSame([['question' => 'Preferred Floor', 'answer' => 'Top Floor']], $rahim->meta_answers);
    }

    public function test_can_import_meta_utf16le_tsv_and_native_xlsx(): void
    {
        $this->signInAs('admin');

        // 1. Meta Lead Ads UTF-16LE TSV with p: and l: prefixes
        $tsvUtf8 = implode("\t", ['id', 'created_time', 'ad_id', 'campaign_name', 'form_id', 'form_name', 'platform', 'कब_কিনতে_চান', 'full_name', 'phone_number'])."\n"
            .implode("\t", ['l:9988776655', '2026-10-20T10:00:00+06:00', 'a:1122', 'Mirpur Campaign', 'f:3344', 'Mirpur DOHS Form', 'fb', 'এই_মাসেই', 'Tanvir Ahmed', 'p:+8801911223344']);
        $utf16le = "\xFF\xFE".mb_convert_encoding($tsvUtf8, 'UTF-16LE', 'UTF-8');

        $metaFile = UploadedFile::fake()->createWithContent('meta_leads.csv', $utf16le);
        $this->postJson('/api/leads/import', ['file' => $metaFile])
            ->assertOk()
            ->assertJsonPath('data.created', 1);

        $metaLead = Lead::where('phone', '+8801911223344')->firstOrFail();
        $this->assertSame('Tanvir Ahmed', $metaLead->name);
        $this->assertSame('9988776655', $metaLead->meta_lead_id);
        $this->assertSame('Facebook Lead Ad', $metaLead->lead_type);
        $this->assertSame('Mirpur DOHS Form', $metaLead->property_title);

        // 2. Native .xlsx file created with ZipArchive
        $xlsxPath = tempnam(sys_get_temp_dir(), 'leads_xlsx_').'.xlsx';
        $zip = new ZipArchive;
        $zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8"?>
            <sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="6" uniqueCount="6">
                <si><t>Full Name</t></si>
                <si><t>Mobile</t></si>
                <si><t>Project</t></si>
                <si><t>Payment Plan</t></si>
                <si><t>Nusrat Jahan</t></si>
                <si><t>01611998877</t></si>
                <si><t>Grambangla Heights</t></si>
                <si><t>Full Cash</t></si>
            </sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?>
            <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
                <sheetData>
                    <row r="1">
                        <c r="A1" t="s"><v>0</v></c>
                        <c r="B1" t="s"><v>1</v></c>
                        <c r="C1" t="s"><v>2</v></c>
                        <c r="D1" t="s"><v>3</v></c>
                    </row>
                    <row r="2">
                        <c r="A2" t="s"><v>4</v></c>
                        <c r="B2" t="s"><v>5</v></c>
                        <c r="C2" t="s"><v>6</v></c>
                        <c r="D2" t="s"><v>7</v></c>
                    </row>
                </sheetData>
            </worksheet>');
        $zip->close();

        $xlsxUpload = new UploadedFile($xlsxPath, 'buyers.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->postJson('/api/leads/import', ['file' => $xlsxUpload])
            ->assertOk()
            ->assertJsonPath('data.created', 1);

        @unlink($xlsxPath);

        $xlsxLead = Lead::where('phone', '+8801611998877')->firstOrFail();
        $this->assertSame('Nusrat Jahan', $xlsxLead->name);
        $this->assertSame('Grambangla Heights', $xlsxLead->property_title);
        $this->assertSame([['question' => 'Payment Plan', 'answer' => 'Full Cash']], $xlsxLead->meta_answers);
    }
}
