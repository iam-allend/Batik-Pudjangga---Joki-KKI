<footer>
    <div class="container">
        <div class="row">
            <!-- About -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-store me-2"></i>Batik Pudjangga
                </h5>
                <p style="color: rgba(255,255,255,0.7);">
                    Premium Indonesian Batik with traditional craftsmanship and modern design. 
                    Discover the beauty of authentic batik patterns.
                </p>
                <div class="social-links mt-4">
                    <a href="#" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Shop</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('shop.men') }}">Men Collection</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shop.women') }}">Women Collection</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shop.pants') }}">Pants</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shop.oneset') }}">One Set</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shop.sale') }}">Sale</a>
                    </li>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Customer Service</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('about') }}">About Us</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('contact') }}">Contact Us</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shipping.info') }}">Shipping Information</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('return.policy') }}">Return Policy</a>
                    </li>
                    @auth
                        <li class="mb-2">
                            <a href="{{ route('orders.index') }}">Track Order</a>
                        </li>
                    @endauth
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Contact Info</h5>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-map-marker-alt me-2" style="color: var(--accent-color);"></i>
                        <span style="color: rgba(255,255,255,0.7);">
                            Pekalongan, Central Java<br>
                            Indonesia
                        </span>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2" style="color: var(--accent-color);"></i>
                        <a href="tel:+6281234567890" style="color: rgba(255,255,255,0.7);">
                            +62 812-3456-7890
                        </a>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-envelope me-2" style="color: var(--accent-color);"></i>
                        <a href="mailto:info@batikpudjangga.com" style="color: rgba(255,255,255,0.7);">
                            info@batikpudjangga.com
                        </a>
                    </li>
                </ul>
                
                <!-- Newsletter -->
                <div class="mt-4">
                    <h6 style="color: var(--accent-color);">Newsletter</h6>
                    <form action="{{ route('subscribe') }}" method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" 
                                   placeholder="Your email" required
                                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <hr style="border-color: rgba(255,255,255,0.1); margin: 40px 0 20px;">
        
        <!-- Copyright -->
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p style="color: rgba(255,255,255,0.5); margin: 0;">
                    &copy; {{ date('Y') }} Batik Pudjangga. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p style="color: rgba(255,255,255,0.5); margin: 0;">
                    Made with <i class="fas fa-heart text-danger"></i> in Indonesia
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary" 
        style="position: fixed; bottom: 30px; right: 30px; display: none; 
               border-radius: 50%; width: 50px; height: 50px; z-index: 999;">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
footer a {
    display: inline-block;
    transition: all 0.3s;
}

footer a:hover {
    color: var(--accent-color) !important;
    transform: translateX(5px);
}

.input-group .form-control::placeholder {
    color: rgba(255,255,255,0.5);
}

.input-group .form-control:focus {
    background: rgba(255,255,255,0.15);
    border-color: var(--accent-color);
    color: white;
    box-shadow: none;
}

#backToTop {
    opacity: 0.7;
    transition: all 0.3s;
}

#backToTop:hover {
    opacity: 1;
    transform: translateY(-5px);
}
</style>

<script>
// Back to Top Button
const backToTop = document.getElementById('backToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        backToTop.style.display = 'block';
    } else {
        backToTop.style.display = 'none';
    }
});

backToTop.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>