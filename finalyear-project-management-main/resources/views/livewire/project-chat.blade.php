<div style="position: fixed; bottom: 20px; right: 20px; z-index: 50; display: flex; flex-direction: column; align-items: flex-end;">
    <!-- Chat Window -->
    <div x-data="{ scrollBottom() { $refs.content.scrollTop = $refs.content.scrollHeight; } }"
         x-init="$watch('$wire.messages', () => { setTimeout(() => scrollBottom(), 100); })"
         class="mb-4 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 transform"
         style="display: {{ $isOpen ? 'flex' : 'none' }}; height: 500px; max-height: 80vh; background-color: white; border-radius: 0.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <!-- Header -->
        <div class="p-4 bg-primary-600 dark:bg-primary-500 rounded-t-lg flex justify-between items-center text-white shadow-sm" style="background-color: #2563EB; padding: 1rem; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; color: white;">
            <div class="flex items-center gap-2">
                <div class="p-1 bg-white/20 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" style="width: 20px; height: 20px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="font-bold text-sm">AI Project Copilot</h3>
            </div>
            <button wire:click="toggleChat" class="text-white/80 hover:text-white focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" style="width: 20px; height: 20px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Messages -->
        <div x-ref="content" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50" style="flex: 1 1 0%; overflow-y: auto; padding: 1rem;">
            @foreach($messages as $msg)
                <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}" style="display: flex; justify-content: {{ $msg['role'] === 'user' ? 'flex-end' : 'flex-start' }}; margin-bottom: 10px;">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm {{ $msg['role'] === 'user' ? 'bg-primary-600 text-white rounded-br-none' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-sm border border-gray-100 dark:border-gray-600 rounded-bl-none' }}"
                         style="max-width: 85%; padding: 0.5rem 1rem; border-radius: 1rem; font-size: 0.875rem; {{ $msg['role'] === 'user' ? 'background-color: #2563EB; color: white; border-bottom-right-radius: 0;' : 'background-color: #f3f4f6; color: #1f2937; border-bottom-left-radius: 0;' }}">
                        {!! nl2br(e($msg['content'])) !!}
                    </div>
                </div>
            @endforeach

            <!-- Loading Indicator -->
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="bg-white dark:bg-gray-700 p-3 rounded-2xl rounded-bl-none shadow-sm border border-gray-100 dark:border-gray-600">
                    <div class="flex space-x-1.5">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce">...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg" style="padding: 1rem; border-top: 1px solid #e5e7eb;">
            <form wire:submit.prevent="sendMessage" class="flex gap-2" style="display: flex; gap: 0.5rem;">
                <input wire:model="input" type="text" placeholder="Hỏi về dự án..." 
                       class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full focus:border-primary-500 focus:ring-primary-500 shadow-sm px-4 py-2"
                       style="flex: 1; border-radius: 9999px; border: 1px solid #d1d5db; padding: 0.5rem 1rem;">
                <button type="submit" 
                        class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:target="sendMessage"
                        style="background-color: #2563EB; color: white; border-radius: 9999px; padding: 0.5rem;">
                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" style="width: 20px; height: 20px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Toggle Button (FAB) -->
    <button wire:click="toggleChat" 
            class="group flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-600 to-primary-500 hover:to-primary-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:focus:ring-primary-900 overflow-hidden"
            style="width: 60px; height: 60px; background-color: #2563EB; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); cursor: pointer; border: none;">
        
        <!-- Icon when closed -->
        <div class="absolute transition-all duration-300 {{ $isOpen ? 'rotate-90 opacity-0' : 'rotate-0 opacity-100' }}">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" style="width: 32px; height: 32px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </div>
        
        <!-- Icon when open -->
        <div class="absolute transition-all duration-300 {{ $isOpen ? 'rotate-0 opacity-100' : '-rotate-90 opacity-0' }}">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" style="width: 32px; height: 32px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </button>
</div>
