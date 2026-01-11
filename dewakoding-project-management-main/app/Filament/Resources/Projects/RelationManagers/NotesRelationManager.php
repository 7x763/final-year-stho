<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static bool $isLazy = true;

    protected static string $relationship = 'notes';

    public static function getBadge(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->notes_count ?? $ownerRecord->notes()->count();
    }

    protected static ?string $title = 'Ghi chú dự án';

    protected static ?string $modelLabel = 'Ghi chú';

    protected static ?string $pluralModelLabel = 'Ghi chú';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DatePicker::make('note_date')
                    ->label('Ngày ghi chú')
                    ->default(now())
                    ->required(),

                RichEditor::make('content')
                    ->label('Nội dung')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->helperText('Viết tóm tắt cuộc họp hoặc ghi chú dự án tại đây.'),

                Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->with(['creator']))
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                TextColumn::make('note_date')
                    ->label('Ngày ghi chú')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('recent')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Gần đây (30 ngày)'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Thêm ghi chú')
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem')
                    ->closeModalByClickingAway(false),
                EditAction::make()
                    ->label('Sửa')
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa các mục đã chọn'),
                ]),
            ])
            ->defaultSort('note_date', 'desc')
            ->emptyStateHeading('Chưa có ghi chú nào')
            ->emptyStateDescription('Bắt đầu ghi lại các cuộc họp và ghi chú quan trọng cho dự án.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
