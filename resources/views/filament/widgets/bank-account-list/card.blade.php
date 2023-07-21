@php
    $url = $getUrl();
@endphp

<x-bank-account-card
    :accountId="$getAccountId()"
    :tag="$url ? 'a' : 'div'"
    :chart="$getChart()"
    :chartColor="$getChartColor()"
    :color="$getColor()"
    :icon="$getIcon()"
    :description="$getDescription()"
    :descriptionColor="$getDescriptionColor()"
    :descriptionIcon="$getDescriptionIcon()"
    :descriptionIconPosition="$getDescriptionIconPosition()"
    :href="$url"
    :target="$shouldOpenUrlInNewTab() ? '_blank' : null"
    :label="$getLabel()"
    :value="$getValue()"
    :extraAttributes="$getExtraAttributes()"
    :iban="$getIban()"
    class="filament-stats-overview-widget-card"
/>
