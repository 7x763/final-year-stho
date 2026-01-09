<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\ColorPalette;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt';

    protected static ?string $title = 'Cài đặt giao diện';

    protected static ?string $navigationLabel = 'Cài đặt giao diện';

    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $userId = auth()->id();

        $this->form->fill([
            'navigation_style' => Setting::getUserValue('filament_navigation_style', 'sidebar', $userId),
            'panel_color' => Setting::getUserValue('filament_primary_color', 'blue', $userId),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bố cục điều hướng')
                    ->description('Chọn phong cách điều hướng yêu thích của bạn')
                    ->icon('heroicon-o-bars-3')
                    ->schema([
                        Radio::make('navigation_style')
                            ->label('Phong cách bố cục')
                            ->options([
                                'sidebar' => 'Điều hướng thanh bên (Sidebar)',
                                'top' => 'Điều hướng thanh trên (Top)',
                            ])
                            ->descriptions([
                                'sidebar' => 'Bố cục thanh bên cổ điển (khuyên dùng cho máy tính)',
                                'top' => 'Thanh điều hướng phía trên hiện đại (phù hợp cho máy tính bảng)',
                            ])
                            ->inline(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state): void {
                                $this->updateNavigationStyle($state);
                            }),
                    ]),

                Section::make('Chủ đề màu sắc')
                    ->description('Cá nhân hóa màu sắc giao diện của bạn')
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Select::make('panel_color')
                            ->label('Màu sắc chính')
                            ->options(ColorPalette::options())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state): void {
                                $this->updateColorTheme($state);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function updateNavigationStyle(string $style): void
    {
        Setting::setUserValue('filament_navigation_style', $style, 'ui', auth()->id());

        $this->dispatch('navigation-style-updated', style: $style);

        Notification::make()
            ->title('Đã cập nhật điều hướng')
            ->body($style === 'top'
                ? 'Tùy chọn điều hướng thanh trên đã được lưu. Tải lại trang để áp dụng.'
                : 'Tùy chọn điều hướng thanh bên đã được lưu.')
            ->success()
            ->send();
    }

    protected function updateColorTheme(string $color): void
    {
        Setting::setUserValue('filament_primary_color', $color, 'ui', auth()->id());

        $this->applyColorChange($color);

        $this->dispatch('color-theme-updated', color: $color);

        Notification::make()
            ->title('Đã cập nhật màu sắc')
            ->body("Màu sắc chính đã được đổi thành {$color}.")
            ->success()
            ->send();
    }

    protected function applyColorChange(string $colorName): void
    {
        FilamentColor::register([
            'primary' => ColorPalette::constantFor($colorName),
        ]);
    }

    public function save(): void
    {
        $this->updateNavigationStyle($this->data['navigation_style']);
        $this->updateColorTheme($this->data['panel_color']);

        Notification::make()
            ->title('Đã lưu cài đặt thành công')
            ->body('Các tùy chọn đã được lưu. Đang tải lại để áp dụng bố cục...')
            ->success()
            ->send();

        $this->dispatch('settings-saved');
    }
}
