<div class="scroll-sidebar">
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav">
        <ul id="sidebarnav" class="pt-3">
            <!-- User Profile-->
            <li class="sidebar-item visible-xs">
                <a class="sidebar-link waves-effect waves-dark sidebar-link {{ ((request()->routeIs('myprofile'))?'active':'') }}" href="{{ route('myprofile') }}"
                    aria-expanded="false">
                    <span class="menu-icon-badge">
                        <i class="fa fa-user" aria-hidden="true"></i>
                    </span>
                    <span class="hide-menu">{{ __('Mi Perfil') }}</span>
                </a>
            </li>
            
            @if(Auth::user()->hasRole('Admin'))
            <li class="sidebar-item">
                <a class="sidebar-link waves-effect waves-dark sidebar-link {{ ((request()->routeIs('dashboard'))?'active':'') }}" href="{{ route('dashboard') }}"
                    aria-expanded="false">
                    <span class="menu-icon-badge">
                        <i class="fas fa-th-large" aria-hidden="true"></i>
                    </span>
                    <span class="hide-menu">{{ __('Dashboard') }}</span>
                </a>
            </li>
            @endif
            
            @foreach ($MenuSistema as $item)
                @if (isset($item['submenu']))
                    @can($item['can'])
                    @php($submenuIsActive = collect($item['submenu'])->contains(function ($subitem) { return request()->routeIs($subitem['route']); }))
                    <li class="sidebar-item {{ $submenuIsActive ? 'selected' : '' }}">
                        <a class="has-arrow sidebar-link waves-effect waves-dark sidebar-link {{ $submenuIsActive ? 'active' : '' }}" href="#"
                            aria-expanded="{{ $submenuIsActive ? 'true' : 'false' }}">
                            <span class="menu-icon-badge">
                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}" aria-hidden="true"></i>
                            </span>
                            <span class="hide-menu">{{ __($item['title']) }}</span>
                            <span class="submenu-arrow" aria-hidden="true">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </a>
                        <ul aria-expanded="{{ $submenuIsActive ? 'true' : 'false' }}" class="collapse first-level {{ $submenuIsActive ? 'show' : '' }}">
                            @foreach ($item['submenu'] as $subitem)
                                @can($subitem['can'])
                                    <li  class="sidebar-item ">
                                        <a class="sidebar-link {{ ((request()->routeIs($subitem['route']))?'active':'') }}" href="{{ route($subitem['route']) }}"
                                        aria-expanded="false">
                                            <span class="submenu-icon-badge">
                                                <i class="{{ $subitem['icon'] ?? 'fas fa-circle' }}" aria-hidden="true"></i>
                                            </span>
                                            <span class="hide-menu">{{ __($subitem['title']) }}</span>
                                        </a>
                                    </li>
                                @endcan
                            @endforeach
                        </ul>
                    </li>
                    @endcan
                @else
                    @can($item['can'])
                    <li class="sidebar-item">
                        <a class=" sidebar-link waves-effect waves-dark sidebar-link {{ ((request()->routeIs($item['route']))?'active':'') }}" href="{{ route($item['route']) }}"
                            aria-expanded="false">
                            <span class="menu-icon-badge">
                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}" aria-hidden="true"></i>
                            </span>
                            @if ($item['route']=='clients.index' && Auth::user()->hasRole('Cobrador'))
                                <span class="hide-menu">Mis {{ __($item['title']) }}</span>
                            @else
                                <span class="hide-menu">{{ __($item['title']) }}</span>
                            @endif
                        </a>
                    </li>
                    @endcan
                @endif
            @endforeach

            

            <li class="sidebar-item visible-xs">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="http://127.0.0.1:8000/logout" onclick="event.preventDefault();
                                    this.closest('form').submit();"><span class="menu-icon-badge"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></span>
                        Logout</a>
                </form>
            </li>
        </ul>
    </nav>
    <!-- End Sidebar navigation -->
    </div>
