<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\TicketStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketStatusesRelationManager extends RelationManager
{
    protected static bool $isLazy = true;

    protected static string $relationship = 'ticketStatuses';

    protected static ?string $title = 'Ticket Statuses';

    protected static ?string $modelLabel = 'Status';

    protected static ?string $pluralModelLabel = 'Statuses';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->ticket_statuses_count ?? $ownerRecord->ticketStatuses()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Status Name'))
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label(__('Color'))
                    ->required()
                    ->default('#3490dc')
                    ->helperText(__('Choose color for this status')),
                TextInput::make('sort_order')
                    ->label(__('Sort Order'))
                    ->numeric()
                    ->default(0)
                    ->helperText(__('Determines the display order on the project board (lower numbers first)')),
                Toggle::make('is_completed')
                    ->label(__('Mark as completed status'))
                    ->helperText(__('Each project can only have one status marked as completed'))
                    ->default(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set, $record): void {
                        if ($state) {
                            // Check if another status in this project is already marked as completed
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                ->first();

                            if ($existingCompleted) {
                                $set('is_completed', false);
                                Notification::make()
                                    ->warning()
                                    ->title(__('Cannot mark as completed'))
                                    ->body(__('Status \':name\' is already marked as completed for this project. Only one status can be selected.', ['name' => $existingCompleted->name]))
                                    ->send();
                            }
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->select(['id', 'project_id', 'name', 'color', 'sort_order', 'is_completed']))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Status Name')),
                ColorColumn::make('color')
                    ->label(__('Color')),
                TextColumn::make('sort_order')
                    ->label(__('Sort Order')),
                IconColumn::make('is_completed')
                    ->label(__('Completed'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Create New Status'))
                    ->mutateDataUsing(function (array $data): array {
                        $maxOrder = $this->getRelationship()->max('sort_order') ?? -1;
                        $data['sort_order'] = $maxOrder + 1;

                        // Additional validation for is_completed
                        if ($data['is_completed'] ?? false) {
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->first();

                            if ($existingCompleted) {
                                $data['is_completed'] = false;
                                Notification::make()
                                    ->warning()
                                    ->title(__('Cannot mark as completed'))
                                    ->body(__('Status \':name\' is already marked as completed for this project.', ['name' => $existingCompleted->name]))
                                    ->send();
                            }
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit'))
                    ->mutateDataUsing(function (array $data, Model $record): array {
                        // Additional validation for is_completed on edit
                        if ($data['is_completed'] ?? false) {
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->where('id', '!=', $record->id)
                                ->first();

                            if ($existingCompleted) {
                                $data['is_completed'] = false;
                                Notification::make()
                                    ->warning()
                                    ->title(__('Cannot mark as completed'))
                                    ->body(__('Status \':name\' is already marked as completed for this project.', ['name' => $existingCompleted->name]))
                                    ->send();
                            }
                        }

                        return $data;
                    }),
                DeleteAction::make()->label(__('Delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('Delete Selected')),
                ]),
            ]);
    }
}
