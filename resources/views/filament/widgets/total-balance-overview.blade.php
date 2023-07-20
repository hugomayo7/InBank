<x-filament::widget class="filament-stats-overview-widget" wire:init="fetchBalance">
    <div id="balance-card"
        {!! ($pollingInterval = $this->getPollingInterval()) ? "wire:poll.{$pollingInterval}" : '' !!}
    >
        <x-filament::stats :columns="$this->getColumns()">
            @foreach ($this->getCachedCards() as $card)
                {{ $card }}
            @endforeach
        </x-filament::stats>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            let slidesContainer = document.getElementById('balance-card');
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
