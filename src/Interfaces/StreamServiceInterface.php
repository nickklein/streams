<?php

namespace NickKlein\Streams\Interfaces;

interface StreamServiceInterface
{
    public function getUsersGroupedStreamerHandles(int $userId, int $favourites): array;
    public function getProfileById(int $userId, int $userStreamId): array;
}
