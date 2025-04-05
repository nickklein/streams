<?php

namespace NickKlein\Streams\Interfaces;

interface StreamServiceInterface
{
    public function getLimitedProfile(int $userId, int $favourites): array;
    public function getProfileById(int $userId, int $userStreamId): array;
}
