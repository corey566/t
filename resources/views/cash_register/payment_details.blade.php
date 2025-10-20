
<div class="mini_print">
  <!-- Payment Methods Table -->
  <div class="tw-overflow-x-auto">
    <table class="tw-w-full tw-text-sm">
      <thead class="tw-bg-gradient-to-r tw-from-indigo-100 tw-to-purple-100">
        <tr class="tw-border-b-2 tw-border-indigo-300">
          <th class="tw-text-left tw-py-2 tw-px-3 tw-font-bold tw-text-indigo-900">@lang('lang_v1.payment_method')</th>
          <th class="tw-text-right tw-py-2 tw-px-3 tw-font-bold tw-text-indigo-900">@lang('sale.sale')</th>
          <th class="tw-text-right tw-py-2 tw-px-3 tw-font-bold tw-text-indigo-900">@lang('lang_v1.expense')</th>
        </tr>
      </thead>
      <tbody class="tw-divide-y tw-divide-gray-200">
        <tr class="tw-bg-green-50 hover:tw-bg-green-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-hand-holding-usd tw-text-green-700 tw-mr-2"></i>
            @lang('cash_register.cash_in_hand')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-text-gray-500">--</td>
        </tr>
        @if($register_details->total_cash != 0 || $register_details->total_cash_expense != 0)
        <tr class="tw-bg-emerald-50 hover:tw-bg-emerald-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-money-bill-wave tw-text-emerald-700 tw-mr-2"></i>
            @lang('cash_register.cash_payment')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-red-700">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_expense }}</span>
          </td>
        </tr>
        @endif
        @if($register_details->total_cheque != 0 || $register_details->total_cheque_expense != 0)
        <tr class="tw-bg-blue-50 hover:tw-bg-blue-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-file-invoice-dollar tw-text-blue-700 tw-mr-2"></i>
            @lang('cash_register.checque_payment')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-red-700">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_expense }}</span>
          </td>
        </tr>
        @endif
        @if($register_details->total_card != 0 || $register_details->total_card_expense != 0)
        <tr class="tw-bg-purple-50 hover:tw-bg-purple-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-credit-card tw-text-purple-700 tw-mr-2"></i>
            @lang('cash_register.card_payment')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-red-700">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_expense }}</span>
          </td>
        </tr>
        @endif
        @if($register_details->total_bank_transfer != 0 || $register_details->total_bank_transfer_expense != 0)
        <tr class="tw-bg-indigo-50 hover:tw-bg-indigo-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-university tw-text-indigo-700 tw-mr-2"></i>
            @lang('cash_register.bank_transfer')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-red-700">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_expense }}</span>
          </td>
        </tr>
        @endif
        @if($register_details->total_advance != 0 || $register_details->total_advance_expense != 0)
        <tr class="tw-bg-cyan-50 hover:tw-bg-cyan-100 tw-transition-colors">
          <td class="tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">
            <i class="fa fa-arrow-circle-down tw-text-cyan-700 tw-mr-2"></i>
            @lang('lang_v1.advance_payment')
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance }}</span>
          </td>
          <td class="tw-py-1.5 tw-px-3 tw-text-right tw-font-semibold tw-text-red-700">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_1', $payment_types) && ($register_details->total_custom_pay_1 != 0 || $register_details->total_custom_pay_1_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_1']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_2', $payment_types) && ($register_details->total_custom_pay_2 != 0 || $register_details->total_custom_pay_2_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_2']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_3', $payment_types) && ($register_details->total_custom_pay_3 != 0 || $register_details->total_custom_pay_3_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_3']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_4', $payment_types) && ($register_details->total_custom_pay_4 != 0 || $register_details->total_custom_pay_4_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_4']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_5', $payment_types) && ($register_details->total_custom_pay_5 != 0 || $register_details->total_custom_pay_5_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_5']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_6', $payment_types) && ($register_details->total_custom_pay_6 != 0 || $register_details->total_custom_pay_6_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_6']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6_expense }}</span>
          </td>
        </tr>
        @endif
        @if(array_key_exists('custom_pay_7', $payment_types) && ($register_details->total_custom_pay_7 != 0 || $register_details->total_custom_pay_7_expense != 0))
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-circle tw-text-gray-600 tw-mr-2"></i>
            {{$payment_types['custom_pay_7']}}
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7 }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7_expense }}</span>
          </td>
        </tr>
        @endif
        @if($register_details->total_other != 0 || $register_details->total_other_expense != 0)
        <tr class="hover:tw-bg-blue-50 tw-transition-colors">
          <td class="tw-py-3 tw-px-4 tw-font-medium tw-text-gray-800">
            <i class="fa fa-ellipsis-h tw-text-gray-600 tw-mr-2"></i>
            @lang('cash_register.other_payments')
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-gray-900">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other }}</span>
          </td>
          <td class="tw-py-3 tw-px-4 tw-text-right tw-font-semibold tw-text-red-600">
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_expense }}</span>
          </td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  <!-- Summary Cards -->
  <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-2 tw-mt-3">
    <div class="tw-bg-gradient-to-br tw-from-green-400 tw-to-emerald-500 tw-border-2 tw-border-green-300 tw-rounded-lg tw-p-2 tw-shadow-md">
      <div class="tw-text-center">
        <p class="tw-text-xs tw-text-white tw-font-semibold tw-mb-0.5">@lang('cash_register.total_sales')</p>
        <p class="tw-text-lg tw-font-bold tw-text-white">
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale }}</span>
        </p>
        <i class="fa fa-shopping-cart tw-text-2xl tw-text-white tw-opacity-70"></i>
      </div>
    </div>

    <div class="tw-bg-gradient-to-br tw-from-red-400 tw-to-rose-500 tw-border-2 tw-border-red-300 tw-rounded-lg tw-p-2 tw-shadow-md">
      <div class="tw-text-center">
        <p class="tw-text-xs tw-text-white tw-font-semibold tw-mb-0.5">@lang('cash_register.total_refund')</p>
        <p class="tw-text-lg tw-font-bold tw-text-white">
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_refund }}</span>
        </p>
        <i class="fa fa-undo tw-text-2xl tw-text-white tw-opacity-70"></i>
      </div>
      @if($register_details->total_refund != 0)
      <div class="tw-mt-2 tw-pt-2 tw-border-t tw-border-red-200 tw-text-xs tw-text-red-600">
        @if($register_details->total_cash_refund != 0)
          <div>Cash: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_refund }}</span></div>
        @endif
        @if($register_details->total_cheque_refund != 0) 
          <div>Cheque: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_refund }}</span></div>
        @endif
        @if($register_details->total_card_refund != 0) 
          <div>Card: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_refund }}</span></div>
        @endif
        @if($register_details->total_bank_transfer_refund != 0)
          <div>Bank: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}</span></div>
        @endif
        @if($register_details->total_other_refund != 0)
          <div>Other: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_refund }}</span></div>
        @endif
      </div>
      @endif
    </div>

    <div class="tw-bg-gradient-to-br tw-from-blue-400 tw-to-indigo-500 tw-border-2 tw-border-blue-300 tw-rounded-lg tw-p-2 tw-shadow-md">
      <div class="tw-text-center">
        <p class="tw-text-xs tw-text-white tw-font-semibold tw-mb-0.5">@lang('lang_v1.total_payment')</p>
        <p class="tw-text-lg tw-font-bold tw-text-white">
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund }}</span>
        </p>
        <i class="fa fa-wallet tw-text-2xl tw-text-white tw-opacity-70"></i>
      </div>
    </div>

    <div class="tw-bg-gradient-to-br tw-from-orange-400 tw-to-amber-500 tw-border-2 tw-border-orange-300 tw-rounded-lg tw-p-2 tw-shadow-md">
      <div class="tw-text-center">
        <p class="tw-text-xs tw-text-white tw-font-semibold tw-mb-0.5">@lang('report.total_expense')</p>
        <p class="tw-text-lg tw-font-bold tw-text-white">
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_expense }}</span>
        </p>
        <i class="fa fa-receipt tw-text-2xl tw-text-white tw-opacity-70"></i>
      </div>
    </div>
  </div>

  <!-- Calculation Formula -->
  <div class="tw-bg-gradient-to-r tw-from-slate-100 tw-to-gray-100 tw-border tw-border-gray-300 tw-rounded-lg tw-p-2 tw-text-xs tw-text-gray-800 tw-mt-2">
    <p class="tw-font-bold tw-mb-1 tw-text-sm">@lang('sale.total') Calculation:</p>
    <p class="tw-font-mono tw-text-xs tw-leading-relaxed">
      @format_currency($register_details->cash_in_hand) <span class="tw-text-gray-600">(Opening)</span> + 
      @format_currency($register_details->total_sale + $register_details->total_refund) <span class="tw-text-gray-600">(Sale)</span> - 
      @format_currency($register_details->total_refund) <span class="tw-text-gray-600">(Refund)</span> - 
      @format_currency($register_details->total_expense) <span class="tw-text-gray-600">(Expense)</span> 
      = <span class="tw-font-bold tw-text-indigo-700">@format_currency($register_details->cash_in_hand + $register_details->total_sale - $register_details->total_expense)</span>
    </p>
  </div>
</div>

@include('cash_register.register_product_details')
