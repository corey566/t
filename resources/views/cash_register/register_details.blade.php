
<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content tw-border-0 tw-shadow-xl">
    <!-- Modern Header -->
    <div class="modal-header tw-bg-gradient-to-r tw-from-blue-600 tw-to-indigo-700 tw-border-0 tw-text-white mini_print">
      <button type="button" class="close no-print tw-text-white tw-opacity-80 hover:tw-opacity-100" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true" class="tw-text-2xl">&times;</span>
      </button>
      <h3 class="modal-title tw-text-lg tw-font-semibold tw-mb-0">
        <i class="fa fa-cash-register tw-mr-2"></i>
        @lang('cash_register.register_details')
      </h3>
    </div>

    <div class="modal-body tw-p-4 tw-bg-gradient-to-br tw-from-blue-50 tw-to-indigo-50">
      <!-- Time Period & User Info Table -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-overflow-hidden tw-mb-3 mini_print">
        <table class="tw-w-full tw-text-sm">
          <tbody>
            <tr class="tw-border-b tw-bg-gradient-to-r tw-from-purple-100 tw-to-pink-100">
              <td class="tw-py-2 tw-px-3 tw-font-semibold tw-text-purple-800">
                <i class="fa fa-clock tw-mr-2"></i>@lang('lang_v1.opening_time')
              </td>
              <td class="tw-py-2 tw-px-3 tw-font-medium tw-text-gray-800">
                {{ \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A') }}
              </td>
              <td class="tw-py-2 tw-px-3 tw-font-semibold tw-text-purple-800">
                <i class="fa fa-clock tw-mr-2"></i>@lang('lang_v1.closing_time')
              </td>
              <td class="tw-py-2 tw-px-3 tw-font-medium tw-text-gray-800">
                {{\Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A')}}
              </td>
            </tr>
            <tr class="tw-border-b tw-bg-gradient-to-r tw-from-teal-100 tw-to-cyan-100">
              <td class="tw-py-2 tw-px-3 tw-font-semibold tw-text-teal-800">
                <i class="fa fa-user tw-mr-2"></i>@lang('report.user')
              </td>
              <td class="tw-py-2 tw-px-3 tw-font-medium tw-text-gray-800">{{ $register_details->user_name}}</td>
              <td class="tw-py-2 tw-px-3 tw-font-semibold tw-text-teal-800">
                <i class="fa fa-envelope tw-mr-2"></i>@lang('business.email')
              </td>
              <td class="tw-py-2 tw-px-3 tw-font-medium tw-text-gray-800">{{ $register_details->email}}</td>
            </tr>
            <tr class="tw-bg-gradient-to-r tw-from-green-100 tw-to-emerald-100">
              <td class="tw-py-2 tw-px-3 tw-font-semibold tw-text-green-800">
                <i class="fa fa-map-marker-alt tw-mr-2"></i>@lang('business.business_location')
              </td>
              <td colspan="3" class="tw-py-2 tw-px-3 tw-font-medium tw-text-gray-800">{{ $register_details->location_name}}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payment Details Card -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-overflow-hidden tw-mb-3">
        <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-indigo-600 tw-text-white tw-py-2 tw-px-3">
          <h4 class="tw-text-base tw-font-semibold tw-mb-0 tw-flex tw-items-center">
            <i class="fa fa-credit-card tw-mr-2"></i>
            @lang('lang_v1.payment_method')
          </h4>
        </div>
        <div class="tw-p-3">
          @include('cash_register.payment_details')
        </div>
      </div>

      <!-- Cash Denominations Card -->
      @if(!empty($register_details->denominations))
        @php
          $total = 0;
        @endphp
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-overflow-hidden tw-mb-3">
          <div class="tw-bg-gradient-to-r tw-from-emerald-500 tw-to-teal-600 tw-text-white tw-py-2 tw-px-3">
            <h4 class="tw-text-base tw-font-semibold tw-mb-0 tw-flex tw-items-center">
              <i class="fa fa-money-bill-wave tw-mr-2"></i>
              @lang('lang_v1.cash_denominations')
            </h4>
          </div>
          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
              <thead class="tw-bg-gradient-to-r tw-from-gray-100 tw-to-gray-200">
                <tr class="tw-border-b-2 tw-border-gray-300">
                  <th class="tw-text-right tw-py-2 tw-px-3 tw-text-gray-700 tw-font-semibold">@lang('lang_v1.denomination')</th>
                  <th class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-700"></th>
                  <th class="tw-text-center tw-py-2 tw-px-3 tw-text-gray-700 tw-font-semibold">@lang('lang_v1.count')</th>
                  <th class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-700"></th>
                  <th class="tw-text-left tw-py-2 tw-px-3 tw-text-gray-700 tw-font-semibold">@lang('sale.subtotal')</th>
                </tr>
              </thead>
              <tbody>
                @foreach($register_details->denominations as $key => $value)
                <tr class="tw-border-b hover:tw-bg-amber-50 tw-transition-colors">
                  <td class="tw-text-right tw-py-1.5 tw-px-3 tw-font-medium tw-text-gray-800">{{$key}}</td>
                  <td class="tw-text-center tw-py-1.5 tw-px-2 tw-text-gray-500">×</td>
                  <td class="tw-text-center tw-py-1.5 tw-px-3 tw-text-gray-800">{{$value ?? 0}}</td>
                  <td class="tw-text-center tw-py-1.5 tw-px-2 tw-text-gray-500">=</td>
                  <td class="tw-text-left tw-py-1.5 tw-px-3 tw-font-semibold tw-text-gray-900">
                    @format_currency($key * $value)
                  </td>
                </tr>
                @php
                  $total += ($key * $value);
                @endphp
                @endforeach
              </tbody>
              <tfoot class="tw-bg-gradient-to-r tw-from-emerald-100 tw-to-teal-100">
                <tr>
                  <th colspan="4" class="tw-text-center tw-py-2 tw-px-3 tw-font-bold tw-text-emerald-800">@lang('sale.total')</th>
                  <td class="tw-py-2 tw-px-3 tw-font-bold tw-text-emerald-900 tw-text-base">@format_currency($total)</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      @endif
      
      <!-- Closing Notes Card -->
      @if(!empty($register_details->closing_note))
        <div class="tw-bg-gradient-to-r tw-from-amber-100 tw-to-orange-100 tw-border-2 tw-border-amber-300 tw-rounded-lg tw-p-3 mini_print tw-shadow-md">
          <div class="tw-flex tw-items-start">
            <i class="fa fa-sticky-note tw-text-amber-700 tw-mt-1 tw-mr-2 tw-text-lg"></i>
            <div>
              <p class="tw-text-sm tw-font-bold tw-text-amber-900 tw-mb-1">@lang('cash_register.closing_note'):</p>
              <p class="tw-text-sm tw-text-amber-950">{{$register_details->closing_note}}</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    <!-- Modern Footer -->
    <div class="modal-footer tw-bg-gradient-to-r tw-from-gray-100 tw-to-gray-200 tw-border-0 tw-flex tw-justify-end tw-gap-2 tw-py-2">
      <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-blue-600 tw-to-indigo-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-from-blue-700 hover:tw-to-indigo-700 tw-transition-all tw-shadow-md no-print print-mini-button" 
              aria-label="Print">
        <i class="fa fa-print tw-mr-2"></i> 
        @lang('messages.print_mini')
      </button>

      <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-gray-500 tw-to-gray-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-from-gray-600 hover:tw-to-gray-700 tw-transition-all tw-shadow-md no-print" 
        data-dismiss="modal">
        <i class="fa fa-times tw-mr-2"></i>
        @lang('messages.cancel')
      </button>
    </div>
  </div>
</div>

<style type="text/css">
  @media print {
    .modal {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        overflow: visible!important;
    }
    .modal-content {
        box-shadow: none !important;
    }
    /* Convert all colors to grayscale for printing */
    * {
        background-image: none !important;
        background-color: white !important;
        color: black !important;
        border-color: #999 !important;
    }
    th, .tw-font-bold, .tw-font-semibold {
        background-color: #f0f0f0 !important;
        font-weight: bold !important;
    }
    table {
        border: 1px solid #000 !important;
    }
    tr {
        border-bottom: 1px solid #999 !important;
    }
    td, th {
        padding: 4px 8px !important;
    }
  }
</style>

<script>
  $(document).ready(function () {
      $(document).on('click', '.print-mini-button', function () {
          $('.mini_print').printThis();
      });
  });
</script>
