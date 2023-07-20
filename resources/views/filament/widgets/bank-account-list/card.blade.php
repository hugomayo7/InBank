@php
    $url = $getUrl();
@endphp

<x-bank-account-card
    :accountId="$getAccountId()"
    :tag="$url ? 'a' : 'div'"
    :chart="$getChart()"
    :chart-color="$getChartColor()"
    :color="$getColor()"
    :icon="$getIcon()"
    :description="$getDescription()"
    :description-color="$getDescriptionColor()"
    :description-icon="$getDescriptionIcon()"
    :description-icon-position="$getDescriptionIconPosition()"
    :href="$url"
    :target="$shouldOpenUrlInNewTab() ? '_blank' : null"
    :label="$getLabel()"
    :value="$getValue()"
    :extra-attributes="$getExtraAttributes()"
    :iban="$getIban()"
    class="filament-stats-overview-widget-card"
/>
