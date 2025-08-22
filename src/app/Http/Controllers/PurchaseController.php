<?php

namespace App\Http\Controllers;

use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
use App\Application\Services\PurchaseService;
use App\Domain\Purchase\ValueObjects\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class PurchaseController extends Controller
{
    private ItemService $itemService;
    private ProfileService $profileService;
    private PurchaseService $purchaseService;

    public function __construct(
        ItemService $itemService,
        ProfileService $profileService,
        PurchaseService $purchaseService
    ) {
        $this->itemService = $itemService;
        $this->profileService = $profileService;
        $this->purchaseService = $purchaseService;
    }

    public function procedure(string $id): View
    {
        $item = $this->itemService->getItem($id);
        $paymentMethods = PaymentMethod::getOptions();
        $profileEntity = $this->profileService->getProfile(Auth::user()->id);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.procedure', compact('item', 'paymentMethods', 'profile'));
    }

    public function editAddress(string $itemId): View
    {
        $profileEntity = $this->profileService->getProfile(Auth::user()->id);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.address', compact('itemId', 'profile'));
    }

    public function modifyAddress(Request $request, string $itemId): RedirectResponse
    {
        $this->profileService->updateProfile(
            Auth::user()->id,
            null,
            $request->postcode,
            $request->address,
            $request->buildingName
        );

        return redirect()->route('purchase.procedure', $itemId)->with('success', '住所を更新しました');
    }

    public function purchase(Request $request, string $itemId): RedirectResponse
    {
        try {
            $this->purchaseService->purchase(
                $itemId,
                $request->payment_method,
                $request->postcode,
                $request->address,
                $request->buildingName
            );

            return redirect()->route('items.detail', $itemId)->with('success', '購入が完了しました');
        } catch (\Exception $e) {
            return redirect()->route('items.detail', $itemId)->with('error', $e->getMessage());
        }
    }
}
