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

    protected static ?string $title = 'Trạng thái vé';

    protected static ?string $modelLabel = 'Trạng thái';

    protected static ?string $pluralModelLabel = 'Trạng thái';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->ticket_statuses_count ?? $ownerRecord->ticketStatuses()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên trạng thái')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Màu sắc')
                    ->required()
                    ->default('#3490dc')
                    ->helperText('Chọn màu sắc cho trạng thái này'),
                TextInput::make('sort_order')
                    ->label('Thứ tự sắp xếp')
                    ->numeric()
                    ->default(0)
                    ->helperText('Xác định thứ tự hiển thị trên bảng dự án (số nhỏ hiện trước)'),
                Toggle::make('is_completed')
                    ->label('Đánh dấu là trạng thái hoàn thành')
                    ->helperText('Mỗi dự án chỉ có thể có một trạng thái được đánh dấu là hoàn thành')
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
                                    ->title('Không thể đánh dấu hoàn thành')
                                    ->body("Trạng thái '{$existingCompleted->name}' đã được đánh dấu là hoàn thành cho dự án này. Chỉ một trạng thái có thể được chọn.")
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
                    ->label('Tên trạng thái'),
                ColorColumn::make('color')
                    ->label('Màu sắc'),
                TextColumn::make('sort_order')
                    ->label('Thứ tự'),
                IconColumn::make('is_completed')
                    ->label('Đã hoàn thành')
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
                    ->label('Tạo trạng thái mới')
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
                                    ->title('Không thể đánh dấu hoàn thành')
                                    ->body("Trạng thái '{$existingCompleted->name}' đã được đánh dấu là hoàn thành cho dự án này.")
                                    ->send();
                            }
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Sửa')
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
                                    ->title('Không thể đánh dấu hoàn thành')
                                    ->body("Trạng thái '{$existingCompleted->name}' đã được đánh dấu là hoàn thành cho dự án này.")
                                    ->send();
                            }
                        }

                        return $data;
                    }),
                DeleteAction::make()->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
            ]);
    }
}
