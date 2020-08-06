@section('header')
<!-- Navbar Start-->
<header class="app-header"><a class="app-header__logo" href="/admin/dashboard">EC Admin</a>
    @php
    $value = session(str_replace(".","_",request()->ip()).'ECGames');
    @endphp

    <!-- Sidebar toggle button-->
    <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
    <!-- Navbar Right Menu-->
    <ul class="app-nav">

        <!-- Search-->
        <li class="app-search">
            <input class="app-search__input" type="search" placeholder="{{__('adminPanel.search')}}">
            <button class="app-search__button"><i class="fa fa-search"></i></button>
        </li>

        <!--Language-->
        <li class="dropdown"><a class="app-nav__item" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-language fa-lg icon-cog" style="color:white"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">
                <li><a class="dropdown-item" href="{{ route('set.language', 'en') }}"><i class="flag-icon flag-icon-us"></i> English</a></li>
                <li><a class="dropdown-item" href="{{ route('set.language', 'zh') }}"><i class="flag-icon flag-icon-cn"></i> 中文</a></li>
                <li><a class="dropdown-item" href="{{ route('set.language', 'th') }}"><i class="flag-icon flag-icon-th"></i> ไทย</a></li>
                <li><a class="dropdown-item" href="{{ route('set.language', 'la') }}"><i class="flag-icon flag-icon-la"></i> ລາວ</a></li>
            </ul>
        </li>

        <!--Notification Menu-->
        {{-- <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i></a>
            <ul class="app-notification dropdown-menu dropdown-menu-right">
                <li class="app-notification__title">You have 4 new notifications.</li>
                <div class="app-notification__content">
                    <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                    <p class="app-notification__message">Lisa sent you a mail</p>
                    <p class="app-notification__meta">2 min ago</p>
                    </div></a></li>
                    <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                    <p class="app-notification__message">Mail server not working</p>
                    <p class="app-notification__meta">5 min ago</p>
                    </div></a></li>
                    <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                    <p class="app-notification__message">Transaction complete</p>
                    <p class="app-notification__meta">2 days ago</p>
                    </div></a></li>
                    <div class="app-notification__content">
                        <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                        <p class="app-notification__message">Lisa sent you a mail</p>
                        <p class="app-notification__meta">2 min ago</p>
                    </div></a></li>
                        <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                        <p class="app-notification__message">Mail server not working</p>
                        <p class="app-notification__meta">5 min ago</p>
                    </div></a></li>
                        <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                        <p class="app-notification__message">Transaction complete</p>
                        <p class="app-notification__meta">2 days ago</p>
                    </div></a></li>
                    </div>
                </div>
                <li class="app-notification__footer"><a href="#">See all notifications.</a></li>
            </ul>
        </li> --}}
        <!-- User Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">
                {{-- <li><a class="dropdown-item" href="#"><i class="fa fa-cog fa-lg"></i> {{__('adminPanel.settings')}}</a></li> --}}
                <li><a class="dropdown-item" href="{!!route('vChangePassword')!!}"><i class="fa fa-key fa-lg"></i> {{__('adminPanel.changePassword')}}</a></li>
                <li><a class="dropdown-item" href="{!!route('vGetProfile')!!}"><i class="fa fa-user fa-lg"></i> {{__('adminPanel.profile')}}</a></li>
                <li><a class="dropdown-item" href="{!!route('vLogout')!!}"><i class="fa fa-sign-out fa-lg"></i> {{__('adminPanel.logOut')}}</a></li>
            </ul>
        </li>
    </ul>
</header>

<style>
    .modal-backdrop {
        position: unset;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: #000;
    }
</style>

<!-- Navbar End-->
@endsection