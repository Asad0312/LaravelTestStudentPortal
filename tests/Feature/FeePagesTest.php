<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Fee;
use App\Models\Student;
use App\Services\FeeCarryForward;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_fee_page_filter_payment_and_views(): void
    {
        $admin = Admin::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'Headmaster',
        ]);

        $student = Student::create([
            'student_id'     => 'STU-1',
            'name'           => 'Test Student',
            'father_name'    => 'Test Father',
            'phone'          => '03001234567',
            'class_no'       => 3,
            'section'        => 'A',
            'admission_date' => '2026-01-05',
            'status'         => 'Active',
            'password'       => bcrypt('x'),
            'monthly_fee'    => 1800,
            'stream'         => 'school',
            'class_level'    => 3,
        ]);

        // Opening the fee page auto-creates the current month's row.
        $this->withSession(['admin_id' => $admin->id])
            ->get('/admin/fees?q=STU-1')
            ->assertOk()
            ->assertSee('STU-1')
            ->assertSee('Test Student');

        $this->assertDatabaseHas('fees', [
            'student_id' => $student->id,
            'month'      => '2026-10',
            'monthly_amount' => 1800,
        ]);

        // Filter by exact student ID.
        $this->withSession(['admin_id' => $admin->id])
            ->get('/admin/fees?q=STU-1')
            ->assertOk()
            ->assertSee('Test Student');

        // Unknown ID shows the empty state.
        $this->withSession(['admin_id' => $admin->id])
            ->get('/admin/fees?q=STU-999')
            ->assertOk()
            ->assertSee('koi student nahi mila');

        // Payment through the modal endpoint.
        $this->withSession(['admin_id' => $admin->id])
            ->post('/admin/fees/payment', [
                'student_id'     => $student->id,
                'month'          => '2026-10',
                'amount'         => 1000,
                'method'         => 'Cash',
                'monthly_amount' => 1800,
            ])
            ->assertRedirect();

        $this->assertSame(1000, (int) Fee::where('student_id', $student->id)->where('month', '2026-10')->value('paid_amount'));

        // Carry-forward button creates next month's row.
        Carbon::setTestNow('2026-11-01 00:20:00');

        $this->withSession(['admin_id' => $admin->id])
            ->post('/admin/fees/generate')
            ->assertRedirect();

        $this->assertDatabaseHas('fees', [
            'student_id'     => $student->id,
            'month'          => '2026-10',
            'carried_out'    => 800,
        ]);

        $this->assertDatabaseHas('fees', [
            'student_id'     => $student->id,
            'month'          => '2026-11',
            'monthly_amount' => 2600,
            'carried_amount' => 800,
        ]);

        // History and slip render with carry information.
        $this->withSession(['admin_id' => $admin->id])
            ->get('/admin/fees/history/' . $student->id)
            ->assertOk()
            ->assertSee('Carried');

        $this->withSession(['admin_id' => $admin->id])
            ->get('/admin/fees/slip/' . $student->id . '/2026-10')
            ->assertOk()
            ->assertSee('CARRIED TO NEXT MONTH');

        // Portal fee card and ID card render.
        $this->withSession(['student_id' => $student->id])
            ->get('/portal')
            ->assertOk()
            ->assertSee('Total Payable');

        $this->withSession(['student_id' => $student->id])
            ->get('/portal/id-card')
            ->assertOk()
            ->assertSee('Student Identity Card')
            ->assertSee('STU-1')
            ->assertSee('Test Student');
    }

    public function test_generate_next_month_uses_carry_forward(): void
    {
        $student = Student::create([
            'student_id'     => 'STU-2',
            'name'           => 'Carry Student',
            'class_no'       => 4,
            'section'        => 'B',
            'admission_date' => '2026-01-01',
            'status'         => 'Active',
            'password'       => bcrypt('x'),
            'monthly_fee'    => 2000,
            'stream'         => 'school',
            'class_level'    => 4,
        ]);

        $svc = app(FeeCarryForward::class);
        $svc->generateForStudent($student, '2026-10');
        $svc->recordPayment($student, '2026-10', 500, 'Cash');

        // Simulate November.
        Carbon::setTestNow('2026-11-01 00:20:00');
        $svc->generate('2026-11');

        $nov = Fee::where('student_id', $student->id)->where('month', '2026-11')->first();
        $this->assertNotNull($nov);
        $this->assertSame(3500, (int) $nov->monthly_amount);
        $this->assertSame(1500, (int) $nov->carried_amount);
        $this->assertSame('2026-10', $nov->carried_from);

        $oct = Fee::where('student_id', $student->id)->where('month', '2026-10')->first();
        $this->assertSame(1500, (int) $oct->carried_out);
        $this->assertSame(0, $oct->outstanding);
    }
}
