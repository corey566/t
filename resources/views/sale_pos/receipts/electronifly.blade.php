
<!-- Electronifly Receipt Layout -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt-{{$receipt_details->invoice_no}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px 20px;
            border: 1px solid #e0e0e0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 50px;
            height: 50px;
        }
        
        .business-name {
            font-size: 24px;
            color: #7B68EE;
            font-weight: bold;
        }
        
        .business-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .contact-info {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 15px;
            text-transform: uppercase;
        }
        
        .invoice-details {
            font-size: 13px;
            margin-bottom: 15px;
            line-height: 1.8;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: 500;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        
        .items-table thead {
            background: #f9f9f9;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table th {
            padding: 10px 5px;
            text-align: left;
            font-weight: 600;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 12px 5px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .item-details {
            font-size: 11px;
            color: #666;
        }
        
        .totals-section {
            margin: 20px 0;
            padding: 15px 0;
            border-top: 2px solid #e0e0e0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .total-row.grand-total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .summary-section {
            background: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .payment-section {
            margin: 20px 0;
            font-size: 13px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .payment-method {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }
        
        .points-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .points-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .points-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .points-table th,
        .points-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .points-table th {
            background: white;
            font-weight: 600;
        }
        
        .barcode-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .barcode {
            margin: 10px 0;
        }
        
        .barcode-text {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-section">
                @if(!empty($receipt_details->logo))
                    <img src="{{$receipt_details->logo}}" class="logo" alt="Logo">
                @endif
                @if(!empty($receipt_details->display_name))
                    <div class="business-name">{{$receipt_details->display_name}}</div>
                @endif
            </div>
            
            @if(!empty($receipt_details->display_name))
                <div class="business-title">{{$receipt_details->display_name}}</div>
            @endif
            
            <div class="contact-info">
                @if(!empty($receipt_details->contact))
                    Phone: {!! $receipt_details->contact !!}<br>
                @endif
                @if(!empty($receipt_details->website))
                    Email: {{ $receipt_details->website }}
                @endif
            </div>
        </div>
        
        <!-- Invoice Title -->
        @if(!empty($receipt_details->invoice_heading))
            <div class="invoice-title">{!! $receipt_details->invoice_heading !!}</div>
        @else
            <div class="invoice-title">TAX INVOICE</div>
        @endif
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="detail-row">
                <span class="detail-label">Invoice:</span>
                <span>{{$receipt_details->invoice_no}}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span>{{$receipt_details->invoice_date}}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer:</span>
                <span>
                    @if(!empty($receipt_details->customer_info))
                        {!! $receipt_details->customer_info !!}
                    @else
                        Walk In Customer
                    @endif
                </span>
            </div>
            @if(!empty($receipt_details->sales_person))
                <div class="detail-row">
                    <span class="detail-label">Sold By:</span>
                    <span>{{$receipt_details->sales_person}}</span>
                </div>
            @endif
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Item</th>
                    <th style="width: 12%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">MRP</th>
                    <th style="width: 15%; text-align: right;">Rate</th>
                    <th style="width: 18%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $item_count = 0; @endphp
                @forelse($receipt_details->lines as $line)
                    @php $item_count++; @endphp
                    <tr>
                        <td>{{$item_count}}</td>
                        <td>
                            <div class="item-name">{{$line['name']}}</div>
                            @if(!empty($line['product_variation']) || !empty($line['variation']))
                                <div class="item-details">{{$line['product_variation']}} {{$line['variation']}}</div>
                            @endif
                            @if(!empty($line['service_staff_name']))
                                <div class="item-details">Staff: {{$line['service_staff_name']}}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{$line['quantity']}} {{$line['units']}}</td>
                        <td style="text-align: right;">{{$line['unit_price_before_discount']}}</td>
                        <td style="text-align: right;">{{$line['unit_price_inc_tax']}}</td>
                        <td style="text-align: right;">{{$line['line_total']}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Totals Section -->
        <div class="totals-section">
            @if($receipt_details->tax_label_1)
                <div class="total-row">
                    <span>{{$receipt_details->tax_label_1}}</span>
                    <span>{{$receipt_details->tax_1}}</span>
                </div>
            @endif
            
            @if($receipt_details->tax_label_2)
                <div class="total-row">
                    <span>{{$receipt_details->tax_label_2}}</span>
                    <span>{{$receipt_details->tax_2}}</span>
                </div>
            @endif
            
            <div class="total-row">
                <span>Order Tax</span>
                <span>{{$receipt_details->tax ?? '₹0.00'}}</span>
            </div>
            
            @if(!empty($receipt_details->discount))
                <div class="total-row">
                    <span>Discount</span>
                    <span>{{$receipt_details->discount}}</span>
                </div>
            @endif
            
            @if(!empty($receipt_details->shipping_charges))
                <div class="total-row">
                    <span>Shipping</span>
                    <span>{{$receipt_details->shipping_charges}}</span>
                </div>
            @endif
        </div>
        
        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <strong>Items: {{count($receipt_details->lines)}}</strong>
                <span></span>
            </div>
            <div class="summary-row">
                <strong>Qty: {{array_sum(array_column($receipt_details->lines, 'quantity'))}}</strong>
                <span></span>
            </div>
            <div class="summary-row">
                <strong>Total:</strong>
                <strong>{{$receipt_details->total}}</strong>
            </div>
        </div>
        
        <!-- Payment Section -->
        <div class="payment-section">
            <div class="payment-row">
                <strong>Paid Amount</strong>
                <strong>Due Amount</strong>
            </div>
            <div class="payment-row">
                <span>{{$receipt_details->total_paid}}</span>
                <span>{{$receipt_details->total_due}}</span>
            </div>
            
            @if(!empty($receipt_details->payments))
                <div class="payment-method">
                    @foreach($receipt_details->payments as $payment)
                        <div class="total-row">
                            <span>Payment Mode:</span>
                            <span>{{$payment['amount']}} ({{$payment['method']}})</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Discount Details -->
        @if(!empty($receipt_details->total_discount))
            <div class="total-row">
                <span>Total Discount On MRP:</span>
                <span>{{$receipt_details->total_discount}}</span>
            </div>
        @endif
        
        @if(!empty($receipt_details->discount_percent))
            <div class="total-row">
                <span>Total Discount:</span>
                <span>{{$receipt_details->discount_percent}}</span>
            </div>
        @endif
        
        <!-- Points Section -->
        @if(!empty($receipt_details->customer_rp_label) && ($receipt_details->rp_earned > 0 || $receipt_details->rp_redeemed > 0))
            <div class="points-section">
                <div class="points-title">Points status</div>
                <table class="points-table">
                    <thead>
                        <tr>
                            <th>Before</th>
                            <th>Used</th>
                            <th>Earned</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $receipt_details->customer_total_rp ?? 0 }}</td>
                            <td>{{ $receipt_details->rp_redeemed ?? 0 }}</td>
                            <td>{{ $receipt_details->rp_earned ?? 0 }}</td>
                            <td>{{ ($receipt_details->customer_total_rp ?? 0) - ($receipt_details->rp_redeemed ?? 0) + ($receipt_details->rp_earned ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        
        <!-- Barcode Section -->
        @if(!empty($receipt_details->invoice_no))
            <div class="barcode-section">
                <div class="barcode">
                    {!! DNS1D::getBarcodeHTML($receipt_details->invoice_no, "C128", 1.5, 50) !!}
                </div>
                <div class="barcode-text">{{$receipt_details->invoice_no}}</div>
            </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            @if(!empty($receipt_details->footer_text))
                {!! $receipt_details->footer_text !!}
            @else
                Thank You For Shopping With Us. Please Come Again
            @endif
        </div>
    </div>
</body>
</html>
