<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\FeeCarryForward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeCarryForwardTest extends TestCase
{
    use RefreshDatabase;

    public function test_carry_forward_and_payment_rules(): void
    {
        $student = Student::create([
            'student_id'     => 'STU-1',
            'name'           => 'Test Student',
            'father_name'    => 'Test Father',
            'phone'          => '03001234567',
            'class_no'       => 3,
            'section'        => 'A',
            'batch'          => 'Session 2026',
            'admission_date' => '2026-01-05',
            'status'         => 'Active',
            'password'       => bcrypt('test123'),
            'monthly_fee'    => 1800,
            'stream'         => 'school',
            'class_level'    => 3,
        ]);

        $svc = app(FeeCarryForward::class);

        $oct = $svc->generateForStudent($student, '2026-10');
        $this->assertNotNull($oct);
        $this->assertSame(1800, $oct->monthly_amount);
        $this->assertSame(0, $oct->carried_amount);

        $pay = $svc->recordPayment($student, '2026-10', 1000, 'Cash');
        $this->assertNotNull($pay['payment']);
        $this->assertSame(1000, $pay['applied']);

        $nov = $svc->generateForStudent($student, '2026-11');
        $this->assertSame(2600, $nov->monthly_amount);
        $this->assertSame(800, $nov->carried_amount);
        $this->assertSame('2026-10', $nov->carried_from);
        $this->assertSame(800, $oct->fresh()->carried_out);

        // October balance was already carried to November, so it cannot be collected twice.
        $blocked = $svc->recordPayment($student, '2026-10', 500, 'Cash');
        $this->assertNull($blocked['payment']);
        $this->assertSame(0, $blocked['applied']);

        $dec = $svc->generateForStudent($student, '2026-12');
        $this->assertSame(4400, $dec->monthly_amount);
        $this->assertSame(2600, $dec->carried_amount);
        $this->assertSame('2026-11', $dec->carried_from);
        $this->assertSame(2600, $nov->fresh()->carried_out);

        $paid = $svc->recordPayment($student, '2026-12', 4400, 'Cash');
        $this->assertSame(4400, $paid['applied']);
        $this->assertSame(0, $dec->fresh()->outstanding);

        $jan = $svc->generateForStudent($student, '2027-01');
        $this->assertSame(1800, $jan->monthly_amount);
        $this->assertSame(0, $jan->carried_amount);

        // Running generation twice must not create duplicates.
        $this->assertNull($svc->generateForStudent($student, '2027-01'));

        // Inactive students are skipped by the bulk generator.
        $student->update(['status' => 'Inactive']);
        $result = $svc->generate('2027-02');
        $this->assertSame(0, $result['created']);
    }

    public function test_bulk_generation_carries_balance_for_active_students(): void
    {
        $a = Student::create([
            'student_id'     => 'STU-1',
            'name'           => 'Student A',
            'class_no'       => 1,
            'section'        => 'A',
            'admission_date' => '2026-01-01',
            'status'         => 'Active',
            'password'       => bcrypt('x'),
            'monthly_fee'    => 2000,
            'stream'         => 'school',
            'class_level'    => 1,
        ]);
        $b = Student::create([
            'student_id'     => 'STU-2',
            'name'           => 'Student B',
            'class_no'       => 2,
            'section'        => 'A',
            'admission_date' => '2026-01-01',
            'status'         => 'Active',
            'password'       => bcrypt('x'),
            'monthly_fee'    => 2500,
            'stream'         => 'school',
            'class_level'    => 2,
        ]);

        $svc = app(FeeCarryForward::class);
        $first = $svc->generate('2026-10');
        $this->assertSame(2, $first['created']);
        $this->assertSame(0, $first['carried']);

        $svc->recordPayment($a, '2026-10', 500, 'Cash');
        $svc->recordPayment($b, '2026-10', 2500, 'Cash');

        $second = $svc->generate('2026-11');
        $this->assertSame(2, $second['created']);
        $this->assertSame(1500, $second['carried']);
    }
}
