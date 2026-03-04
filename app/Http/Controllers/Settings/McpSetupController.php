<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class McpSetupController extends Controller
{
    /**
     * Show the MCP setup settings page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/McpSetup', [
            'mcpUrl' => url('/mcp/donno'),
        ]);
    }
}
