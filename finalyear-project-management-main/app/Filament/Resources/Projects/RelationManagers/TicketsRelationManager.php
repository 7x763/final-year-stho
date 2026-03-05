<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Exports\TicketTemplateExport;
use App\Imports\TicketsImport;
use App\Models\Epic;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TicketsRelationManager extends RelationManager
{
    protected static bool $isLazy = true;

    protected static string $relationship = 'tickets';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->tickets_count ?? $ownerRecord->tickets()->count();
    }

    protected static ?string $title = 'Vé hỗ trợ';

    protected static ?string $modelLabel = 'Vé hỗ trợ';

    protected static ?string $pluralModelLabel = 'Vé hỗ trợ';

    public function form(Schema $schema): Schema
    {
        $projectId = $this->getOwnerRecord()->id;

        $defaultStatus = TicketStatus::where('project_id', $projectId)->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : null;

        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('Ticket Name')),

                Select::make('ticket_status_id')
                    ->label(__('Status'))
                    ->options(function () use ($projectId) {
                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($defaultStatusId)
                    ->required()
                    ->searchable(),

                Select::make('epic_id')
                    ->label('Epic')
                    ->options(function () use ($projectId) {
                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->nullable(),

                // UPDATED: Multi-user assignment
                Select::make('assignees')
                    ->label(__('Assignee'))
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $projectId = $this->getOwnerRecord()->id;

                            // Only show project members
                            return $query->whereHas('projects', function ($query) use ($projectId): void {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->default(function ($record) {
                        if ($record && $record->exists) {
                            return $record->assignees->pluck('id')->toArray();
                        }

                        // Auto-assign current user if they're a project member
                        $project = $this->getOwnerRecord();
                        $isCurrentUserMember = $project->members()->where('users.id', auth()->id())->exists();

                        return $isCurrentUserMember ? [auth()->id()] : [];
                    })
                    ->helperText(__('Choose one or more people to assign this ticket to. Only project members can be assigned.')),

                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->nullable(),

                DatePicker::make('due_date')
                    ->label(__('Due Date'))
                    ->nullable()
                    ->afterOrEqual('start_date'),

                RichEditor::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->nullable(),

                // Show created by in edit mode
                Select::make('created_by')
                    ->label(__('Creator'))
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn('create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                ->select(['tickets.id', 'tickets.project_id', 'tickets.ticket_status_id', 'tickets.epic_id', 'tickets.created_by', 'tickets.uuid', 'tickets.name', 'tickets.start_date', 'tickets.due_date', 'tickets.created_at'])
                ->with([
                    'status:id,name',
                    'epic:id,name',
                    'creator:id,name',
                    'assignees:id,name',
                ])
            )
            ->columns([
                TextColumn::make('uuid')
                    ->label(__('Ticket Code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label(__('Ticket Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($record) => match ($record->status?->name) {
                        'To Do' => 'warning',
                        'In Progress' => 'info',
                        'Review' => 'primary',
                        'Done' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('epic.name')
                    ->label(__('Epic'))
                    ->badge()
                    ->color('warning')
                    ->placeholder(__('None'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('assignees.name')
                    ->label(__('Assignee'))
                    ->badge()
                    ->separator(',')
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label(__('Creator'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ticket_status_id')
                    ->label(__('Status'))
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                // UPDATED: Filter by assignees
                SelectFilter::make('assignees')
                    ->label(__('Assignee'))
                    ->relationship('assignees', 'name', modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                        $projectId = $this->getOwnerRecord()->id;
                        return $query->whereHas('projects', fn ($q) => $q->where('projects.id', $projectId));
                    })
                    ->multiple()
                    ->searchable()
                    ->preload(),

                // Filter by creator
                SelectFilter::make('created_by')
                    ->label(__('Creator'))
                    ->relationship('creator', 'name', modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                        $projectId = $this->getOwnerRecord()->id;
                        return $query->whereHas('projects', fn ($q) => $q->where('projects.id', $projectId));
                    })
                    ->searchable()
                    ->preload(),

                // Filter by epic
                SelectFilter::make('epic_id')
                    ->label(__('Epic'))
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('New Ticket'))
                    ->mutateDataUsing(function (array $data): array {
                        // Set project_id and created_by
                        $data['project_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->using(function (array $data, string $model): Model {
                        $project = $this->getOwnerRecord();
                        
                        $ticket = new $model($data);
                        // Kích hoạt tối ưu hóa trong Ticket::booted()
                        // Bằng cách set relation này, Ticket model sẽ không query lại DB để tìm project nữa
                        $ticket->setRelation('project', $project);
                        
                        $ticket->save();
                        
                        return $ticket;
                    }),

                // NEW: Import from Excel action
                Action::make('import_tickets')
                    ->label(__('Import from Excel'))
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success')
                    ->schema([
                        Section::make(__('Import tickets from Excel'))
                            ->description(__('Upload an Excel file to import tickets into this project. You can download a template file below.'))
                            ->schema([
                                Actions::make([
                                    Action::make('download_template')
                                        ->label(__('Download template'))
                                        ->icon('heroicon-m-arrow-down-tray')
                                        ->color('gray')
                                        ->action(function (RelationManager $livewire) {
                                            $project = $livewire->getOwnerRecord();
                                            $filename = 'ticket-import-template-'.str($project->name)->slug().'.xlsx';

                                            return Excel::download(
                                                new TicketTemplateExport($project),
                                                $filename
                                            );
                                        }),
                                ])->fullWidth(),

                                FileUpload::make('excel_file')
                                    ->label(__('Excel File'))
                                    ->helperText(__('Upload an Excel file containing ticket data. Make sure to use the correct template format above.'))
                                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                                    ->maxSize(5120) // 5MB
                                    ->required()
                                    ->disk('local')
                                    ->directory('temp-imports')
                                    ->visibility('private'),
                            ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $project = $livewire->getOwnerRecord();
                        $filePath = Storage::disk('local')->path($data['excel_file']);

                        try {
                            $import = new TicketsImport($project);
                            Excel::import($import, $filePath);

                            $importedCount = $import->getImportedCount();
                            $errors = $import->errors();
                            $failures = $import->failures();

                            // Clean up uploaded file
                            Storage::disk('local')->delete($data['excel_file']);

                            if ($importedCount > 0) {
                                $message = __('Successfully imported :count tickets into project \':project\'.', ['count' => $importedCount, 'project' => $project->name]);

                                if (count($errors) > 0 || count($failures) > 0) {
                                    $message .= ' '.__('Some rows encountered errors and were skipped.');
                                }

                                Notification::make()
                                    ->title(__('Import Completed'))
                                    ->body($message)
                                    ->success()
                                    ->send();
                            } else {
                                // Get actual errors and failures
                                $importErrors = $import->errors();
                                $importFailures = $import->failures();

                                $errorMessage = __('No tickets were imported.');

                                // Show actual validation failures if they exist
                                if (! empty($importFailures)) {
                                    $errorMessage .= "\n\n**".__('Validation Errors:').'**';
                                    foreach ($importFailures as $failure) {
                                        $row = $failure->row();
                                        $errors = implode(', ', $failure->errors());
                                        $errorMessage .= "\n• ".__('Row')." {$row}: {$errors}";
                                    }
                                }

                                // Show actual processing errors if they exist
                                if (! empty($importErrors)) {
                                    $errorMessage .= "\n\n**".__('Processing Errors:').'**';
                                    foreach ($importErrors as $error) {
                                        $errorMessage .= "\n• {$error}";
                                    }
                                }

                                Notification::make()
                                    ->title(__('Import Failed'))
                                    ->body($errorMessage)
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            // Clean up uploaded file on error
                            Storage::disk('local')->delete($data['excel_file']);

                            Notification::make()
                                ->title(__('Import Error'))
                                ->body(__('An error occurred during import:').' '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()->label(__('Edit')),
                DeleteAction::make()->label(__('Delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('Delete Selected')),

                    BulkAction::make('updateStatus')
                        ->label(__('Update Status'))
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('ticket_status_id')
                                ->label(__('Status'))
                                ->options(function (RelationManager $livewire) {
                                    $projectId = $livewire->getOwnerRecord()->id;

                                    return TicketStatus::where('project_id', $projectId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'ticket_status_id' => $data['ticket_status_id'],
                                ]);
                            }

                            Notification::make()
                                ->success()
                                ->title(__('Status Updated'))
                                ->body(__('count tickets have been updated.', ['count' => count($records)]))
                                ->send();
                        }),

                    // NEW: Bulk assign users
                    BulkAction::make('assignUsers')
                        ->label(__('Assign member'))
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('assignees')
                                ->label(__('Assignee'))
                                ->multiple()
                                ->options(function (RelationManager $livewire) {
                                    return $livewire->getOwnerRecord()
                                        ->members()
                                        ->pluck('name', 'users.id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required(),

                            Radio::make('assignment_mode')
                                ->label(__('Assign Mode'))
                                ->options([
                                    'replace' => __('Replace existing roles'),
                                    'add' => __('Add to existing roles'),
                                ])
                                ->default('add')
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                if ($data['assignment_mode'] === 'replace') {
                                    $record->assignees()->sync($data['assignees']);
                                } else {
                                    $record->assignees()->syncWithoutDetaching($data['assignees']);
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title(__('Assigned tickets'))
                                ->body(__('count tickets have been updated with new assignees.', ['count' => count($records)]))
                                ->send();
                        }),
                    BulkAction::make('updatePriority')
                        ->label(__('Update priority'))
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority_id')
                                ->label(__('Ticket Priority'))
                                ->options(TicketPriority::pluck('name', 'id')->toArray())
                                ->nullable(),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'priority_id' => $data['priority_id'],
                                ]);
                            }
                        }),

                    BulkAction::make('assignToEpic')
                        ->label(__('Assign to Epic'))
                        ->icon('heroicon-o-bookmark')
                        ->form([
                            Select::make('epic_id')
                                ->label(__('Epic'))
                                ->options(function (RelationManager $livewire) {
                                    $projectId = $livewire->getOwnerRecord()->id;

                                    return Epic::where('project_id', $projectId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText(__('Choose an Epic to assign selected tickets. Leave blank to unassign.')),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'epic_id' => $data['epic_id'],
                                ]);
                            }

                            $epicName = $data['epic_id']
                                ? Epic::find($data['epic_id'])->name
                                : __('None');

                            Notification::make()
                                ->success()
                                ->title(__('Epic Updated'))
                                ->body(__('count tickets have been assigned to: :epic', ['count' => count($records), 'epic' => $epicName]))
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
