<?php

namespace App\Http\Controllers;

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use App\Http\Requests\StoreDonationBoxRequest;
use App\Http\Requests\UpdateDonationBoxRequest;
use App\Models\DonationBox;
use App\Services\DonationBoxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationBoxController extends Controller
{
    public function __construct(
        private DonationBoxService $service
    ) {}

    /**
     * Display a listing of the user's donation boxes.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('donation-boxes/Index', [
            'donationBoxes' => $this->service->listForUser($request->user()),
        ]);
    }

    /**
     * Show the form for creating a new donation box.
     */
    public function create(): Response
    {
        return Inertia::render('donation-boxes/Create', [
            'visibilities' => array_map(
                fn (DonationBoxVisibility $v) => ['value' => $v->value, 'label' => ucfirst($v->value)],
                DonationBoxVisibility::cases()
            ),
            'statuses' => array_map(
                fn (DonationBoxStatus $s) => ['value' => $s->value, 'label' => ucfirst($s->value)],
                DonationBoxStatus::cases()
            ),
        ]);
    }

    /**
     * Store a newly created donation box.
     */
    public function store(StoreDonationBoxRequest $request): RedirectResponse
    {
        $donationBox = $this->service->create($request->user(), $request->validated());

        return to_route('donation-boxes.show', $donationBox);
    }

    /**
     * Display the specified donation box.
     */
    public function show(DonationBox $donationBox): Response
    {
        $donationBox->load('user');

        return Inertia::render('donation-boxes/Show', [
            'donationBox' => $donationBox,
            'canEdit' => request()->user()?->id === $donationBox->user_id,
        ]);
    }

    /**
     * Show the form for editing the specified donation box.
     */
    public function edit(DonationBox $donationBox): Response
    {
        if (request()->user()->id !== $donationBox->user_id) {
            abort(403);
        }

        return Inertia::render('donation-boxes/Edit', [
            'donationBox' => $donationBox,
            'visibilities' => array_map(
                fn (DonationBoxVisibility $v) => ['value' => $v->value, 'label' => ucfirst($v->value)],
                DonationBoxVisibility::cases()
            ),
            'statuses' => array_map(
                fn (DonationBoxStatus $s) => ['value' => $s->value, 'label' => ucfirst($s->value)],
                DonationBoxStatus::cases()
            ),
        ]);
    }

    /**
     * Update the specified donation box.
     */
    public function update(UpdateDonationBoxRequest $request, DonationBox $donationBox): RedirectResponse
    {
        $this->service->update($donationBox, $request->validated());

        return to_route('donation-boxes.show', $donationBox);
    }

    /**
     * Remove the specified donation box.
     */
    public function destroy(DonationBox $donationBox): RedirectResponse
    {
        if (request()->user()->id !== $donationBox->user_id) {
            abort(403);
        }

        $this->service->delete($donationBox);

        return to_route('donation-boxes.index');
    }
}
