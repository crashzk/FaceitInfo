<div class="faceit-info-container">
    <div class="faceit-info-header">
        <img src="{{ asset('assets/img/ranks/faceit/'.$faceitInfo['games'][config('faceit.game')]['skill_level'].'.svg') }}"
            alt="@t('faceitinfo.level') {{ $faceitInfo['games'][config('faceit.game')]['skill_level'] }}"
            class="faceit-info-level-icon">

        <a href="https://www.faceit.com/en/players/{{ $faceitInfo['nickname'] }}" target="_blank"
            class="faceit-info-nickname">
            {{ $faceitInfo['nickname'] }}
            @if(isset($faceitInfo['country']))
                <span class="faceit-info-country">{{ strtoupper($faceitInfo['country']) }}</span>
            @endif
        </a>
    </div>

    <div class="faceit-info-stats">
        <div class="faceit-info-stat">
            <span
                class="faceit-info-stat-value">{{ $faceitInfo['games'][config('faceit.game')]['faceit_elo'] ?? 'N/A' }}</span>
            <span class="faceit-info-stat-label">@t('faceitinfo.elo')</span>
        </div>

        <div class="faceit-info-stat">
            <span class="faceit-info-stat-value">{{ $playerStats['lifetime']['Average K/D Ratio'] ?? 'N/A' }}</span>
            <span class="faceit-info-stat-label">KD</span>
        </div>

        <div class="faceit-info-stat">
            <span class="faceit-info-stat-value">{{ $playerStats['lifetime']['Average Headshots %'] ?? 'N/A' }}%</span>
            <span class="faceit-info-stat-label">HS</span>
        </div>

        <div class="faceit-info-stat">
            <span class="faceit-info-stat-value">{{ $playerStats['lifetime']['Win Rate %'] ?? 'N/A' }}%</span>
            <span class="faceit-info-stat-label">WIN RATE</span>
        </div>

        <div class="faceit-info-stat">
            <span class="faceit-info-stat-value">{{ $playerStats['lifetime']['Matches'] ?? 'N/A' }}</span>
            <span class="faceit-info-stat-label">MATCHES</span>
        </div>
        <div class="faceit-info-stat">
            <span class="faceit-info-stat-value">{{ $playerStats['lifetime']['Wins'] ?? 'N/A' }}</span>
            <span class="faceit-info-stat-label">WINS</span>
        </div>
    </div>

    @if(isset($playerStats['lifetime']['Recent Results']) && is_array($playerStats['lifetime']['Recent Results']))
        <div class="faceit-info-recent-matches">
            <div class="faceit-info-matches-title">@t('faceitinfo.recent_matches')</div>
            <div class="faceit-info-matches-results">
                @foreach($playerStats['lifetime']['Recent Results'] as $result)
                    <span class="faceit-info-match-result faceit-info-match-{{ $result == '1' ? 'win' : 'loss' }}">
                        {{ $result == '1' ? 'W' : 'L' }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>