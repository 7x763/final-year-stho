<?php

namespace App\Filament\Resources\TicketComments;

use App\Filament\Resources\TicketComments\Pages\CreateTicketComment;
use App\Filament\Resources\TicketComments\Pages\EditTicketComment;
use App\Filament\Resources\TicketComments\Pages\ListTicketComments;
use App\Models\TicketComment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketCommentResource extends Resource
{
    protected static ?string $model = TicketComment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Ticket Comment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Ticket Comments');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                RichEditor::make('comment')
                    ->label(__('Comment'))
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ticket', 'user']);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket.name')
                    ->label(__('Ticket'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->numeric()
                    ->sortable(),
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
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicketComments::route('/'),
            'create' => CreateTicketComment::route('/create'),
            'edit' => EditTicketComment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
