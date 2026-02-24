<?php

namespace App\Services;

use App\Models\DonationBox;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DonationBoxService
{
    /**
     * Create a new donation box for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): DonationBox
    {
        $donationBox = new DonationBox($data);
        $donationBox->user()->associate($user);
        $donationBox->save();

        return $donationBox;
    }

    /**
     * Update an existing donation box.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(DonationBox $donationBox, array $data): DonationBox
    {
        $donationBox->update($data);

        return $donationBox->fresh();
    }

    /**
     * Delete a donation box.
     */
    public function delete(DonationBox $donationBox): bool
    {
        return $donationBox->delete();
    }

    /**
     * List all donation boxes for a user.
     *
     * @return Collection<int, DonationBox>
     */
    public function listForUser(User $user): Collection
    {
        return DonationBox::query()
            ->forUser($user)
            ->latest()
            ->get();
    }

    /**
     * Find a public donation box by ID.
     */
    public function findPublic(int $id): ?DonationBox
    {
        return DonationBox::query()
            ->public()
            ->find($id);
    }
}
