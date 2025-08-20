<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyService $service;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(CompanyService::class);
        $this->company = Company::factory()->create();
    }

    /** @test */
    public function it_updates_company_custom_labels_successfully()
    {
        $labels = [
            [
                'name' => 'Team Meeting',
                'color' => '#3B82F6',
                'description' => 'Regular team meetings and standups'
            ],
            [
                'name' => 'Client Meeting',
                'color' => '#EF4444',
                'description' => 'Client presentations and consultations'
            ]
        ];

        $result = $this->service->updateCustomLabels($this->company->id, $labels);

        $this->assertTrue($result['success']);
        $this->assertEquals('Custom labels updated successfully.', $result['message']);
        $this->assertEquals($labels, $result['data']);
        
        // Check that the labels were saved to the database
        $company = $this->company->fresh();
        $this->assertNotNull($company->custom_labels);
        $this->assertCount(2, $company->custom_labels);
        $this->assertEquals('Team Meeting', $company->custom_labels[0]['name']);
        $this->assertEquals('#3B82F6', $company->custom_labels[0]['color']);
    }

    /** @test */
    public function it_overwrites_existing_custom_labels()
    {
        // Set initial labels
        $initialLabels = [
            [
                'name' => 'Old Label',
                'color' => '#000000',
                'description' => 'Old description'
            ]
        ];
        
        $this->company->update(['custom_labels' => $initialLabels]);
        
        // Update with new labels
        $newLabels = [
            [
                'name' => 'New Label',
                'color' => '#FF0000',
                'description' => 'New description'
            ],
            [
                'name' => 'Another Label',
                'color' => '#00FF00',
                'description' => 'Another description'
            ]
        ];

        $result = $this->service->updateCustomLabels($this->company->id, $newLabels);

        $this->assertTrue($result['success']);
        
        // Check that old labels were replaced
        $company = $this->company->fresh();
        $this->assertCount(2, $company->custom_labels);
        $this->assertEquals('New Label', $company->custom_labels[0]['name']);
        $this->assertEquals('Another Label', $company->custom_labels[1]['name']);
    }

    /** @test */
    public function it_handles_empty_labels_array()
    {
        $labels = [];

        $result = $this->service->updateCustomLabels($this->company->id, $labels);

        $this->assertTrue($result['success']);
        
        $company = $this->company->fresh();
        $this->assertEquals([], $company->custom_labels);
    }

    /** @test */
    public function it_fails_gracefully_when_company_not_found()
    {
        $nonExistentCompanyId = 99999;
        $labels = [['name' => 'Test', 'color' => '#000000']];

        $result = $this->service->updateCustomLabels($nonExistentCompanyId, $labels);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Failed to update custom labels', $result['message']);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        // Simulate database error
        DB::shouldReceive('beginTransaction')->andThrow(new \Exception('Database error'));
        
        $labels = [['name' => 'Test', 'color' => '#000000']];
        $result = $this->service->updateCustomLabels($this->company->id, $labels);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Failed to update custom labels', $result['message']);
    }

    /** @test */
    public function it_gets_custom_labels_successfully()
    {
        $labels = [
            [
                'name' => 'Meeting',
                'color' => '#3B82F6',
                'description' => 'General meetings'
            ]
        ];
        
        $this->company->update(['custom_labels' => $labels]);

        $result = $this->service->getCustomLabels($this->company->id);

        $this->assertNotNull($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Meeting', $result[0]['name']);
        $this->assertEquals('#3B82F6', $result[0]['color']);
    }

    /** @test */
    public function it_returns_null_for_non_existent_company_labels()
    {
        $nonExistentCompanyId = 99999;

        $result = $this->service->getCustomLabels($nonExistentCompanyId);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_company_with_no_labels()
    {
        // Company exists but has no custom labels set
        $result = $this->service->getCustomLabels($this->company->id);

        $this->assertNull($result);
    }

    /** @test */
    public function it_gets_company_successfully()
    {
        $result = $this->service->getCompany($this->company->id);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Company::class, $result);
        $this->assertEquals($this->company->id, $result->id);
        $this->assertEquals($this->company->name, $result->name);
    }

    /** @test */
    public function it_returns_null_for_non_existent_company()
    {
        $nonExistentCompanyId = 99999;

        $result = $this->service->getCompany($nonExistentCompanyId);

        $this->assertNull($result);
    }

    /** @test */
    public function it_checks_if_company_is_active()
    {
        // Test active company
        $this->company->update(['is_active' => true]);
        
        $isActive = $this->service->isCompanyActive($this->company->id);
        $this->assertTrue($isActive);
        
        // Test inactive company
        $this->company->update(['is_active' => false]);
        
        $isActive = $this->service->isCompanyActive($this->company->id);
        $this->assertFalse($isActive);
    }

    /** @test */
    public function it_returns_false_for_non_existent_company_active_check()
    {
        $nonExistentCompanyId = 99999;

        $isActive = $this->service->isCompanyActive($nonExistentCompanyId);

        $this->assertFalse($isActive);
    }

    /** @test */
    public function it_handles_json_encoding_of_complex_labels()
    {
        $complexLabels = [
            [
                'name' => 'Complex Label',
                'color' => '#3B82F6',
                'description' => 'Description with special chars: àáâãäåæçèé',
                'metadata' => [
                    'priority' => 'high',
                    'tags' => ['urgent', 'client-facing'],
                    'settings' => [
                        'reminder' => true,
                        'notification' => false
                    ]
                ]
            ]
        ];

        $result = $this->service->updateCustomLabels($this->company->id, $complexLabels);

        $this->assertTrue($result['success']);
        
        $company = $this->company->fresh();
        $savedLabels = $company->custom_labels;
        
        $this->assertEquals('Complex Label', $savedLabels[0]['name']);
        $this->assertEquals('high', $savedLabels[0]['metadata']['priority']);
        $this->assertContains('urgent', $savedLabels[0]['metadata']['tags']);
        $this->assertTrue($savedLabels[0]['metadata']['settings']['reminder']);
    }
}