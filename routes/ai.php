<?php

use App\Mcp\Servers\AiDonationBoxServer;
use App\Mcp\Servers\WeatherServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/donno', AiDonationBoxServer::class)
    ->middleware('auth:sanctum');
Mcp::web('/mcp/weather', WeatherServer::class);
