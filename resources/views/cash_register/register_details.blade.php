
<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content tw-border-0 tw-shadow-xl">
    <!-- Modern Header -->
    <div class="modal-header tw-bg-gradient-to-r tw-from-primary-600 tw-to-primary-700 tw-border-0 tw-text-white mini_print">
      <button type="button" class="close no-print tw-text-white tw-opacity-80 hover:tw-opacity-100" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true" class="tw-text-2xl">&times;</span>
      </button>
      <h3 class="modal-title tw-text-lg tw-font-semibold tw-mb-0">
        <i class="fa fa-cash-register tw-mr-2"></i>
        @lang('cash_register.register_details')
      </h3>
    </div>

    <div class="modal-body tw-p-6 tw-bg-gray-50">
      <!-- Time Period Card -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tw-mb-4 mini_print">
        <div class="tw-flex tw-items-center tw-text-gray-700">
          <i class="fa fa-clock tw-text-primary-600 tw-mr-3"></i>
          <div>
            <span class="tw-font-medium">{{ \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A') }}</span>
            <span class="tw-mx-2 tw-text-gray-400">→</span>
            <span class="tw-font-medium">{{\Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A')}}</span>
          </div>
        </div>
      </div>

      <!-- User Information Card -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tw-mb-4 mini_print">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
          <div class="tw-flex tw-items-start">
            <i class="fa fa-user tw-text-primary-600 tw-mt-1 tw-mr-3"></i>
            <div>
              <p class="tw-text-xs tw-text-gray-500 tw-mb-1">@lang('report.user')</p>
              <p class="tw-font-medium tw-text-gray-800">{{ $register_details->user_name}}</p>
            </div>
          </div>
          <div class="tw-flex tw-items-start">
            <i class="fa fa-envelope tw-text-primary-600 tw-mt-1 tw-mr-3"></i>
            <div>
              <p class="tw-text-xs tw-text-gray-500 tw-mb-1">@lang('business.email')</p>
              <p class="tw-font-medium tw-text-gray-800">{{ $register_details->email}}</p>
            </div>
          </div>
          <div class="tw-flex tw-items-start">
            <i class="fa fa-map-marker-alt tw-text-primary-600 tw-mt-1 tw-mr-3"></i>
            <div>
              <p class="tw-text-xs tw-text-gray-500 tw-mb-1">@lang('business.business_location')</p>
              <p class="tw-font-medium tw-text-gray-800">{{ $register_details->location_name}}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Details Card -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tw-mb-4">
        <h4 class="tw-text-base tw-font-semibold tw-text-gray-800 tw-mb-4 tw-flex tw-items-center">
          <i class="fa fa-credit-card tw-text-primary-600 tw-mr-2"></i>
          @lang('lang_v1.payment_method')
        </h4>
        @include('cash_register.payment_details')
      </div>

      <!-- Cash Denominations Card -->
      @if(!empty($register_details->denominations))
        @php
          $total = 0;
        @endphp
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tw-mb-4">
          <h4 class="tw-text-base tw-font-semibold tw-text-gray-800 tw-mb-4 tw-flex tw-items-center">
            <i class="fa fa-money-bill-wave tw-text-primary-600 tw-mr-2"></i>
            @lang('lang_v1.cash_denominations')
          </h4>
          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
              <thead class="tw-bg-gray-50">
                <tr class="tw-border-b">
                  <th class="tw-text-right tw-py-2 tw-px-3 tw-text-gray-600 tw-font-medium">@lang('lang_v1.denomination')</th>
                  <th class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-600"></th>
                  <th class="tw-text-center tw-py-2 tw-px-3 tw-text-gray-600 tw-font-medium">@lang('lang_v1.count')</th>
                  <th class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-600"></th>
                  <th class="tw-text-left tw-py-2 tw-px-3 tw-text-gray-600 tw-font-medium">@lang('sale.subtotal')</th>
                </tr>
              </thead>
              <tbody>
                @foreach($register_details->denominations as $key => $value)
                <tr class="tw-border-b hover:tw-bg-gray-50 tw-transition-colors">
                  <td class="tw-text-right tw-py-2 tw-px-3 tw-font-medium tw-text-gray-700">{{$key}}</td>
                  <td class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-400">×</td>
                  <td class="tw-text-center tw-py-2 tw-px-3 tw-text-gray-700">{{$value ?? 0}}</td>
                  <td class="tw-text-center tw-py-2 tw-px-2 tw-text-gray-400">=</td>
                  <td class="tw-text-left tw-py-2 tw-px-3 tw-font-semibold tw-text-gray-800">
                    @format_currency($key * $value)
                  </td>
                </tr>
                @php
                  $total += ($key * $value);
                @endphp
                @endforeach
              </tbody>
              <tfoot class="tw-bg-primary-50">
                <tr>
                  <th colspan="4" class="tw-text-center tw-py-3 tw-px-3 tw-font-semibold tw-text-gray-800">@lang('sale.total')</th>
                  <td class="tw-py-3 tw-px-3 tw-font-bold tw-text-primary-700 tw-text-lg">@format_currency($total)</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      @endif
      
      <!-- Closing Notes Card -->
      @if(!empty($register_details->closing_note))
        <div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-lg tw-p-4 mini_print">
          <div class="tw-flex tw-items-start">
            <i class="fa fa-sticky-note tw-text-amber-600 tw-mt-1 tw-mr-3"></i>
            <div>
              <p class="tw-text-sm tw-font-semibold tw-text-amber-800 tw-mb-1">@lang('cash_register.closing_note'):</p>
              <p class="tw-text-sm tw-text-amber-900">{{$register_details->closing_note}}</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    <!-- Modern Footer -->
    <div class="modal-footer tw-bg-gray-100 tw-border-0 tw-flex tw-justify-end tw-gap-2">
      <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-primary-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm no-print print-mini-button" 
              aria-label="Print">
        <i class="fa fa-print tw-mr-2"></i> 
        @lang('messages.print_mini')
      </button>

      <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-500 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-gray-600 tw-transition-colors no-print" 
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
  }
</style>

<script>
  $(document).ready(function () {
      $(document).on('click', '.print-mini-button', function () {
          $('.mini_print').printThis();
      });
  });
</script>
