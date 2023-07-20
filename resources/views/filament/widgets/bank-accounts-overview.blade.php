<x-filament::widget class="filament-stats-overview-widget" wire:init="fetchAccountsData">
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
                <div class="glide__track" data-glide-el="track">
                    <ul class="glide__slides" id="bank-accounts-slides" wire:ignore.self>
                        @foreach ($this->getCachedCards() as $card)
                            <li class="glide__slide mb-1" wire:ignore.self>
                                {{ $card }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="glide__bullets" data-glide-el="controls[nav]">
                    @foreach (range(0, ceil(count($this->getCachedCards()) / 3)) as $i)
                        <button class="glide__bullet" data-glide-dir="={{$i}}" wire:ignore.self></button>
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

        document.addEventListener('livewire:load', function () {
            let slidesContainer = document.getElementById('bank-accounts-slides');
            let valueElements = slidesContainer.querySelectorAll('.text-3xl');

            valueElements.forEach(element => {
                if (element.textContent.trim() === 'N/A') {
                    element.innerHTML = '<span class="loader"></span>';
                }
            });
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

        .loader {
            display: inline-block;
            width: 29px;
            height: 29px;
            border: 4px solid rgba(255, 255, 255, 0.3); /* Couleur de la bordure du loader */
            border-top: 4px solid #2563eb; /* Couleur de la partie supérieure du loader */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</x-filament::widget>
