<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
use App\Application\Services\PurchaseService;
use App\Http\Requests\Purchase\AddressUpdateRequest;
use App\Http\Requests\Purchase\PurchaseRequest;
use App\Domain\Purchase\ValueObjects\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class PurchaseController extends Controller
{
    private ItemService $itemService;
    private ProfileService $profileService;
    private PurchaseService $purchaseService;
    private AuthenticationService $authService;

    public function __construct(
        ItemService $itemService,
        ProfileService $profileService,
        PurchaseService $purchaseService,
        AuthenticationService $authService
    ) {
        $this->itemService = $itemService;
        $this->profileService = $profileService;
        $this->purchaseService = $purchaseService;
        $this->authService = $authService;
    }

    public function procedure(string $id): View
    {
        $item = $this->itemService->getItem($id);
        $paymentMethods = PaymentMethod::getOptions();
        $userId = $this->authService->requireAuthentication();
        $profileEntity = $this->profileService->getProfile($userId);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.procedure', compact('item', 'paymentMethods', 'profile'));
    }

    public function editAddress(string $itemId): View
    {
        $userId = $this->authService->requireAuthentication();
        $profileEntity = $this->profileService->getProfile($userId);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.address', compact('itemId', 'profile'));
    }

    public function purchase(PurchaseRequest $request, string $itemId): RedirectResponse
    {
        try {
            // バリデーション
            $validatedData = $request->validated();

            $this->purchaseService->purchase(
                $itemId,
                $validatedData['payment_method'],
                $validatedData['postcode'],
                $validatedData['address'],
                $validatedData['buildingName']
            );

            return redirect()->route('items.detail', $itemId)->with('success', '購入が完了しました');
        } catch (\Exception $e) {
            return redirect()->route('items.detail', $itemId)->with('error', $e->getMessage());
        }
    }
}
