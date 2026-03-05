<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ProjectHealthCheck;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\RelationManagers\EpicsRelationManager;
use App\Filament\Resources\Projects\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Projects\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Projects\RelationManagers\TicketsRelationManager;
use App\Filament\Resources\Projects\RelationManagers\TicketStatusesRelationManager;
use App\Models\Project;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Project Management');
    }

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('Project');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Projects');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Project Name'))
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'video/mp4'])
                    ->fileAttachmentsVisibility('public'),
                TextInput::make('ticket_prefix')
                    ->label(__('Ticket Prefix'))
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label(__('Project Color'))
                    ->helperText(__('Choose project color'))
                    ->nullable(),
                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label(__('End Date'))
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                Toggle::make('create_default_statuses')
                    ->label(__('Use default ticket statuses'))
                    ->helperText(__('Auto-create default statuses'))
                    ->default(true)
                    ->dehydrated(false)
                    ->visible(fn ($livewire) => $livewire instanceof CreateProject),

                Toggle::make('is_pinned')
                    ->label(__('Pin Project'))
                    ->helperText(__('Pinned projects appear on dash'))
                    ->live()
                    ->afterStateUpdated(function ($state, $set): void {
                        if ($state) {
                            $set('pinned_date', now());
                        } else {
                            $set('pinned_date', null);
                        }
                    })
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $get): void {
                        $component->state(! is_null($get('pinned_date')));
                    }),
                DateTimePicker::make('pinned_date')
                    ->label(__('Pinned Date'))
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->visible(fn ($get) => $get('is_pinned'))
                    ->dehydrated(true),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('40px')
                    ->default('#6B7280'),
                TextColumn::make('name')
                    ->label(__('Project Name'))
                    ->searchable(),
                TextColumn::make('ticket_prefix')
                    ->label(__('Ticket Prefix'))
                    ->searchable(),
                TextColumn::make('progress_percentage')
                    ->label(__('Progress'))
                    ->getStateUsing(function (Project $record): string {
                        return $record->progress_percentage.'%';
                    })
                    ->badge()
                    ->color(
                        fn (Project $record): string => $record->progress_percentage >= 100 ? 'success' :
                        ($record->progress_percentage >= 75 ? 'info' :
                            ($record->progress_percentage >= 50 ? 'warning' :
                                ($record->progress_percentage >= 25 ? 'gray' : 'danger')))
                    )
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('remaining_days')
                    ->label(__('Remaining Days'))
                    ->getStateUsing(function (Project $record): ?string {
                        if (! $record->end_date) {
                            return null;
                        }

                        return $record->remaining_days.' '.__('days');
                    })
                    ->badge()
                    ->color(
                        fn (Project $record): string => ! $record->end_date ? 'gray' :
                        ($record->remaining_days <= 0 ? 'danger' :
                            ($record->remaining_days <= 7 ? 'warning' : 'success'))
                    ),
                ToggleColumn::make('is_pinned')
                    ->label(__('Pinned'))
                    ->updateStateUsing(function ($record, $state) {
                        // Gunakan method pin/unpin yang sudah ada di model
                        if ($state) {
                            $record->pin();
                        } else {
                            $record->unpin();
                        }

                        return $state;
                    }),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->label(__('Members')),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label(__('Tickets')),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('health_check')
                    ->label(__('Health Check'))
                    ->icon('heroicon-o-heart')
                    ->color('danger')
                    ->url(fn (Project $record): string => static::getUrl('health-check', ['record' => $record])),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TicketStatusesRelationManager::class,
            MembersRelationManager::class,
            EpicsRelationManager::class,
            TicketsRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
            'health-check' => ProjectHealthCheck::route('/{record}/health-check'),
            // Hapus baris ini: 'gantt-chart' => Pages\ProjectGanttChart::route('/gantt-chart'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withCount(['members', 'tickets', 'epics', 'ticketStatuses', 'notes', 'completedTickets']);

        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $query->whereHas('members', function (Builder $query): void {
                $query->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}
