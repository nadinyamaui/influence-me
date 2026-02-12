<?php

namespace App\Services\Facebook;

use App\Enums\DemographicType;
use App\Enums\MediaType;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Carbon\Carbon;
use FacebookAds\Http\Exception\AuthorizationException;
use FacebookAds\Http\Exception\RequestException;

class InstagramGraphService
{
    protected Client $client;

    public function __construct(protected InstagramAccount $account)
    {
        $this->client = app(Client::class, [
            'user_id' => $this->account->instagram_user_id,
            'access_token' => $this->account->access_token,
        ]);
    }

    public function retrieveMedia(): void
    {
        $mediaPosts = $this->client->getAllMedia();
        foreach ($mediaPosts as $media) {
            $this->account->instagramMedia()->updateOrCreate([
                'instagram_media_id' => $media['id'],
            ], [
                'instagram_account_id' => $this->account->id,
                'media_type' => MediaType::parse($media),
                'caption' => $media['caption'] ?? null,
                'permalink' => $media['permalink'] ?? null,
                'media_url' => $media['media_url'] ?? null,
                'thumbnail_url' => $media['thumbnail_url'] ?? null,
                'published_at' => Carbon::parse($media['timestamp']),
                'like_count' => $media['like_count'] ?? 0,
                'comments_count' => $media['comments_count'] ?? 0,
            ]);
        }
    }

    public function syncMediaInsights(): void
    {
        $this->account->instagramMedia()
            ->where('published_at', '>=', now()->subDays(90))
            ->where('media_type', '!=', MediaType::Story->value)
            ->chunkById(50, function ($mediaItems): void {
                $mediaItems->each(function (InstagramMedia $media): void {
                    $insights = $this->client->getMediaInsights($media->instagram_media_id, $media->media_type);
                    $reach = (int) ($insights->get('reach') ?? 0);
                    $saved = (int) ($insights->get('saved') ?? 0);
                    $shares = (int) ($insights->get('shares') ?? 0);
                    $engagementRate = 0;
                    if ($reach > 0) {
                        $engagementRate = (($media->like_count + $media->comments_count + $saved + $shares) / $reach) * 100;
                    }

                    $media->update([
                        'reach' => $reach,
                        'impressions' => $insights->get('views'),
                        'saved_count' => $saved,
                        'shares_count' => $shares,
                        'engagement_rate' => $engagementRate,
                    ]);
                    usleep(100000);
                });
            });
    }

    public function getProfile(): array
    {
        try {
            $profile = $this->client->getProfile();
        } catch (AuthorizationException $exception) {
            throw new InstagramTokenExpiredException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (RequestException $exception) {
            throw new InstagramApiException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return [
            'username' => $profile['username'] ?? $this->account->username,
            'name' => $profile['name'] ?? $this->account->name,
            'biography' => $profile['biography'] ?? $this->account->biography,
            'profile_picture_url' => $profile['profile_picture_url'] ?? $this->account->profile_picture_url,
            'followers_count' => $profile['followers_count'] ?? $this->account->followers_count,
            'following_count' => $profile['following_count'] ?? $this->account->following_count,
            'media_count' => $profile['media_count'] ?? $this->account->media_count,
        ];
    }

    public function syncAudienceDemographics(): void
    {
        if (($this->account->followers_count ?? 0) < 100) {
            return;
        }

        $records = $this->buildAudienceDemographicsRecords($this->client->getAudienceDemographics());
        $recordedAt = now();

        $this->account->audienceDemographics()->delete();

        if ($records === []) {
            return;
        }

        $this->account->audienceDemographics()->createMany(
            collect($records)->map(fn (array $record): array => [
                'type' => $record['type'],
                'dimension' => $record['dimension'],
                'value' => $record['value'],
                'recorded_at' => $recordedAt,
            ])->all()
        );
    }

    private function buildAudienceDemographicsRecords(array $demographics): array
    {
        $records = [];
        $ageTotals = [];
        $genderTotals = [];

        $genderAgeBreakdown = $demographics['audience_gender_age'] ?? [];
        if (is_array($genderAgeBreakdown)) {
            foreach ($genderAgeBreakdown as $dimension => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                $parts = explode('.', (string) $dimension, 2);
                if (count($parts) !== 2) {
                    continue;
                }

                $gender = $this->normalizeGenderDimension($parts[0]);
                $age = trim($parts[1]);
                $metricValue = (float) $value;

                if ($gender !== '') {
                    $genderTotals[$gender] = ($genderTotals[$gender] ?? 0) + $metricValue;
                }

                if ($age !== '') {
                    $ageTotals[$age] = ($ageTotals[$age] ?? 0) + $metricValue;
                }
            }
        }

        $mapping = [
            'age' => DemographicType::Age,
            'audience_age' => DemographicType::Age,
            'gender' => DemographicType::Gender,
            'audience_gender' => DemographicType::Gender,
            'city' => DemographicType::City,
            'audience_city' => DemographicType::City,
            'country' => DemographicType::Country,
            'audience_country' => DemographicType::Country,
        ];

        foreach ($mapping as $key => $type) {
            $breakdown = $demographics[$key] ?? [];
            if (! is_array($breakdown)) {
                continue;
            }

            foreach ($breakdown as $dimension => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                $normalizedDimension = trim((string) $dimension);
                if ($normalizedDimension === '') {
                    continue;
                }

                if ($type === DemographicType::Gender) {
                    $normalizedDimension = $this->normalizeGenderDimension($normalizedDimension);
                }

                if ($normalizedDimension === '') {
                    continue;
                }

                $records[] = [
                    'type' => $type,
                    'dimension' => $normalizedDimension,
                    'value' => round((float) $value, 2),
                ];
            }
        }

        foreach ($ageTotals as $dimension => $value) {
            $records[] = [
                'type' => DemographicType::Age,
                'dimension' => $dimension,
                'value' => round($value, 2),
            ];
        }

        foreach ($genderTotals as $dimension => $value) {
            $records[] = [
                'type' => DemographicType::Gender,
                'dimension' => $dimension,
                'value' => round($value, 2),
            ];
        }

        return $records;
    }

    private function normalizeGenderDimension(string $value): string
    {
        $normalizedValue = strtolower(trim($value));

        return match ($normalizedValue) {
            'm', 'male' => 'Male',
            'f', 'female' => 'Female',
            default => ucfirst($normalizedValue),
        };
    }
}
