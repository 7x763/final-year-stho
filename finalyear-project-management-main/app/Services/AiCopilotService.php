<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Collection;

class AiCopilotService
{

    /**
     * Answer a user's question about a project.
     */
    public function ask(string $question, ?int $projectId = null): string
    {
        if (!$question) {
            return "Tôi có thể giúp gì cho bạn về dự án này?";
        }

        // 1. Phát hiện ý định
        $isTechnical = $this->detectIntent($question);

        // 2. Chuẩn bị ngữ cảnh & Prompt
        if ($isTechnical) {
            $systemPrompt = "Bạn là một Senior Laravel Developer & Technical Lead có nhiều năm kinh nghiệm. \n";
            $systemPrompt .= "NHIỆM VỤ: Hỗ trợ lập trình viên giải quyết các vấn đề kỹ thuật, đưa ra code mẫu tối ưu, giải thích các khái niệm lập trình (Laravel, PHP, VueJS, MySQL...). \n";
            $systemPrompt .= "QUY TẮC: \n";
            $systemPrompt .= "- Trả lời CHUYÊN SÂU về kỹ thuật. \n";
            $systemPrompt .= "- Luôn đưa ra ví dụ code (code snippets) nếu có thể. \n";
            $systemPrompt .= "- Giải thích ngắn gọn, đi thẳng vào vấn đề. \n";
            $systemPrompt .= "- Nếu câu hỏi quá mơ hồ, hãy yêu cầu thêm thông tin (code, error log). \n";
            
            // Ngữ cảnh cho các câu hỏi kỹ thuật có thể là stack công nghệ của dự án hoặc các phương pháp hay nhất nói chung
            // Hiện tại, chúng tôi không lấy dữ liệu DB cho các câu hỏi kỹ thuật trừ khi thực sự cần thiết, 
            // để tránh làm nhiễu ngữ cảnh với các ticket không liên quan.
            $context = "Ngôn ngữ: PHP (Laravel), Database: MySQL. \n"; 
        } else {
            // Ngữ cảnh Quản lý/Dự án
            $context = $this->retrieveContext($question, $projectId);
            
            $systemPrompt = "Bạn là AI Copilot chuyên nghiệp hỗ trợ quản lý dự án. \n";
            $systemPrompt .= "NHIỆM VỤ: Chỉ trả lời các câu hỏi liên quan đến dự án dựa trên thông tin được cung cấp dưới đây. \n";
            $systemPrompt .= "QUY TẮC: \n";
            $systemPrompt .= "- Nếu câu hỏi không liên quan đến Quản lý dự án hoặc thông tin ngữ cảnh, hãy từ chối trả lời lịch sự.\n";
            $systemPrompt .= "- Trả lời ngắn gọn, súc tích, chuyên nghiệp.\n\n";
            $systemPrompt .= "THÔNG TIN DỰ ÁN (CONTEXT):\n{$context}\n\n";
        }

        // 3. Gọi Google Gemini API
        try {
            $apiKey = trim(config('services.gemini.api_key'));
            // Use gemini-1.5-flash (Updated model name if needed, assuming 2.5 was a typo strictly speaking usually 1.5 is standard, but keeping user's 2.5 if that worked, though 1.5-flash is current standard for speed)
            // Let's stick to what was there or use a standard one. The previous code had `gemini-2.5-flash` which might be a custom or preview. 
            // I will keep the original model string if it works, but `gemini-1.5-flash` is safer. Let's start with keeping it as is or safe.
            // Actually, let's just use the URL construction as before but variable system prompt.
            // $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}"; // Using a generally available fast model or keep previous?
             // Sử dụng gemini-2.5-flash (Model khả dụng cho API Key này)
             $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";


            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false,
                'force_ip_resolve' => 'v4', // Bắt buộc dùng IPv4 để tránh lỗi DNS
            ])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $systemPrompt . "\n\nUser Question: " . $question]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $response->body());
                throw new \Exception("Gemini API Error: " . $response->status() . " - " . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                 throw new \Exception("Invalid response format from Gemini.");
            }

            return $data['candidates'][0]['content']['parts'][0]['text'];

        } catch (\Exception $e) {
            return "Xin lỗi, tôi đang gặp sự cố kết nối với Gemini AI: " . $e->getMessage();
        }
    }

    /**
     * Phát hiện xem câu hỏi liên quan đến kỹ thuật hay quản lý.
     */
    private function detectIntent(string $question): bool
    {
        $technicalKeywords = [
            // Tiếng Anh
            'code', 'bug', 'error', 'exception', 'php', 'laravel', 'mysql', 
            'controller', 'model', 'view', 'route', 'api', 'deploy', 'git', 
            'database', 'query', 'sql', 'function', 'class', 'method', 'variable',
            'example', 'implement', 'fix', 'debug', 'console', 'terminal', 
            'composer', 'npm', 'yarn', 'run', 'import', 'export', 'algorithm', 
            'logic', 'calculate', 'sort', 'search', 'optimize',
            
            // Tiếng Việt
            'lỗi', 'hàm', 'biến', 'viết code', 'viết hàm', 'tạo api', 'sửa lỗi', 'xây dựng hệ thống', 'lập trình',
            'hướng dẫn', 'làm sao để', 'làm thế nào', 'giải pháp', 'đoạn mã',
            'kết nối db', 'cấu hình', 'triển khai', 'cơ sở dữ liệu', 'thư viện',
            'thuật toán', 'logic', 'tính toán', 'sắp xếp', 'tìm kiếm', 'tối ưu', 'số lớn nhất'
        ];

        $questionLower = mb_strtolower($question);

        foreach ($technicalKeywords as $keyword) {
            if (str_contains($questionLower, $keyword)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Lấy dữ liệu liên quan dựa trên từ khóa.
     */
    private function retrieveContext(string $question, ?int $projectId): string
    {
        $context = "";
        $question = strtolower($question);

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $context .= "Dự án hiện tại: {$project->name}\n";
            } else {
                $projectId = null; 
            }
        } else {
            // 1. Kiểm tra câu hỏi toàn cục (Tất cả dự án / So sánh / Rủi ro)
            if (str_contains($question, 'tất cả dự án') || str_contains($question, 'dự án nào') || str_contains($question, 'so sánh') || str_contains($question, 'thất bại') || str_contains($question, 'rủi ro')) {
                 $projects = Project::withCount(['tickets', 'members'])->get();
                 
                 $context .= "Tổng quan tất cả dự án:\n";
                 foreach ($projects as $p) {
                     $openTickets = $p->tickets()->whereHas('status', fn($q) => $q->where('is_completed', false))->count();
                     $overdueTickets = $p->tickets()
                        ->where('due_date', '<', now())
                        ->whereHas('status', fn($q) => $q->where('is_completed', false))
                        ->count();
                     
                     $context .= "- {$p->name}: Ticket mở ({$openTickets}), Quá hạn ({$overdueTickets}), Deadline ({$p->end_date})\n";
                 }
                 return $context; // Trả về ngay, không cần tìm project cụ thể nữa
            }

            // 2. Cố gắng tìm dự án theo tên trong câu hỏi
            // Regex để bắt mẫu "dự án X" hoặc "project X"
            if (preg_match('/dự án\s+([\w\s]+)/iu', $question, $matches) || preg_match('/project\s+([\w\s]+)/iu', $question, $matches)) {
                $possibleName = trim($matches[1]);
                // Tìm kiếm mờ đơn giản hoặc gần đúng
                $project = Project::where('name', 'LIKE', "%{$possibleName}%")->first();
                if ($project) {
                     $projectId = $project->id;
                     $context .= "Dự án được nhắc đến: {$project->name}\n";
                }
            }
        }

        // Từ khóa: "ticket", "vé", "task"
        if (str_contains($question, 'ticket') || str_contains($question, 'vé') || str_contains($question, 'task') || str_contains($question, 'công việc')) {
            $query = Ticket::query();
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
            
            // Từ khóa: "quá hạn", "chậm", "overdue"
            if (str_contains($question, 'quá hạn') || str_contains($question, 'chậm') || str_contains($question, 'overdue')) {
                $query->where('due_date', '<', now())->whereDoesntHave('status', fn($q) => $q->where('is_completed', true));
                $context .= "Các ticket quá hạn:\n";
            }
            // Từ khóa: "tôi", "my"
            elseif (str_contains($question, 'tôi') || str_contains($question, 'của tôi') || str_contains($question, 'my')) {
                 $query->whereHas('assignees', fn($q) => $q->where('user_id', auth()->id()));
                 $context .= "Các ticket của bạn:\n";
            }
            else {
                $query->limit(10); // Giới hạn mặc định
                $context .= "Danh sách ticket gần đây:\n";
            }

            $tickets = $query->with(['status', 'assignees'])->latest()->get()->take(10);
            
            if ($tickets->isEmpty()) {
                $context .= "(Không tìm thấy ticket nào)\n";
            } else {
                foreach ($tickets as $ticket) {
                    $assignees = $ticket->assignees->pluck('name')->join(', ');
                    $context .= "- [#{$ticket->id}] {$ticket->name} (Status: {$ticket->status?->name}, Assignee: {$assignees}, Due: {$ticket->due_date})\n";
                }
            }
        }

        // Từ khóa: "thành viên", "member", "ai" (ai làm)
        if (str_contains($question, 'thành viên') || str_contains($question, 'member') || str_contains($question, 'ai làm')) {
             // Logic lấy danh sách thành viên...
             if ($projectId) {
                 $project = Project::with('users')->find($projectId);
                 $context .= "Thành viên dự án:\n";
                 foreach ($project->users as $user) {
                     $context .= "- {$user->name} ({$user->email})\n";
                 }
             }
        }

        // Từ khóa: "dự án", "tình hình", "tiến độ", "thế nào" (trạng thái chung)
        if (str_contains($question, 'dự án') || str_contains($question, 'tình hình') || str_contains($question, 'tiến độ') || str_contains($question, 'status')) {
             if ($projectId) {
                 $project = Project::withCount(['tickets', 'members'])->find($projectId);
                 $ticketsCount = $project->tickets_count;
                 $openTickets = $project->tickets()->whereHas('status', fn($q) => $q->where('is_completed', false))->count();
                 $context .= "Thống kê dự án:\n";
                 $context .= "- Tổng số ticket: {$ticketsCount}\n";
                 $context .= "- Ticket chưa hoàn thành: {$openTickets}\n";
                 $context .= "- Ngày bắt đầu: {$project->start_date}\n";
                 $context .= "- Ngày kết thúc: {$project->end_date}\n";
             }
        }

        // Từ khóa: "người dùng", "user", "nhân viên" (số lượng user hệ thống)
        if ((str_contains($question, 'người dùng') || str_contains($question, 'user') || str_contains($question, 'nhân viên')) 
            && (str_contains($question, 'bao nhiêu') || str_contains($question, 'số lượng') || str_contains($question, 'tổng'))) {
            $userCount = User::count();
            $context .= "Thống kê người dùng hệ thống:\n";
            $context .= "- Tổng số người dùng hiện tại: {$userCount}\n";
        }

        return $context;
    }
}
