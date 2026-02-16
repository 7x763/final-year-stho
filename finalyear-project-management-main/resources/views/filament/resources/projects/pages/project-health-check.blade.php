<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Overall Health --}}
        <x-filament::section>
            <x-slot name="heading">Trạng thái tổng quát</x-slot>
            <div class="flex flex-col items-center justify-center p-6">
                <div class="text-4xl font-bold mb-2 {{ $analysis['overall_status'] === 'Good' ? 'text-success-600' : 'text-danger-600' }}">
                    {{ $analysis['overall_status'] }}
                </div>
                <div class="text-gray-500">{{ $analysis['forecast']['message'] }}</div>
                
                {{-- Quick Stats Row --}}
                <div class="flex gap-4 mt-6 w-full justify-center">
                    <div class="text-center">
                        <div class="text-xl font-bold text-danger-600">{{ $analysis['overdue_count'] }}</div>
                        <div class="text-xs text-gray-400">Quá hạn</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-warning-600">{{ $analysis['stale_count'] ?? 0 }}</div>
                        <div class="text-xs text-gray-400">Bị quên (>7 ngày)</div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Forecast Stats --}}
        <x-filament::section>
            <x-slot name="heading">Dự báo tiến độ</x-slot>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span>Tiến độ hiện tại:</span>
                    <span class="font-bold">{{ $analysis['forecast']['progress'] }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Ngày hoàn thành dự kiến:</span>
                    <span class="font-bold">{{ $analysis['forecast']['estimated_completion_date'] ?: 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Số ngày cần thiết:</span>
                    <span class="font-bold">{{ $analysis['forecast']['days_remaining_needed'] ?? 'N/A' }} ngày</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Tốc độ xử lý:</span>
                    <span class="font-bold">{{ $analysis['forecast']['velocity'] ?? 0 }} ticket/ngày</span>
                </div>
                
                @if(isset($analysis['priority_breakdown']) && $analysis['priority_breakdown']->isNotEmpty())
                    <hr class="my-2 border-gray-100 dark:border-gray-700">
                    <div class="text-sm font-semibold mb-2">Phân bổ độ ưu tiên (Ticket chưa xong):</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($analysis['priority_breakdown'] as $priority => $count)
                            <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                                {{ $priority }}: <strong>{{ $count }}</strong>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Bottlenecks --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">Nút thắt cổ chai (Phân tích TicketHistory)</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="py-2 px-4">Trạng thái</th>
                            <th class="py-2 px-4">Thời gian TB (giờ)</th>
                            <th class="py-2 px-4">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analysis['bottlenecks'] as $bottleneck)
                            <tr class="border-b dark:border-gray-700 {{ $bottleneck['is_bottleneck'] ? 'bg-danger-50 dark:bg-danger-900/10' : '' }}">
                                <td class="py-2 px-4 font-medium">{{ $bottleneck['status_name'] }}</td>
                                <td class="py-2 px-4">{{ $bottleneck['avg_duration_hours'] }} h</td>
                                <td class="py-2 px-4">
                                    @if($bottleneck['is_bottleneck'])
                                        <span class="text-danger-600 font-bold">⚠️ Nút thắt</span>
                                    @else
                                        <span class="text-success-600">Bình thường</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">Chua có dữ liệu lịch sử đủ để phân tích.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- AI Analysis --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-sparkles class="w-5 h-5 text-yellow-500" />
                        <span>Phân tích chuyên sâu từ Gemini AI</span>
                    </div>
                    <x-filament::button wire:click="analyzeAction" size="sm" color="primary">
                        {{ $analysis['analysis_status'] === 'processing' ? 'Đang phân tích...' : 'Phân tích lại' }}
                    </x-filament::button>
                </div>
            </x-slot>

            @if($analysis['analysis_status'] === 'processing')
                <div wire:poll.3s="refreshAnalysis" class="p-6 bg-gray-50 dark:bg-gray-800 rounded-lg flex flex-col items-center justify-center gap-4">
                    <x-filament::loading-indicator class="h-8 w-8 text-primary-600" />
                    <span class="text-gray-600 dark:text-gray-400">Đang gửi dữ liệu tới Gemini để phân tích...</span>
                </div>
            @else
                <div class="prose dark:prose-invert max-w-none p-4 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                    {!! \Illuminate\Support\Str::markdown($analysis['ai_summary']) !!}
                </div>
                @if(isset($analysis['analysis_at']))
                    <div class="text-right text-xs text-gray-400 mt-2">
                        Cập nhật lần cuối: {{ \Carbon\Carbon::parse($analysis['analysis_at'])->diffForHumans() }}
                    </div>
                @endif
            @endif
        </x-filament::section>

        {{-- Recommendations (Generated locally) --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">Gợi ý nhanh</x-slot>
            <ul class="list-disc list-inside space-y-2">
                @foreach($analysis['recommendations'] as $recommendation)
                    <li class="text-gray-700 dark:text-gray-300">{{ $recommendation }}</li>
                @endforeach
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
