@extends('layouts.auth')

@section('title', \App\Services\SystemSettingService::get('app_name', 'SmartAccounting') . ' – ' . __('app.login_by_pin'))

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-4 my-lg-0 pin-login-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10 col-xxl-9">
                    <div class="mb-3 text-center">
                        @include('incs.languageSwitcher')
                    </div>

                    <div class="card rounded-4 border-0 shadow-lg pin-login-card overflow-hidden">
                        <div class="row g-0">
                            {{-- Left: login display --}}
                            <div class="col-lg-5 pin-login-left">
                                <div class="p-4 p-lg-5 h-100 d-flex flex-column justify-content-center">
                                    <div class="text-center mb-4">
                                        <img src="{{ asset('assets/images/logo1.png') }}" width="160" alt="" class="mb-3" />
                                        <h4 class="fw-bold mb-1">{{ __('app.login_by_pin') }}</h4>
                                        <p class="text-muted mb-0">{{ __('app.pin_login_help') }}</p>
                                    </div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger py-2 text-center">
                                            {{ $errors->first() }}
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('login.pin.submit') }}" id="pinLoginForm">
                                        @csrf
                                        <input type="hidden" name="pin" id="pin" value="{{ old('pin') }}">

                                        <div class="pin-display-box text-center mb-4">
                                            <div class="pin-dots" id="pinDots" aria-live="polite">
                                                <span class="pin-dot"></span>
                                                <span class="pin-dot"></span>
                                                <span class="pin-dot"></span>
                                                <span class="pin-dot"></span>
                                            </div>
                                            <small class="text-muted">{{ __('app.enter_pin') }}</small>
                                        </div>

                                        <div class="text-center">
                                            <a href="{{ route('login.phone') }}" class="text-decoration-none small">
                                                {{ __('app.login_with_phone_password') }}
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Right: numeric keypad --}}
                            <div class="col-lg-7 pin-login-right">
                                <div class="p-4 p-lg-5 h-100 d-flex flex-column justify-content-center">
                                    <div class="pin-keypad mx-auto">
                                        <div class="row g-3">
                                            @foreach(['1','2','3','4','5','6','7','8','9'] as $digit)
                                            <div class="col-4">
                                                <button type="button" class="btn pin-key" data-digit="{{ $digit }}">{{ $digit }}</button>
                                            </div>
                                            @endforeach
                                            <div class="col-4">
                                                <button type="button" class="btn pin-key pin-key-clear" id="pinClear">
                                                    <i class="bx bx-eraser"></i>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn pin-key" data-digit="0">0</button>
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn pin-key pin-key-enter" id="pinEnter">
                                                    <i class="bx bx-right-arrow-alt me-1"></i> {{ __('app.sign_in') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .pin-login-page {
        min-height: calc(100vh - 2rem);
    }

    .pin-login-left {
        background: linear-gradient(160deg, #f8f9fc 0%, #eef2ff 100%);
        border-right: 1px solid rgba(0, 0, 0, 0.06);
    }

    .pin-login-right {
        background: #fff;
    }

    .pin-display-box {
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 1rem;
        padding: 1.75rem 1rem;
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .pin-dots {
        display: flex;
        justify-content: center;
        gap: 1.25rem;
        min-height: 2rem;
        margin-bottom: 0.5rem;
    }

    .pin-dot {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        border: 2px solid #adb5bd;
        background: transparent;
        transition: all 0.15s ease;
    }

    .pin-dot.filled {
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    }

    .pin-dot.error {
        border-color: #dc3545;
        background: #dc3545;
        animation: pin-shake 0.4s ease;
    }

    @keyframes pin-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        75% { transform: translateX(6px); }
    }

    .pin-keypad {
        width: 100%;
        max-width: 420px;
    }

    .pin-key {
        width: 100%;
        min-height: 72px;
        font-size: 1.75rem;
        font-weight: 600;
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #212529;
        box-shadow: 0 4px 0 #dee2e6;
        transition: transform 0.08s ease, box-shadow 0.08s ease, background 0.15s ease;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    .pin-key:hover {
        background: #e9ecef;
    }

    .pin-key:active {
        transform: translateY(3px);
        box-shadow: 0 1px 0 #dee2e6;
    }

    .pin-key-clear {
        font-size: 1.5rem;
        color: #6c757d;
    }

    .pin-key-enter {
        font-size: 1.1rem;
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        box-shadow: 0 4px 0 #0a58ca;
    }

    .pin-key-enter:hover {
        background: #0b5ed7;
        color: #fff;
    }

    .pin-key-enter:active {
        box-shadow: 0 1px 0 #0a58ca;
    }

    .pin-key-enter:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
        box-shadow: 0 4px 0 #0a58ca;
    }

    @media (max-width: 991.98px) {
        .pin-login-left {
            border-right: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .pin-key {
            min-height: 64px;
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    const maxLength = 4;
    let pinValue = document.getElementById('pin').value.replace(/\D/g, '').slice(0, maxLength);
    const pinInput = document.getElementById('pin');
    const pinDots = document.querySelectorAll('.pin-dot');
    const pinForm = document.getElementById('pinLoginForm');
    const enterBtn = document.getElementById('pinEnter');
    const clearBtn = document.getElementById('pinClear');

    let isSubmitting = false;

    function updateDisplay() {
        pinInput.value = pinValue;
        pinDots.forEach(function (dot, index) {
            dot.classList.toggle('filled', index < pinValue.length);
            dot.classList.remove('error');
        });
        if (enterBtn) {
            enterBtn.disabled = pinValue.length !== maxLength || isSubmitting;
        }
    }

    function appendDigit(digit) {
        if (pinValue.length >= maxLength || isSubmitting) return;
        pinValue += digit;
        updateDisplay();
        if (pinValue.length === maxLength) {
            submitPin();
        }
    }

    function clearPin() {
        pinValue = '';
        updateDisplay();
    }

    function backspacePin() {
        pinValue = pinValue.slice(0, -1);
        updateDisplay();
    }

    function submitPin() {
        if (pinValue.length !== maxLength || isSubmitting) return;
        isSubmitting = true;
        if (enterBtn) {
            enterBtn.disabled = true;
        }
        pinForm.submit();
    }

    document.querySelectorAll('.pin-key[data-digit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            appendDigit(this.getAttribute('data-digit'));
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (pinValue.length > 0) {
                backspacePin();
            } else {
                clearPin();
            }
        });
    }

    if (enterBtn) {
        enterBtn.addEventListener('click', submitPin);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key >= '0' && e.key <= '9') {
            appendDigit(e.key);
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            backspacePin();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            submitPin();
        }
    });

    @if ($errors->any())
    pinDots.forEach(function (dot) {
        dot.classList.add('error');
    });
    pinValue = '';
    updateDisplay();
    @endif

    updateDisplay();
});
</script>
@endpush
