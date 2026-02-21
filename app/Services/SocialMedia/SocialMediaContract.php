<?php

namespace App\Services\SocialMedia;

interface SocialMediaContract
{
    public function retrieveMedia(): void;

    public function syncMediaInsights(): void;

    public function syncStories(): void;

    public function getProfile(): array;

    public function refreshLongLivedToken(): string;

    public function syncAudienceDemographics(): void;
}
