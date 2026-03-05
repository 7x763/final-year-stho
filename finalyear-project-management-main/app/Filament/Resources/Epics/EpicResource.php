<?php

namespace App\Filament\Resources\Epics;

use App\Filament\Resources\Epics\Pages\CreateEpic;
use App\Filament\Resources\Epics\Pages\EditEpic;
use App\Filament\Resources\Epics\Pages\ListEpics;
use App\Models\Epic;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EpicResource extends Resource
{
    protected static ?string $model = Epic::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    public static function getNavigationGroup(): ?string
    {
        return __('Project Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Epics');
    }

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('Epic');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Epics');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label(__('Project'))
                    ->relationship('project', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label(__('Epic Name'))
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label(__('End Date'))
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                TextInput::make('sort_order')
                    ->label(__('Sort Order'))
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Epic Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label(__('Tickets')),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListEpics::route('/'),
            'create' => CreateEpic::route('/create'),
            'edit' => EditEpic::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['project']);

        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $query->whereHas('project.members', function (Builder $query): void {
                $query->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}
