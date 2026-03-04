<?php

namespace App\Mcp\Tools;

use App\Models\DonationBox;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Lists my Donation Boxes (the ones belonging to that specific user).')]
class ShowMyDonationBoxes extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        if ($request->user() === null) {
            return Response::error('User not authenticated');
        }

        $all = $request->user()->donationBoxes()->get()->all();

        return Response::json($all);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
