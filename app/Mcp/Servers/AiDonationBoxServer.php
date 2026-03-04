<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ShowAllDonationBoxes;
use App\Mcp\Tools\ShowMyDonationBoxes;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Ai Donation Box Server')]
#[Version('0.0.1')]
#[Instructions('Tools to use when donation box operations are mentioned / requested.')]
class AiDonationBoxServer extends Server
{
    protected array $tools = [
        //
        'ListAllDonationBoxes' => ShowAllDonationBoxes::class,
        'GetMyDonationBoxes' => ShowMyDonationBoxes::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
