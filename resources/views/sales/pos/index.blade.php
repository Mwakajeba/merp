@extends('layouts.main')

@section('title', 'Point of Sale')

@push('head-meta')
<meta http-equiv="Permissions-Policy" content="camera=(self), microphone=(), geolocation=()">
@endpush

@push('styles')
<style>
.product-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.product-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-card-clickable {
    cursor: pointer;
}

.product-card-disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.product-card-disabled:hover {
    border-color: transparent;
    transform: none;
    box-shadow: none;
}

.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn {
    border-radius: 8px;
}

.form-control, .form-select {
    border-radius: 8px;
}

/* Custom scrollbar for products container */
.products-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.products-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.products-scroll-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.products-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Firefox scrollbar */
.products-scroll-container {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

/* Custom scrollbar for customer modal */
.customer-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.customer-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.customer-scroll-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.customer-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Firefox scrollbar for customer modal */
.customer-scroll-container {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

/* Customer item hover effect */
.customer-item:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

/* QR Scanner Styles */
#qr-reader {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

#qr-reader video {
    width: 100%;
    height: auto;
}

#qr-reader__dashboard {
    padding: 10px;
    background-color: #f8f9fa;
}

.pos-panel-tabs .nav-link {
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
}

.today-bill-card {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.today-bill-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Sales', 'url' => route('sales.index'), 'icon' => 'bx bx-store'],
            ['label' => 'Point of Sale', 'url' => '#', 'icon' => 'bx bx-cart']
        ]" />

        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Point of Sale</h4>
                    @if(($posSaleMode ?? 'direct') !== 'bill')
                    @can('access pos list')
                    <a href="{{ route('sales.pos.list') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-list-ul me-1"></i> POS Sales List
                    </a>
                    @endcan
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Product Grid -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
                            @if(($posSaleMode ?? 'direct') === 'bill')
                            <ul class="nav nav-pills pos-panel-tabs mb-0" id="productsPanelTabs">
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" id="tabProducts" data-panel="products">
                                        <i class="bx bx-package me-1"></i> Products
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" id="tabTodayBills" data-panel="today-bills">
                                        <i class="bx bx-receipt me-1"></i> My Today's Bills
                                        <span class="badge bg-primary ms-1" id="todayBillsCount">0</span>
                                    </button>
                                </li>
                            </ul>
                            @else
                            <h5 class="mb-0">Products</h5>
                            @endif
                            <div class="d-flex gap-2 align-items-center" id="productFilters">
                                <div class="position-relative">
                                    <button type="button" class="btn btn-sm btn-primary" id="btnScanQR" title="Scan QR Code with Camera">
                                        <i class="bx bx-qr-scan me-1"></i> Scan QR
                                    </button>
                                    <input type="text" id="qrScannerInput" class="form-control form-control-sm d-none" placeholder="Or type/paste QR data..." style="width: 200px;" autocomplete="off">
                                </div>
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search products..." style="width: 200px;">
                                <select id="categoryFilter" class="form-control form-control-sm" style="width: 150px;">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="productsPanel">
                        <div class="products-scroll-container" style="height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px;">
                            <div class="row" id="productGrid"></div>
                            <div id="productGridLoading" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading products...</p>
                            </div>
                            <div id="productGridEmpty" class="text-center py-4 d-none">
                                <i class="bx bx-package fs-1 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">No products found</p>
                            </div>
                            <div id="productGridLoadMore" class="text-center py-2 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading more...</span>
                                </div>
                            </div>
                        </div>
                        </div>
                        @if(($posSaleMode ?? 'direct') === 'bill')
                        <div id="todayBillsPanel" class="d-none">
                            <div class="products-scroll-container" style="height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px;">
                                <div id="todayBillsLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2 mb-0">Loading today's bills...</p>
                                </div>
                                <div id="todayBillsList" class="row g-2"></div>
                                <div id="todayBillsEmpty" class="text-center py-4 d-none">
                                    <i class="bx bx-receipt fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">No bills created today</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Cart</h5>
                    </div>
                    <div class="card-body">
                        <!-- Date Field -->
                        <div class="mb-3">
                            <label for="saleDate" class="form-label">Sale Date</label>
                            <input type="datetime-local" class="form-control bg-light" id="saleDate" value="{{ now()->format('Y-m-d\TH:i') }}" readonly>
                        </div>

                        <!-- Currency Fields (hidden — defaults to functional currency) -->
                        <div class="row mb-3 d-none" aria-hidden="true">
                            <div class="col-md-6">
                                @php
                                    $functionalCurrency = \App\Models\SystemSetting::getValue('functional_currency', auth()->user()->company->functional_currency ?? 'TZS');
                                    $currencies = \App\Models\Currency::where('company_id', auth()->user()->company_id)
                                        ->where('is_active', true)
                                        ->orderBy('currency_code')
                                        ->get();
                                    
                                    // Fallback to API currencies if database is empty
                                    if ($currencies->isEmpty()) {
                                        $supportedCurrencies = app(\App\Services\ExchangeRateService::class)->getSupportedCurrencies();
                                        $currencies = collect($supportedCurrencies)->map(function($name, $code) {
                                            return (object)['currency_code' => $code, 'currency_name' => $name];
                                        });
                                    }
                                @endphp
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select select2-single" id="currency">
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->currency_code }}" 
                                                {{ $functionalCurrency == $currency->currency_code ? 'selected' : '' }}>
                                            {{ $currency->currency_name ?? $currency->currency_code }} ({{ $currency->currency_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="exchange_rate" class="form-label">Exchange Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="exchange_rate" value="1.000000" step="0.000001" min="0.000001" placeholder="1.000000">
                                    <button type="button" class="btn btn-outline-secondary" id="fetch-rate-btn">
                                        <i class="bx bx-refresh"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Rate relative to functional currency</small>
                                <div id="rate-info" class="mt-1" style="display: none;">
                                    <small class="text-info">
                                        <i class="bx bx-info-circle"></i>
                                        <span id="rate-source">Rate fetched from API</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectWalkInCustomer()">
                                    Walk-in Customer
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showCustomerModal()">
                                    Select Customer
                                </button>
                            </div>
                            <div class="mt-2 {{ ($posSaleMode ?? 'direct') === 'bill' ? '' : 'd-none' }}" id="billCustomerNameWrap">
                                <label for="billCustomerName" class="form-label small mb-1">Table / Customer Name</label>
                                <input type="text" class="form-control form-control-sm" id="billCustomerName"
                                    value="{{ ($posSaleMode ?? 'direct') === 'bill' ? ($defaultBillCustomerName ?? auth()->user()->name) : '' }}"
                                    placeholder="Defaults to logged-in user: {{ auth()->user()->name }}">
                                <small class="text-muted">Leave as your name or change to table number / guest name.</small>
                            </div>
                            <div class="mt-2">
                                <div class="alert alert-info py-2" id="selectedCustomerDisplay">
                                    <small><strong>Selected:</strong> <span id="selectedCustomerText">{{ ($posSaleMode ?? 'direct') === 'bill' ? ($defaultBillCustomerName ?? auth()->user()->name) : 'Walk-in Customer' }}</span></small>
                                </div>
                            </div>
                            <input type="hidden" id="selectedCustomerId" value="0">
                            <input type="hidden" id="selectedCustomerName" value="{{ ($posSaleMode ?? 'direct') === 'bill' ? ($defaultBillCustomerName ?? auth()->user()->name) : 'Walk-in Customer' }}">
                        </div>

                        <!-- Cart Items -->
                        <div class="cart-items mb-3" id="cartItems">
                            <div class="text-muted text-center py-3">No items in cart</div>
                        </div>

                        <!-- Cart Totals -->
                        <div class="cart-totals mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="subtotal">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span id="vatLabel">VAT:</span>
                                <span id="vatAmount">0</span>
                            </div>
                            
                            <!-- Cart Level Discount -->
                            <div class="mb-3">
                                <label for="cartDiscountType" class="form-label">Discount</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-control form-control-sm" id="cartDiscountType" onchange="toggleCartDiscount()" style="width: 120px;">
                                        <option value="none">No Discount</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                    <input type="number" class="form-control form-control-sm" id="cartDiscountRate" min="0" step="0.01" value="0" onchange="updateCartTotals()" style="width: 100px;" placeholder="Rate">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <span>Discount:</span>
                                <span id="discountAmount">0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span id="totalAmount">0</span>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="payment-section {{ ($posSaleMode ?? 'direct') === 'bill' ? 'd-none' : '' }}" id="paymentSection">
                            <h6>Payment</h6>
                            
                            <!-- Paid From -->
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Paid From <span class="text-danger">*</span></label>
                                <select class="form-select" id="paymentMethod" required>
                                    @foreach($bankAccounts as $bank)
                                        <option value="{{ $bank->id }}" data-account-id="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="bankAccountId" name="bank_account_id">
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label for="amountPaid" class="form-label">Amount Paid (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amountPaid" name="bank_amount" step="0.01" required>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="roundToHundreds" onchange="updateAmountPaid()">
                                    <label class="form-check-label" for="roundToHundreds">
                                        Round to nearest hundred
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" id="completeSaleBtn" onclick="processSale()">
                                    <i class="bx bx-check"></i>
                                    {{ ($posSaleMode ?? 'direct') === 'bill' ? 'Create Bill' : 'Complete Sale' }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearCart()">
                                    <i class="bx bx-refresh"></i> Clear Cart
                                </button>
                            </div>
                        </div>
                        @if(($posSaleMode ?? 'direct') === 'bill')
                        <div class="d-grid gap-2 mt-2" id="billActionSection">
                            <button type="button" class="btn btn-success btn-lg" onclick="processSale()">
                                <i class="bx bx-receipt"></i> Create Bill
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-qr-scan me-2"></i>Scan QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeQrScanner"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto; min-height: 300px;"></div>
                <div id="qr-reader-results" class="mt-3"></div>
                <div id="camera-error-message" class="alert alert-warning d-none" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    <div>
                        <strong>Camera not available.</strong>
                        <p class="mb-2 small">Camera access was denied or not available. Please:</p>
                        <ul class="small mb-2">
                            <li>Check browser permissions (click the lock icon in address bar)</li>
                            <li>Ensure you're using HTTPS or localhost</li>
                            <li>Refresh the page after granting permissions</li>
                        </ul>
                        <button type="button" class="btn btn-sm btn-primary" id="requestCameraPermission">
                            <i class="bx bx-camera me-1"></i> Request Camera Permission
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="stopQrScanner">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" id="modalItemName" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Available Stock</label>
                    <input type="text" class="form-control" id="modalAvailableStock" readonly>
                </div>
                <div class="mb-3">
                    <label for="modalQuantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="modalQuantity" min="0.01" step="0.01" value="1" max="999999" onchange="validateQuantity()">
                    <small class="text-muted">Maximum available: <span id="maxQuantity">0</span> units</small>
                </div>
                <div class="mb-3">
                    <label for="modalUnitPrice" class="form-label">Unit Price (TZS)</label>
                    <input type="number" class="form-control" id="modalUnitPrice" min="0" step="100">
                </div>
                <div class="mb-3" id="modalPriceTierRow" style="display: none;">
                    <label for="modal_price_tier_pos" class="form-label">Price type</label>
                    <select class="form-control" id="modal_price_tier_pos">
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                    </select>
                </div>
                <input type="hidden" id="modalVatType" value="no_vat">
                <input type="hidden" id="modalVatRate" value="0">
                <div class="mb-3">
                    <label class="form-label">Line Total</label>
                    <input type="text" class="form-control" id="modalLineTotal" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addItemToCart()" data-processing-text="Adding...">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Selection Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="customerSearch" placeholder="Search customers...">
                </div>
                <div class="customer-scroll-container" style="height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px;">
                    <div id="customerList">
                        <!-- Customer list will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script nonce="{{ $cspNonce ?? '' }}">
let cart = [];
let currentItem = null;
const posSaleMode = @json($posSaleMode ?? 'direct');
const posAutoPrintReceipt = @json($posAutoPrintReceipt ?? true);
const defaultBillCustomerName = @json($defaultBillCustomerName ?? auth()->user()->name ?? 'Walk-in Customer');

function getBillCustomerName() {
    const input = document.getElementById('billCustomerName');
    const value = (input?.value || '').trim();
    return value || defaultBillCustomerName;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    
    // Initialize Select2 for currency dropdown
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('#currency').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
});

// POS product grid (server-side pagination)
const posProductsConfig = {
    url: @json(route('sales.pos.products')),
    findByCodeUrl: @json(route('sales.pos.products.by-code')),
    defaultVatType: @json($defaultVatType),
    defaultVatRate: {{ $defaultVatRate }},
    perPage: 48,
};

let posProductsState = {
    page: 1,
    hasMore: true,
    loading: false,
    search: '',
    category: '',
    searchTimer: null,
};

const posProductCache = new Map();

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatExpiryBadge(expiryDate) {
    if (!expiryDate) {
        return '';
    }

    const expiry = new Date(expiryDate + 'T00:00:00');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const daysUntilExpiry = Math.round((expiry - today) / (1000 * 60 * 60 * 24));

    let badgeClass = 'bg-info';
    if (daysUntilExpiry < 0) {
        badgeClass = 'bg-danger';
    } else if (daysUntilExpiry <= 30) {
        badgeClass = 'bg-warning';
    }

    const formatted = expiry.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    return `<div class="mt-1">
        <span class="badge ${badgeClass}" style="font-size: 0.65rem;" title="Earliest expiry date">
            <i class="bx bx-calendar me-1"></i>${formatted}
        </span>
    </div>`;
}

function openPosProductModal(product) {
    showItemModal(
        product.id,
        product.name,
        product.unit_price,
        product.current_stock,
        product.vat_type,
        product.vat_rate,
        product.item_type,
        product.track_stock,
        product.has_wholesale,
        product.wholesale_unit_price || 0
    );
}

function buildProductCard(product) {
    if (!product.is_out_of_stock) {
        posProductCache.set(product.id, product);
    }

    const isService = product.item_type === 'service' || !product.track_stock;
    const stockHtml = isService
        ? '<small class="text-muted" style="font-size: 0.65rem;">Service</small>'
        : (product.is_out_of_stock
            ? '<small class="text-danger fw-semibold" style="font-size: 0.65rem;">Out of stock</small>'
            : `<small class="text-muted" style="font-size: 0.65rem;">Stock: ${product.current_stock}</small>`);

    const cardClass = product.is_out_of_stock ? 'product-card-disabled' : 'product-card-clickable';

    const categoryHtml = product.category
        ? `<div class="mt-1"><small class="text-muted" style="font-size: 0.65rem;">${escapeHtml(product.category)}</small></div>`
        : '';

    return `<div class="col-6 col-md-4 col-lg-2 col-xl-2 mb-3 product-item"
        data-id="${product.id}"
        data-name="${escapeHtml((product.name || '').toLowerCase())}"
        data-code="${escapeHtml((product.code || '').toLowerCase())}"
        data-category="${escapeHtml(product.category)}">
        <div class="card h-100 product-card ${cardClass}">
            <div class="card-body text-center p-2">
                <div class="mb-1"><i class="bx bx-package fs-4 text-primary"></i></div>
                <h6 class="mb-1" style="font-size: 0.8rem; line-height: 1.2;">${escapeHtml(product.name)}</h6>
                <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(product.code)}</small>
                <div class="mt-1">
                    <span class="badge bg-success" style="font-size: 0.7rem;">${Number(product.unit_price).toLocaleString('en-US', { maximumFractionDigits: 0 })} TZS</span>
                </div>
                <div class="mt-1">${stockHtml}</div>
                ${formatExpiryBadge(product.earliest_expiry)}
                ${categoryHtml}
            </div>
        </div>
    </div>`;
}

function setProductGridLoading(isInitial, show) {
    const loadingEl = document.getElementById('productGridLoading');
    const loadMoreEl = document.getElementById('productGridLoadMore');
    if (isInitial) {
        loadingEl.classList.toggle('d-none', !show);
    } else {
        loadMoreEl.classList.toggle('d-none', !show);
    }
}

function setProductGridEmpty(show) {
    document.getElementById('productGridEmpty').classList.toggle('d-none', !show);
}

async function loadPosProducts(reset = false) {
    if (posProductsState.loading) {
        return;
    }
    if (!reset && !posProductsState.hasMore) {
        return;
    }

    if (reset) {
        posProductsState.page = 1;
        posProductsState.hasMore = true;
        posProductCache.clear();
        document.getElementById('productGrid').innerHTML = '';
        setProductGridEmpty(false);
    }

    posProductsState.loading = true;
    setProductGridLoading(reset, true);

    const params = new URLSearchParams({
        page: String(posProductsState.page),
        per_page: String(posProductsConfig.perPage),
        search: posProductsState.search,
        category: posProductsState.category,
    });

    try {
        const response = await fetch(`${posProductsConfig.url}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to load products');
        }

        const data = await response.json();
        const grid = document.getElementById('productGrid');

        if (reset) {
            grid.innerHTML = '';
        }

        const html = (data.products || []).map(buildProductCard).join('');
        grid.insertAdjacentHTML('beforeend', html);

        const pagination = data.pagination || {};
        posProductsState.hasMore = !!pagination.has_more;
        posProductsState.page = (pagination.current_page || posProductsState.page) + 1;

        setProductGridEmpty((pagination.total || 0) === 0);
    } catch (error) {
        console.error(error);
        if (reset) {
            setProductGridEmpty(true);
        }
    } finally {
        posProductsState.loading = false;
        setProductGridLoading(reset, false);
        setProductGridLoading(false, false);
    }
}

function schedulePosProductSearch() {
    clearTimeout(posProductsState.searchTimer);
    posProductsState.searchTimer = setTimeout(() => loadPosProducts(true), 300);
}

document.addEventListener('DOMContentLoaded', function() {
    loadPosProducts(true);

    document.getElementById('productGrid').addEventListener('click', function(event) {
        const card = event.target.closest('.product-card-clickable');
        if (!card) {
            return;
        }

        const productItem = card.closest('.product-item');
        const productId = parseInt(productItem?.dataset.id, 10);
        const product = posProductCache.get(productId);
        if (product) {
            openPosProductModal(product);
        }
    });

    const scrollContainer = document.querySelector('#productsPanel .products-scroll-container');
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 120) {
                loadPosProducts(false);
            }
        });
    }
});

document.getElementById('searchInput').addEventListener('input', function() {
    posProductsState.search = this.value.trim();
    schedulePosProductSearch();
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    posProductsState.category = this.value;
    loadPosProducts(true);
});

async function openPosProductByCode(code) {
    const params = new URLSearchParams({ code });
    const response = await fetch(`${posProductsConfig.findByCodeUrl}?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        return false;
    }

    const data = await response.json();
    const product = data.product;
    if (!product || product.is_out_of_stock) {
        return false;
    }

    openPosProductModal(product);

    return true;
}

// Today's bills (bill mode)
const todayBillsConfig = posSaleMode === 'bill' ? {
    url: @json(route('sales.pos.today-bills')),
} : null;

let todayBillsLoaded = false;

function formatMoney(amount, currency) {
    return Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (currency || 'TZS');
}

function buildTodayBillCard(bill) {
    const statusBadge = bill.is_paid
        ? '<span class="badge bg-success">Paid</span>'
        : '<span class="badge bg-warning text-dark">Unpaid</span>';

    const printBtn = bill.receipt_url
        ? `<a href="${bill.receipt_url}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print bill">
                <i class="bx bx-printer"></i>
           </a>`
        : '';

    return `<div class="col-12 col-md-6">
        <div class="card today-bill-card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="fw-bold text-primary">${escapeHtml(bill.invoice_number)}</div>
                        <div class="small text-muted">${escapeHtml(bill.invoice_time)} · ${escapeHtml(bill.invoice_date)}</div>
                    </div>
                    ${statusBadge}
                </div>
                <div class="mb-2">
                    <i class="bx bx-user me-1 text-muted"></i>
                    <span class="fw-semibold">${escapeHtml(bill.customer_name)}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total</small>
                        <span class="fw-bold">${formatMoney(bill.total_amount, bill.currency)}</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Balance</small>
                        <span class="fw-bold ${bill.is_paid ? 'text-success' : 'text-danger'}">${formatMoney(bill.balance_due, bill.currency)}</span>
                    </div>
                    <div>${printBtn}</div>
                </div>
            </div>
        </div>
    </div>`;
}

async function loadTodayBills(force = false) {
    if (!todayBillsConfig) {
        return;
    }
    if (todayBillsLoaded && !force) {
        return;
    }

    const loadingEl = document.getElementById('todayBillsLoading');
    const listEl = document.getElementById('todayBillsList');
    const emptyEl = document.getElementById('todayBillsEmpty');
    const countEl = document.getElementById('todayBillsCount');

    if (!loadingEl || !listEl) {
        return;
    }

    loadingEl.classList.remove('d-none');
    emptyEl.classList.add('d-none');
    listEl.innerHTML = '';

    try {
        const response = await fetch(todayBillsConfig.url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to load bills');
        }

        const data = await response.json();
        const bills = data.bills || [];

        if (countEl) {
            countEl.textContent = String(data.count ?? bills.length);
        }

        if (bills.length === 0) {
            emptyEl.classList.remove('d-none');
        } else {
            listEl.innerHTML = bills.map(buildTodayBillCard).join('');
        }

        todayBillsLoaded = true;
    } catch (error) {
        console.error(error);
        emptyEl.classList.remove('d-none');
        emptyEl.querySelector('p').textContent = 'Failed to load today\'s bills';
    } finally {
        loadingEl.classList.add('d-none');
    }
}

function switchProductsPanel(panel) {
    const productsPanel = document.getElementById('productsPanel');
    const todayBillsPanel = document.getElementById('todayBillsPanel');
    const productFilters = document.getElementById('productFilters');
    const tabProducts = document.getElementById('tabProducts');
    const tabTodayBills = document.getElementById('tabTodayBills');

    const showProducts = panel === 'products';

    if (productsPanel) productsPanel.classList.toggle('d-none', !showProducts);
    if (todayBillsPanel) todayBillsPanel.classList.toggle('d-none', showProducts);
    if (productFilters) productFilters.classList.toggle('d-none', !showProducts);
    if (tabProducts) tabProducts.classList.toggle('active', showProducts);
    if (tabTodayBills) tabTodayBills.classList.toggle('active', !showProducts);

    if (!showProducts) {
        loadTodayBills(true);
    }
}

if (posSaleMode === 'bill') {
    document.getElementById('tabProducts')?.addEventListener('click', () => switchProductsPanel('products'));
    document.getElementById('tabTodayBills')?.addEventListener('click', () => switchProductsPanel('today-bills'));
    loadTodayBills(true);
}

// QR Code Scanner Variables
let qrScanTimeout;
let html5QrcodeScanner = null;
let isScanning = false;
let qrLibraryLoaded = false;

// Check if QR library is loaded
function checkQRLibrary() {
    if (typeof Html5Qrcode !== 'undefined') {
        qrLibraryLoaded = true;
        return true;
    }
    return false;
}

// Try to load QR library if not already loaded
function ensureQRLibrary() {
    if (checkQRLibrary()) {
        return Promise.resolve();
    }
    
    return new Promise((resolve, reject) => {
        // Load QR library from local assets if not already available
        const script = document.createElement('script');
        script.src = "{{ asset('assets/vendor/html5-qrcode/html5-qrcode.min.js') }}";
        script.onload = () => {
            if (checkQRLibrary()) {
                resolve();
            } else {
                reject(new Error('Library loaded but Html5Qrcode not defined'));
            }
        };
        script.onerror = () => reject(new Error('Failed to load QR library'));
        document.head.appendChild(script);
    });
}

// Process scanned QR code data
function processQRCodeData(qrData) {
    try {
        // Try to parse as JSON
        const data = JSON.parse(qrData);
        
        // Check if it's an inventory item QR code
        if (data.t === 'inventory_item' && data.id) {
            // Extract data from QR code
            const itemId = data.id;
            const itemName = data.n || 'Unknown Item';
            const unitPrice = parseFloat(data.p) || 0;
            const stock = parseFloat(data.s) || 0;
            const vatType = data.vt || 'no_vat';
            const vatRate = parseFloat(data.vr) || 18.00;
            const itemType = data.it || 'product';
            const trackStock = data.ts === true || data.ts === 'true' || data.ts === 1;
            
            // Close scanner modal
            const scannerModal = bootstrap.Modal.getInstance(document.getElementById('qrScannerModal'));
            if (scannerModal) {
                scannerModal.hide();
            }
            
            // Open the item modal with scanned data
            showItemModal(itemId, itemName, unitPrice, stock, vatType, vatRate, itemType, trackStock);
            
            return true;
        } else {
            // Not a valid inventory item QR code
            Swal.fire({
                icon: 'warning',
                title: 'Invalid QR Code',
                text: 'This QR code is not for an inventory item.',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }
    } catch (e) {
        // Not valid JSON, might be a barcode or other format
        openPosProductByCode(qrData).then((found) => {
            if (found) {
                const scannerModal = bootstrap.Modal.getInstance(document.getElementById('qrScannerModal'));
                if (scannerModal) {
                    scannerModal.hide();
                }
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Item Not Found',
                text: 'No item found with this code.',
                timer: 2000,
                showConfirmButton: false
            });
        });
        return false;
    }
}

// QR Code Scanner Button Click
document.getElementById('btnScanQR').addEventListener('click', function() {
    const scannerModal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    scannerModal.show();
});

// Initialize QR Scanner when modal opens
document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function() {
    if (!isScanning) {
        // Check if library is loaded
        if (!checkQRLibrary()) {
            // Show loading message
            const qrReader = document.getElementById('qr-reader');
            if (qrReader) {
                qrReader.innerHTML = '<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin fs-1 text-primary"></i><p class="mt-2">Loading QR scanner...</p></div>';
            }
            
            // Try to load library
            ensureQRLibrary()
                .then(() => {
                    startQRScanner();
                })
                .catch((err) => {
                    console.error('Failed to load QR library:', err);
                    showCameraError('QR Scanner library failed to load. Please use manual input below.');
                });
        } else {
            startQRScanner();
        }
    }
});

// Stop QR Scanner when modal closes
document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function() {
    stopQRScanner();
});

// Start QR Scanner
function startQRScanner() {
    if (isScanning) return;
    
    const qrReader = document.getElementById('qr-reader');
    const errorMessage = document.getElementById('camera-error-message');
    if (!qrReader) return;
    
    // Check if Html5Qrcode library is loaded
    if (typeof Html5Qrcode === 'undefined') {
        showCameraError('QR Scanner library not loaded. Please refresh the page or use manual input.');
        console.error('Html5Qrcode library is not defined. Check if the script loaded correctly.');
        return;
    }
    
    qrReader.innerHTML = '<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin fs-1 text-primary"></i><p class="mt-2">Initializing camera...</p></div>';
    errorMessage.classList.add('d-none');
    
    // Check if we're in a secure context (HTTPS or localhost)
    if (!window.isSecureContext) {
        showCameraError('Camera requires a secure connection (HTTPS). Please use HTTPS or localhost.');
        return;
    }
    
    // Check if camera is available
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showCameraError('Camera API not supported in this browser.');
        return;
    }
    
    try {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        
        // Try different camera configurations
        const cameraConfigs = [
            { facingMode: "environment" }, // Back camera (mobile)
            { facingMode: "user" }, // Front camera
            { deviceId: { exact: undefined } } // Any available camera
        ];
        
        let configIndex = 0;
        
        function tryNextConfig() {
            if (configIndex >= cameraConfigs.length) {
                // Check if it's a permission error
                const lastError = arguments[0]?.message || '';
                if (lastError.includes('Permission') || lastError.includes('NotAllowedError') || lastError.includes('NotReadableError')) {
                    showCameraError('Camera permission denied. Please grant camera access in your browser settings or use manual input.');
                } else {
                    showCameraError('No camera found or camera is in use. Please use manual input.');
                }
                return;
            }
            
            const config = cameraConfigs[configIndex];
            
            html5QrcodeScanner.start(
                config,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                (decodedText, decodedResult) => {
                    // Successfully scanned
                    if (processQRCodeData(decodedText)) {
                        stopQRScanner();
                    }
                },
                (errorMessage) => {
                    // Error ignored for continuous scanning
                }
            ).then(() => {
                isScanning = true;
                errorMessage.classList.add('d-none');
            }).catch((err) => {
                console.error('Camera config failed:', config, err);
                const errorMsg = err.message || err.toString() || '';
                
                // If it's a permission error, show helpful message
                if (errorMsg.includes('Permission') || errorMsg.includes('NotAllowedError') || errorMsg.includes('NotReadableError')) {
                    showCameraError('Camera permission denied. Please grant camera access in your browser settings or use manual input.');
                    return; // Don't try other configs if permission is denied
                }
                
                configIndex++;
                tryNextConfig();
            });
        }
        
        tryNextConfig();
        
    } catch (err) {
        console.error('Error starting QR scanner:', err);
        showCameraError('Unable to access camera. Please check browser permissions or use manual input.');
    }
}

// Show camera error with fallback option
function showCameraError(message) {
    const qrReader = document.getElementById('qr-reader');
    const errorMessage = document.getElementById('camera-error-message');
    
    if (qrReader) {
        qrReader.innerHTML = '<div class="text-center p-4 text-muted"><i class="bx bx-camera-off fs-1"></i><p class="mt-2">' + message + '</p></div>';
    }
    
    if (errorMessage) {
        errorMessage.classList.remove('d-none');
    }
}

// Stop QR Scanner
function stopQRScanner() {
    if (html5QrcodeScanner && isScanning) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            html5QrcodeScanner.clear();
        }).catch((err) => {
            console.error('Error stopping QR scanner:', err);
            isScanning = false;
        });
    }
}

// Manual QR Code Input (for barcode scanners that type into input)
document.getElementById('qrScannerInput').addEventListener('input', function() {
    const qrData = this.value.trim();
    
    // Clear previous timeout
    if (qrScanTimeout) {
        clearTimeout(qrScanTimeout);
    }
    
    // Wait for complete scan (QR scanners usually input data quickly)
    qrScanTimeout = setTimeout(() => {
        if (!qrData) return;
        
        if (processQRCodeData(qrData)) {
            // Clear the input after successful processing
            this.value = '';
        } else {
            // Clear on error too
            this.value = '';
        }
    }, 300); // Wait 300ms after last input to ensure complete scan
});

// Request camera permission explicitly
document.getElementById('requestCameraPermission').addEventListener('click', function() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        Swal.fire({
            icon: 'error',
            title: 'Not Supported',
            text: 'Camera API is not supported in this browser.',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }
    
    // Request permission
    navigator.mediaDevices.getUserMedia({ video: true })
        .then((stream) => {
            // Permission granted, stop the stream and restart scanner
            stream.getTracks().forEach(track => track.stop());
            Swal.fire({
                icon: 'success',
                title: 'Permission Granted',
                text: 'Camera permission granted. Restarting scanner...',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                stopQRScanner();
                setTimeout(() => {
                    startQRScanner();
                }, 500);
            });
        })
        .catch((err) => {
            console.error('Permission request failed:', err);
            let errorMsg = 'Unable to access camera. ';
            if (err.name === 'NotAllowedError') {
                errorMsg += 'Please grant camera permission in your browser settings.';
            } else if (err.name === 'NotFoundError') {
                errorMsg += 'No camera found on this device.';
            } else {
                errorMsg += err.message || 'Unknown error.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Camera Access Denied',
                text: errorMsg,
                footer: '<small>You can still use the manual input option below.</small>'
            });
        });
});

// Stop scanner when close button is clicked
document.getElementById('closeQrScanner').addEventListener('click', function() {
    stopQRScanner();
});

document.getElementById('stopQrScanner').addEventListener('click', function() {
    stopQRScanner();
});

// Focus QR scanner on page load for quick scanning
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Auto-focus QR scanner (uncomment if desired)
    // document.getElementById('qrScannerInput').focus();
});

// Show item modal
function showItemModal(itemId, itemName, unitPrice, stock, vatType, vatRate, itemType = 'product', trackStock = true, hasWholesale = false, wholesaleUnitPrice = 0) {
    const retailBase = unitPrice;
    const wholesaleBase = hasWholesale ? parseFloat(wholesaleUnitPrice) || 0 : 0;
    const saleCurrency = getCurrentSaleCurrency();
    const exchangeRate = getCurrentExchangeRate();

    const tierRow = document.getElementById('modalPriceTierRow');
    const tierSelect = document.getElementById('modal_price_tier_pos');
    if (hasWholesale && wholesaleBase > 0) {
        tierRow.style.display = 'block';
        tierSelect.value = 'retail';
    } else {
        tierRow.style.display = 'none';
        tierSelect.value = 'retail';
    }

    const basePrice = (hasWholesale && tierSelect.value === 'wholesale' && wholesaleBase > 0) ? wholesaleBase : retailBase;
    const convertedPrice = convertItemPrice(basePrice, saleCurrency, exchangeRate);

    currentItem = {
        id: itemId,
        name: itemName,
        unitPrice: retailBase,
        originalPrice: retailBase,
        originalWholesalePrice: wholesaleBase,
        hasWholesale: !!(hasWholesale && wholesaleBase > 0),
        stock: stock,
        itemType: itemType,
        trackStock: trackStock
    };

    document.getElementById('modalItemName').value = itemName;
    
    // For service items or items that don't track stock, show "N/A" instead of stock
    if (itemType === 'service' || !trackStock) {
        document.getElementById('modalAvailableStock').value = 'N/A';
        document.getElementById('maxQuantity').textContent = 'N/A';
        document.getElementById('modalQuantity').value = 1;
        document.getElementById('modalQuantity').removeAttribute('max');
    } else {
        document.getElementById('modalAvailableStock').value = stock + ' units';
        document.getElementById('maxQuantity').textContent = stock + ' units';
        document.getElementById('modalQuantity').value = 1;
        document.getElementById('modalQuantity').max = stock;
    }
    document.getElementById('modalUnitPrice').value = convertedPrice.toFixed(2);
    document.getElementById('modalUnitPrice').setAttribute('data-original-price', basePrice);

    const syncPosModalPriceFromTier = () => {
        if (!currentItem) return;
        const t = document.getElementById('modal_price_tier_pos').value;
        const sc = getCurrentSaleCurrency();
        const er = getCurrentExchangeRate();
        const fcBase = (currentItem.hasWholesale && t === 'wholesale') ? currentItem.originalWholesalePrice : currentItem.originalPrice;
        const conv = convertItemPrice(fcBase, sc, er);
        document.getElementById('modalUnitPrice').value = conv.toFixed(2);
        document.getElementById('modalUnitPrice').setAttribute('data-original-price', fcBase);
        if (sc !== functionalCurrency && er !== 1) {
            document.getElementById('modalUnitPrice').setAttribute('title', `Converted from ${fcBase.toFixed(2)} ${functionalCurrency} at rate ${er}`);
        } else {
            document.getElementById('modalUnitPrice').removeAttribute('title');
        }
        updateVatCalculation();
    };
    document.getElementById('modal_price_tier_pos').onchange = syncPosModalPriceFromTier;
    document.getElementById('modalVatType').value = vatType;
    document.getElementById('modalVatRate').value = vatRate;
    
    // Add tooltip if converted
    if (saleCurrency !== functionalCurrency && exchangeRate !== 1) {
        document.getElementById('modalUnitPrice').setAttribute('title', `Converted from ${basePrice.toFixed(2)} ${functionalCurrency} at rate ${exchangeRate}`);
    } else {
        document.getElementById('modalUnitPrice').removeAttribute('title');
    }
    
    updateVatCalculation();
    
    new bootstrap.Modal(document.getElementById('itemModal')).show();
}

// Update VAT calculation
function updateVatCalculation() {
    const quantity = parseFloat(document.getElementById('modalQuantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('modalUnitPrice').value) || 0;
    const vatType = document.getElementById('modalVatType').value;
    const vatRate = parseFloat(document.getElementById('modalVatRate').value) || 0;
    
    const baseAmount = quantity * unitPrice;
    let vatAmount = 0;
    let lineTotal = baseAmount;
    
    if (vatType === 'inclusive') {
        vatAmount = baseAmount * (vatRate / (100 + vatRate));
    } else if (vatType === 'exclusive') {
        vatAmount = baseAmount * (vatRate / 100);
        lineTotal += vatAmount;
    }
    
    const saleCurrency = getCurrentSaleCurrency();
    document.getElementById('modalLineTotal').value = lineTotal.toFixed(2) + ' ' + saleCurrency;
    
    // Store original price if manually edited
    if (currentItem && !document.getElementById('modalUnitPrice').getAttribute('data-original-price')) {
        const basePrice = currentItem.originalPrice || currentItem.unitPrice;
        if (basePrice) {
            document.getElementById('modalUnitPrice').setAttribute('data-original-price', basePrice);
        }
    }
}

// Get functional currency
const functionalCurrency = '{{ \App\Models\SystemSetting::getValue("functional_currency", auth()->user()->company->functional_currency ?? "TZS") }}';

// Function to convert item price from functional currency to sale currency
function convertItemPrice(basePrice, saleCurrency, exchangeRate) {
    if (!basePrice || !saleCurrency || !exchangeRate) {
        return basePrice;
    }
    
    // If sale currency is functional currency, no conversion needed
    if (saleCurrency === functionalCurrency) {
        return parseFloat(basePrice);
    }
    
    // Convert: Price in FCY = Price in TZS / Exchange Rate
    // Example: 10,000 TZS / 2,500 = 4 USD
    const convertedPrice = parseFloat(basePrice) / parseFloat(exchangeRate);
    return parseFloat(convertedPrice.toFixed(2));
}

// Function to get current exchange rate
function getCurrentExchangeRate() {
    const rate = parseFloat(document.getElementById('exchange_rate').value) || 1.000000;
    return rate;
}

// Function to get current sale currency
function getCurrentSaleCurrency() {
    const currencySelect = document.getElementById('currency');
    if (typeof jQuery !== 'undefined' && currencySelect && jQuery(currencySelect).data('select2')) {
        return jQuery(currencySelect).val() || functionalCurrency;
    }
    return currencySelect ? currencySelect.value || functionalCurrency : functionalCurrency;
}

// Function to convert all cart item prices when currency/exchange rate changes
function convertAllCartPrices() {
    const saleCurrency = getCurrentSaleCurrency();
    const exchangeRate = getCurrentExchangeRate();
    
    // Convert prices in cart items (per retail vs wholesale)
    cart.forEach(item => {
        const tier = item.price_tier || 'retail';
        let fcBase = item.originalPrice;
        if (tier === 'wholesale' && item.originalWholesalePrice) {
            fcBase = item.originalWholesalePrice;
        }
        if (fcBase != null && fcBase !== '') {
            item.unit_price = convertItemPrice(parseFloat(fcBase), saleCurrency, exchangeRate);
        } else if (item.unitPrice) {
            item.originalPrice = item.unitPrice;
            fcBase = item.unitPrice;
            if (tier === 'wholesale' && item.originalWholesalePrice) {
                fcBase = item.originalWholesalePrice;
            }
            item.unit_price = convertItemPrice(parseFloat(fcBase), saleCurrency, exchangeRate);
        }
    });
    
    if (currentItem && document.getElementById('modalUnitPrice')) {
        const tierEl = document.getElementById('modal_price_tier_pos');
        const t = tierEl ? tierEl.value : 'retail';
        const fcBase = (currentItem.hasWholesale && t === 'wholesale')
            ? (currentItem.originalWholesalePrice || currentItem.originalPrice)
            : currentItem.originalPrice;
        if (fcBase > 0) {
            const convertedPrice = convertItemPrice(fcBase, saleCurrency, exchangeRate);
            document.getElementById('modalUnitPrice').value = convertedPrice.toFixed(2);
            document.getElementById('modalUnitPrice').setAttribute('data-original-price', fcBase);
            if (saleCurrency !== functionalCurrency && exchangeRate !== 1) {
                document.getElementById('modalUnitPrice').setAttribute('title', `Converted from ${fcBase.toFixed(2)} ${functionalCurrency} at rate ${exchangeRate}`);
            } else {
                document.getElementById('modalUnitPrice').removeAttribute('title');
            }
            updateVatCalculation();
        }
    }
    
    // Update cart display
    updateCartDisplay();
}

// Handle currency change
function handleCurrencyChange(selectedCurrency) {
    if (selectedCurrency && selectedCurrency !== functionalCurrency) {
        document.getElementById('exchange_rate').required = true;
        fetchExchangeRate(selectedCurrency);
    } else {
        document.getElementById('exchange_rate').required = false;
        document.getElementById('exchange_rate').value = '1.000000';
        document.getElementById('rate-info').style.display = 'none';
    }
    
    // Convert all cart item prices when currency changes
    convertAllCartPrices();
}

// Initialize currency change handlers
document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('currency');
    
    // Use Select2 event if available, otherwise use change event
    if (typeof jQuery !== 'undefined' && currencySelect) {
        jQuery(currencySelect).on('select2:select', function(e) {
            handleCurrencyChange(jQuery(this).val());
        }).on('change', function() {
            handleCurrencyChange(this.value);
        });
    } else {
        currencySelect.addEventListener('change', function() {
            handleCurrencyChange(this.value);
        });
    }
    
    // Handle exchange rate changes
    document.getElementById('exchange_rate').addEventListener('input', function() {
        const saleCurrency = getCurrentSaleCurrency();
        if (saleCurrency !== functionalCurrency) {
            convertAllCartPrices();
        }
    });
    
    document.getElementById('exchange_rate').addEventListener('change', function() {
        const saleCurrency = getCurrentSaleCurrency();
        if (saleCurrency !== functionalCurrency) {
            convertAllCartPrices();
        }
    });
    
    // Fetch exchange rate button
    const fetchBtn = document.getElementById('fetch-rate-btn');
    if (fetchBtn) {
        fetchBtn.addEventListener('click', function() {
            const currency = getCurrentSaleCurrency();
            fetchExchangeRate(currency);
        });
    }
});

// Function to fetch exchange rate from API
function fetchExchangeRate(currency) {
    if (!currency || currency === functionalCurrency) {
        document.getElementById('exchange_rate').value = '1.000000';
        return;
    }

    const btn = document.getElementById('fetch-rate-btn');
    const rateInput = document.getElementById('exchange_rate');
    const originalBtnHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader bx-spin"></i>';
    rateInput.disabled = true;
    
    fetch(`{{ route("accounting.fx-rates.get-rate") }}?from_currency=${currency}&to_currency=${functionalCurrency}&date=${new Date().toISOString().split('T')[0]}&rate_type=spot`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rate) {
                const rate = parseFloat(data.rate);
                rateInput.value = rate.toFixed(6);
                document.getElementById('rate-source').textContent = `Rate fetched: 1 ${currency} = ${rate.toFixed(6)} ${functionalCurrency}`;
                document.getElementById('rate-info').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Failed to fetch exchange rate:', error);
            // Try fallback API
            fetch(`{{ route("api.exchange-rates.rate") }}?from=${currency}&to=${functionalCurrency}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.rate) {
                        const rate = parseFloat(data.data.rate);
                        rateInput.value = rate.toFixed(6);
                        document.getElementById('rate-source').textContent = `Rate fetched (fallback): 1 ${currency} = ${rate.toFixed(6)} ${functionalCurrency}`;
                        document.getElementById('rate-info').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Fallback API also failed:', err);
                });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
            rateInput.disabled = false;
        });
}

// Add item to cart
function addItemToCart() {
    if (!currentItem) return;
    
    const quantity = parseFloat(document.getElementById('modalQuantity').value);
    const unitPrice = parseFloat(document.getElementById('modalUnitPrice').value);
    const vatType = document.getElementById('modalVatType').value;
    const vatRate = parseFloat(document.getElementById('modalVatRate').value);
    
    if (quantity <= 0 || unitPrice <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Please enter valid quantity and unit price',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Skip stock validation for service items or items that don't track stock
    const itemType = currentItem.itemType || currentItem.item_type;
    const trackStock = currentItem.trackStock !== undefined ? currentItem.trackStock : (currentItem.track_stock !== undefined ? currentItem.track_stock : true);
    
    if ((itemType !== 'service' && trackStock) && quantity > currentItem.stock) {
        Swal.fire({
            icon: 'error',
            title: 'Insufficient Stock',
            text: `Available stock: ${currentItem.stock} units`,
            confirmButtonColor: '#d33'
        });
        return;
    }
    
    const priceTier = (currentItem.hasWholesale && document.getElementById('modal_price_tier_pos').value === 'wholesale')
        ? 'wholesale'
        : 'retail';

    const cartItem = {
        id: currentItem.id,
        name: currentItem.name,
        quantity: quantity,
        unit_price: unitPrice,
        originalPrice: currentItem.originalPrice,
        originalWholesalePrice: currentItem.originalWholesalePrice || 0,
        price_tier: priceTier,
        vat_type: vatType,
        vat_rate: vatRate,
        stock: currentItem.stock
    };
    
    console.log('Adding item to cart:', {
        name: cartItem.name,
        vat_type: cartItem.vat_type,
        vat_rate: cartItem.vat_rate,
        unit_price: cartItem.unit_price,
        quantity: cartItem.quantity
    });
    
    // Check if item already exists in cart
    const existingIndex = cart.findIndex(item => item.id === cartItem.id && (item.price_tier || 'retail') === cartItem.price_tier);
    if (existingIndex >= 0) {
        // Update quantity and also update VAT type/rate in case they changed
        cart[existingIndex].quantity += quantity;
        cart[existingIndex].vat_type = vatType; // Update VAT type
        cart[existingIndex].vat_rate = vatRate; // Update VAT rate
        cart[existingIndex].unit_price = unitPrice; // Update unit price in case it changed
        Swal.fire({
            icon: 'info',
            title: 'Item Updated',
            text: `Quantity updated for ${cartItem.name}`,
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        cart.push(cartItem);
        Swal.fire({
            icon: 'success',
            title: 'Item Added',
            text: `${cartItem.name} added to cart`,
            timer: 1500,
            showConfirmButton: false
        });
    }
    
    updateCartDisplay();
    bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
}

// Update cart display
function updateCartDisplay() {
    const cartContainer = document.getElementById('cartItems');
    cartContainer.innerHTML = '';
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '<div class="text-muted text-center py-3">No items in cart</div>';
        updateCartTotals();
        return;
    }
    
    cart.forEach((item, index) => {
        // Calculate line total
        const baseAmount = item.quantity * item.unit_price;
        let vatAmount = 0;
        
        // Calculate VAT and line total
        // Normalize vat_type to handle case variations
        const displayVatType = String(item.vat_type || '').toLowerCase().trim();
        let lineTotal = 0;
        
        if (displayVatType === 'inclusive') {
            vatAmount = baseAmount * (item.vat_rate / (100 + item.vat_rate));
            lineTotal = baseAmount; // For inclusive, baseAmount already includes VAT
        } else if (displayVatType === 'exclusive') {
            vatAmount = baseAmount * (item.vat_rate / 100);
            lineTotal = baseAmount + vatAmount; // For exclusive, add VAT to base amount
        } else {
            vatAmount = 0;
            lineTotal = baseAmount; // For no VAT, line total is just base amount
        }
        
        const cartItem = document.createElement('div');
        cartItem.className = 'd-flex justify-content-between align-items-center border-bottom py-2';
        const saleCurrency = getCurrentSaleCurrency();
        const currencyDisplay = saleCurrency;
        
        const tierBadge = (item.price_tier === 'wholesale') ? '<span class="badge bg-secondary ms-1">Wholesale</span>' : '';
        cartItem.innerHTML = `
            <div class="flex-grow-1">
                <h6 class="mb-1">${item.name}${tierBadge}</h6>
                <small class="text-muted">
                    ${item.quantity} x ${parseFloat(item.unit_price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} ${currencyDisplay}
                </small>
                <br>
                <small class="text-muted">
                    VAT: ${displayVatType === 'inclusive' ? 'Inclusive' : displayVatType === 'exclusive' ? 'Exclusive' : 'No VAT'} 
                    (${item.vat_rate}%)
                </small>
            </div>
            <div class="text-end">
                <div class="fw-bold">${parseFloat(lineTotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} ${currencyDisplay}</div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });
    
    updateCartTotals();
}

// Remove item from cart
function removeFromCart(index) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    
    Swal.fire({
        icon: 'success',
        title: 'Item Removed',
        text: `${itemName} removed from cart`,
        timer: 1500,
        showConfirmButton: false
    });
    
    updateCartDisplay();
}

// Toggle cart discount fields
function toggleCartDiscount() {
    const discountType = document.getElementById('cartDiscountType').value;
    const discountRateInput = document.getElementById('cartDiscountRate');
    
    if (discountType === 'none') {
        discountRateInput.style.display = 'none';
        discountRateInput.value = 0;
    } else {
        discountRateInput.style.display = 'block';
        if (discountType === 'percentage') {
            discountRateInput.placeholder = 'Percentage %';
        } else {
            discountRateInput.placeholder = 'Amount TZS';
        }
    }
    
    updateCartTotals();
}

// Update cart totals
function updateCartTotals() {
    let subtotal = 0;
    let vatTotal = 0;
    let totalLineTotals = 0; // Sum of all line totals (for VAT inclusive, this already includes VAT)
    
    cart.forEach(item => {
        const baseAmount = item.quantity * item.unit_price;
        let vatAmount = 0;
        let netAmount = 0;
        let lineTotal = 0;
        
        // Calculate VAT and net amount
        // Normalize vat_type to handle case variations and trim whitespace
        const vatType = String(item.vat_type || '').toLowerCase().trim();
        
        console.log(`Processing item: ${item.name}, vat_type: "${item.vat_type}" (normalized: "${vatType}"), vat_rate: ${item.vat_rate}, baseAmount: ${baseAmount}`);
        
        if (vatType === 'inclusive') {
            // VAT is included in the base amount
            vatAmount = baseAmount * (item.vat_rate / (100 + item.vat_rate));
            netAmount = baseAmount - vatAmount; // Net amount = gross - VAT
            lineTotal = baseAmount; // For inclusive, line total is baseAmount (includes VAT)
            console.log(`✓ VAT Inclusive - vatAmount: ${vatAmount.toFixed(2)}, netAmount: ${netAmount.toFixed(2)}, lineTotal: ${lineTotal.toFixed(2)}`);
        } else if (vatType === 'exclusive') {
            // VAT is added on top of the base amount
            vatAmount = baseAmount * (item.vat_rate / 100);
            netAmount = baseAmount; // For exclusive, unit price is already net
            lineTotal = baseAmount + vatAmount; // For exclusive, line total = base + VAT
            console.log(`✓ VAT Exclusive - vatAmount: ${vatAmount.toFixed(2)}, netAmount: ${netAmount.toFixed(2)}, lineTotal: ${lineTotal.toFixed(2)}`);
        } else {
            // No VAT
            vatAmount = 0;
            netAmount = baseAmount;
            lineTotal = baseAmount; // No VAT, line total = base amount
            console.log(`✓ No VAT - netAmount: ${netAmount.toFixed(2)}, lineTotal: ${lineTotal.toFixed(2)}`);
        }
        
        subtotal += netAmount; // Add net amount to subtotal
        vatTotal += vatAmount;
        totalLineTotals += lineTotal; // Sum of all line totals
    });
    
    console.log(`Cart Totals - subtotal: ${subtotal.toFixed(2)}, vatTotal: ${vatTotal.toFixed(2)}, totalLineTotals: ${totalLineTotals.toFixed(2)}`);
    
    // Calculate cart-level discount
    const discountType = document.getElementById('cartDiscountType').value;
    const discountRate = parseFloat(document.getElementById('cartDiscountRate').value) || 0;
    let discountTotal = 0;
    
    if (discountType === 'percentage') {
        discountTotal = subtotal * (discountRate / 100);
    } else if (discountType === 'fixed') {
        discountTotal = discountRate;
    }
    
    // For VAT inclusive items, total should be sum of line totals minus discount
    // For VAT exclusive items, total should be subtotal + VAT minus discount
    // Since we have mixed items, use the sum of line totals (which already accounts for VAT type)
    const total = totalLineTotals - discountTotal;
    
    console.log(`Final Calculation - subtotal: ${subtotal.toFixed(2)}, vatTotal: ${vatTotal.toFixed(2)}, discountTotal: ${discountTotal.toFixed(2)}, totalLineTotals: ${totalLineTotals.toFixed(2)}, finalTotal: ${total.toFixed(2)}`);
    
    // Determine VAT label (show rate if all items have same rate, otherwise show "Mixed")
    let vatRates = [];
    cart.forEach(item => {
        if (item.vat_type !== 'no_vat' && item.vat_rate > 0) {
            if (!vatRates.includes(item.vat_rate)) {
                vatRates.push(item.vat_rate);
            }
        }
    });
    
    let vatLabel = 'VAT:';
    if (vatRates.length === 1) {
        vatLabel = `VAT (${vatRates[0].toFixed(2)}%):`;
    } else if (vatRates.length > 1) {
        vatLabel = 'VAT (Mixed):';
    }
    
    // Update display with calculated values
    const formattedSubtotal = parseFloat(subtotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const formattedVat = parseFloat(vatTotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const formattedDiscount = parseFloat(discountTotal).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const formattedTotal = parseFloat(total).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    console.log(`Display Values - Subtotal: ${formattedSubtotal}, VAT: ${formattedVat}, Discount: ${formattedDiscount}, Total: ${formattedTotal}`);
    
    const saleCurrency = getCurrentSaleCurrency();
    const currencyDisplay = saleCurrency;
    
    document.getElementById('subtotal').textContent = formattedSubtotal + ' ' + currencyDisplay;
    document.getElementById('vatLabel').textContent = vatLabel;
    document.getElementById('vatAmount').textContent = formattedVat + ' ' + currencyDisplay;
    document.getElementById('discountAmount').textContent = formattedDiscount + ' ' + currencyDisplay;
    document.getElementById('totalAmount').textContent = formattedTotal + ' ' + currencyDisplay;
    
    // Update amount paid to match total (especially when items are removed)
    const amountPaidInput = document.getElementById('amountPaid');
    const currentAmountPaid = parseFloat(amountPaidInput.value) || 0;
    
    if (cart.length === 0) {
        // If cart is empty, reset amount paid to 0
        amountPaidInput.value = 0;
        console.log('Cart empty, amount paid reset to 0');
    } else {
        // Always update amount paid to match total when items change
        // This ensures it updates when items are removed
        amountPaidInput.value = parseFloat(total).toFixed(2);
        console.log('Amount paid updated from', currentAmountPaid, 'to', total);
        // Check if rounding to hundreds is enabled
        updateAmountPaid();
    }
}

// Update amount paid with rounding to hundreds if checkbox is checked
function updateAmountPaid() {
    console.log('updateAmountPaid function called');
    const roundToHundreds = document.getElementById('roundToHundreds').checked;
    const amountPaidInput = document.getElementById('amountPaid');
    let currentAmount = parseFloat(amountPaidInput.value) || 0;
    
    // If no amount is set, get the total from the display
    if (currentAmount === 0) {
        const totalText = document.getElementById('totalAmount').textContent;
        const totalMatch = totalText.match(/[\d,]+\.\d+/);
        if (totalMatch) {
            currentAmount = parseFloat(totalMatch[0].replace(/,/g, ''));
        }
    }
    
    console.log('roundToHundreds:', roundToHundreds, 'currentAmount:', currentAmount);
    
    if (roundToHundreds && currentAmount > 0) {
        // Round to nearest hundred
        const roundedAmount = Math.round(currentAmount / 100) * 100;
        amountPaidInput.value = roundedAmount.toFixed(2);
        console.log('Rounded amount:', currentAmount, 'to', roundedAmount);
    }
}

// Toggle payment fields based on payment method
function togglePaymentFields() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const bankAccountId = document.getElementById('bankAccountId');
    
    if (paymentMethod) {
        bankAccountId.value = paymentMethod;
    } else {
        bankAccountId.value = '';
    }
}

// Add event listener for payment method change
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('paymentMethod');
    const bankAccountIdInput = document.getElementById('bankAccountId');

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', togglePaymentFields);

        // Set default to the first bank account and sync hidden input
        if (paymentMethodSelect.options.length > 0) {
            paymentMethodSelect.selectedIndex = 0;
            if (bankAccountIdInput) {
                bankAccountIdInput.value = paymentMethodSelect.value;
            }
        }
    }
    
    // Format amount paid to 2 decimal places and apply rounding if needed
    document.getElementById('amountPaid').addEventListener('blur', function() {
        const value = parseFloat(this.value) || 0;
        this.value = value.toFixed(2);
        // Check if rounding to hundreds is enabled
        updateAmountPaid();
    });
});

// Select customer
function selectWalkInCustomer() {
    const name = posSaleMode === 'bill' ? defaultBillCustomerName : 'Walk-in Customer';
    selectCustomer(0, name);
}

function selectCustomer(customerId, customerName) {
    document.getElementById('selectedCustomerId').value = customerId;
    document.getElementById('selectedCustomerName').value = customerName;
    
    // Update visual display
    document.getElementById('selectedCustomerText').textContent = customerName;

    const billWrap = document.getElementById('billCustomerNameWrap');
    if (billWrap && posSaleMode === 'bill') {
        billWrap.classList.toggle('d-none', parseInt(customerId, 10) !== 0);
        if (parseInt(customerId, 10) === 0) {
            const billNameInput = document.getElementById('billCustomerName');
            if (billNameInput && !billNameInput.value.trim()) {
                billNameInput.value = defaultBillCustomerName;
            }
        }
    }
    
    // Close the modal
    const customerModal = bootstrap.Modal.getInstance(document.getElementById('customerModal'));
    if (customerModal) {
        customerModal.hide();
    }
    
    // Show success message
    Swal.fire({
        icon: 'success',
        title: 'Customer Selected',
        text: `Selected: ${customerName}`,
        timer: 1500,
        showConfirmButton: false
    });
}

// Show customer modal
function showCustomerModal() {
    // Load all customers initially
    loadCustomers('');
    new bootstrap.Modal(document.getElementById('customerModal')).show();
}

// Load customers function
function loadCustomers(searchTerm) {
    fetch('/customers/search?term=' + encodeURIComponent(searchTerm))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<div class="text-center text-muted p-3">No customers found</div>';
            } else {
                data.forEach(customer => {
                    html += `
                        <div class="customer-item p-2 border-bottom" onclick="selectCustomer(${customer.id}, '${customer.name.replace(/'/g, "\\'")}')" style="cursor: pointer;">
                            <div class="fw-bold">${customer.name}</div>
                            <small class="text-muted">${customer.phone || 'No phone'}</small>
                        </div>
                    `;
                });
            }
            document.getElementById('customerList').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            document.getElementById('customerList').innerHTML = '<div class="text-center text-danger p-3">Error loading customers</div>';
        });
}

// Add event listener for customer search
document.addEventListener('DOMContentLoaded', function() {
    const customerSearchInput = document.getElementById('customerSearch');
    if (customerSearchInput) {
        customerSearchInput.addEventListener('input', function() {
            loadCustomers(this.value);
        });
    }
    
    // Initialize discount input visibility
    toggleCartDiscount();
});

// Validate quantity input
function validateQuantity() {
    const quantityInput = document.getElementById('modalQuantity');
    const currentQuantity = parseFloat(quantityInput.value) || 0;
    const currentStock = currentItem ? currentItem.stock : 0;
    const itemType = currentItem ? currentItem.itemType : null;
    const trackStock = currentItem ? currentItem.trackStock : true;

    // Skip stock validation for service items or items that don't track stock
    if (itemType === 'service' || !trackStock) {
        updateVatCalculation();
        return;
    }

    if (currentQuantity > currentStock) {
        quantityInput.value = currentStock;
        Swal.fire({
            icon: 'warning',
            title: 'Quantity Exceeds Stock',
            text: `Quantity cannot exceed available stock of ${currentStock} units.`,
            confirmButtonColor: '#f39c12'
        });
    }
    
    updateVatCalculation();
}

// Process sale
function processSale() {
    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Cart',
            text: 'Please add items to cart before processing sale',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const customerId = parseInt(document.getElementById('selectedCustomerId').value, 10);
    const saleDate = document.getElementById('saleDate').value;
    const isBillMode = posSaleMode === 'bill';

    if (!isBillMode) {
        const bankAccountId = document.getElementById('bankAccountId').value;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

        if (!bankAccountId) {
            Swal.fire({
                icon: 'warning',
                title: 'Payment Method Required',
                text: 'Please select a bank account',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (amountPaid <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Amount',
                text: 'Please enter a valid amount',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    }
    
    const discountType = document.getElementById('cartDiscountType').value;
    const discountRate = parseFloat(document.getElementById('cartDiscountRate').value) || 0;
    
    const saleData = {
        customer_id: customerId,
        customer_name: isBillMode
            ? (customerId === 0
                ? getBillCustomerName()
                : document.getElementById('selectedCustomerName').value)
            : document.getElementById('selectedCustomerName').value,
        payment_method: 'bank',
        bank_account_id: isBillMode ? null : document.getElementById('bankAccountId').value,
        cash_amount: 0,
        bank_amount: isBillMode ? 0 : (parseFloat(document.getElementById('amountPaid').value) || 0),
        mobile_money_amount: 0,
        sale_date: saleDate,
        currency: document.getElementById('currency').value,
        exchange_rate: parseFloat(document.getElementById('exchange_rate').value) || 1.000000,
        discount_type: discountType,
        discount_rate: discountRate,
        notes: '',
        items: cart.map(item => ({
            inventory_item_id: item.id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            vat_type: item.vat_type,
            vat_rate: item.vat_rate,
            price_tier: item.price_tier || 'retail'
        }))
    };
    

    
    // Show loading
    Swal.fire({
        title: isBillMode ? 'Creating Bill...' : 'Processing Sale...',
        text: isBillMode ? 'Please wait while we create the bill' : 'Please wait while we process your sale',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Send request
    fetch('{{ route("sales.pos.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(saleData)
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message || data.error || Object.values(data.errors || {})[0]?.[0] || `Request failed (${response.status})`;
            throw new Error(message);
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            const isBill = data.mode === 'bill';
            const refLabel = isBill ? ('Bill #' + (data.invoice_number || '')) : ('POS Sale #' + data.pos_number);

            if (data.receipt_url && posAutoPrintReceipt) {
                window.open(data.receipt_url, '_blank', 'width=400,height=600');
            }

            Swal.fire({
                icon: 'success',
                title: isBill ? 'Bill Created!' : 'Sale Completed!',
                text: refLabel + ' has been processed successfully',
                confirmButtonColor: '#28a745',
                timer: 2000,
                showConfirmButton: true
            }).then((result) => {
                cart = [];
                if (!isBillMode) {
                    document.getElementById('amountPaid').value = 0;
                }
                if (isBillMode) {
                    const billNameInput = document.getElementById('billCustomerName');
                    if (billNameInput) billNameInput.value = defaultBillCustomerName;
                    selectCustomer(0, defaultBillCustomerName);
                    todayBillsLoaded = false;
                    loadTodayBills(true);
                }
                document.getElementById('cartDiscountType').value = 'none';
                document.getElementById('cartDiscountRate').value = 0;
                toggleCartDiscount();
                updateCartDisplay();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Sale Failed',
                text: data.message || 'An error occurred while processing the sale',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Sale Failed',
            text: error.message || 'Failed to process sale. Please try again.',
            confirmButtonColor: '#d33'
        });
    });
}

// Clear cart
function clearCart() {
    Swal.fire({
        title: 'Clear Cart?',
        text: 'Are you sure you want to clear all items from the cart?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = [];
            document.getElementById('amountPaid').value = 0;
            document.getElementById('cartDiscountType').value = 'none';
            document.getElementById('cartDiscountRate').value = 0;
            toggleCartDiscount();
            updateCartDisplay();
            
            Swal.fire({
                icon: 'success',
                title: 'Cart Cleared!',
                text: 'All items have been removed from the cart',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush 