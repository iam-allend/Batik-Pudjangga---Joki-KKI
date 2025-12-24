<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fas fa-store me-2"></i>Batik Pudjangga
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        Home
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('shop.*') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        Shop
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('shop.index') }}">All Products</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('shop.men') }}">
                            <i class="fas fa-male me-2"></i>Men Collection
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('shop.women') }}">
                            <i class="fas fa-female me-2"></i>Women Collection
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('shop.pants') }}">
                            <i class="fas fa-tshirt me-2"></i>Pants
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('shop.oneset') }}">
                            <i class="fas fa-vest me-2"></i>One Set
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('shop.new') }}">
                            <i class="fas fa-star me-2 text-warning"></i>New Arrivals
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('shop.sale') }}">
                            <i class="fas fa-tag me-2 text-danger"></i>Sale
                        </a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                        About
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                        Contact
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <!-- Search -->
                <li class="nav-item">
                    <form action="{{ route('shop.search') }}" method="GET" class="d-flex">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" 
                                   placeholder="Search..." value="{{ request('q') }}"
                                   style="border-radius: 20px 0 0 20px;">
                            <button class="btn btn-primary" type="submit" 
                                    style="border-radius: 0 20px 20px 0;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
                
                @auth
                    <!-- Wishlist -->
                    <li class="nav-item ms-3">
                        <a class="nav-link position-relative" href="{{ route('wishlist.index') }}">
                            <i class="fas fa-heart"></i>
                            @if(auth()->user()->wishlists->count() > 0)
                                <span class="cart-badge">{{ auth()->user()->wishlists->count() }}</span>
                            @endif
                        </a>
                    </li>
                    
                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-badge" style="display: {{ auth()->user()->carts->count() > 0 ? 'flex' : 'none' }};">
                                {{ auth()->user()->carts->count() }}
                            </span>
                        </a>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                <i class="fas fa-shopping-bag me-2"></i>My Orders
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('address.index') }}">
                                <i class="fas fa-map-marker-alt me-2"></i>My Addresses
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            @if(auth()->user()->is_admin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}" target="_blank">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Login/Register -->
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<style>
.dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: none;
    padding: 10px 0;
}

.dropdown-item {
    padding: 10px 20px;
    transition: all 0.3s;
}

.dropdown-item:hover {
    background: var(--light-color);
    color: var(--primary-color);
    padding-left: 25px;
}

.navbar-toggler {
    border: none;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.input-group .form-control {
    border: 1px solid #ddd;
}

.input-group .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}

@media (max-width: 991px) {
    .navbar-nav {
        margin-top: 15px;
    }
    
    .navbar-nav .nav-link {
        padding: 8px 0;
    }
    
    .input-group {
        margin: 10px 0;
    }
}
</style>