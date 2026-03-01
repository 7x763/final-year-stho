<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ActivityDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Roles exist
        $this->call(RoleSeeder::class);

        // 2. Create Users with specific roles
        $usersData = [
            [
                'name' => 'Super Admin Demo',
                'email' => 'superadmin@demo.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Project Manager Demo',
                'email' => 'manager@demo.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Developer Demo',
                'email' => 'developer@demo.com',
                'password' => Hash::make('password'),
                'role' => 'member',
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $roleName = $data['role'];
            unset($data['role']);
            
            $user = User::firstOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$roleName]);
            $users[$roleName] = $user;
        }

        // 3. Create Demo Project
        $project = Project::firstOrCreate(
            ['name' => 'Hệ thống Quản lý Dự án 2026'],
            [
                'description' => 'Dự án mẫu để chạy demo với các hoạt động từ tháng 1/2026.',
                'ticket_prefix' => 'DEMO',
                'color' => '#10b981',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ]
        );

        // Associate users with project
        $project->members()->sync(array_map(fn($u) => $u->id, array_values($users)));

        // 4. Create Ticket Statuses
        $statusesRaw = [
            ['name' => 'Cần làm', 'color' => '#64748b', 'is_completed' => false, 'sort_order' => 1],
            ['name' => 'Đang thực hiện', 'color' => '#3b82f6', 'is_completed' => false, 'sort_order' => 2],
            ['name' => 'Đang kiểm tra', 'color' => '#eab308', 'is_completed' => false, 'sort_order' => 3],
            ['name' => 'Hoàn thành', 'color' => '#22c55e', 'is_completed' => true, 'sort_order' => 4],
        ];

        $statuses = [];
        foreach ($statusesRaw as $statusData) {
            $statusData['project_id'] = $project->id;
            $statuses[] = TicketStatus::firstOrCreate(
                ['name' => $statusData['name'], 'project_id' => $project->id],
                $statusData
            );
        }

        // 5. Create Ticket Priorities
        $prioritiesRaw = [
            ['name' => 'Thấp', 'color' => '#22c55e'],
            ['name' => 'Trung bình', 'color' => '#eab308'],
            ['name' => 'Cao', 'color' => '#f97316'],
            ['name' => 'Khẩn cấp', 'color' => '#ef4444'],
        ];

        $priorities = [];
        foreach ($prioritiesRaw as $priorityData) {
            $priorities[] = TicketPriority::firstOrCreate(['name' => $priorityData['name']], $priorityData);
        }

        // 6. Generate Activity (January - February 2026)
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now(); // Feb 28, 2026

        $ticketNames = [
            'Phân tích yêu cầu hệ thống',
            'Thiết kế database schema',
            'Setup môi trường development',
            'Xây dựng module xác thực (Auth)',
            'Phát triển API cho Project Management',
            'Thiết kế giao diện Kanban Board',
            'Tích hợp AI Assistant',
            'Viết unit tests cho các model chính',
            'Báo cáo tiến độ tháng 1',
            'Fix bug UI trên thiết bị di động',
            'Triển khai tính năng thông báo (Push)',
            'Tối ưu hóa query database',
        ];

        foreach ($ticketNames as $index => $name) {
            // Random date between Jan 1 and now
            $createdAt = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($endDate)));
            
            $ticket = Ticket::create([
                'project_id' => $project->id,
                'ticket_status_id' => $statuses[rand(0, 3)]->id,
                'priority_id' => $priorities[rand(0, 3)]->id,
                'name' => $name,
                'description' => "Mô tả chi tiết cho nhiệm vụ: $name",
                'start_date' => $createdAt->toDateString(),
                'due_date' => $createdAt->copy()->addWeeks(2)->toDateString(),
                'created_by' => $users['admin']->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Assign users
            $ticket->assignees()->attach($users['member']->id);
            if (rand(0, 1)) {
                $ticket->assignees()->attach($users['admin']->id);
            }

            // 7. Simulated interactions (Comments)
            if (rand(0, 3) > 0) { // 75% chance of comment
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $users['member']->id,
                    'comment' => 'Tôi đang bắt đầu thực hiện nhiệm vụ này.',
                    'created_at' => $createdAt->copy()->addHours(2),
                ]);
            }

            if (rand(0, 3) > 1) { // 50% chance of manager feedback
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $users['admin']->id,
                    'comment' => 'Lưu ý kiểm tra kỹ phần bảo mật nhé.',
                    'created_at' => $createdAt->copy()->addDays(1),
                ]);
            }

            // 8. Ticket History
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $users['member']->id,
                'ticket_status_id' => $ticket->ticket_status_id,
                'created_at' => $createdAt->copy()->addDays(1)->addHours(2),
            ]);
        }
        
        $this->command->info('ActivityDemoSeeder completed successfully!');
    }
}
