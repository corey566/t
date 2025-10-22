<!-- Edit discount Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="posEditDiscountModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">@lang('sale.discount')</h4>
			</div>
			<div class="modal-body">
				@php
					$discount_type = $discount_type ?? 'percentage';
					$sales_discount = $sales_discount ?? 0;
					$rp_redeemed = $rp_redeemed ?? 0;
					$rp_redeemed_amount = $rp_redeemed_amount ?? 0;
					$max_available = $max_available ?? 0;
					$hcm_loyalty_amount = $hcm_loyalty_amount ?? 0;
					$is_hcm_enabled = !empty(session('business.enable_hcm_loyalty'));
				@endphp
				
				<!-- Regular Discount Section -->
				<div class="row @if(!$is_discount_enabled) hide @endif">
					<div class="col-md-12">
						<h4 class="modal-title">@lang('sale.edit_discount'):</h4>
					</div>
					<div class="col-md-6">
				        <div class="form-group">
				            {!! Form::label('discount_type_modal', __('sale.discount_type') . ':*' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::select('discount_type_modal', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], $discount_type , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required']); !!}
				            </div>
				        </div>
				    </div>
				    @php
				    	$max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';

				    	//if sale discount is more than user max discount change it to max discount
				    	if($discount_type == 'percentage' && $max_discount != '' && $sales_discount > $max_discount) $sales_discount = $max_discount;
				    @endphp
				    <div class="col-md-6">
				        <div class="form-group">
				            {!! Form::label('discount_amount_modal', __('sale.discount_amount') . ':*' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::text('discount_amount_modal', @num_format($sales_discount), ['class' => 'form-control input_number', 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? @num_format($max_discount) : '']) ]); !!}
				            </div>
				        </div>
				    </div>
				</div>

				<!-- HCM Loyalty Discount Section -->
				@if($is_hcm_enabled)
				<div class="row" style="margin-top: 15px;">
					<div class="col-md-12">
						<div class="well well-sm" style="background-color: #f5f5f5; padding: 15px;">
							<h4 style="margin-top: 0; margin-bottom: 15px; color: #333;">@lang('lang_v1.hcm_loyalty_discount'):</h4>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										{!! Form::label('hcm_loyalty_type_modal', __('lang_v1.discount_type') . ':' ) !!}
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-info"></i>
											</span>
											{!! Form::select('hcm_loyalty_type_modal', ['fixed' => __('lang_v1.fixed')], 'fixed', ['class' => 'form-control', 'readonly' => true, 'disabled' => true]); !!}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										{!! Form::label('hcm_loyalty_amount_modal', __('lang_v1.discount_amount') . ':' ) !!}
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-money"></i>
											</span>
											{!! Form::text('hcm_loyalty_amount_modal', @num_format($hcm_loyalty_amount), ['class' => 'form-control input_number', 'placeholder' => '0']); !!}
										</div>
									</div>
								</div>
							</div>
							<p class="help-block" style="margin-bottom: 0;"><i class="fa fa-info-circle"></i> @lang('lang_v1.hcm_loyalty_discount_help')</p>
						</div>
					</div>
				</div>
				@endif
				<div class="row @if(!$is_rp_enabled) hide @endif" style="margin-top: 15px;">
					<div class="col-md-12">
						<div class="well well-sm" style="background-color: #f0f8ff; padding: 15px;">
							<h4 style="margin-top: 0; margin-bottom: 15px; color: #333;">
								<i class="fa fa-gift"></i> {{session('business.rp_name')}}:
							</h4>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										{!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ) !!}
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-gift"></i>
											</span>
											{!! Form::number('rp_redeemed_modal', $rp_redeemed, ['class' => 'form-control', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'data-max_points' => $max_available, 'min' => 0, 'data-min_order_total' => session('business.min_order_total_for_redeem'), 'placeholder' => '0' ]); !!}
											<input type="hidden" id="rp_name" value="{{session('business.rp_name')}}">
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>@lang('lang_v1.available'):</label>
										<p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #27ae60;">
											<span id="available_rp">{{$max_available}}</span> points
										</p>
									</div>
									<div class="form-group">
										<label>@lang('lang_v1.redeemed_amount'):</label>
										<p class="form-control-static" style="font-size: 16px; font-weight: bold; color: #2980b9;">
											<span id="rp_redeemed_amount_text">{{@num_format($rp_redeemed_amount)}}</span>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="posEditDiscountModalUpdate">@lang('messages.update')</button>
			    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.cancel')</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->