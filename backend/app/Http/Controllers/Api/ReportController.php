<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $games = $request->user()->games()
            ->select('id', 'name', 'cover_image', 'status', 'steam_app_id', 'steam_url',
                     'itch_url', 'epic_url', 'gog_url', 'android_url', 'ios_url',
                     'website_url', 'steam_data', 'genre', 'engine', 'release_date')
            ->orderBy('name')
            ->get()
            ->map(function (Game $game) {
                return [
                    'id'           => $game->id,
                    'name'         => $game->name,
                    'cover_image'  => $game->cover_image
                        ?? ($game->steam_data['header_image'] ?? null),
                    'status'       => $game->status,
                    'genre'        => $game->genre,
                    'engine'       => $game->engine,
                    'release_date' => $game->release_date,
                    'platforms'    => $this->availablePlatforms($game),
                ];
            });

        return response()->json($games);
    }

    public function show(Request $request, Game $game): JsonResponse
    {
        abort_if($game->user_id !== $request->user()->id, 403);

        $report = [
            'game'      => $game,
            'platforms' => [],
        ];

        if ($game->steam_app_id) {
            $report['platforms']['steam'] = $this->buildSteamReport($game);
        }

        if ($game->itch_url) {
            $report['platforms']['itchio'] = $this->buildItchReport($game);
        }

        if ($game->epic_url) {
            $report['platforms']['epic'] = [
                'available' => true,
                'url'       => $game->epic_url,
                'name'      => 'Epic Games Store',
                'icon'      => 'epic',
            ];
        }

        if ($game->gog_url) {
            $report['platforms']['gog'] = [
                'available' => true,
                'url'       => $game->gog_url,
                'name'      => 'GOG',
                'icon'      => 'gog',
            ];
        }

        if ($game->android_url) {
            $report['platforms']['android'] = [
                'available' => true,
                'url'       => $game->android_url,
                'name'      => 'Google Play',
                'icon'      => 'android',
            ];
        }

        if ($game->ios_url) {
            $report['platforms']['ios'] = [
                'available' => true,
                'url'       => $game->ios_url,
                'name'      => 'App Store',
                'icon'      => 'ios',
            ];
        }

        if ($game->website_url) {
            $report['platforms']['website'] = [
                'available' => true,
                'url'       => $game->website_url,
                'name'      => 'Website',
                'icon'      => 'website',
            ];
        }

        return response()->json($report);
    }

    // ────────────────────────────────────────────────────────────────────────

    private function availablePlatforms(Game $game): array
    {
        $platforms = [];
        if ($game->steam_url || $game->steam_app_id) $platforms[] = 'steam';
        if ($game->itch_url)    $platforms[] = 'itchio';
        if ($game->epic_url)    $platforms[] = 'epic';
        if ($game->gog_url)     $platforms[] = 'gog';
        if ($game->android_url) $platforms[] = 'android';
        if ($game->ios_url)     $platforms[] = 'ios';
        if ($game->website_url) $platforms[] = 'website';
        return $platforms;
    }

    private function buildSteamReport(Game $game): array
    {
        $appId     = $game->steam_app_id;
        $details   = $game->steam_data ?? $this->fetchSteamDetails($appId);
        $spy       = $this->fetchSteamSpy($appId);
        [$reviews, $countryDistribution] = $this->fetchSteamReviews($appId);
        $news      = $this->fetchSteamNews($appId);

        $reviewSummary = $details['recommendations'] ?? null;
        $totalReviews  = ($reviewSummary['total'] ?? 0);
        $positiveCount = $spy['positive'] ?? 0;
        $negativeCount = $spy['negative'] ?? 0;
        $totalFromSpy  = $positiveCount + $negativeCount;
        $positivePct   = $totalFromSpy > 0
            ? round(($positiveCount / $totalFromSpy) * 100, 1)
            : null;

        return [
            'available'     => true,
            'app_id'        => $appId,
            'url'           => $game->steam_url ?? "https://store.steampowered.com/app/{$appId}",
            'name'          => 'Steam',
            'icon'          => 'steam',

            // Overview
            'header_image'  => $details['header_image'] ?? null,
            'description'   => $details['short_description'] ?? $game->description,
            'release_date'  => $details['release_date']['date'] ?? null,
            'developers'    => $details['developers'] ?? [],
            'publishers'    => $details['publishers'] ?? [],
            'genres'        => collect($details['genres'] ?? [])->pluck('description')->toArray(),
            'categories'    => collect($details['categories'] ?? [])->pluck('description')->take(8)->toArray(),
            'price'         => $details['price_overview'] ?? null,
            'metacritic'    => $details['metacritic'] ?? null,
            'screenshots'   => collect($details['screenshots'] ?? [])->take(6)
                                ->map(fn($s) => $s['path_thumbnail'])->values()->toArray(),

            // Player / ownership stats (SteamSpy)
            'owners'           => $spy['owners'] ?? 'N/A',
            'players_forever'  => $spy['players_forever'] ?? 0,
            'players_2weeks'   => $spy['players_2weeks'] ?? 0,
            'ccu'              => $spy['ccu'] ?? 0,
            'avg_playtime_forever' => $spy['average_forever'] ?? 0,
            'avg_playtime_2weeks'  => $spy['average_2weeks'] ?? 0,
            'tags'             => $spy['tags'] ?? [],
            'languages'        => $spy['languages'] ?? null,

            // Reviews
            'review_score'       => $positivePct,
            'review_total'       => $totalFromSpy ?: $totalReviews,
            'review_positive'    => $positiveCount,
            'review_negative'    => $negativeCount,
            'review_label'       => $this->reviewLabel($positivePct),
            'recent_reviews'     => $reviews,
            'country_distribution' => $countryDistribution,

            // News
            'news'             => $news,
        ];
    }

    private function fetchSteamDetails(string $appId): array
    {
        try {
            $resp = Http::timeout(8)->get('https://store.steampowered.com/api/appdetails', [
                'appids' => $appId,
                'cc'     => 'br',
                'l'      => 'english',
            ]);
            if ($resp->successful()) {
                $data = $resp->json("{$appId}.data");
                return $data ?? [];
            }
        } catch (\Exception) {}
        return [];
    }

    private function fetchSteamSpy(string $appId): array
    {
        try {
            $resp = Http::timeout(8)->get('https://steamspy.com/api.php', [
                'request' => 'appdetails',
                'appid'   => $appId,
            ]);
            if ($resp->successful()) return $resp->json() ?? [];
        } catch (\Exception) {}
        return [];
    }

    private function fetchSteamReviews(string $appId): array
    {
        // Map Steam language codes → country name + flag
        $langToCountry = [
            'english'              => ['label' => 'Estados Unidos', 'flag' => '🇺🇸'],
            'schinese'             => ['label' => 'China',          'flag' => '🇨🇳'],
            'tchinese'             => ['label' => 'Taiwan',         'flag' => '🇹🇼'],
            'russian'              => ['label' => 'Rússia',         'flag' => '🇷🇺'],
            'brazilian'            => ['label' => 'Brasil',         'flag' => '🇧🇷'],
            'portuguese'           => ['label' => 'Portugal',       'flag' => '🇵🇹'],
            'german'               => ['label' => 'Alemanha',       'flag' => '🇩🇪'],
            'french'               => ['label' => 'França',         'flag' => '🇫🇷'],
            'spanish'              => ['label' => 'Espanha',        'flag' => '🇪🇸'],
            'latam'                => ['label' => 'América Latina', 'flag' => '🌎'],
            'japanese'             => ['label' => 'Japão',          'flag' => '🇯🇵'],
            'koreana'              => ['label' => 'Coreia do Sul',  'flag' => '🇰🇷'],
            'turkish'              => ['label' => 'Turquia',        'flag' => '🇹🇷'],
            'polish'               => ['label' => 'Polónia',        'flag' => '🇵🇱'],
            'italian'              => ['label' => 'Itália',         'flag' => '🇮🇹'],
            'dutch'                => ['label' => 'Países Baixos',  'flag' => '🇳🇱'],
            'hungarian'            => ['label' => 'Hungria',        'flag' => '🇭🇺'],
            'czech'                => ['label' => 'República Checa','flag' => '🇨🇿'],
            'romanian'             => ['label' => 'Roménia',        'flag' => '🇷🇴'],
            'thai'                 => ['label' => 'Tailândia',      'flag' => '🇹🇭'],
            'ukrainian'            => ['label' => 'Ucrânia',        'flag' => '🇺🇦'],
        ];

        try {
            $resp = Http::timeout(10)->get("https://store.steampowered.com/appreviews/{$appId}", [
                'json'          => 1,
                'language'      => 'all',
                'purchase_type' => 'all',
                'num_per_page'  => 100,
                'filter'        => 'recent',
            ]);

            if ($resp->successful()) {
                $rawReviews = $resp->json('reviews') ?? [];

                // Build recent reviews list (first 20)
                $reviews = collect($rawReviews)->take(20)->map(fn($r) => [
                    'id'               => $r['recommendationid'] ?? null,
                    'author'           => $r['author']['steamid'] ?? 'Unknown',
                    'language'         => $r['language'] ?? 'unknown',
                    'review'           => mb_substr($r['review'] ?? '', 0, 300),
                    'voted_up'         => $r['voted_up'] ?? false,
                    'votes_up'         => $r['votes_up'] ?? 0,
                    'playtime_hours'   => isset($r['author']['playtime_forever'])
                        ? round($r['author']['playtime_forever'] / 60, 1) : 0,
                    'timestamp'        => $r['timestamp_created'] ?? null,
                    'written_during_early_access' => $r['written_during_early_access'] ?? false,
                ])->values()->toArray();

                // Build country distribution from all 100 reviews
                $langCounts = collect($rawReviews)
                    ->groupBy(fn($r) => $r['language'] ?? 'unknown')
                    ->map(fn($g) => $g->count())
                    ->sortDesc();

                $total = $langCounts->sum();
                $distribution = [];
                $otherPct = 0;

                foreach ($langCounts as $lang => $count) {
                    $pct = $total > 0 ? round($count / $total * 100, 1) : 0;
                    if (isset($langToCountry[$lang])) {
                        $distribution[] = [
                            'label'   => $langToCountry[$lang]['label'],
                            'flag'    => $langToCountry[$lang]['flag'],
                            'count'   => $count,
                            'percent' => $pct,
                        ];
                    } else {
                        $otherPct += $pct;
                    }
                }

                if ($otherPct > 0) {
                    $distribution[] = [
                        'label'   => 'Outros',
                        'flag'    => '🌍',
                        'count'   => 0,
                        'percent' => round($otherPct, 1),
                    ];
                }

                return [$reviews, $distribution];
            }
        } catch (\Exception) {}
        return [[], []];
    }

    private function fetchSteamNews(string $appId): array
    {
        try {
            $resp = Http::timeout(8)->get('https://api.steampowered.com/ISteamNews/GetNewsForApp/v2/', [
                'appid'    => $appId,
                'count'    => 5,
                'maxlength'=> 300,
                'format'   => 'json',
            ]);
            if ($resp->successful()) {
                return collect($resp->json('appnews.newsitems') ?? [])
                    ->map(fn($n) => [
                        'title' => $n['title'] ?? '',
                        'url'   => $n['url'] ?? '',
                        'date'  => $n['date'] ?? null,
                        'feed'  => $n['feedlabel'] ?? '',
                    ])->toArray();
            }
        } catch (\Exception) {}
        return [];
    }

    private function buildItchReport(Game $game): array
    {
        return [
            'available'   => true,
            'url'         => $game->itch_url,
            'name'        => 'itch.io',
            'icon'        => 'itchio',
            'description' => $game->description,
            'note'        => 'O itch.io não possui API pública de analytics. Clica no botão para ver a página do jogo.',
        ];
    }

    private function reviewLabel(?float $pct): string
    {
        if ($pct === null) return 'Sem avaliações';
        if ($pct >= 95)    return 'Extremamente Positivo';
        if ($pct >= 80)    return 'Muito Positivo';
        if ($pct >= 70)    return 'Principalmente Positivo';
        if ($pct >= 40)    return 'Misto';
        if ($pct >= 20)    return 'Principalmente Negativo';
        return 'Negativo';
    }
}
