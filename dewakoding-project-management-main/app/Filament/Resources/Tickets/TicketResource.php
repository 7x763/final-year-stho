<?php

namespace App\Filament\Resources\Tickets;

use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Closure;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets';

    protected static string|\UnitEnum|null $navigationGroup = 'Quản lý dự án';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return 'Vé hỗ trợ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Vé hỗ trợ';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['status', 'priority', 'assignees', 'creator', 'epic']);
        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $userId = $user->id;
            $query->where(function ($query) use ($userId): void {
                $query->whereHas('assignees', function ($query) use ($userId): void {
                    $query->where('users.id', $userId);
                })
                    ->orWhere('created_by', $userId)
                    ->orWhereHas('project.members', function ($query) use ($userId): void {
                        $query->where('users.id', $userId);
                    });
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        $projectId = request()->query('project_id') ?? request()->input('project_id');
        $statusId = request()->query('ticket_status_id') ?? request()->input('ticket_status_id');

        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Dự án')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->isSuperAdmin()) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return $user ? $user->projects()->pluck('name', 'projects.id')->toArray() : [];
                    })
                    ->default($projectId)
                    ->disabledOn('ticket_on_board')
                    ->dehydrated()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set): void {
                        $set('ticket_status_id', null);
                        $set('assignees', []);
                        $set('epic_id', null);
                    }),

                Select::make('ticket_status_id')
                    ->label('Trạng thái')
                    ->options(function ($get) {
                        $projectId = $get('project_id');
                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($statusId)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! TicketStatus::where('id', $value)->where('project_id', $projectId)->exists()) {
                                $fail("Trạng thái đã chọn không thuộc về dự án đã chọn.");
                            }
                        },
                    ]),

                Select::make('priority_id')
                    ->label('Mức độ ưu tiên')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('epic_id')
                    ->label('Epic')
                    ->options(function (Get $get) {
                        $projectId = $get('project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hidden(fn (Get $get): bool => ! $get('project_id'))
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! Epic::where('id', $value)->where('project_id', $projectId)->exists()) {
                                $fail("Epic đã chọn không thuộc về dự án đã chọn.");
                            }
                        },
                    ]),

                TextInput::make('name')
                    ->label('Tên vé hỗ trợ')
                    ->required()
                    ->maxLength(255),

                RichEditor::make('description')
                    ->label('Mô tả')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'video/mp4'])
                    ->columnSpanFull(),

                // // Multi-user assignment
                Select::make('assignees')
                    ->label('Người thực hiện')
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return $query->whereRaw('1 = 0');
                            }

                            $project = Project::find($projectId);
                            if (! $project) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query->whereHas('projects', function ($query) use ($projectId): void {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Chọn một hoặc nhiều người để giao vé này. Chỉ thành viên dự án mới có thể được giao.')
                    ->hidden(fn (Get $get): bool => ! $get('project_id'))
                    ->live()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return;
                            }
                            // $value is an array of IDs for multiple select
                            $userIds = is_array($value) ? $value : [$value];
                            $project = Project::find($projectId);
                            if (! $project) {
                                return;
                            }
                            $memberIds = $project->members()->pluck('users.id')->map(fn($id) => (string) $id)->toArray();
                            foreach ($userIds as $userId) {
                                if (! in_array((string) $userId, $memberIds)) {
                                    $fail("Một hoặc nhiều người được chọn không phải là thành viên của dự án này.");
                                    break;
                                }
                            }
                        },
                    ]),

                DatePicker::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->default(now())
                    ->nullable()
                    ->live(),

                DatePicker::make('due_date')
                    ->label('Hạn chót')
                    ->nullable()
                    ->afterOrEqual('start_date'),
                Select::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn(['create', 'ticket_on_board']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('Mã vé')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Tên vé')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('status.name')
                    ->label('Trạng thái')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $color = e($record->status?->color ?? '#6B7280');
                        $name = e($record->status?->name ?? 'Không rõ');

                        return new HtmlString(<<<HTML
                            <span class="fi-badge fi-size-sm" style="color: #fff; background-color: {$color};">
                                {$name}
                            </span>
                        HTML);
                    }),

                TextColumn::make('priority.name')
                    ->label('Ưu tiên')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        'Low' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->default('—')
                    ->placeholder('Không có'),

                // Display multiple assignees
                TextColumn::make('assignees.name')
                    ->label('Người thực hiện')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Hạn chót')
                    ->date()
                    ->sortable(),

                TextColumn::make('epic.name')
                    ->label('Epic')
                    ->sortable()
                    ->searchable()
                    ->default('—')
                    ->placeholder('Không có'),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Dự án')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->isSuperAdmin()) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return $user ? $user->projects()->pluck('name', 'projects.id')->toArray() : [];
                    })
                    ->searchable(),

                SelectFilter::make('ticket_status_id')
                    ->label('Trạng thái')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('epic_id')
                    ->label('Epic')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('priority_id')
                    ->label('Ưu tiên')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable(),

                // Filter by assignees
                SelectFilter::make('assignees')
                    ->label('Người thực hiện')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->searchable(),

                // Filter by creator
                SelectFilter::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name')
                    ->searchable(),

                Filter::make('due_date')
                    ->label('Hạn chót')
                    ->schema([
                        DatePicker::make('due_from')->label('Từ ngày'),
                        DatePicker::make('due_until')->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('copy')
                    ->label('Sao chép')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function ($record, $livewire) {
                        // Redirect ke halaman create, dengan parameter copy_from
                        return $livewire->redirect(
                            static::getUrl('create', [
                                'copy_from' => $record->id,
                            ])
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa các mục đã chọn')
                        ->visible(fn() => auth()->user() && auth()->user()->isSuperAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $query = Ticket::query();

        if (! $user->isSuperAdmin()) {
            $userId = $user->id;
            $query->where(function ($query) use ($userId): void {
                $query->whereHas('assignees', function ($query) use ($userId): void {
                    $query->where('users.id', $userId);
                })
                    ->orWhere('created_by', $userId)
                    ->orWhereHas('project.members', function ($query) use ($userId): void {
                        $query->where('users.id', $userId);
                    });
            });
        }

        return (string) $query->count();
    }
}
