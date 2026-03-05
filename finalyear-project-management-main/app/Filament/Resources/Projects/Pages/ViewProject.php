<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Pages\ProjectBoard;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('health_check')
                ->label(__('Health Check'))
                ->icon('heroicon-o-heart')
                ->color('danger')
                ->url(fn () => ProjectResource::getUrl('health-check', ['record' => $this->record])),
            Action::make('board')
                ->label(__('Project Board'))
                ->icon('heroicon-o-view-columns')
                ->color('info')
                ->url(fn () => ProjectBoard::getUrl(['project_id' => $this->record->id])),
            Action::make('external_access')
                ->label(__('External Dashboard'))
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole('super_admin'))
                ->modalHeading(__('External Dashboard Access'))
                ->modalDescription(__('Share these credentials with external users to access the project dashboard.'))
                ->modalContent(function () {
                    $record = $this->record;
                    $externalAccess = $record->externalAccess;

                    if (! $externalAccess) {
                        $externalAccess = $record->generateExternalAccess();
                    }

                    $dashboardUrl = url('/external/'.$externalAccess->access_token);

                    return view('filament.components.external-access-modal', [
                        'dashboardUrl' => $dashboardUrl,
                        'password' => $externalAccess->password,
                        'lastAccessed' => $externalAccess->last_accessed_at ? $externalAccess->last_accessed_at->format('d/m/Y H:i') : null,
                        'isActive' => $externalAccess->is_active,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('Close')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Project Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('Project Name'))
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                TextEntry::make('ticket_prefix')
                                    ->label(__('Ticket Prefix'))
                                    ->badge()
                                    ->color('primary'),
                            ]),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->html()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label(__('Start Date'))
                                    ->date('d/m/Y')
                                    ->placeholder(__('Not set')),
                                TextEntry::make('end_date')
                                    ->label(__('End Date'))
                                    ->date('d/m/Y')
                                    ->placeholder(__('Not set')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('remaining_days')
                                    ->label(__('Remaining Days'))
                                    ->getStateUsing(function ($record): ?string {
                                        if (! $record->end_date) {
                                            return __('Not set');
                                        }

                                        return $record->remaining_days.' '.__('days');
                                    })
                                    ->badge()
                                    ->color(fn ($record): string => ! $record->end_date ? 'gray' :
                                        ($record->remaining_days <= 0 ? 'danger' :
                                        ($record->remaining_days <= 7 ? 'warning' : 'success'))
                                    ),
                                TextEntry::make('pinned_date')
                                    ->label(__('Pinned Status'))
                                    ->getStateUsing(function ($record): string {
                                        return $record->pinned_date ? __('Pinned on').' '.$record->pinned_date->format('d/m/Y H:i') : __('Not pinned');
                                    })
                                    ->badge()
                                    ->color(fn ($record): string => $record->pinned_date ? 'success' : 'gray'),
                            ]),
                    ]),

                Section::make(__('Project Statistics'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('members_count')
                                    ->label(__('Total Members'))
                                    ->getStateUsing(fn ($record) => $record->members_count ?? $record->members()->count())
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('tickets_count')
                                    ->label(__('Total Tickets'))
                                    ->getStateUsing(fn ($record) => $record->tickets_count ?? $record->tickets()->count())
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('epics_count')
                                    ->label(__('Total Epics'))
                                    ->getStateUsing(fn ($record) => $record->epics_count ?? $record->epics()->count())
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('statuses_count')
                                    ->label(__('Ticket Statuses'))
                                    ->getStateUsing(fn ($record) => $record->ticket_statuses_count ?? $record->ticketStatuses()->count())
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                Section::make(__('Timestamps'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
