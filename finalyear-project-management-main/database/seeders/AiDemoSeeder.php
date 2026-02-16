<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Users team
        $dev1 = User::firstOrCreate(['email' => 'dev1@demo.com'], ['name' => 'Nguyễn Văn Dev A', 'password' => bcrypt('password')]);
        $dev2 = User::firstOrCreate(['email' => 'dev2@demo.com'], ['name' => 'Trần Thị Dev B', 'password' => bcrypt('password')]);
        $qa1 = User::firstOrCreate(['email' => 'qa1@demo.com'], ['name' => 'Lê Văn QA', 'password' => bcrypt('password')]);

        // 2. Scenario A: Dự án "Sắp chết" (At Risk) - Nhiều overdue, bottleneck
        $pRisk = Project::create([
            'name' => 'Dự án Cổng thanh toán (Demo Risk)',
            'description' => 'Tích hợp cổng thanh toán quốc tế.',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addDays(5), // Sắp hết hạn
            'ticket_prefix' => 'PAY',
        ]);
        $pRisk->users()->sync([$dev1->id, $dev2->id, $qa1->id]);

        // Statuses
        $sTodo = TicketStatus::firstOrCreate(['project_id' => $pRisk->id, 'name' => 'To Do', 'sort_order' => 1]);
        $sInPro = TicketStatus::firstOrCreate(['project_id' => $pRisk->id, 'name' => 'In Progress', 'sort_order' => 2]);
        $sReview = TicketStatus::firstOrCreate(['project_id' => $pRisk->id, 'name' => 'Code Review', 'sort_order' => 3]);
        $sDone = TicketStatus::firstOrCreate(['project_id' => $pRisk->id, 'name' => 'Done', 'sort_order' => 4, 'is_completed' => true]);

        // Overdue Tickets (Dev A ôm việc)
        for ($i = 1; $i <= 5; $i++) {
            $t = Ticket::create([
                'project_id' => $pRisk->id,
                'name' => "Tích hợp API Bank $i (Quá hạn)",
                'ticket_status_id' => $sInPro->id,
                'priority_id' => 1, // High/Critical (giả lập ID)
                'created_by' => $dev1->id,
                'due_date' => now()->subDays(rand(1, 5)), // Đã quá hạn
            ]);
            $t->assignees()->sync([$dev1->id]);
            
            // Fake History: Kẹt ở In Progress 10 ngày
            DB::table('ticket_histories')->insert([
                'ticket_id' => $t->id,
                'ticket_status_id' => $sInPro->id,
                'user_id' => $dev1->id,
                'created_at' => now()->subDays(10), 
                'updated_at' => now()->subDays(10),
            ]);
        }

        // Bottleneck Ticket at Review
        $tReview = Ticket::create([
            'project_id' => $pRisk->id,
            'name' => "Kiểm thử bảo mật (Kẹt Review)",
            'ticket_status_id' => $sReview->id,
            'due_date' => now()->addDays(1),
        ]);
        $tReview->assignees()->sync([$dev2->id]);
        DB::table('ticket_histories')->insert([
            'ticket_id' => $tReview->id,
            'ticket_status_id' => $sReview->id,
            'user_id' => $dev2->id,
            'created_at' => now()->subDays(15), // Kẹt 15 ngày
        ]);

        // 3. Scenario B: Dự án "Khỏe mạnh" (On Track)
        $pGood = Project::create([
            'name' => 'Dự án Landing Page (Demo Good)',
            'description' => 'Trang giới thiệu sản phẩm.',
            'start_date' => now()->subWeek(),
            'end_date' => now()->addMonths(1),
            'ticket_prefix' => 'LAND',
        ]);
        $pGood->users()->sync([$dev2->id]);
        
        $sGoodDone = TicketStatus::firstOrCreate(['project_id' => $pGood->id, 'name' => 'Done', 'is_completed' => true]);
        
        // Completed Tickets
        for ($i = 1; $i <= 8; $i++) {
            Ticket::create([
                'project_id' => $pGood->id,
                'name' => "Thiết kế Section $i",
                'ticket_status_id' => $sGoodDone->id,
                'created_by' => $dev2->id,
            ]);
        }
    }
}
