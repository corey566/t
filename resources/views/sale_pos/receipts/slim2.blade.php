<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt-{{$receipt_details->invoice_no}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 10px;
        }

        .receipt {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            background: #fff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .logo-section {
            margin-bottom: 5px;
        }

        .logo-section img {
            max-height: 50px;
            width: auto;
        }

        .business-name {
            font-size: 18px;
            font-weight: bold;
            color: #5b6abf;
            margin: 5px 0;
        }

        .contact-info {
            font-size: 10px;
            color: #333;
            margin: 3px 0;
        }

        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            padding: 5px 0;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        /* Invoice Details */
        .invoice-details {
            margin: 10px 0;
            font-size: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .detail-label {
            font-weight: 600;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }

        .items-table thead {
            background: #f5f5f5;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .items-table th {
            padding: 5px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px 3px;
            vertical-align: top;
            border-bottom: 1px solid #f0f0f0;
        }

        .col-num {
            width: 8%;
            text-align: center;
        }

        .col-item {
            width: 42%;
        }

        .col-qty {
            width: 12%;
            text-align: center;
        }

        .col-mrp {
            width: 15%;
            text-align: right;
        }

        .col-rate {
            width: 15%;
            text-align: right;
        }

        .col-total {
            width: 18%;
            text-align: right;
        }

        .item-name {
            font-weight: 600;
            font-size: 10px;
        }

        .item-details {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }

        /* Summary Section */
        .summary-section {
            margin: 10px 0;
            padding: 5px 0;
            font-size: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding: 2px 5px;
        }

        .summary-label {
            text-align: left;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
        }

        /* Totals Section */
        .totals-section {
            background: #f5f5f5;
            padding: 8px 5px;
            margin: 10px 0;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Payment Details */
        .payment-section {
            margin: 10px 0;
            padding: 8px 5px;
            background: #fafafa;
            border: 1px solid #eee;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .payment-label {
            font-weight: 600;
        }

        .payment-mode {
            margin-top: 5px;
            font-size: 10px;
            text-align: center;
        }

        /* Discount and Tax Details */
        .details-section {
            font-size: 9px;
            color: #555;
            margin: 5px 0;
        }

        /* Barcode Section */
        .barcode-section {
            text-align: center;
            margin: 15px 0 10px;
        }

        .barcode-section img {
            max-width: 150px;
            height: auto;
        }

        .barcode-number {
            font-size: 9px;
            margin-top: 3px;
            color: #666;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            color: #555;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        @media print {
            body {
                padding: 0;
            }
            .receipt {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header Section -->
        <div class="header">
            @if(!empty($receipt_details->logo))
                <div class="logo-section">
                    <img src="{{$receipt_details->logo}}" alt="Logo">
                </div>
            @endif

            @if(!empty($receipt_details->display_name))
                <div class="business-name">{{$receipt_details->display_name}}</div>
            @endif

            @if(!empty($receipt_details->contact))
                <div class="contact-info">Phone: {!! strip_tags($receipt_details->contact) !!}</div>
            @endif

            @if(!empty($receipt_details->email))
                <div class="contact-info">Email: {{$receipt_details->email}}</div>
            @endif

            @if(!empty($receipt_details->address))
                <div class="contact-info">{!! $receipt_details->address !!}</div>
            @endif

            @if(!empty($receipt_details->website))
                <div class="contact-info">{{$receipt_details->website}}</div>
            @endif

            @if(!empty($receipt_details->tax_info1))
                <div class="contact-info">{{ $receipt_details->tax_label1 }}: {{ $receipt_details->tax_info1 }}</div>
            @endif
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">TAX INVOICE</div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            @if(!empty($receipt_details->invoice_no))
                <div class="detail-row">
                    <span class="detail-label">Invoice :</span>
                    <span>{{$receipt_details->invoice_no}}</span>
                </div>
            @endif

            @if(!empty($receipt_details->invoice_date))
                <div class="detail-row">
                    <span class="detail-label">Date :</span>
                    <span>{{$receipt_details->invoice_date}}</span>
                </div>
            @endif

            @if(!empty($receipt_details->customer_info))
                <div class="detail-row">
                    <span class="detail-label">Customer :</span>
                    <span>{!! strip_tags($receipt_details->customer_info) !!}</span>
                </div>
            @endif

            @if(!empty($receipt_details->customer_label))
                <div class="detail-row">
                    <span>{{$receipt_details->customer_label}}</span>
                </div>
            @endif

            @if(!empty($receipt_details->service_staff))
                <div class="detail-row">
                    <span class="detail-label">Sold By :</span>
                    <span>{{$receipt_details->service_staff}}</span>
                </div>
            @endif

            @if(!empty($receipt_details->table))
                <div class="detail-row">
                    <span class="detail-label">{!! $receipt_details->table_label !!} :</span>
                    <span>{{$receipt_details->table}}</span>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        @if(!empty($receipt_details->lines))
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-item">Item</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-rate">Rate</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt_details->lines as $line)
                <tr>
                    <td class="col-num">{{$loop->iteration}}</td>
                    <td class="col-item">
                        <div class="item-name">{{$line['name']}}</div>
                        @if(!empty($line['sub_sku']))
                            <div class="item-details">SKU: {{$line['sub_sku']}}</div>
                        @endif
                        @if(!empty($line['product_variation']) || !empty($line['variation']))
                            <div class="item-details">{{$line['product_variation']}} {{$line['variation']}}</div>
                        @endif
                        @if(!empty($line['service_staff']))
                            <div class="item-details">Staff: {{$line['service_staff']}}</div>
                        @endif
                        @if(!empty($line['service_staff_name']))
                            <div class="item-details">Service Staff: {{$line['service_staff_name']}}</div>
                        @endif
                    </td>
                    <td class="col-qty">{{$line['quantity']}}</td>
                    <td class="col-rate">{{$line['unit_price_before_discount']}}</td>
                    <td class="col-total">{{$line['line_total']}}</td>
                </tr>
                @if(!empty($line['modifiers']))
                    @foreach($line['modifiers'] as $modifier)
                    <tr>
                        <td class="col-num"></td>
                        <td class="col-item">
                            <div class="item-details">+ {{$modifier['name']}}</div>
                        </td>
                        <td class="col-qty">{{$modifier['quantity']}}</td>
                        <td class="col-rate">{{$modifier['unit_price']}}</td>
                        <td class="col-total">{{$modifier['line_total']}}</td>
                    </tr>
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Summary Section -->
        <div class="summary-section">
            @if(!empty($receipt_details->tax))
            <div class="summary-row">
                <span class="summary-label">Order Tax</span>
                <span class="summary-value">{{$receipt_details->tax}}</span>
            </div>
            @endif

            @if(!empty($receipt_details->discount))
            <div class="summary-row">
                <span class="summary-label">Discount</span>
                <span class="summary-value">{{$receipt_details->discount}}</span>
            </div>
            @endif

            @if(!empty($receipt_details->shipping_charges))
            <div class="summary-row">
                <span class="summary-label">Shipping</span>
                <span class="summary-value">{{$receipt_details->shipping_charges}}</span>
            </div>
            @endif
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            @php
                $total_items = 0;
                $total_quantity = 0;
                if(!empty($receipt_details->lines)) {
                    $total_items = count($receipt_details->lines);
                    foreach($receipt_details->lines as $line) {
                        $total_quantity += $line['quantity'];
                    }
                }
            @endphp
            <div class="summary-row">
                <span>Items: {{$total_items}}</span>
                <span>Qty: {{$total_quantity}}</span>
                @if(!empty($receipt_details->total))
                    <span>Total: {{$receipt_details->total}}</span>
                @endif
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-section">
            <div class="payment-row">
                <span class="payment-label">Paid Amount</span>
                <span class="payment-label">Due Amount</span>
            </div>
            <div class="payment-row">
                <span>{{$receipt_details->total_paid ?? '0.00'}}</span>
                <span>{{$receipt_details->total_due ?? '0.00'}}</span>
            </div>

            @if(!empty($receipt_details->payments))
                @foreach($receipt_details->payments as $payment)
                <div class="payment-mode">
                    Payment Mode: {{$payment['amount']}} ({{$payment['method']}})
                </div>
                @endforeach
            @endif
        </div>

        <!-- Discount and Tax Details -->
        @if(!empty($receipt_details->total_line_discount))
        <div class="details-section">
            Total Discount On MRP : {{$receipt_details->total_line_discount}}
        </div>
        @endif

        @if(!empty($receipt_details->discount_percent))
        <div class="details-section">
            Total Discount : {{$receipt_details->discount_percent}}
        </div>
        @endif

        @if(!empty($receipt_details->tax))
        <div class="details-section">
            Total Tax : {{$receipt_details->tax}}
        </div>
        @endif

        <!-- Barcode Section -->
        @if($receipt_details->show_barcode)
            <div class="barcode-section">
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 1, 40, array(0, 0, 0), true)}}" alt="Barcode">
                <div class="barcode-number">{{$receipt_details->invoice_no}}</div>
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