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
                    ->label('Tên vé hỗ trợ'),

                Select::make('ticket_status_id')
                    ->label('Trạng thái')
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
                    ->label('Người thực hiện')
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
                    ->helperText('Chọn một hoặc nhiều người để giao vé này. Chỉ thành viên dự án mới có thể được giao.'),

                DatePicker::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->nullable(),

                DatePicker::make('due_date')
                    ->label('Hạn chót')
                    ->nullable()
                    ->afterOrEqual('start_date'),

                RichEditor::make('description')
                    ->label('Mô tả')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->nullable(),

                // Show created by in edit mode
                Select::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn('create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query
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
                    ->label('Mã vé')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Tên vé')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label('Trạng thái')
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
                    ->label('Epic')
                    ->badge()
                    ->color('warning')
                    ->placeholder('Không có Epic')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('assignees.name')
                    ->label('Người thực hiện')
                    ->badge()
                    ->separator(',')
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Hạn chót')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ticket_status_id')
                    ->label('Trạng thái')
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                // UPDATED: Filter by assignees
                SelectFilter::make('assignees')
                    ->label('Người thực hiện')
                    ->relationship('assignees', 'name', modifyQueryUsing: function (Builder $query) {
                        $projectId = $this->getOwnerRecord()->id;
                        return $query->whereHas('projects', fn ($q) => $query->where('projects.id', $projectId));
                    })
                    ->multiple()
                    ->searchable()
                    ->preload(),

                // Filter by creator
                SelectFilter::make('created_by')
                    ->label('Người tạo')
                    ->relationship('creator', 'name', modifyQueryUsing: function (Builder $query) {
                        $projectId = $this->getOwnerRecord()->id;
                        return $query->whereHas('projects', fn ($q) => $query->where('projects.id', $projectId));
                    })
                    ->searchable()
                    ->preload(),

                // Filter by epic
                SelectFilter::make('epic_id')
                    ->label('Epic')
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tạo vé mới')
                    ->mutateDataUsing(function (array $data): array {
                        // Set project_id and created_by
                        $data['project_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),

                // NEW: Import from Excel action
                Action::make('import_tickets')
                    ->label('Nhập từ Excel')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success')
                    ->schema([
                        Section::make('Nhập vé hỗ trợ từ Excel')
                            ->description('Tải lên tệp Excel để nhập vé vào dự án này. Bạn có thể tải tệp mẫu bên dưới.')
                            ->schema([
                                Actions::make([
                                    Action::make('download_template')
                                        ->label('Tải tệp mẫu')
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
                                    ->label('Tệp Excel')
                                    ->helperText('Tải lên tệp Excel chứa dữ liệu vé. Đảm bảo sử dụng đúng định dạng mẫu bên trên.')
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
                                $message = "Đã nhập thành công {$importedCount} vé vào dự án '{$project->name}'.";

                                if (count($errors) > 0 || count($failures) > 0) {
                                    $message .= ' Một số dòng gặp lỗi và đã bị bỏ qua.';
                                }

                                Notification::make()
                                    ->title('Nhập dữ liệu hoàn tất')
                                    ->body($message)
                                    ->success()
                                    ->send();
                            } else {
                                // Get actual errors and failures
                                $importErrors = $import->errors();
                                $importFailures = $import->failures();

                                $errorMessage = 'Không có vé nào được nhập.';

                                // Show actual validation failures if they exist
                                if (! empty($importFailures)) {
                                    $errorMessage .= "\n\n**Lỗi xác thực:**";
                                    foreach ($importFailures as $failure) {
                                        $row = $failure->row();
                                        $errors = implode(', ', $failure->errors());
                                        $errorMessage .= "\n• Dòng {$row}: {$errors}";
                                    }
                                }

                                // Show actual processing errors if they exist
                                if (! empty($importErrors)) {
                                    $errorMessage .= "\n\n**Lỗi xử lý:**";
                                    foreach ($importErrors as $error) {
                                        $errorMessage .= "\n• {$error}";
                                    }
                                }

                                Notification::make()
                                    ->title('Nhập dữ liệu thất bại')
                                    ->body($errorMessage)
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            // Clean up uploaded file on error
                            Storage::disk('local')->delete($data['excel_file']);

                            Notification::make()
                                ->title('Lỗi nhập dữ liệu')
                                ->body('Đã xảy ra lỗi trong quá trình nhập: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xóa đã chọn'),

                    BulkAction::make('updateStatus')
                        ->label('Cập nhật trạng thái')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('ticket_status_id')
                                ->label('Trạng thái')
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
                                ->title('Đã cập nhật trạng thái')
                                ->body(count($records).' vé đã được cập nhật.')
                                ->send();
                        }),

                    // NEW: Bulk assign users
                    BulkAction::make('assignUsers')
                        ->label('Giao cho người dùng')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('assignees')
                                ->label('Người thực hiện')
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
                                ->label('Chế độ giao việc')
                                ->options([
                                    'replace' => 'Thay thế người hiện tại',
                                    'add' => 'Thêm vào danh sách hiện tại',
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
                                ->title('Đã giao việc')
                                ->body(count($records).' vé đã được cập nhật người thực hiện mới.')
                                ->send();
                        }),
                    BulkAction::make('updatePriority')
                        ->label('Cập nhật mức ưu tiên')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority_id')
                                ->label('Mức ưu tiên')
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
                        ->label('Gán vào Epic')
                        ->icon('heroicon-o-bookmark')
                        ->form([
                            Select::make('epic_id')
                                ->label('Epic')
                                ->options(function (RelationManager $livewire) {
                                    $projectId = $livewire->getOwnerRecord()->id;

                                    return Epic::where('project_id', $projectId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Chọn một Epic để gán các vé đã chọn. Để trống để gỡ khỏi Epic.'),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'epic_id' => $data['epic_id'],
                                ]);
                            }

                            $epicName = $data['epic_id']
                                ? Epic::find($data['epic_id'])->name
                                : 'Không có Epic';

                            Notification::make()
                                ->success()
                                ->title('Đã cập nhật Epic')
                                ->body(count($records).' vé đã được gán vào: '.$epicName)
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
