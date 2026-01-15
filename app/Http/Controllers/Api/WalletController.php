<?php

namespace App\Http\Controllers\Api;

use App\Actions\Wallet\Mutations\CashoutRequestMutation;
use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Actions\Wallet\Queries\GetWalletQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletCashoutRequest;
use App\Http\Resources\CashoutResource;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\CashoutRequest;
use App\Models\Enums\TransactionType;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * If the user is a customer, this will return the customer wallet.
     * If the user is a service provider, this will return the payout result.
     *
     * @group Wallet
     *
     * @response 200 {
     *     "data": [{
     *         "id": 1,
     *         "balance": 0,
     *         "user_id": 1,
     *         "transactions": [
     *             {
     *                 "id": 1,
     *                 "wallet_id": 1,
     *                 "amount": 100,
     *                 "type": "in",
     *                 "description": "Add balance",
     *                 "created_at": "2021-09-29T12:00:00.000000Z"
     *             }
     *         ]
     *     }]
     * }
     * @response 200 {
     *     "data": [{
     *         "id": 1,
     *         "available_balance": 0,
     *         "pending_balance": 0,
     *        "user_id": 1,
     *         "transactions": [
     *             {
     *                 "id": 1,
     *                 "amount": 100,
     *                 "type": "out",
     *                 "wallet_id": 1,
     *                 "description": "Payout request",
     *                 "created_at": "2021-09-29T12:00:00.000000Z"
     *             }
     *         ]
     *     }]
     * }
     * @response 422 {
     *    "message": "The given data was invalid.",
     *   "errors": {
     *      "amount": [
     *        "The amount must be at least 1."
     *     ]
     *  }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function index(): WalletResource|PayoutResource
    {
        return (new GetWalletQuery())->handle();
    }

    /**
     * @group Wallet
     */
    public function walletTransactions()
    {
        $transactions=auth()->user()->wallet->transactions()->orderByDesc('id')->paginate(10);
        return response()->json(WalletTransactionResource::collection($transactions)->response()->getData(true));
    }




    /**
     * @group Wallet
     */
    public function addBalance(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        (new CreateWalletTransactionMutation())->handle(
            auth()->user()->wallet,
            $request->get('amount'),
            TransactionType::IN,
            'Add balance'
        );

        return response()->json([
            'message' => 'Balance added successfully',
        ]);
    }

    /**
     * @group Wallet
     *
     * @response 200 {
     *    "message": "Payout request sent successfully"
     * }
     */
    public function cashout(WalletCashoutRequest $request): \Illuminate\Http\JsonResponse
    {
        (new CashoutRequestMutation())->handle($request);

        return response()->json([
            'message' => __('Payout request sent successfully'),
        ]);
    }

    /**
     * @group Wallet
     *
     * @response 200 {
     *   "data": [{
     *      "id": 1,
     *      "amount": 100,
     *      "status": "pending",
     *      "created_at": "2021-09-29T12:00:00.000000Z"
     * }]
     */
    public function getCashouts()
    {
        $cash_outs = CashoutRequest::where('user_id', auth()->id())->get();

        return CashoutResource::collection($cash_outs);
    }

    /**
     * @group Wallet
     *
     * @response 200 {
     *  "data": {
     *   "id": 1,
     *   "amount": 100,
     *   "status": "pending",
     *   "created_at": "2021-09-29T12:00:00.000000Z"
     * }
     */
    public function showCashOut(CashoutRequest $cashout)
    {
        return new CashoutResource($cashout);
    }
}
