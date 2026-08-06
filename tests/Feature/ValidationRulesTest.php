<?php

namespace Tests\Feature;

use App\Support\ValidationRules;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    public function test_valid_rules_pass_check(): void
    {
        [$ok, $err] = ValidationRules::check('min:5|max:100');
        $this->assertTrue($ok);
        $this->assertNull($err);

        [$ok, $err] = ValidationRules::check('');
        $this->assertTrue($ok);

        [$ok, $err] = ValidationRules::check(null);
        $this->assertTrue($ok);
    }

    public function test_invalid_rules_fail_check(): void
    {
        [$ok, $err] = ValidationRules::check('not_a_real_rule');
        $this->assertFalse($ok);
        $this->assertNotNull($err);

        [$ok, $err] = ValidationRules::check('regex:/[unclosed/');
        $this->assertFalse($ok);

        [$ok, $err] = ValidationRules::check('between:1');
        $this->assertFalse($ok);
    }

    public function test_sanitize_keeps_valid_and_drops_invalid(): void
    {
        $this->assertSame('min:5', ValidationRules::sanitize('min:5|not_a_real_rule'));
        $this->assertSame('', ValidationRules::sanitize('bogus'));
        $this->assertSame('', ValidationRules::sanitize([]));
        $this->assertSame('required|min:3', ValidationRules::sanitize('required|min:3'));
    }
}