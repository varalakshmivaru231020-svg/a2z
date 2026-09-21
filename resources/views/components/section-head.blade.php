@props(['title', 'eyebrow' => null, 'lead' => null, 'align' => 'center'])
<div class="section-head section-head--{{ $align }}">
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif
    <h2>{{ $title }}</h2>
    @if ($lead)
        <p class="section-head__lead">{{ $lead }}</p>
    @endif
</div>
