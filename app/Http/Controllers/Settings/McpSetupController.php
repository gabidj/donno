<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CreateMcpTokenRequest;
use App\Services\McpTokenService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class McpSetupController extends Controller
{
    public function __construct(
        private McpTokenService $tokenService
    ) {}

    /**
     * Show the MCP setup settings page.
     */
    public function show(): Response
    {
        $user = auth()->user();

        return Inertia::render('settings/McpSetup', [
            'mcpUrl' => url('/mcp/donno'),
            'tokens' => $this->tokenService->getUserTokens($user)->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'created_at' => $token->created_at->toDateTimeString(),
                'last_used_at' => $token->last_used_at?->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Create a new MCP token.
     */
    public function store(CreateMcpTokenRequest $request): RedirectResponse
    {
        $token = $this->tokenService->createToken(
            $request->user(),
            $request->validated('name')
        );

        return back()->with('newToken', $token->plainTextToken);
    }

    /**
     * Revoke an MCP token.
     */
    public function destroy(int $token): RedirectResponse
    {
        $this->tokenService->revokeToken(auth()->user(), $token);

        return back();
    }
}
