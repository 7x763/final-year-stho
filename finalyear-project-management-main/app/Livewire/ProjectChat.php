<?php

namespace App\Livewire;

use App\Services\AiCopilotService;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class ProjectChat extends Component
{
    public $isOpen = false;
    public $input = '';
    public $messages = [];
    public $projectId = null;

    public function mount()
    {
        // Initial greeting
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Xin chào! Tôi là AI Copilot. Tôi có thể giúp gì cho bạn về dự án?',
        ];

        // Try to detect project ID from URL
        $route = Route::current();
        if ($route && $route->parameter('record')) {
             // Assuming we are on a Project resource page where the record is the project
             $param = $route->parameter('record');
             if (is_numeric($param)) {
                 $this->projectId = (int) $param;
             } elseif ($param instanceof \App\Models\Project) {
                 $this->projectId = $param->id;
             }
        }
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage(AiCopilotService $service)
    {
        if (trim($this->input) === '') return;

        // User message
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->input,
        ];

        $question = $this->input;
        $this->input = ''; // Clear input

        // AI processing state (UI can show loading based on wire:loading)
        
        // Call service
        $response = $service->ask($question, $this->projectId);

        // Assistant message
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response,
        ];
    }

    public function render()
    {
        return view('livewire.project-chat');
    }
}
