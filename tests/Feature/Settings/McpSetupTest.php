<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('mcp setup page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('mcp-setup.show'));

    $response->assertOk();
});

test('mcp setup page contains the mcp url and tokens', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('mcp-setup.show'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/McpSetup')
        ->has('mcpUrl')
        ->has('tokens')
        ->where('mcpUrl', url('/mcp/donno'))
    );
});

test('mcp setup page requires authentication', function () {
    $response = $this->get(route('mcp-setup.show'));

    $response->assertRedirect(route('login'));
});

test('user can create a new mcp token', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('mcp-setup.store'), [
            'name' => 'Test Token',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('newToken');

    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('Test Token');
});

test('token name is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('mcp-setup.store'), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors('name');
});

test('user can revoke their mcp token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');

    expect($user->tokens()->count())->toBe(1);

    $response = $this
        ->actingAs($user)
        ->delete(route('mcp-setup.destroy', $token->accessToken->id));

    $response->assertRedirect();
    expect($user->tokens()->count())->toBe(0);
});

test('mcp endpoint requires authentication', function () {
    $response = $this->postJson('/mcp/donno');

    $response->assertUnauthorized();
});

test('mcp endpoint works with valid sanctum token', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['mcp:access']);

    $response = $this->postJson('/mcp/donno', [
        'jsonrpc' => '2.0',
        'method' => 'initialize',
        'id' => 1,
    ]);

    $response->assertOk();
});
