
<?php

namespace Modules\HCMIntegration\Utils;

use App\Transaction;
use Modules\HCMIntegration\Entities\HcmTenantConfig;
use Carbon\Carbon;

class InvoiceMapper
{
    /**
     * Map POS transaction to HCM invoice format
     */
    public static function mapTransaction(Transaction $transaction, HcmTenantConfig $config)
    {
        $contact = $transaction->contact;
        $location = $transaction->location;
        
        // Calculate totals
        $totalInvoice = $transaction->final_total;
        $totalTax = $transaction->tax_amount;
        $totalDiscount = $transaction->discount_amount;
        
        // Payment breakdown
        $paidByCash = 0;
        $paidByCard = 0;
        $cardBank = '';
        $cardCategory = '';
        $cardType = '';
        $hcmLoyalty = 0;
        $otherPayments = 0;
        
        foreach ($transaction->payment_lines as $payment) {
            if ($payment->method == 'cash') {
                $paidByCash += $payment->amount;
            } elseif ($payment->method == 'card') {
                $paidByCard += $payment->amount;
                $cardBank = $payment->card_number ?? '';
                $cardType = $payment->card_type ?? 'VISA';
                $cardCategory = 'Credit';
            } elseif ($payment->method == 'custom_pay_1' || stripos($payment->method, 'hcm') !== false) {
                // HCM Loyalty payment
                $hcmLoyalty += $payment->amount;
            } else {
                $otherPayments += $payment->amount;
            }
        }
        
        // Gift voucher calculations
        $totalGiftVoucherSale = 0;
        $totalGiftVoucherTax = 0;
        $totalGiftVoucherDiscount = 0;
        $giftVoucherBurn = 0;
        
        // Check if any sell lines are gift vouchers
        foreach ($transaction->sell_lines as $line) {
            if ($line->product && stripos($line->product->name, 'gift') !== false && stripos($line->product->name, 'voucher') !== false) {
                $totalGiftVoucherSale += $line->line_total_inc_tax;
                $totalGiftVoucherTax += $line->item_tax;
                $totalGiftVoucherDiscount += $line->line_discount_amount ?? 0;
            }
        }
        
        return [
            'tenantId' => $config->username,
            'posId' => $config->pos_id,
            'cashierId' => $transaction->created_by ?? 'SYSTEM',
            'customerMobileNo' => $contact ? ($contact->mobile ?? '') : '',
            'invoiceType' => $transaction->type == 'sell' ? 'Sale' : 'Return',
            'invoiceNo' => $transaction->invoice_no,
            'invoiceDate' => Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i:s'),
            'currencyCode' => 'LKR',
            'currencyRate' => 1.0000,
            'totalInvoice' => round($totalInvoice, 2),
            'totalTax' => round($totalTax, 2),
            'totalDiscount' => round($totalDiscount, 2),
            'totalGiftVoucherSale' => round($totalGiftVoucherSale, 2),
            'totalGiftVoucherTax' => round($totalGiftVoucherTax, 2),
            'totalGiftVoucherDiscount' => round($totalGiftVoucherDiscount, 2),
            'paidByCash' => round($paidByCash, 2),
            'paidByCard' => round($paidByCard, 2),
            'cardBank' => $cardBank,
            'cardCategory' => $cardCategory,
            'cardType' => $cardType,
            'GiftVoucherBurn' => round($giftVoucherBurn, 2),
            'hcmLoyalty' => round($hcmLoyalty, 2),
            'tenantLoyalty' => 0.00,
            'creditNotes' => 0.00,
            'otherPayments' => round($otherPayments, 2)
        ];
    }
}
