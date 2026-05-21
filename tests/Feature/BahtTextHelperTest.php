<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class BahtTextHelperTest extends TestCase
{
    public function test_baht_text_helper_formats_zero_and_integer_amounts(): void
    {
        $this->assertSame('ศูนย์บาทถ้วน', velocrm_baht_text(0));
        $this->assertSame('สิบเอ็ดบาทถ้วน', velocrm_baht_text(11));
        $this->assertSame('ยี่สิบเอ็ดบาทถ้วน', velocrm_baht_text(21));
        $this->assertSame('หนึ่งร้อยเอ็ดบาทถ้วน', velocrm_baht_text(101));
        $this->assertSame('หนึ่งล้านหนึ่งบาทถ้วน', velocrm_baht_text(1000001));
    }

    public function test_baht_text_helper_formats_satang_and_rounding(): void
    {
        $this->assertSame(
            'หนึ่งแสนสองหมื่นสามพันสี่ร้อยห้าสิบหกบาทเจ็ดสิบแปดสตางค์',
            velocrm_baht_text(123456.78)
        );
        $this->assertSame('หนึ่งบาทถ้วน', velocrm_baht_text(0.999));
        $this->assertSame('หนึ่งพันสองร้อยสามสิบสี่บาทห้าสิบสตางค์', velocrm_baht_text('1,234.50'));
    }
}
