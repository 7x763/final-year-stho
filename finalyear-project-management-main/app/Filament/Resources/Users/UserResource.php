<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\ProjectsRelationManager;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('System Admin');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Full Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->unique(
                        ignoreRecord: true
                    )
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at')
                    ->label(__('Email Verified At')),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null
                    )
                    ->dehydrated(fn ($state) => ! empty($state))
                    ->required(fn (string $operation): bool => in_array($operation, ['create', 'attach.createOption']))
                    ->maxLength(255),
                Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['roles']))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Full Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->badge()
                    ->separator(',')
                    ->sortable(),

                TextColumn::make('projects_count')
                    ->label(__('Projects'))
                    ->counts('projects')
                    ->sortable(),

                TextColumn::make('assigned_tickets_count')
                    ->label(__('Has Assigned Tickets'))
                    ->counts('assignedTickets')
                    ->sortable(),

                TextColumn::make('created_tickets_count')
                    ->label(__('Has Created Tickets'))
                    ->counts('createdTickets')
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label(__('Email Verified'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Filter::make('has_projects')
                    ->label(__('Has Projects'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('projects')),

                Filter::make('has_assigned_tickets')
                    ->label(__('Has Assigned Tickets'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('assignedTickets')),

                Filter::make('has_created_tickets')
                    ->label(__('Has Created Tickets'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('createdTickets')),

                // Filter by role
                SelectFilter::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Filter::make('email_unverified')
                    ->label(__('Email Unverified'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected')),

                    // NEW: Bulk action to assign role
                    BulkAction::make('assignRole')
                        ->label(__('Assign Role'))
                        ->icon('heroicon-o-shield-check')
                        ->form([
                            Select::make('roles')
                                ->label(__('Roles'))
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required(),

                            Radio::make('role_mode')
                                ->label(__('Assign Mode'))
                                ->options([
                                    'replace' => __('Replace existing roles'),
                                    'add' => __('Add to existing roles'),
                                ])
                                ->default('add')
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                if ($data['role_mode'] === 'replace') {
                                    $record->roles()->sync($data['roles']);
                                } else {
                                    $record->roles()->syncWithoutDetaching($data['roles']);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('nav_badge_users_count', now()->addMinutes(5), function () {
            return (string) static::getModel()::count();
        });
    }
}
