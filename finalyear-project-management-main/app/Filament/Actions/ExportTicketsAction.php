<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;

class ExportTicketsAction
{
    public static function make(): Action
    {
        return Action::make('export_tickets')
            ->label(__('Export to Excel'))
            ->icon('heroicon-m-arrow-down-tray')
            ->color('success')
            ->schema([
                Section::make(__('Select Columns to Export'))
                    ->description(__('Choose which columns you want to include in the Excel export'))
                    ->schema([
                        CheckboxList::make('columns')
                            ->label(__('Columns'))
                            ->options([
                                'uuid' => __('Ticket ID'),
                                'name' => __('Title'),
                                'description' => __('Description'),
                                'status' => __('Status'),
                                'assignee' => __('Assignee'),
                                'project' => __('Project'),
                                'epic' => __('Epic'),
                                'due_date' => __('Due Date'),
                                'created_at' => __('Created At'),
                                'updated_at' => __('Updated At'),
                            ])
                            ->default(['uuid', 'name', 'status', 'assignee', 'due_date', 'created_at'])
                            ->required()
                            ->minItems(1)
                            ->columns(2)
                            ->gridDirection('row'),
                    ]),
            ])
            ->action(function (array $data, $livewire): void {
                $livewire->exportTickets($data['columns'] ?? []);
            });
    }
}
