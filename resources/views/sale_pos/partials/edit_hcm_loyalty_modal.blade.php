
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
