<?php

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use App\Models\DonationBox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access donation boxes index', function () {
    $response = $this->get(route('donation-boxes.index'));

    $response->assertRedirect(route('login'));
});

test('guests cannot access donation box create page', function () {
    $response = $this->get(route('donation-boxes.create'));

    $response->assertRedirect(route('login'));
});

test('users can view their donation boxes', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('donation-boxes.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('donation-boxes/Index')
            ->has('donationBoxes', 1)
    );
});

test('users can view donation box create page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('donation-boxes.create'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('donation-boxes/Create')
            ->has('visibilities')
            ->has('statuses')
    );
});

test('users can create a donation box', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('donation-boxes.store'), [
            'title' => 'Help My Family',
            'purpose' => 'We need support for medical expenses.',
            'target_amount' => 5000,
            'visibility' => DonationBoxVisibility::Public->value,
            'status' => DonationBoxStatus::Open->value,
        ]);

    $response->assertSessionHasNoErrors();

    $donationBox = DonationBox::first();
    expect($donationBox)->not->toBeNull();
    expect($donationBox->title)->toBe('Help My Family');
    expect($donationBox->purpose)->toBe('We need support for medical expenses.');
    expect($donationBox->target_amount)->toBe('5000.00');
    expect($donationBox->visibility)->toBe(DonationBoxVisibility::Public);
    expect($donationBox->status)->toBe(DonationBoxStatus::Open);
    expect($donationBox->user_id)->toBe($user->id);

    $response->assertRedirect(route('donation-boxes.show', $donationBox));
});

test('users can create an open-ended donation box', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('donation-boxes.store'), [
            'title' => 'Open Ended Campaign',
            'purpose' => 'Raising funds with no specific target.',
            'target_amount' => null,
            'visibility' => DonationBoxVisibility::Public->value,
            'status' => DonationBoxStatus::Open->value,
        ]);

    $response->assertSessionHasNoErrors();

    $donationBox = DonationBox::first();
    expect($donationBox->target_amount)->toBeNull();
});

test('users can view a donation box', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('donation-boxes.show', $donationBox));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('donation-boxes/Show')
            ->has('donationBox')
            ->where('canEdit', true)
    );
});

test('users can view edit page for their donation box', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('donation-boxes.edit', $donationBox));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('donation-boxes/Edit')
            ->has('donationBox')
            ->has('visibilities')
            ->has('statuses')
    );
});

test('users cannot view edit page for another users donation box', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('donation-boxes.edit', $donationBox));

    $response->assertForbidden();
});

test('users can update their donation box', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('donation-boxes.update', $donationBox), [
            'title' => 'Updated Title',
            'purpose' => 'Updated purpose description.',
            'visibility' => DonationBoxVisibility::Private->value,
            'status' => DonationBoxStatus::Closed->value,
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('donation-boxes.show', $donationBox));

    $donationBox->refresh();
    expect($donationBox->title)->toBe('Updated Title');
    expect($donationBox->purpose)->toBe('Updated purpose description.');
    expect($donationBox->visibility)->toBe(DonationBoxVisibility::Private);
    expect($donationBox->status)->toBe(DonationBoxStatus::Closed);
});

test('users cannot update another users donation box', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($user)
        ->patch(route('donation-boxes.update', $donationBox), [
            'title' => 'Hacked Title',
        ]);

    $response->assertForbidden();
});

test('users can delete their donation box', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('donation-boxes.destroy', $donationBox));

    $response->assertRedirect(route('donation-boxes.index'));
    expect(DonationBox::find($donationBox->id))->toBeNull();
});

test('users cannot delete another users donation box', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $donationBox = DonationBox::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('donation-boxes.destroy', $donationBox));

    $response->assertForbidden();
    expect(DonationBox::find($donationBox->id))->not->toBeNull();
});

test('users can close a donation box', function () {
    $user = User::factory()->create();
    $donationBox = DonationBox::factory()->create([
        'user_id' => $user->id,
        'status' => DonationBoxStatus::Open,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('donation-boxes.update', $donationBox), [
            'status' => DonationBoxStatus::Closed->value,
        ]);

    $response->assertSessionHasNoErrors();

    $donationBox->refresh();
    expect($donationBox->status)->toBe(DonationBoxStatus::Closed);
});

test('donation box requires title', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('donation-boxes.store'), [
            'purpose' => 'Some purpose.',
            'visibility' => DonationBoxVisibility::Public->value,
            'status' => DonationBoxStatus::Open->value,
        ]);

    $response->assertSessionHasErrors('title');
});

test('donation box requires purpose', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('donation-boxes.store'), [
            'title' => 'Some title',
            'visibility' => DonationBoxVisibility::Public->value,
            'status' => DonationBoxStatus::Open->value,
        ]);

    $response->assertSessionHasErrors('purpose');
});
