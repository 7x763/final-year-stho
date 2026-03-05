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

    protected static ?string $title = 'Project Notes';

    protected static ?string $modelLabel = 'Note';

    protected static ?string $pluralModelLabel = 'Notes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DatePicker::make('note_date')
                    ->label(__('Note Date'))
                    ->default(now())
                    ->required(),

                RichEditor::make('content')
                    ->label(__('Content'))
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
                    ->helperText(__('Write meeting summary or project notes here.')),

                Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query
                ->select(['id', 'project_id', 'title', 'note_date', 'created_by', 'created_at'])
                ->with(['creator:id,name'])
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                TextColumn::make('note_date')
                    ->label(__('Note Date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label(__('Creator'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('recent')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label(__('Recent (30 days)')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label(__('Add Note'))
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('View'))
                    ->closeModalByClickingAway(false),
                EditAction::make()
                    ->label(__('Edit'))
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->label(__('Delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected')),
                ]),
            ])
            ->defaultSort('note_date', 'desc')
            ->emptyStateHeading(__('No notes yet'))
            ->emptyStateDescription(__('Start capturing meeting minutes and important project notes.'))
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
