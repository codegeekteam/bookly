<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Elibyy\TCPDF\Facades\TCPDF;

class InvoiceService
{
    const VAT_PERCENTAGE = 0.15;

    /**
     * Generate invoice for a paid appointment
     */
    public function generateInvoice(Appointment $appointment): Invoice
    {
        // Check if invoice already exists
        if ($appointment->invoice) {
            return $appointment->invoice;
        }

        // Get customer information
        $customer = $appointment->customer;
        $customerName = trim($customer->first_name . ' ' . $customer->last_name);

        // Prepare invoice items
        $items = $this->prepareInvoiceItems($appointment);

        // Calculate totals
        $subtotal = $this->calculateSubtotal($items);
        $vatAmount = round($subtotal * self::VAT_PERCENTAGE, 2);
        $totalAmount = round($subtotal + $vatAmount, 2);

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Company details for Bookly Saudi Arabia
        $companyDetails = $this->getCompanyDetails();

        // Create invoice record
        $invoice = Invoice::create([
            'appointment_id' => $appointment->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => Carbon::now(),
            'customer_name' => $customerName,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone_number,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'items' => $items,
            'company_details' => $companyDetails,
        ]);

        // Generate PDF
        $pdfPath = $this->generatePdf($invoice, $appointment);

        // Update invoice with PDF path
        $invoice->update(['pdf_path' => $pdfPath]);

        return $invoice;
    }

    /**
     * Prepare invoice items from appointment services
     */
    protected function prepareInvoiceItems(Appointment $appointment): array
    {
        $items = [];

        foreach ($appointment->appointmentServices as $service) {
            $attachedService = $appointment->serviceProvider
                ->attachedServices()
                ->where('service_id', $service->service_id)
                ->first();

            if ($attachedService) {
                $serviceName = $attachedService->service->title;
                if (is_array($serviceName) || is_object($serviceName)) {
                    $serviceName = $serviceName['en'] ?? 'Service';
                }

                $quantity = $service->number_of_beneficiaries ?? 1;
                $unitPrice = $attachedService->price;
                $subtotalExclVat = $unitPrice * $quantity;
                $vatAmount = round($subtotalExclVat * self::VAT_PERCENTAGE, 2);
                $totalInclVat = $subtotalExclVat + $vatAmount;

                $items[] = [
                    'title' => $serviceName,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'subtotal_excl_vat' => $subtotalExclVat,
                    'vat_amount' => $vatAmount,
                    'total_incl_vat' => $totalInclVat,
                    'subtotal' => $subtotalExclVat
                ];
            }
        }

        return $items;
    }

    /**
     * Calculate subtotal from items
     */
    protected function calculateSubtotal(array $items): float
    {
        return array_sum(array_column($items, 'subtotal'));
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');

        // Get last invoice number for this month
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        return "INV-{$year}{$month}-{$nextNumber}";
    }

    /**
     * Get company details for Bookly Saudi Arabia
     */
    protected function getCompanyDetails(): array
    {
        return [
            'name' => 'Bookly Saudi Arabia',
            'name_ar' => 'شركة احجز لي لخدمات الأعمال',
            'address' => 'شارع الأقطار 5369، حي الملقا 8125، الرياض 13525',
            'cr_number' => '1010910375',
            'vat_number' => '310230004991623',
        ];
    }

    /**
     * Generate PDF for invoice
     */
    protected function generatePdf(Invoice $invoice, Appointment $appointment): string
    {
        $html = view('invoices.pdf-ar', [
            'invoice' => $invoice,
            'appointment' => $appointment,
            'vatPercentage' => self::VAT_PERCENTAGE * 100,
        ])->render();

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('Bookly');
        $pdf->SetAuthor('Bookly');
        $pdf->SetTitle('Invoice ' . $invoice->invoice_number);

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->setRTL(true);

        $pdf->SetFont('dejavusans', '', 10);

        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');

        $fileName = 'invoice_' . $invoice->invoice_number . '.pdf';
        $filePath = 'invoices/' . Carbon::now()->format('Y/m') . '/' . $fileName;

        $fullPath = storage_path('app/public/' . $filePath);
        $directory = dirname($fullPath);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->Output($fullPath, 'F');

        return $filePath;
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            // Regenerate PDF if not exists
            $appointment = $invoice->appointment;
            $pdfPath = $this->generatePdf($invoice, $appointment);
            $invoice->update(['pdf_path' => $pdfPath]);
        }

        return Storage::disk('public')->download(
            $invoice->pdf_path,
            'invoice_' . $invoice->invoice_number . '.pdf'
        );
    }
}