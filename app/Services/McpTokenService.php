<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\NewAccessToken;

class McpTokenService
{
    /**
     * Create a new MCP token for the user.
     */
    public function createToken(User $user, string $name): NewAccessToken
    {
        return $user->createToken($name, ['mcp:access']);
    }

    /**
     * Get all MCP tokens for the user.
     *
     * @return Collection<int, \Laravel\Sanctum\PersonalAccessToken>
     */
    public function getUserTokens(User $user): Collection
    {
        return $user->tokens()
            ->where('name', 'like', '%')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Revoke a specific token for the user.
     */
    public function revokeToken(User $user, int $tokenId): bool
    {
        return $user->tokens()->where('id', $tokenId)->delete() > 0;
    }
}
