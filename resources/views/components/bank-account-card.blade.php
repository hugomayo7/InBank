@props([
    'chart' => null,
    'chartColor' => null,
    'color' => null,
    'icon' => null,
    'description' => null,
    'descriptionColor' => null,
    'descriptionIcon' => null,
    'descriptionIconPosition' => 'after',
    'flat' => false,
    'label' => null,
    'tag' => 'div',
    'value' => null,
    'extraAttributes' => [],
    'iban' => null,
    'accountId' => null
])

<{!! $tag !!}
{{
    $attributes->merge($extraAttributes)->class([
        'filament-stats-card relative rounded-2xl bg-white p-6 shadow',
        'dark:bg-gray-800' => config('filament.dark_mode'),
    ])
}}
>
<div @class([
        'space-y-2',
    ])>
    <div
        @class([
            'flex items-center justify-between space-x-2 text-sm font-medium text-gray-500 rtl:space-x-reverse',
            'dark:text-gray-200' => config('filament.dark_mode'),
        ])
    >
        <div class="flex space-x-2 items-center py-1">
            @if ($icon)
                <x-dynamic-component :component="$icon" class="h-4 w-4"/>
            @endif
            <span>{{ $label }}</span>
        </div>
        @if($iban)
            <div id="iban_{{ $accountId }}"
                class="iban flex space-x-2 items-center dark:hover:text-gray-800 text-xs py-1 px-2 border rounded-lg hover:bg-gray-100 cursor-pointer transition delay-50">
                <p>IBAN</p>
                <i class="fa-regular fa-clipboard"></i>
            </div>
        @endif
    </div>

    <div class="text-3xl">
        {{ $value }}
    </div>

    @if ($description)
        <div
            @class([
                'flex items-center space-x-1 text-sm font-medium rtl:space-x-reverse',
                match ($descriptionColor) {
                    'danger' => 'text-danger-600',
                    'primary' => 'text-primary-600',
                    'success' => 'text-success-600',
                    'warning' => 'text-warning-600',
                    default => 'text-gray-600',
                },
            ])
        >
            @if ($descriptionIcon && $descriptionIconPosition === 'before')
                <x-dynamic-component
                    :component="$descriptionIcon"
                    class="h-4 w-4"
                />
            @endif

            <span>{{ $description }}</span>

            @if ($descriptionIcon && $descriptionIconPosition === 'after')
                <x-dynamic-component
                    :component="$descriptionIcon"
                    class="h-4 w-4"
                />
            @endif
        </div>
    @endif
</div>

@if ($chart)
    <div
        x-title="filament-stats-card-chart"
        x-data="{
                chart: null,

                labels: {{ json_encode(array_keys($chart)) }},
                values: {{ json_encode(array_values($chart)) }},

                init: function () {
                    this.chart ? this.updateChart() : this.initChart()
                },

                initChart: function () {
                    return (this.chart = new Chart(this.$refs.canvas, {
                        type: 'line',
                        data: {
                            labels: this.labels,
                            datasets: [
                                {
                                    data: this.values,
                                    backgroundColor: getComputedStyle(
                                        $refs.backgroundColorElement,
                                    ).color,
                                    borderColor: getComputedStyle($refs.borderColorElement)
                                        .color,
                                    borderWidth: 2,
                                    fill: 'start',
                                    tension: 0.5,
                                },
                            ],
                        },
                        options: {
                            elements: {
                                point: {
                                    radius: 0,
                                },
                            },
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                            scales: {
                                x: {
                                    display: false,
                                },
                                y: {
                                    display: false,
                                },
                            },
                            tooltips: {
                                enabled: false,
                            },
                        },
                    }))
                },

                updateChart: function () {
                    this.chart.data.labels = this.labels
                    this.chart.data.datasets[0].data = this.values
                    this.chart.update()
                },
            }"
        x-on:dark-mode-toggled.window="
                chart.destroy()
                initChart()
            "
        class="absolute inset-x-0 bottom-0 overflow-hidden rounded-b-2xl"
    >
        <canvas wire:ignore x-ref="canvas" class="h-6">
                <span
                    x-ref="backgroundColorElement"
                    @class([
                        match ($chartColor) {
                            'danger' => \Illuminate\Support\Arr::toCssClasses(['text-danger-50', 'dark:text-danger-700' => config('filament.dark_mode')]),
                            'primary' => \Illuminate\Support\Arr::toCssClasses(['text-primary-50', 'dark:text-primary-700' => config('filament.dark_mode')]),
                            'success' => \Illuminate\Support\Arr::toCssClasses(['text-success-50', 'dark:text-success-700' => config('filament.dark_mode')]),
                            'warning' => \Illuminate\Support\Arr::toCssClasses(['text-warning-50', 'dark:text-warning-700' => config('filament.dark_mode')]),
                            default => \Illuminate\Support\Arr::toCssClasses(['text-gray-50', 'dark:text-gray-700' => config('filament.dark_mode')]),
                        },
                    ])
                ></span>

            <span
                x-ref="borderColorElement"
                    @class([
                        match ($chartColor) {
                            'danger' => 'text-danger-400',
                            'primary' => 'text-primary-400',
                            'success' => 'text-success-400',
                            'warning' => 'text-warning-400',
                            default => 'text-gray-400',
                        },
                    ])
                ></span>
        </canvas>
    </div>
@endif
<script>
    document.addEventListener('livewire:load', function () {
        if (document.getElementById('iban_' + '{!! $accountId !!}')) {
            document.getElementById('iban_' + '{!! $accountId !!}').addEventListener('click', () => {
                var formattedIban = '{!! $iban !!}'.replace(/(.{4})/g, '$1 ').trim();
                navigator.clipboard.writeText(formattedIban);
                const ibanElement = document.getElementById('iban_' + '{!! $accountId !!}');
                const ibanText = ibanElement.getElementsByTagName('p')[0];
                const iconElement = ibanElement.getElementsByTagName('i')[0];
                ibanText.innerText = 'Copié';
                iconElement.classList.remove('fa-regular', 'fa-clipboard');
                iconElement.classList.add('fa-solid', 'fa-check', 'fa-bounce');
                ibanElement.classList.add('iban-copied');

                setTimeout(() => {
                    // Reset the icon and remove the green background class after a short delay
                    iconElement.classList.remove('fa-solid', 'fa-check', 'fa-bounce');
                    iconElement.classList.add('fa-regular', 'fa-clipboard');
                    ibanElement.classList.remove('iban-copied');
                    ibanText.innerText = 'IBAN';
                }, 2000);
            });
        }
    })
</script>
<style>
    .iban-copied {
        background-color: #2563eb;
        color: #fff;
    }
    .iban-copied:hover {
        background-color: #2563eb;
    }
</style>
</{!! $tag !!}>
