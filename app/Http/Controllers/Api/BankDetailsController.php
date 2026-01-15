<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddBankDetailsRequest;
use App\Http\Requests\UpdateBankDetailsRequest;
use App\Http\Resources\BankDetailsResource;
use App\Models\BankDetails;
use App\Services\BankDetailsService;
use Exception;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
#[Group('bank-details', 'APIs for manage bank details')]
class BankDetailsController extends Controller
{
    private $BankDetailsService;

    public function __construct(BankDetailsService $BankDetailsService)
    {
        $this->BankDetailsService = $BankDetailsService;
    }

    #[Endpoint('get-bank-details')]
    #[Authenticated]
    #[ResponseFromApiResource(BankDetailsResource::class, BankDetails::class, collection: true)]
    public function index(Request $request)
    {
        $data = $this->BankDetailsService->index($request);

        return BankDetailsResource::collection($data);
    }


    #[Endpoint('add-bank-details')]
    #[Authenticated]
    #[ResponseFromApiResource(BankDetailsResource::class, BankDetails::class, 200)]
    public function create(AddBankDetailsRequest $request)
    {
        try {
            $details = $this->BankDetailsService->create($request);

            return response()->json(
                BankDetailsResource::make($details), 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    #[Endpoint('update-bank-details')]
    #[Authenticated]
    #[ResponseFromApiResource(BankDetailsResource::class, BankDetails::class, 200)]
    public function update(UpdateBankDetailsRequest $request)
    {
        try {
            $details = $this->BankDetailsService->update($request);

            return response()->json(
                BankDetailsResource::make($details), 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    #[Endpoint('delete-bank-details')]
    #[Authenticated]
    #[Response('{"message": "bank details deleted successfully"}', 200)]
    public function destroy($id)
    {
        try {
            $this->BankDetailsService->delete($id);

            return response()->json(['message' => __('bank details deleted successfully')], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

}
