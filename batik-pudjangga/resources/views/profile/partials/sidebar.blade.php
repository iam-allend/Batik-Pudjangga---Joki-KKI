<div class="card">
    <div class="list-group list-group-flush">
        <a href="{{ route('profile.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('profile.index') ? 'active' : '' }}">
            <i class="fas fa-user me-2"></i> My Profile
        </a>
        <a href="{{ route('profile.edit') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="fas fa-edit me-2"></i> Edit Profile
        </a>
        <a href="{{ route('orders.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag me-2"></i> My Orders
        </a>
        <a href="{{ route('address.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('address.*') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt me-2"></i> Addresses
        </a>
        <a href="{{ route('wishlist.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('wishlist.*') ? 'active' : '' }}">
            <i class="fas fa-heart me-2"></i> Wishlist
        </a>
        <a href="{{ route('cart.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('cart.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart me-2"></i> Shopping Cart
        </a>
        <hr class="my-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
        </form>
    </div>
</div>
