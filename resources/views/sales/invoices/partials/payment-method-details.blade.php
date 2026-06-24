@php
    $companyName = $invoice->company->name ?? '';
@endphp
@if($bankAccounts && $bankAccounts->count() > 0)
<div class="payment-method-bar" style="text-align: center;">
    <strong>PAYMENT METHOD :</strong>
</div>
<div class="payment-details">
    @foreach($bankAccounts as $account)
        @php
            $isCashRegister = stripos($account->name ?? '', 'cash register') !== false;
        @endphp
        <div style="display: block; margin-bottom: 4px;">
            @if($isCashRegister)
                <strong>{{ strtoupper('Cash Register') }}:</strong> {{ $account->account_number ?? 'N/A' }}
            @else
                <strong>{{ strtoupper($account->name ?? 'BANK') }} - {{ strtoupper($companyName) }} - {{ strtoupper($account->currency ?? 'TZS') }}:</strong>
                {{ $account->account_number ?? 'N/A' }}
                @if(filled($account->swift_code))
                    | SWIFT: {{ $account->swift_code }}
                @endif
                @if(filled($account->bank_branch_name))
                    | BRANCH: {{ strtoupper($account->bank_branch_name) }}
                @endif
            @endif
        </div>
    @endforeach
</div>
@endif
