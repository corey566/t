<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <div class="modal-header mini_print">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h3 class="modal-title">@lang( 'cash_register.register_details' ) ( {{ \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A') }} -  {{\Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A')}} )</h3>
    </div>

    <div class="modal-body">
      <div class="row mini_print">
        <div class="col-xs-12">
          <p><strong>@lang('report.user'):</strong> {{ $register_details->user_name}}</p>
          <p><strong>@lang('business.email'):</strong> {{ $register_details->email}}</p>
          <p><strong>@lang('business.business_location'):</strong> {{ $register_details->location_name}}</p>
        </div>
      </div>
      <hr>
      @include('cash_register.payment_details')
      <hr>
      @if(!empty($register_details->denominations))
        @php
          $total = 0;
        @endphp
        <div class="row">
          <div class="col-md-8 col-sm-12">
            <h3>@lang( 'lang_v1.cash_denominations' )</h3>
            <table class="table table-slim">
              <thead>
                <tr>
                  <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                  <th width="20%">&nbsp;</th>
                  <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                  <th width="20%">&nbsp;</th>
                  <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                </tr>
              </thead>
              <tbody>
                @foreach($register_details->denominations as $key => $value)
                <tr>
                  <td class="text-right">{{$key}}</td>
                  <td class="text-center">X</td>
                  <td class="text-center">{{$value ?? 0}}</td>
                  <td class="text-center">=</td>
                  <td class="text-left">
                    @format_currency($key * $value)
                  </td>
                </tr>
                @php
                  $total += ($key * $value);
                @endphp
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="4" class="text-center">@lang('sale.total')</th>
                  <td>@format_currency($total)</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      @endif
      
      @if(!empty($register_details->closing_note))
        <div class="row mini_print">
          <div class="col-xs-12">
            <strong>@lang('cash_register.closing_note'):</strong><br>
            {{$register_details->closing_note}}
          </div>
        </div>
      @endif
    </div>

    <div class="modal-footer">
  <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print print-mini-button" 
          aria-label="Print">
      <i class="fa fa-print"></i> @lang('messages.print_mini')
  </button>

      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" 
        data-dismiss="modal">@lang( 'messages.cancel' )
      </button>
    </div>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
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
}
</style>
<script>
  $(document).ready(function () {
      $(document).on('click', '.print-mini-button', function () {
          $('.mini_print').printThis();
      });
  });
</script>