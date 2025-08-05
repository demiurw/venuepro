<?php

namespace Tests\Unit\Enums;

use App\Enums\UserStatus;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_enum_values()
    {
        $this->assertEquals('active', UserStatus::ACTIVE->value);
        $this->assertEquals('inactive', UserStatus::INACTIVE->value);
        $this->assertEquals('pending', UserStatus::PENDING->value);
    }

    /** @test */
    public function it_can_get_all_values_as_array()
    {
        $values = UserStatus::values();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('active', $values);
        $this->assertContains('inactive', $values);
        $this->assertContains('pending', $values);
    }

    /** @test */
    public function it_provides_correct_labels()
    {
        $this->assertEquals('Active', UserStatus::ACTIVE->label());
        $this->assertEquals('Inactive', UserStatus::INACTIVE->label());
        $this->assertEquals('Pending Verification', UserStatus::PENDING->label());
    }

    /** @test */
    public function it_provides_correct_descriptions()
    {
        $this->assertEquals('User can access the system normally', UserStatus::ACTIVE->description());
        $this->assertEquals('User account is disabled', UserStatus::INACTIVE->description());
        $this->assertEquals('User needs to verify their account', UserStatus::PENDING->description());
    }

    /** @test */
    public function it_correctly_determines_system_access()
    {
        $this->assertTrue(UserStatus::ACTIVE->allowsAccess());
        $this->assertFalse(UserStatus::INACTIVE->allowsAccess());
        $this->assertFalse(UserStatus::PENDING->allowsAccess());
    }

    /** @test */
    public function it_correctly_determines_verification_requirement()
    {
        $this->assertFalse(UserStatus::ACTIVE->requiresVerification());
        $this->assertFalse(UserStatus::INACTIVE->requiresVerification());
        $this->assertTrue(UserStatus::PENDING->requiresVerification());
    }

    /** @test */
    public function it_provides_correct_css_classes()
    {
        $this->assertEquals('text-green-600 bg-green-50', UserStatus::ACTIVE->cssClass());
        $this->assertEquals('text-red-600 bg-red-50', UserStatus::INACTIVE->cssClass());
        $this->assertEquals('text-yellow-600 bg-yellow-50', UserStatus::PENDING->cssClass());
    }

    /** @test */
    public function it_can_be_created_from_string_value()
    {
        $this->assertEquals(UserStatus::ACTIVE, UserStatus::from('active'));
        $this->assertEquals(UserStatus::INACTIVE, UserStatus::from('inactive'));
        $this->assertEquals(UserStatus::PENDING, UserStatus::from('pending'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_value()
    {
        $this->expectException(\ValueError::class);
        UserStatus::from('invalid');
    }

    /** @test */
    public function it_can_be_used_in_comparisons()
    {
        $status1 = UserStatus::ACTIVE;
        $status2 = UserStatus::ACTIVE;
        $status3 = UserStatus::INACTIVE;

        $this->assertTrue($status1 === $status2);
        $this->assertFalse($status1 === $status3);
    }

    /** @test */
    public function it_can_be_used_in_match_expressions()
    {
        $result = match(UserStatus::ACTIVE) {
            UserStatus::ACTIVE => 'active_user',
            UserStatus::INACTIVE => 'inactive_user',
            UserStatus::PENDING => 'pending_user',
        };

        $this->assertEquals('active_user', $result);
    }

    /** @test */
    public function it_serializes_correctly_to_string()
    {
        $this->assertEquals('active', (string) UserStatus::ACTIVE);
        $this->assertEquals('inactive', (string) UserStatus::INACTIVE);
        $this->assertEquals('pending', (string) UserStatus::PENDING);
    }
}