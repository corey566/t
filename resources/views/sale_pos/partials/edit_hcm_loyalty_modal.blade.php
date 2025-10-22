
<div class="modal fade" id="posEditHcmLoyaltyModal" tabindex="-1" role="dialog" aria-labelledby="posEditHcmLoyaltyModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="posEditHcmLoyaltyModalLabel">@lang('lang_v1.edit_hcm_loyalty')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="modal_hcm_loyalty_type">@lang('lang_v1.hcm_loyalty_type'):</label>
                            <select class="form-control" id="modal_hcm_loyalty_type">
                                <option value="fixed">@lang('lang_v1.fixed')</option>
                                <option value="percentage">@lang('lang_v1.percentage')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="modal_hcm_loyalty_amount">@lang('lang_v1.hcm_loyalty_amount'):</label>
                            <input type="text" class="form-control input_number" id="modal_hcm_loyalty_amount" value="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                <button type="button" class="btn btn-primary" id="save_hcm_loyalty">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('HCM Loyalty Discount')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('hcm_loyalty_modal_type', __('sale.discount_type') . ':*') !!}
                        {!! Form::select('hcm_loyalty_modal_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'fixed', ['class' => 'form-control', 'required', 'id' => 'hcm_loyalty_modal_type']); !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('hcm_loyalty_modal_amount', __('sale.discount_amount') . ':*') !!}
                        {!! Form::text('hcm_loyalty_modal_amount', 0, ['class' => 'form-control input_number', 'required', 'id' => 'hcm_loyalty_modal_amount']); !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="hcm_loyalty_modal_update">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
