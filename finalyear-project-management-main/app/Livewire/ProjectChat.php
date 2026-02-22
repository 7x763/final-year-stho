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
    public $isTyping = false;

    public function mount()
    {
        // Load messages from session or initialize with greeting
        $this->messages = session()->get('chat_messages', [
            [
                'role' => 'assistant',
                'content' => 'Xin chào! Tôi là AI Copilot. Tôi có thể giúp gì cho bạn về dự án?',
            ]
        ]);

        // Load open state from session
        $this->isOpen = session()->get('chat_is_open', false);

        // Try to detect project ID from URL
        $route = Route::current();
        if ($route && $route->parameter('record')) {
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
        session()->put('chat_is_open', $this->isOpen);
    }

    public function clearChat()
    {
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => 'Xin chào! Tôi đã làm mới cuộc hội thoại. Tôi giúp được gì cho bạn?',
            ]
        ];
        session()->put('chat_messages', $this->messages);
    }

    public function sendMessage()
    {
        if (trim($this->input) === '') return;

        $userMessage = [
            'role' => 'user',
            'content' => $this->input,
        ];

        $this->messages[] = $userMessage;
        $question = $this->input;
        $this->input = ''; 
        $this->isTyping = true;

        // Save to session immediately
        session()->put('chat_messages', $this->messages);

        // Dispatch event to self for background processing
        $this->dispatch('process-ai-response', question: $question);
    }

    #[\Livewire\Attributes\On('process-ai-response')]
    public function handleAiResponse(AiCopilotService $service, string $question)
    {
        // Call slow service
        $response = $service->ask($question, $this->projectId);

        // Assistant message
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response,
        ];

        $this->isTyping = false;

        // Save updated history
        session()->put('chat_messages', $this->messages);
    }

    public function render()
    {
        return view('livewire.project-chat');
    }
}
