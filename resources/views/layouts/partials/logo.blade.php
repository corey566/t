<div class="row text-center">
	@if(file_exists(public_path('uploads/logo.png')))
		<div class="col-xs-12">
			<img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150" style="margin-bottom: 30px;">
		</div>
	@else
    	<div class="col-xs-12" style="margin-bottom: 30px;">
    		<img src="{{ asset('modules/gallface/images/one-gallface-logo.png') }}" alt="Logo" style="height: 60px;">
    	</div>
    @endif
</div>