<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />

    <!-- Plugins -->
    <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />

    <!-- Loader -->
    <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/js/pace.min.js') }}"></script>

    <!-- Bootstrap -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />

    <!-- App CSS -->
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" />

    <!-- Fonts (offline: system stack) -->
    <style nonce="{{ $cspNonce ?? '' }}">
        body { font-family: Roboto, Arial, Helvetica, sans-serif; }
    </style>

    <title>@yield('title', 'Login')</title>
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        @yield('content')
    </div>

    <!-- Scripts: jQuery MUST load first -->
    <script src="{{ asset('assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script nonce="{{ $cspNonce ?? '' }}">
        $(document).ready(function () {
            $("#show_hide_password a").on('click', function (event) {
                event.preventDefault();
                let input = $('#show_hide_password input');
                let icon = $('#show_hide_password i');
                if (input.attr("type") == "text") {
                    input.attr('type', 'password');
                    icon.addClass("bx-hide").removeClass("bx-show");
                } else {
                    input.attr('type', 'text');
                    icon.removeClass("bx-hide").addClass("bx-show");
                }
            });
        });
    </script>
    @if(session('success'))
    <script nonce="{{ $cspNonce ?? '' }}">
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
    @endif
    <script nonce="{{ $cspNonce ?? '' }}">
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('expired') !== '1') {
                return;
            }

            const cleanUrl = window.location.pathname + (window.location.hash || '');

            Swal.fire({
                icon: 'warning',
                title: @json(__('app.session_expired_title')),
                text: @json(__('app.session_expired')),
                confirmButtonText: @json(__('app.sign_in')),
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(function () {
                window.location.replace(cleanUrl);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
