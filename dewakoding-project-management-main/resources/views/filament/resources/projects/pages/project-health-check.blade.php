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
            </div>
        </x-filament::section>

        {{-- Bottlenecks --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">Nút thắt cổ chai (Phân tích TicketHistory)</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 px-4">Trạng thái</th>
                            <th class="py-2 px-4">Thời gian TB (giờ)</th>
                            <th class="py-2 px-4">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysis['bottlenecks'] as $bottleneck)
                            <tr class="border-b {{ $bottleneck['is_bottleneck'] ? 'bg-danger-50 dark:bg-danger-900/10' : '' }}">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Recommendations --}}
        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">Gợi ý từ AI</x-slot>
            <ul class="list-disc list-inside space-y-2">
                @foreach($analysis['recommendations'] as $recommendation)
                    <li class="text-gray-700 dark:text-gray-300">{{ $recommendation }}</li>
                @endforeach
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
