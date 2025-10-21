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
                <a class="navbar-brand" href="{{action([\Modules\Gallface\Http\Controllers\GallfaceController::class, 'dashboard'])}}"> Gallface</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="{{ url('gallface/setting') }}" class="nav-link {{ request()->is('gallface/setting') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>One Gallface Mall</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('gallface/hcm/credentials') }}" class="nav-link {{ request()->is('gallface/hcm/credentials') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Havek Con</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/gallface/integra/credentials') }}" class="nav-link {{ request()->is('gallface/integra*') ? 'active' : '' }}">
                            <i class="fas fa-city"></i>
                            <span>Colombo City Center (Integra)</span>
                        </a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
<li class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; display: flex; align-items: center; gap: 10px;">
    <img src="{{ asset('attached_assets/unnamed-removebg-preview_1761014565294.png') }}" alt="One Gallface" style="height: 40px; width: 40px;">
    <span style="font-weight: 600; font-size: 1.1rem;">One Gallface Integration</span>
</li>
