<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages\ListNotifications;
use App\Models\Notification;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Thông báo';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Thông báo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Thông báo';
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(fn () => (auth()->user() && auth()->user()->isSuperAdmin())
                    ? Notification::with(['user', 'ticket.project'])
                    : Notification::where('user_id', auth()->id())->with(['ticket.project'])
            )
            ->columns([
                IconColumn::make('read_status')
                    ->label('')
                    ->icon(fn (Notification $record) => $record->isUnread() ? 'heroicon-o-bell' : 'heroicon-o-bell-slash')
                    ->color(fn (Notification $record) => $record->isUnread() ? 'warning' : 'gray')
                    ->size('sm'),

                TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->visible(fn () => auth()->user() && auth()->user()->isSuperAdmin()),

                TextColumn::make('message')
                    ->label('Nội dung')
                    ->limit(50)
                    ->weight(fn (Notification $record) => $record->isUnread() ? 'bold' : 'normal'),

                TextColumn::make('ticket.name')
                    ->label('Vé hỗ trợ')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('ticket.project.name')
                    ->label('Dự án')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Đánh dấu đã đọc')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Notification $record) => $record->isUnread() && (auth()->id() === $record->user_id || (auth()->user() && auth()->user()->isSuperAdmin())))
                    ->action(function (Notification $record): void {
                        app(NotificationService::class)->markAsRead($record->id, $record->user_id);

                        FilamentNotification::make()
                            ->title('Đã đánh dấu thông báo là đã đọc')
                            ->success()
                            ->send();
                    }),

                Action::make('viewTicket')
                    ->label('Xem vé hỗ trợ')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (Notification $record) => isset($record->data['ticket_id']))
                    ->url(fn (Notification $record) => route('filament.admin.resources.tickets.view', ['record' => $record->data['ticket_id']])
                    )
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('markAllAsRead')
                    ->label('Đánh dấu tất cả đã đọc')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => auth()->user() && ! auth()->user()->isSuperAdmin())
                    ->action(function (): void {
                        app(NotificationService::class)->markAllAsRead(auth()->id());

                        FilamentNotification::make()
                            ->title('Tất cả thông báo đã được đánh dấu là đã đọc')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                Filter::make('unread')
                    ->label('Chỉ hiện chưa đọc')
                    ->query(fn (Builder $query) => $query->unread()),

                SelectFilter::make('user')
                    ->label('Người dùng')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user() && auth()->user()->isSuperAdmin()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return cache()->remember('nav_badge_unread_notifications_' . $user->id, now()->addMinutes(1), function () use ($user) {
            return (string) ($user->unreadNotifications()->count() ?: '');
        }) ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
