<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Download invoice PDF
     *
     * @group appointments
     * @authenticated
     *
     * @url api/invoices/{invoice}/download
     *
     * @response 200 Binary PDF file
     * @response 404 {"message": "Invoice not found"}
     */
    public function download(Invoice $invoice)
    {
        // Check if user has permission to download this invoice
        $user = auth()->user();

        if ($user->customer && $invoice->appointment->customer_id !== $user->customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->serviceProvider && $invoice->appointment->service_provider_id !== $user->serviceProvider->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $this->invoiceService->downloadInvoice($invoice);
    }
}
