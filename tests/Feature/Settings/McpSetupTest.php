<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('mcp setup page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('mcp-setup.show'));

    $response->assertOk();
});

test('mcp setup page contains the mcp url', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('mcp-setup.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/McpSetup')
        ->has('mcpUrl')
        ->where('mcpUrl', url('/mcp/donno'))
    );
});

test('mcp setup page requires authentication', function () {
    $response = $this->get(route('mcp-setup.show'));

    $response->assertRedirect(route('login'));
});

test('mcp setup page requires email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('mcp-setup.show'));

    $response->assertRedirect(route('verification.notice'));
});
