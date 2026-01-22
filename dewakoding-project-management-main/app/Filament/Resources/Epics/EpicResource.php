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

    protected static string|\UnitEnum|null $navigationGroup = 'Quản lý dự án';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Epic';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Epic';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Dự án')
                    ->relationship('project', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label('Tên Epic')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->label('Mô tả')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label('Ngày kết thúc')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                TextInput::make('sort_order')
                    ->label('Thứ tự sắp xếp')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tên Epic')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date('d/m/Y')
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
            ->recordActions([
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
