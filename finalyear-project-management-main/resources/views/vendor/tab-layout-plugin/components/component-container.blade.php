@use('Illuminate\View\Component', 'ViewComponent')
@use('SolutionForest\TabLayoutPlugin\Schemas\Components\LivewireContainer')
<div
    class="filament-component-container grid gap-6"
    style="
        --cols-default: {{ $getColumns('default') ?? 1 }};
        --cols-sm: {{ $getColumns('sm') ?? $getColumns('default') ?? 1 }};
        --cols-md: {{ $getColumns('md') ?? $getColumns('sm') ?? $getColumns('default') ?? 1 }};
        --cols-lg: {{ $getColumns('lg') ?? $getColumns('md') ?? $getColumns('sm') ?? $getColumns('default') ?? 1 }};
        --cols-xl: {{ $getColumns('xl') ?? $getColumns('lg') ?? $getColumns('md') ?? $getColumns('sm') ?? $getColumns('default') ?? 1 }};
        --cols-2xl: {{ $getColumns('2xl') ?? $getColumns('xl') ?? $getColumns('lg') ?? $getColumns('md') ?? $getColumns('sm') ?? $getColumns('default') ?? 1 }};
    "
>
    @foreach ($getComponents(withHidden: false) as $tabContainer)
        @php
            $columns = $tabContainer->getColumnSpan() ?? [];
            $defaultSpan = $columns['default'] ?? 'full';
        @endphp

        <div
            class="fi-fo-grid-column {{ (method_exists($tabContainer, 'getMaxWidth') && $maxWidth = $tabContainer->getMaxWidth()) ? match ($maxWidth) {
                'xs' => 'max-w-xs',
                'sm' => 'max-w-sm',
                'md' => 'max-w-md',
                'lg' => 'max-w-lg',
                'xl' => 'max-w-xl',
                '2xl' => 'max-w-2xl',
                '3xl' => 'max-w-3xl',
                '4xl' => 'max-w-4xl',
                '5xl' => 'max-w-5xl',
                '6xl' => 'max-w-6xl',
                '7xl' => 'max-w-7xl',
                default => $maxWidth,
            } : null }}"
            style="
                --col-span-default: {{ $columns['default'] ?? 'full' }};
                --col-span-sm: {{ $columns['sm'] ?? $columns['default'] ?? 'full' }};
                --col-span-md: {{ $columns['md'] ?? $columns['sm'] ?? $columns['default'] ?? 'full' }};
                --col-span-lg: {{ $columns['lg'] ?? $columns['md'] ?? $columns['sm'] ?? $columns['default'] ?? 'full' }};
                --col-span-xl: {{ $columns['xl'] ?? $columns['lg'] ?? $columns['md'] ?? $columns['sm'] ?? $columns['default'] ?? 'full' }};
                --col-span-2xl: {{ $columns['2xl'] ?? $columns['xl'] ?? $columns['lg'] ?? $columns['md'] ?? $columns['sm'] ?? $columns['default'] ?? 'full' }};
            "
        >
            @if ($tabContainer instanceof ViewComponent)
                {{ $tabContainer->render() }}
            @else
                @php
                    $livewireComponent = $tabContainer->getComponent();
                @endphp
                @if ($livewireComponent)
                    @livewire($livewireComponent, $tabContainer->getData() ?? [])
                @endif
             @endif
        </div>
    @endforeach
</div>
