<?php

namespace NickKlein\Streams\Interfaces;

interface StreamServiceInterface
{
    public function getProfileIds(int $userId): array;
    public function getProfileById(int $userId, int $userStreamId): array;
}
