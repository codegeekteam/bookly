<?php

namespace App\Services;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Http\Resources\GiftCardCollection;
use App\Http\Resources\GiftCardResource;
use App\Http\Resources\GiftCardThemeResource;
use App\Models\GiftCard;
use App\Models\GiftCardTheme;
use App\Models\User;
use App\Models\Enums\TransactionType;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiftCardService
{
    /**
     * @throws Exception
     */
    public function index(User $user): GiftCardCollection
    {
        $giftCards = $user->giftCards()->with('user', 'theme', 'usedBy')->orderByDesc('created_at')->paginate(10);

        return new GiftCardCollection($giftCards);
    }

    public function show(User $user, $id): GiftCardResource
    {
        if (!$user->customer) {
            throw new Exception(__('no data found'));
        }

        $giftCard = GiftCard::where('id', $id)->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('used_by', $user->customer->id)
                ->orWhere('recipient_phone_number', $user->customer->phone_number);
        })
            ->first();
        if (!$giftCard) {
            throw new Exception(__('no data found'));
        }
        return new GiftCardResource($giftCard);
    }

    public function create(User $user, $gift_card_theme_id, $recipient_name, $recipient_phone_number,$amount): GiftCardResource
    {
        $giftCard = $user->giftCards()->create([
            'code' => $this->generateRandomString($user),
            'gift_card_theme_id' => $gift_card_theme_id,
            'amount' => $amount,
            'recipient_name' => $recipient_name,
            'recipient_phone_number' => $recipient_phone_number,
            'is_used' => false,
            'payment_status' => 'unpaid',
        ]);

        return new GiftCardResource($giftCard);
    }

    private function generateRandomString($user)
    {
        do {
            $random = Str::random(6);
            $code = 'Bookly-' . $random . '' . $user->id;
        } while (GiftCard::where('code', $code)->exists());
        return $code;
    }

    public function usedGiftCards(User $user): GiftCardCollection
    {
        if ($user->customer) {
            $giftCards = $user->customer->usedGiftCards()->with('user', 'theme', 'usedBy')->orderByDesc('created_at')->paginate(10);

            return new GiftCardCollection($giftCards);
        } else {
            throw new Exception(__('user should be customer'));
        }

    }

    public function receivedGiftCards(User $user): GiftCardCollection
    {
        if ($user->customer) {
            $giftCards = GiftCard::where('recipient_phone_number', $user->customer->phone_number)
                ->where('payment_status', 'paid')
                ->with('user', 'theme', 'usedBy')
                ->orderByDesc('created_at')
                ->paginate(10);

            return new GiftCardCollection($giftCards);
        } else {
            throw new Exception(__('user should be customer'));
        }

    }

    public function giftCardThemes()
    {
        $themes = GiftCardTheme::where('active',1)->get();
        return GiftCardThemeResource::collection($themes);

    }

    public function redeem(User $user, $code)
    {
        try {
            //check auth user is customer
            if (!$user->customer) {
                throw new Exception(__('user should be customer'));
            }
            $giftCard = GiftCard::where('code', $code)->where('payment_status', 'paid')->first();
            //check code
            if (!$giftCard) {
                throw new Exception(__('invalid code'));
            }
            //check recipient phone
            if ($giftCard->recipient_phone_number != $user->customer->phone_number) {
                throw new Exception(__('you dont receive this code'));
            }
            //check code usage
            if ($giftCard->is_used == 1) {
                throw new Exception(__('you redeemed this code before'));
            }
            //success check
            DB::beginTransaction();
            //increment customer wallet
            $this->addAmountToCustomerWallet($user, $giftCard);
            //update gift card data
            $giftCard->update(['is_used' => 1, 'used_by' => $user->customer->id, 'used_at' => Carbon::now()]);
            DB::commit();
            return $giftCard;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

    }

    private function addAmountToCustomerWallet($user, $giftCard)
    {
        //add total to provider wallet
        (new CreateWalletTransactionMutation())
            ->handle(
                $user->wallet,
                $giftCard->amount,
                TransactionType::IN,
                "redeem gift card : $giftCard->code amount : $giftCard->amount ",
                false,
                "استرداد بطاقة الهدايا: $giftCard->code، المبلغ: $giftCard->amount"
            );
    }
}
