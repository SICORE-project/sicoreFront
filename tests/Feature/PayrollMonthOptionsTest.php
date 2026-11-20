<?php

namespace Tests\Feature;

use Tests\TestCase;

class PayrollMonthOptionsTest extends TestCase
{
    public function test_months_are_unique_and_status_free_with_correct_year_ids(): void
    {
        request()->merge(['academic_year_id' => 2]);
        $data = ['academic_years' => [['id' => 2, 'start_date' => '2027-10-01', 'end_date' => '2028-09-30']],
            'periods' => [
                ['id' => 1, 'code' => '2027-04', 'label' => 'Avril 2027 — Clôturée'],
                ['id' => 2, 'code' => '2028-04', 'label' => 'Avril 2028 — Ouverte'],
                ['id' => 3, 'code' => '2028-04', 'label' => 'Avril 2028 — Calculée'],
                ['id' => 4, 'code' => '2028-05', 'label' => 'Mai 2028'],
            ]];
        $html = view('pages.paie.partials.month-options', ['moduleData' => $data, 'monthSelected' => 2])->render();
        $this->assertSame(1, substr_count($html, '>Avril</option>'));
        $this->assertStringContainsString('value="2" selected', $html);
        $this->assertStringNotContainsString('value="1"', $html);
        $this->assertStringNotContainsString('value="3"', $html);
        $this->assertStringNotContainsString('Ouverte', $html);
        $this->assertStringNotContainsString('Clôturée', $html);
        $this->assertStringNotContainsString('Calculée', $html);
        $this->assertStringContainsString('>Mai</option>', $html);
    }

    public function test_without_year_the_selected_period_is_kept_without_duplicate_month(): void
    {
        $html = view('pages.paie.partials.month-options', ['moduleData' => ['periods' => [
            ['id' => 1, 'code' => '2027-04', 'label' => 'Avril 2027'],
            ['id' => 2, 'code' => '2028-04', 'label' => 'Avril 2028'],
        ]], 'monthSelected' => 2])->render();
        $this->assertSame(1, substr_count($html, '>Avril</option>'));
        $this->assertStringContainsString('value="2" selected', $html);
    }
}
