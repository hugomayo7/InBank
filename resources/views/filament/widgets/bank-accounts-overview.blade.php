<x-filament::widget class="filament-stats-overview-widget" wire:ignore>
    <header
        class="filament-header items-start justify-between space-y-2 sm:flex sm:space-x-4 sm:space-y-0 pb-4 sm:rtl:space-x-reverse">
        <div>
            <h2 @class(['filament-header-heading', 'text-xl', 'font-bold', 'tracking-tight'])>{{ $this->getTitle() }}</h2>
        </div>
    </header>
    <div
        {!! ($pollingInterval = $this->getPollingInterval()) ? "wire:poll.{$pollingInterval}" : '' !!}
    >
        <div @class(['pb-8'])>
            <div class="glide">
                <div class="glide__track" data-glide-el="track" wire:ignore>
                    <ul class="glide__slides">
                        @foreach ($this->getCachedCards() as $card)
                            <li class="glide__slide">
                                {{ $card }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="glide__bullets" data-glide-el="controls[nav]" wire:ignore>
                    @foreach (range(0, ceil(count($this->getCachedCards()) / 3)) as $i)
                        <button class="glide__bullet" data-glide-dir="={{$i}}"></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/css/glide.core.min.css"
          integrity="sha512-YQlbvfX5C6Ym6fTUSZ9GZpyB3F92hmQAZTO5YjciedwAaGRI9ccNs4iw2QTCJiSPheUQZomZKHQtuwbHkA9lgw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/css/glide.theme.min.css"
          integrity="sha512-wCwx+DYp8LDIaTem/rpXubV/C1WiNRsEVqoztV0NZm8tiTvsUeSlA/Uz02VTGSiqfzAHD4RnqVoevMcRZgYEcQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.2.0/glide.min.js"
            integrity="sha512-IkLiryZhI6G4pnA3bBZzYCT9Ewk87U4DGEOz+TnRD3MrKqaUitt+ssHgn2X/sxoM7FxCP/ROUp6wcxjH/GcI5Q=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Glide('.glide', {
                type: 'slider',
                startAt: 0,
                perView: 3,
                rewind: false,
                bound: true,
                breakpoints: {
                    1260: {
                        perView: 2
                    },
                    780: {
                        perView: 1
                    }
                },
            }).mount()
        })
    </script>
    <style>
        .glide__bullets {
            bottom: -1.3rem !important;
        }
        .glide__bullet {
            background-color: rgb(107 114 128 / 0.5) !important;
        }
        .glide__bullet--active {
            background-color: rgb(107 114 128 / 1) !important;
        }
    </style>
</x-filament::widget>
