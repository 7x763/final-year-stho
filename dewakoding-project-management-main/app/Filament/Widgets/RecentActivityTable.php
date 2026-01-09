<?php

namespace App\Filament\Widgets;

use App\Models\TicketHistory;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityTable extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Hoạt động gần đây';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TicketHistory::query()
                    ->with(['ticket.project', 'user', 'status'])
                    ->when(! auth()->user()->hasRole('super_admin'), function ($query): void {
                        $query->whereHas('ticket.project.members', function ($subQuery): void {
                            $subQuery->where('user_id', auth()->id());
                        });
                    })
                    ->latest()
            )
            ->columns([
                TextColumn::make('activity_summary')
                    ->label('Hoạt động')
                    ->state(function (TicketHistory $record): string {
                        $ticketName = $record->ticket->name ?? 'Vé không rõ';
                        $trimmedName = strlen($ticketName) > 40 ? substr($ticketName, 0, 40).'...' : $ticketName;
                        $userName = $record->user->name ?? 'Người dùng không rõ';

                        return "<span class='text-primary-600 font-medium'>{$userName}</span> đã thay đổi \"{$trimmedName}\"";
                    })
                    ->description(function (TicketHistory $record): string {
                        $isToday = $record->created_at->isToday();
                        $time = $isToday
                            ? $record->created_at->format('H:i')
                            : $record->created_at->format('d/m, H:i');
                        $project = $record->ticket->project->name ?? 'Không có dự án';
                        $uuid = $record->ticket->uuid ?? '';

                        return "{$time} • {$uuid} • {$project}";
                    })
                    ->html()
                    ->searchable(['users.name', 'tickets.name', 'tickets.uuid'])
                    ->weight('medium'),
                TextColumn::make('status.name')
                    ->label('Trạng thái')
                    ->badge()
                    ->alignEnd()
                    ->color(fn (TicketHistory $record): string => match ($record->status->name ?? '') {
                        'To Do', 'Backlog' => 'gray',
                        'In Progress', 'Doing' => 'warning',
                        'Review', 'Testing' => 'info',
                        'Done', 'Completed' => 'success',
                        'Cancelled', 'Blocked' => 'danger',
                        default => 'primary',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->label('Khoảng thời gian')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Từ ngày')
                            ->default(today()),
                        DatePicker::make('end_date')
                            ->label('Đến ngày')
                            ->default(today()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date'], function ($query, $date): void {
                                $query->whereDate('created_at', '>=', $date);
                            })
                            ->when($data['end_date'], function ($query, $date): void {
                                $query->whereDate('created_at', '<=', $date);
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_date'] ?? null) {
                            $indicators[] = 'Từ: '.Carbon::parse($data['start_date'])->format('d/m/Y');
                        }
                        if ($data['end_date'] ?? null) {
                            $indicators[] = 'Đến: '.Carbon::parse($data['end_date'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                Filter::make('today')
                    ->label('Chỉ hôm nay')
                    ->query(fn ($query) => $query->whereDate('created_at', today()))
                    ->toggle(),

                SelectFilter::make('user')
                    ->label('Người dùng')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->size('sm')
                    ->tooltip('Mở vé hỗ trợ')
                    ->url(fn (TicketHistory $record): string => route('filament.admin.resources.tickets.view', $record->ticket)
                    )
                    ->openUrlInNewTab(),
            ])
            ->recordUrl(fn (TicketHistory $record) => route('filament.admin.resources.tickets.view', $record->ticket)
            )
            ->paginated([5, 25, 50])
            ->poll('30s')
            ->striped()
            ->emptyStateHeading('Không có hoạt động nào')
            ->emptyStateDescription('Không tìm thấy hoạt động nào của vé hỗ trợ trong khoảng thời gian đã chọn.')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
