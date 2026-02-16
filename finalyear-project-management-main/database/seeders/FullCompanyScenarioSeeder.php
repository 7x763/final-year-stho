<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Eloquent\Model;

class FullCompanyScenarioSeeder extends Seeder
{
    public function run(): void
    {
        Model::preventLazyLoading(false);

        try {
            // 1. Setup Users (Team Members)
            $users = [
                'pm' => User::firstOrCreate(['email' => 'pm@company.com'], ['name' => 'Alice (PM)', 'password' => bcrypt('password')]),
                'backend_lead' => User::firstOrCreate(['email' => 'techlead@company.com'], ['name' => 'Bob (Backend Lead)', 'password' => bcrypt('password')]),
                'frontend' => User::firstOrCreate(['email' => 'frontend@company.com'], ['name' => 'Charlie (Frontend)', 'password' => bcrypt('password')]),
                'qa' => User::firstOrCreate(['email' => 'qa@company.com'], ['name' => 'David (QA)', 'password' => bcrypt('password')]),
                'designer' => User::firstOrCreate(['email' => 'design@company.com'], ['name' => 'Eve (Designer)', 'password' => bcrypt('password')]),
            ];

            // Assign Roles (Assuming standard roles exist, or default to member)
            $memberRole = Role::where('name', 'member')->first();
            foreach ($users as $user) {
                $user->load('roles');
                if ($memberRole && $user->roles->isEmpty()) {
                    $user->assignRole($memberRole);
                }
            }

            // 2. Create Project
            $project = Project::create([
                'name' => 'SuperApp Mobile Banking 2025',
                'description' => 'Revamp toàn bộ ứng dụng Mobile Banking với giao diện mới và tích hợp AI.',
                'ticket_prefix' => 'SAM',
                'start_date' => Carbon::parse('2025-01-01'),
                'end_date' => Carbon::parse('2025-06-30'),
            ]);

            // Add members to project
            $project->users()->sync(collect($users)->pluck('id'));

            // 3. Create Statuses (Workflow)
            $statuses = [
                'backlog' => TicketStatus::create(['project_id' => $project->id, 'name' => 'Backlog', 'sort_order' => 1, 'color' => 'gray', 'is_completed' => false]),
                'todo' => TicketStatus::create(['project_id' => $project->id, 'name' => 'To Do', 'sort_order' => 2, 'color' => 'info', 'is_completed' => false]),
                'in_progress' => TicketStatus::create(['project_id' => $project->id, 'name' => 'In Progress', 'sort_order' => 3, 'color' => 'primary', 'is_completed' => false]),
                'review' => TicketStatus::create(['project_id' => $project->id, 'name' => 'Code Review', 'sort_order' => 4, 'color' => 'warning', 'is_completed' => false]),
                'testing' => TicketStatus::create(['project_id' => $project->id, 'name' => 'Testing (QA)', 'sort_order' => 5, 'color' => 'danger', 'is_completed' => false]),
                'done' => TicketStatus::create(['project_id' => $project->id, 'name' => 'Done', 'sort_order' => 6, 'color' => 'success', 'is_completed' => true]),
            ];

            // 4. Priorities (Global or Project specific? Assuming global/check existence)
            $priorities = [
                'low' => TicketPriority::firstOrCreate(['name' => 'Low'], ['color' => 'gray']),
                'medium' => TicketPriority::firstOrCreate(['name' => 'Medium'], ['color' => 'info']),
                'high' => TicketPriority::firstOrCreate(['name' => 'High'], ['color' => 'warning']),
                'critical' => TicketPriority::firstOrCreate(['name' => 'Critical'], ['color' => 'danger']),
            ];

            // 5. Generate Tickets (Phased approach)

            // 5. Generate Tickets (Phased approach) for Main Project
            $this->createTicket($project, 'Thiết kế màn hình Login', $users['designer'], $users['pm'], $statuses['done'], $priorities['medium'], 'Hoàn thiện UI/UX cho màn hình đăng nhập, hỗ trợ Dark Mode.');
            $this->createTicket($project, 'API Login & Register', $users['backend_lead'], $users['pm'], $statuses['done'], $priorities['high'], 'Implement JWT Auth, Rate Limiting.');
            $this->createTicket($project, 'Frontend Login Integration', $users['frontend'], $users['backend_lead'], $statuses['done'], $priorities['high'], 'Ghép API Login vào UI.');
            $t1 = $this->createTicket($project, 'API Chuyển khoản nội bộ', $users['backend_lead'], $users['pm'], $statuses['review'], $priorities['critical'], 'Xử lý logic chuyển tiền, rollback nếu lỗi DB.', Carbon::now()->addDays(2));
            $this->addComment($t1, $users['frontend'], 'API này trả về lỗi 500 khi số dư không đủ, check lại nhé.');
            $this->addComment($t1, $users['backend_lead'], 'Đã fix, trả về 400 Bad Request rồi.');
            $t2 = $this->createTicket($project, 'Màn hình Chuyển khoản', $users['frontend'], $users['pm'], $statuses['testing'], $priorities['high'], 'Form nhập số tài khoản, xác thực tên người nhận tự động.');
            $this->addComment($t2, $users['qa'], 'Nhập số tiền âm vẫn submit được form.');
            $this->addComment($t2, $users['frontend'], 'Oops, để fix validation.');
            $this->createTicket($project, 'Tích hợp cổng VNPay', $users['backend_lead'], $users['pm'], $statuses['todo'], $priorities['high'], 'Đang chờ tài khoản Sandbox từ đối tác.');
            $this->createTicket($project, 'UI Scan QR Code', $users['frontend'], $users['designer'], $statuses['backlog'], $priorities['medium'], 'Tính năng quét QR thanh toán.');
            $bug1 = $this->createTicket($project, '[BUG] App crash trên iOS 15', $users['frontend'], $users['qa'], $statuses['in_progress'], $priorities['critical'], 'Khách hàng báo cáo app bị văng khi mở màn hình Lịch sử giao dịch.', Carbon::now()->subDay());
            $this->addComment($bug1, $users['pm'], 'Cái này gấp nhé, ảnh hưởng khách hàng VIP.');
            $this->addComment($bug1, $users['frontend'], 'Đang điều tra, nghi do thư viện animation cũ.');
            $this->createTicket($project, '[BUG] Sai font chữ tiếng Việt', $users['designer'], $users['qa'], $statuses['todo'], $priorities['low'], 'Một số chỗ bị lỗi hiển thị font.');
            $this->createTicket($project, 'AI Phân tích chi tiêu', $users['backend_lead'], $users['pm'], $statuses['backlog'], $priorities['medium'], 'Gợi ý tiết kiệm dựa trên lịch sử mua sắm.');

            // 6. Create Additional Demo Projects
            $extraProjects = [
                ['E-commerce Platform', 'Hệ thống bán hàng trực tuyến đa kênh.', 'ECOMM', 15],
                ['HR Management System', 'Quản lý nhân sự, chấm công, tính lương.', 'HRM', 8],
                ['AI Content Generator', 'Tool tạo nội dung marketing tự động bằng AI.', 'AICG', 20],
            ];

            foreach ($extraProjects as [$pName, $pDesc, $pPrefix, $ticketCount]) {
                $p = Project::create([
                    'name' => $pName,
                    'description' => $pDesc,
                    'ticket_prefix' => $pPrefix,
                    'start_date' => Carbon::now()->subDays(rand(10, 30)),
                    'end_date' => Carbon::now()->addMonths(rand(3, 12)),
                ]);
                
                // Add all users to these projects too
                $p->users()->sync(collect($users)->pluck('id'));

                // Create default statuses for new project (Simplified)
                $pStatuses = [];
                $statusNames = ['To Do', 'In Progress', 'Done'];
                foreach ($statusNames as $idx => $name) {
                    $color = match($name) { 'To Do' => 'gray', 'In Progress' => 'primary', 'Done' => 'success' };
                    $pStatuses[] = TicketStatus::create([
                        'project_id' => $p->id, 
                        'name' => $name, 
                        'sort_order' => $idx+1, 
                        'color' => $color, 
                        'is_completed' => $name === 'Done'
                    ]);
                }

                // Generate random tickets
                for ($i = 0; $i < $ticketCount; $i++) {
                    $status = $pStatuses[array_rand($pStatuses)];
                    $priority = $priorities[array_rand($priorities)];
                    $assignee = collect($users)->random();
                    $creator = collect($users)->random();
                    
                    $this->createTicket(
                        $p, 
                        "Ticket mẫu #{$i} cho {$pPrefix}", 
                        $assignee, 
                        $creator, 
                        $status, 
                        $priority, 
                        "Mô tả tự động cho ticket {$i}..."
                    );
                }
            }

            \Illuminate\Support\Facades\Log::info("Full Scenario Seeding Completed!");
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error($e->getMessage());
        }
    }


    private function createTicket($project, $name, $assignee, $creator, $status, $priority, $description, $dueDate = null)
    {
        $ticket = Ticket::create([
            'project_id' => $project->id,
            'name' => $name,
            'description' => $description,
            'ticket_status_id' => $status->id,
            'priority_id' => $priority->id,

            'created_by' => $creator->id,
            'due_date' => $dueDate ?? Carbon::now()->addDays(rand(3, 10)),
        ]);
        
        $ticket->assignees()->attach($assignee->id);

        // Generate History
        // 1. Created
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $creator->id,
            'ticket_status_id' => $status->id, // Assuming created directly in target status for simplicity, or we could simulate flow
        ]);
        
        return $ticket;
    }

    private function addComment($ticket, $user, $content)
    {
        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => $content,
        ]);
    }
}
