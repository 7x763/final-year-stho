<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo người dùng demo
        $users = [
            [
                'name' => 'Nguyễn Văn A',
                'email' => 'vana@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Trần Thị B',
                'email' => 'thib@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Lê Văn C',
                'email' => 'vanc@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        $allUsers = User::all();

        // 2. Tạo mức độ ưu tiên (Priorities)
        $priorities = [
            ['name' => 'Thấp', 'color' => '#22c55e'], // green
            ['name' => 'Trung bình', 'color' => '#eab308'], // yellow
            ['name' => 'Cao', 'color' => '#f97316'], // orange
            ['name' => 'Khẩn cấp', 'color' => '#ef4444'], // red
        ];

        foreach ($priorities as $priorityData) {
            TicketPriority::firstOrCreate(['name' => $priorityData['name']], $priorityData);
        }

        $allPriorities = TicketPriority::all();

        // 3. Tạo dự án demo
        $projects = [
            [
                'name' => 'Xây dựng Website Bán Hàng',
                'description' => 'Dự án phát triển nền tảng thương mại điện tử cho doanh nghiệp.',
                'ticket_prefix' => 'WEB',
                'color' => '#3b82f6',
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ],
            [
                'name' => 'Phát triển App Mobile Quản Lý',
                'description' => 'Ứng dụng di động giúp quản lý kho và đơn hàng trên iOS và Android.',
                'ticket_prefix' => 'APP',
                'color' => '#a855f7',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(2),
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::firstOrCreate(['name' => $projectData['name']], $projectData);

            // Gán thành viên cho dự án
            $project->members()->sync($allUsers->pluck('id'));

            // 4. Tạo trạng thái cho từng dự án
            $statuses = [
                ['name' => 'Cần làm', 'color' => '#64748b', 'is_completed' => false, 'sort_order' => 1],
                ['name' => 'Đang thực hiện', 'color' => '#3b82f6', 'is_completed' => false, 'sort_order' => 2],
                ['name' => 'Đang kiểm tra', 'color' => '#eab308', 'is_completed' => false, 'sort_order' => 3],
                ['name' => 'Hoàn thành', 'color' => '#22c55e', 'is_completed' => true, 'sort_order' => 4],
            ];

            $createdStatuses = [];
            foreach ($statuses as $statusData) {
                $statusData['project_id'] = $project->id;
                $createdStatuses[] = TicketStatus::firstOrCreate(
                    ['name' => $statusData['name'], 'project_id' => $project->id],
                    $statusData
                );
            }

            // 5. Tạo các vé (Tickets) mẫu
            $ticketSamples = [
                [
                    'name' => 'Thiết kế giao diện trang chủ',
                    'description' => 'Thiết kế UI/UX cho trang chủ theo phong cách hiện đại.',
                    'status_index' => 3, // Hoàn thành
                    'priority_index' => 2, // Cao
                ],
                [
                    'name' => 'Thiết lập cơ sở dữ liệu',
                    'description' => 'Thiết kế schema và cài đặt database PostgreSQL.',
                    'status_index' => 1, // Đang thực hiện
                    'priority_index' => 3, // Khẩn cấp
                ],
                [
                    'name' => 'Xây dựng API đăng nhập',
                    'description' => 'Sử dụng Laravel Sanctum để triển khai xác thực.',
                    'status_index' => 0, // Cần làm
                    'priority_index' => 1, // Trung bình
                ],
                [
                    'name' => 'Kiểm thử hiệu năng',
                    'description' => 'Chạy load test cho hệ thống trước khi deploy.',
                    'status_index' => 2, // Đang kiểm tra
                    'priority_index' => 0, // Thấp
                ],
            ];

            foreach ($ticketSamples as $sample) {
                $ticket = Ticket::create([
                    'project_id' => $project->id,
                    'ticket_status_id' => $createdStatuses[$sample['status_index']]->id,
                    'priority_id' => $allPriorities[$sample['priority_index']]->id,
                    'name' => $sample['name'],
                    'description' => $sample['description'],
                    'start_date' => now(),
                    'due_date' => now()->addWeeks(2),
                    'created_by' => $allUsers->first()->id,
                ]);

                // Gán người phụ trách ngẫu nhiên
                $ticket->assignees()->attach($allUsers->random()->id);
            }
        }
    }
}
