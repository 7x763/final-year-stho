<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EpicsRelationManager extends RelationManager
{
    protected static bool $isLazy = true;

    protected static string $relationship = 'epics';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->epics_count ?? $ownerRecord->epics()->count();
    }

    protected static ?string $title = 'Epic';

    protected static ?string $label = 'Epic';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tên Epic'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Thứ tự sắp xếp')
                    ->helperText('Số càng nhỏ thì hiển thị trước'),
                DatePicker::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->nullable(),
                DatePicker::make('end_date')
                    ->label('Ngày kết thúc')
                    ->nullable(),
                RichEditor::make('description')
                    ->label('Mô tả')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->select(['id', 'project_id', 'name', 'sort_order', 'start_date', 'end_date', 'created_at']))
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tên Epic')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date()
                    ->sortable(),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Số lượng vé'),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo Epic mới'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa các mục đã chọn'),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
