
<div class="pos-tab-content">
    <div class="row">
        <div class="col-xs-12">
            <h4>@lang('lang_v1.hcm_loyalty_settings')</h4>
            <p class="help-block">@lang('lang_v1.hcm_loyalty_settings_help')</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('enable_hcm_loyalty', 1, $business->enable_hcm_loyalty, ['class' => 'input-icheck', 'id' => 'enable_hcm_loyalty']); !!}
                        @lang('lang_v1.enable_hcm_loyalty')
                    </label>
                    @show_tooltip(__('lang_v1.enable_hcm_loyalty_help'))
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="hcm_loyalty_locations_section" @if(empty($business->enable_hcm_loyalty)) style="display:none;" @endif>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('hcm_loyalty_locations', __('lang_v1.hcm_loyalty_enabled_locations') . ':') !!}
                @show_tooltip(__('lang_v1.hcm_loyalty_locations_help'))
                {!! Form::select('hcm_loyalty_locations[]', $business_locations, !empty($business->hcm_loyalty_locations) ? json_decode($business->hcm_loyalty_locations) : [], ['class' => 'form-control select2', 'multiple', 'id' => 'hcm_loyalty_locations', 'style' => 'width:100%;']); !!}
            </div>
        </div>
    </div>
    <div class="row" id="hcm_loyalty_api_section" @if(empty($business->enable_hcm_loyalty)) style="display:none;" @endif>
        <div class="col-sm-12">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> @lang('lang_v1.hcm_loyalty_api_config_info')
                <br>
                <a href="{{ action([\Modules\Gallface\Http\Controllers\GallfaceController::class, 'hcmCredentials']) }}" class="btn btn-sm btn-primary" style="margin-top: 10px;">
                    <i class="fa fa-cog"></i> @lang('lang_v1.configure_hcm_api_credentials')
                </a>
            </div>
        </div>
    </div>
    <div class="row" id="hcm_loyalty_discount_section" @if(empty($business->enable_hcm_loyalty)) style="display:none;" @endif>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.hcm_loyalty_discount_behavior')</h4>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('hcm_loyalty_independent_discount', 1, !empty($business->hcm_loyalty_independent_discount) ? $business->hcm_loyalty_independent_discount : 0, ['class' => 'input-icheck']); !!}
                        @lang('lang_v1.hcm_loyalty_independent_discount')
                    </label>
                    @show_tooltip(__('lang_v1.hcm_loyalty_independent_discount_help'))
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Toggle HCM loyalty sections
        $('#enable_hcm_loyalty').on('ifChanged', function() {
            if ($(this).is(':checked')) {
                $('#hcm_loyalty_locations_section').slideDown();
                $('#hcm_loyalty_api_section').slideDown();
                $('#hcm_loyalty_discount_section').slideDown();
            } else {
                $('#hcm_loyalty_locations_section').slideUp();
                $('#hcm_loyalty_api_section').slideUp();
                $('#hcm_loyalty_discount_section').slideUp();
            }
        });
    });
</script>
