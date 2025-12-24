<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Batik Pudjangga - Premium Indonesian Batik')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
            --accent-color: #CD853F;
            --dark-color: #2c2c2c;
            --light-color: #f8f9fa;
            --gold-color: #DAA520;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            background: transparent;
            padding: 10px 30px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .back-to-top:hover {
            background: var(--secondary-color);
            transform: translateY(-5px);
        }
        
        .back-to-top.show {
            display: flex;
        }
        
        /* Alert Messages */
        .alert {
            border: none;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Product Card Hover Effect */
        .product-card {
            transition: all 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            position: relative;
            overflow: hidden;
            padding-top: 100%;
        }
        
        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        /* Badge Styles */
        .badge-new {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }
        
        .badge-sale {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
            }
        }


        .page-header {
            background: linear-gradient(135deg, var(--light-color) 0%, #fff 100%);
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item a {
            color: var(--text-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 100px;
        }

        .filter-group {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .filter-group h6 {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .form-check {
            margin-bottom: 10px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Empty State */
        .empty-state {
            background: white;
            padding: 80px 20px;
            border-radius: 15px;
        }

        .empty-state i {
            opacity: 0.3;
        }

        /* Pagination */
        .pagination {
            justify-content: center;
        }

        .page-link {
            color: var(--primary-color);
            border: 1px solid #ddd;
            margin: 0 3px;
            border-radius: 5px;
        }

        .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 991px) {
            .filters-sidebar {
                position: static;
                margin-bottom: 30px;
            }
        }

    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <!-- Navigation -->
    @include('layouts.partials.navbar')
    
    <!-- Main Content -->
    <main>
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @if(session('info'))
            <div class="container mt-4">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @if($errors->any())
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Whoops!</strong> There were some problems with your input.
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('layouts.partials.footer')
    
    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // CSRF Token Setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Auto dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Back to Top Button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#backToTop').addClass('show');
            } else {
                $('#backToTop').removeClass('show');
            }
        });
        
        $('#backToTop').click(function() {
            $('html, body').animate({scrollTop: 0}, 600);
            return false;
        });
        
        // Loading Overlay Functions
        window.showLoading = function() {
            $('#loadingOverlay').addClass('show');
        }
        
        window.hideLoading = function() {
            $('#loadingOverlay').removeClass('show');
        }
        
        // Add to Cart Function (AJAX)
        window.addToCart = function(productId, size = null, notes = null) {
            showLoading();
            
            $.ajax({
                url: '/api/cart/add',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: 1,
                    size: size,
                    notes: notes
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        // Update cart count in navbar
                        updateCartCount();
                        // Show success message
                        showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    let message = 'Failed to add to cart';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('error', message);
                }
            });
        }
        
        // Toggle Wishlist Function (AJAX)
        window.toggleWishlist = function(productId, element) {
            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                window.location.href = '/login';
                return;
            }
            
            $.ajax({
                url: '/api/wishlist/toggle/' + productId,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        $(element).toggleClass('active');
                        if (response.data.in_wishlist) {
                            $(element).html('<i class="fas fa-heart"></i>');
                        } else {
                            $(element).html('<i class="far fa-heart"></i>');
                        }
                        showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Failed to update wishlist');
                }
            });
        }
        
        // Update Cart Count
        function updateCartCount() {
            $.ajax({
                url: '/api/cart/count',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#cartCount').text(response.data.count);
                    }
                }
            });
        }
        
        // Toast Notification
        function showToast(type, message) {
            const bgColor = type === 'success' ? '#28a745' : '#dc3545';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const toast = $(`
                <div class="toast-notification" style="position: fixed; top: 80px; right: 20px; background: ${bgColor}; color: white; padding: 15px 20px; border-radius: 8px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); animation: slideInRight 0.3s ease;">
                    <i class="fas ${icon} me-2"></i>${message}
                </div>
            `);
            
            $('body').append(toast);
            
            setTimeout(function() {
                toast.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // Format Currency
        window.formatCurrency = function(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }
        
        // Update cart count on page load
        $(document).ready(function() {
            @auth
                updateCartCount();
            @endauth
        });
    </script>
    
    @stack('scripts')
</body>
</html>