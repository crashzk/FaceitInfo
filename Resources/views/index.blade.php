@php
    $game = config('faceit.game', 'cs2');
    $gameData = $faceitInfo['games'][$game] ?? [];
    $elo = $gameData['faceit_elo'] ?? null;
    $level = $gameData['skill_level'] ?? null;
    $nickname = $faceitInfo['nickname'] ?? '';
    $country = $faceitInfo['country'] ?? null;
    $profileUrl = $faceitInfo['faceit_url'] ?? ('https://www.faceit.com/en/players/' . urlencode($nickname));
    $profileUrl = str_replace('{lang}', 'en', $profileUrl);

    $lifetime = $playerStats['lifetime'] ?? [];
    $kd = $lifetime['Average K/D Ratio'] ?? null;
    $winRate = $lifetime['Win Rate %'] ?? null;
    $matches = $lifetime['Matches'] ?? null;
    $hs = $lifetime['Average Headshots %'] ?? null;

    $recentResults = $lifetime['Recent Results'] ?? [];
    if (is_string($recentResults)) {
        $recentResults = array_filter(explode(',', $recentResults), fn($v) => $v !== '');
    }
@endphp

<div class="profile__section faceit-profile">
    <div class="profile__section-title">Faceit</div>

    <div class="faceit-profile__card">
        <div class="faceit-profile__card-header">
            @if ($level)
                <img src="{{ asset('assets/img/ranks/faceit/' . $level . '.svg') }}"
                    alt="Level {{ $level }}" class="faceit-profile__level-icon">
            @endif
            <div class="faceit-profile__card-info">
                <div class="faceit-profile__card-name">
                    {{ $nickname }}
                    @if ($country)
                        <span class="faceit-profile__country">{{ strtoupper($country) }}</span>
                    @endif
                </div>
                <div class="faceit-profile__card-elo">
                    @if ($elo)
                        {{ number_format($elo) }} ELO
                    @endif
                </div>
            </div>
            <a href="{{ $profileUrl }}" target="_blank" rel="noopener nofollow"
                class="faceit-profile__link">
                <x-icon path="ph.regular.arrow-square-out" />
            </a>
        </div>

        @if ($kd || $winRate || $matches || $hs)
            <div class="faceit-profile__quick-stats">
                @if ($kd)
                    <span class="faceit-profile__quick-stat">
                        <span class="faceit-profile__quick-stat-value">{{ $kd }}</span>
                        <span class="faceit-profile__quick-stat-label">K/D</span>
                    </span>
                @endif
                @if ($hs)
                    <span class="faceit-profile__quick-stat">
                        <span class="faceit-profile__quick-stat-value">{{ $hs }}%</span>
                        <span class="faceit-profile__quick-stat-label">HS</span>
                    </span>
                @endif
                @if ($winRate)
                    <span class="faceit-profile__quick-stat">
                        <span class="faceit-profile__quick-stat-value">{{ $winRate }}%</span>
                        <span class="faceit-profile__quick-stat-label">WR</span>
                    </span>
                @endif
                @if ($matches)
                    <span class="faceit-profile__quick-stat">
                        <span class="faceit-profile__quick-stat-value">{{ $matches }}</span>
                        <span class="faceit-profile__quick-stat-label">@t('faceitinfo.matches')</span>
                    </span>
                @endif
            </div>
        @endif

        @if (is_array($recentResults) && count($recentResults) > 0)
            <div class="faceit-profile__recent">
                @foreach ($recentResults as $result)
                    <span class="faceit-profile__recent-dot faceit-profile__recent-dot--{{ $result == '1' ? 'win' : 'loss' }}"></span>
                @endforeach
            </div>
        @endif
    </div>
</div>
