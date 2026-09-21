<a class="service-card" href="{{ $service->url() }}">
    <span class="icon-badge"><x-icon :name="$service->icon" :size="26" /></span>
    <h3>{{ $service->title }}</h3>
    <p>{{ $service->summary }}</p>
    <span class="service-card__more">Learn more <x-icon name="arrow-right" :size="16" /></span>
</a>
