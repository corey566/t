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
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background: #fff;
            padding: 5px;
        }

        .receipt {
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
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

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .divider-thick {
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .header {
            margin-bottom: 5px;
        }

        .business-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .business-subtitle {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .business-info {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 8px 0;
        }

        .invoice-details {
            margin: 5px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            table-layout: fixed;
        }

        .items-table th {
            text-align: left;
            padding: 3px 2px;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .items-table td {
            padding: 3px 2px;
            vertical-align: top;
        }

        .item-name {
            width: 55%;
            word-wrap: break-word;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 30%;
            text-align: right;
        }

        .item-details {
            font-size: 10px;
            color: #555;
        }

        .summary-section {
            margin: 5px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .total-row {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        .payment-section {
            margin: 8px 0;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 11px;
        }

        .loyalty-section {
            margin: 8px 0;
            padding: 5px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .points-section {
            margin: 8px 0;
        }

        .points-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .points-table th, .points-table td {
            text-align: center;
            padding: 2px;
            border: 1px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
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
        <div class="header text-center">
            @if(!empty($receipt_details->logo))
                <img style="max-height: 60px; width: auto; margin-bottom: 5px;" src="{{$receipt_details->logo}}" alt="Logo">
            @endif

            @if(!empty($receipt_details->display_name))
                <div class="business-name">{{$receipt_details->display_name}}</div>
            @endif

            @if(!empty($receipt_details->sub_heading_line1))
                <div class="business-subtitle">{{ $receipt_details->sub_heading_line1 }}</div>
            @endif

            @if(!empty($receipt_details->address))
                <div class="business-info">{!! $receipt_details->address !!}</div>
            @endif

            @if(!empty($receipt_details->contact))
                <div class="business-info">{!! $receipt_details->contact !!}</div>
            @endif

            @if(!empty($receipt_details->website))
                <div class="business-info">{{ $receipt_details->website }}</div>
            @endif

            @if(!empty($receipt_details->tax_info1))
                <div class="business-info">{{ $receipt_details->tax_label1 }}: {{ $receipt_details->tax_info1 }}</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Invoice Title -->
        <div class="invoice-title">TAX INVOICE</div>

        <div class="divider"></div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            @if(!empty($receipt_details->invoice_no))
                <div class="detail-row">
                    <div class="text-left bold">Invoice : {{$receipt_details->invoice_no}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->customer_info))
                <div class="detail-row">
                    <div class="text-left">Customer: {!! strip_tags($receipt_details->customer_info) !!}</div>
                </div>
            @endif

            @if(!empty($receipt_details->customer_label))
                <div class="detail-row">
                    <div class="text-left">{{$receipt_details->customer_label}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->commission_agent_label))
                <div class="detail-row">
                    <div>{{$receipt_details->commission_agent_label}}</div>
                    <div>{{$receipt_details->commission_agent}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                <div class="detail-row">
                    <div>{{$receipt_details->brand_label}}</div>
                    <div>{{$receipt_details->repair_brand}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                <div class="detail-row">
                    <div>{{$receipt_details->device_label}}</div>
                    <div>{{$receipt_details->repair_device}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                <div class="detail-row">
                    <div>{{$receipt_details->model_no_label}}</div>
                    <div>{{$receipt_details->repair_model_no}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                <div class="detail-row">
                    <div>{{$receipt_details->serial_no_label}}</div>
                    <div>{{$receipt_details->repair_serial_no}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                <div class="detail-row">
                    <div>{!! $receipt_details->repair_status_label !!}</div>
                    <div>{{$receipt_details->repair_status}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                <div class="detail-row">
                    <div>{!! $receipt_details->repair_warranty_label !!}</div>
                    <div>{{$receipt_details->repair_warranty}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                <div class="detail-row">
                    <div>{!! $receipt_details->service_staff_label !!}</div>
                    <div>{{$receipt_details->service_staff}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
                <div class="detail-row">
                    <div>{!! $receipt_details->table_label !!}</div>
                    <div>{{$receipt_details->table}}</div>
                </div>
            @endif

            @if (!empty($receipt_details->sell_custom_field_1_value))
                <div class="detail-row">
                    <div>{!! $receipt_details->sell_custom_field_1_label !!}</div>
                    <div>{{$receipt_details->sell_custom_field_1_value}}</div>
                </div>
            @endif
            @if (!empty($receipt_details->sell_custom_field_2_value))
                <div class="detail-row">
                    <div>{!! $receipt_details->sell_custom_field_2_label !!}</div>
                    <div>{{$receipt_details->sell_custom_field_2_value}}</div>
                </div>
            @endif
            @if (!empty($receipt_details->sell_custom_field_3_value))
                <div class="detail-row">
                    <div>{!! $receipt_details->sell_custom_field_3_label !!}</div>
                    <div>{{$receipt_details->sell_custom_field_3_value}}</div>
                </div>
            @endif
            @if (!empty($receipt_details->sell_custom_field_4_value))
                <div class="detail-row">
                    <div>{!! $receipt_details->sell_custom_field_4_label !!}</div>
                    <div>{{$receipt_details->sell_custom_field_4_value}}</div>
                </div>
            @endif

            @if(!empty($receipt_details->customer_rp_label))
                <div class="detail-row">
                    <div>{{ $receipt_details->customer_rp_label }}</div>
                    <div>{{ $receipt_details->customer_total_rp }}</div>
                </div>
            @endif

            @if(!empty($receipt_details->shipping_custom_field_1_label))
                <div class="detail-row">
                    <div>{!!$receipt_details->shipping_custom_field_1_label!!}</div>
                    <div>{!!$receipt_details->shipping_custom_field_1_value ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_2_label))
                <div class="detail-row">
                    <div>{!!$receipt_details->shipping_custom_field_2_label!!}</div>
                    <div>{!!$receipt_details->shipping_custom_field_2_value ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_3_label))
                <div class="detail-row">
                    <div>{!!$receipt_details->shipping_custom_field_3_label!!}</div>
                    <div>{!!$receipt_details->shipping_custom_field_3_value ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_4_label))
                <div class="detail-row">
                    <div>{!!$receipt_details->shipping_custom_field_4_label!!}</div>
                    <div>{!!$receipt_details->shipping_custom_field_4_value ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_5_label))
                <div class="detail-row">
                    <div>{!!$receipt_details->shipping_custom_field_5_label!!}</div>
                    <div>{!!$receipt_details->shipping_custom_field_5_value ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->sale_orders_invoice_no))
                <div class="detail-row">
                    <div>@lang('restaurant.order_no')</div>
                    <div>{!!$receipt_details->sale_orders_invoice_no ?? ''!!}</div>
                </div>
            @endif
            @if(!empty($receipt_details->sale_orders_invoice_date))
                <div class="detail-row">
                    <div>@lang('lang_v1.order_dates')</div>
                    <div>{!!$receipt_details->sale_orders_invoice_date ?? ''!!}</div>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Invoice Number and Date -->
        <div class="detail-row">
            @if(!empty($receipt_details->invoice_no))
                <div class="text-left">Invoice : {{$receipt_details->invoice_no}}</div>
            @endif
            @if(!empty($receipt_details->invoice_date))
                <div class="text-right">Date : {{$receipt_details->invoice_date}}</div>
            @endif
        </div>

        <!-- Items Table -->
        @if(!empty($receipt_details->lines))
        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-name"># Item</th>
                    <th class="item-qty">Qty</th>
                    <th class="item-price">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt_details->lines as $line)
                <tr>
                    <td class="item-name">
                        <div>{{$loop->iteration}} {{$line['name']}}</div>
                        @if(!empty($line['sub_sku']))
                            <div class="item-details">{{$line['sub_sku']}}</div>
                        @endif
                        @if(!empty($line['product_variation']) || !empty($line['variation']))
                            <div class="item-details">{{$line['product_variation']}} {{$line['variation']}}</div>
                        @endif
                        @if(!empty($line['service_staff']))
                            <div class="item-details">Staff: {{$line['service_staff']}}</div>
                        @endif
                    </td>
                    <td class="item-qty">{{$line['quantity']}}</td>
                    <td class="item-price">{{$line['line_total']}}</td>
                </tr>
                @if(!empty($line['modifiers']))
                    @foreach($line['modifiers'] as $modifier)
                    <tr>
                        <td class="item-name">
                            <div class="item-details">+ {{$modifier['name']}}</div>
                        </td>
                        <td class="item-qty">{{$modifier['quantity']}}</td>
                        <td class="item-price">{{$modifier['line_total']}}</td>
                    </tr>
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="divider"></div>

        <!-- Summary Section -->
        @if(!empty($receipt_details->subtotal))
        <div class="summary-section">
            <div class="summary-row">
                <div>Subtotal:</div>
                <div>{{$receipt_details->subtotal}}</div>
            </div>

            @if(!empty($receipt_details->discount))
            <div class="summary-row">
                <div>Discount:</div>
                <div>(-) {{$receipt_details->discount}}</div>
            </div>
            @endif

            @if(!empty($receipt_details->tax))
            <div class="summary-row">
                <div>Tax:</div>
                <div>(+) {{$receipt_details->tax}}</div>
            </div>
            @endif

            @if(!empty($receipt_details->shipping_charges))
            <div class="summary-row">
                <div>Shipping:</div>
                <div>{{$receipt_details->shipping_charges}}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Total Due -->
        @if(!empty($receipt_details->total))
        <div class="summary-row total-row">
            <div>Total due:</div>
            <div>{{$receipt_details->total}}</div>
        </div>
        @endif

        <!-- Loyalty Section -->
        @if(!empty($receipt_details->customer_name))
        <div class="loyalty-section">
            <div class="detail-row">
                <div class="bold">Loyalty Card:</div>
            </div>
            <div class="detail-row">
                <div>Name on Card: {{strtoupper($receipt_details->customer_name)}}</div>
            </div>
        </div>
        @endif

        <!-- Payment Section -->
        @if(!empty($receipt_details->payments))
        <div class="payment-section">
            @foreach($receipt_details->payments as $payment)
            <div class="payment-row">
                <div>{{$payment['method']}}: {{$payment['amount']}}</div>
            </div>
            @endforeach

            @if(!empty($receipt_details->total_paid))
            <div class="payment-row">
                <div>Total Paid:</div>
                <div>{{$receipt_details->total_paid}}</div>
            </div>
            @endif

            @if(!empty($receipt_details->total_due))
            <div class="payment-row">
                <div>Balance Due:</div>
                <div>{{$receipt_details->total_due}}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Reward Points Section -->
        @if(!empty($receipt_details->rp_enabled))
        <div class="points-section">
            <div class="text-center bold">Points status:</div>
            <table class="points-table">
                <tr>
                    <th>Before</th>
                    <th>Used</th>
                    <th>Earned</th>
                    <th>Balance</th>
                </tr>
                <tr>
                    <td>{{ $receipt_details->rp_before ?? 0 }}</td>
                    <td>{{ $receipt_details->rp_used ?? 0 }}</td>
                    <td>{{ $receipt_details->rp_earned ?? 0 }}</td>
                    <td>{{ $receipt_details->rp_available ?? 0 }}</td>
                </tr>
            </table>
        </div>
        @endif

        <div class="divider-thick"></div>

        <!-- Footer -->
        <div class="footer">
            @if(!empty($receipt_details->footer_text))
                {!! $receipt_details->footer_text !!}
            @else
                Thank You For Shopping. Please Come Again!
            @endif
        </div>

        @if($receipt_details->show_barcode)
            <div class="text-center" style="margin-top: 10px;">
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 1,30,array(39, 48, 54), true)}}">
            </div>
        @endif
    </div>
</body>
</html>