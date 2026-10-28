@extends('payment.layout')

@section('title', 'Make a payment')

@section('content')
    @php
        $locked = request()->hasValidSignature();   // signed pay link = fixed amount
        $fields = [
            'customer_name'   => ['Full name', 'text', 'autocomplete="name" maxlength="45"'],
            'customer_email'  => ['Email', 'email', 'autocomplete="email" maxlength="48"'],
            'customer_mobile' => ['Mobile number', 'tel', 'autocomplete="tel-national" inputmode="numeric" maxlength="10"'],
            'amount'          => ['Amount (₹)', 'number', 'min="1" step="0.01" inputmode="decimal"'.($locked ? ' readonly' : '')],
        ];
    @endphp

    <h1>Make a payment</h1>
    <p>You will pay on the secure {{ $bankLabel }} page by UPI, card or net banking.</p>
    @if (request('order_ref'))
        <p>Paying for: <strong>{{ request('order_ref') }}</strong></p>
    @endif
    @error('payment') <p class="err">{{ $message }}</p> @enderror

    {{-- Posts back to the same URL so a pay link's signature travels with it --}}
    <form method="post" action="{{ request()->getRequestUri() }}" novalidate>
        @csrf
        <input type="hidden" name="order_ref" value="{{ old('order_ref', request('order_ref')) }}">
        @foreach ($fields as $name => [$label, $type, $attrs])
            <label for="{{ $name }}">{{ $label }}</label>
            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" required {!! $attrs !!}
                   value="{{ $locked && $name === 'amount' ? request('amount') : old($name, request()->query($name)) }}">
            @error($name) <p class="err">{{ $message }}</p> @enderror
        @endforeach
        <button type="submit">Continue to payment</button>
    </form>
@endsection
