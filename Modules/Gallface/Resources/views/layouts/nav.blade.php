<section class="no-print">
    <nav class="navbar-default tw-transition-all tw-duration-5000 tw-shrink-0 tw-rounded-2xl tw-m-[16px] tw-border-2 !tw-bg-white">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false" style="margin-top: 3px; margin-right: 3px;">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action([\Modules\Gallface\Http\Controllers\GallfaceController::class, 'dashboard'])}}" style="display: flex; align-items: center; gap: 10px;">
                    <img src="{{ asset('modules/gallface/images/one-gallface-logo.png') }}" alt="Mall Integrations" style="height: 30px; width: 30px;">
                    <span>Mall Integrations</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="{{ url('gallface/setting') }}" class="nav-link {{ request()->is('gallface/setting') ? 'active' : '' }}" style="display: flex; align-items: center; gap: 8px;">
                            <img src="{{ asset('modules/gallface/images/one-gallface-logo.png') }}" alt="One Gallface" style="height: 24px; width: 24px;">
                            <span>One Gallface Mall</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('gallface/hcm/credentials') }}" class="nav-link {{ request()->is('gallface/hcm/credentials') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <span>Havelock City Mall</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/gallface/integra/credentials') }}" class="nav-link {{ request()->is('gallface/integra*') ? 'active' : '' }}">
                            <i class="fas fa-city"></i>
                            <span>Colombo City Center</span>
                        </a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
