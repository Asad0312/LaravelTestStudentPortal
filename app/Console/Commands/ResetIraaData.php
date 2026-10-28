<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetIraaData extends Command
{
    protected $signature = 'iraa:reset-data {--force : Force the operation without confirmation}';
    protected $description = 'Clear all student, fee, attendance, exam, test data and reset student IDs';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will delete ALL student related data. Are you sure?')) {
            return 0;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('exam_fees')->truncate();
        DB::table('exam_events')->truncate();
        DB::table('marks')->truncate();
        DB::table('tests')->truncate();
        DB::table('attendance')->truncate();
        DB::table('payments')->truncate();
        DB::table('fees')->truncate();
        DB::table('alerts')->truncate();
        DB::table('messages')->truncate();
        DB::table('admissions')->truncate();
        DB::table('students')->truncate();
        DB::table('classes')->truncate();
        DB::table('books')->truncate();
        DB::table('notification_logs')->truncate();
        DB::table('activity_logs')->truncate();
        DB::table('portal_alerts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All student/fee/attendance/exam data cleared.');
        return 0;
    }
}
