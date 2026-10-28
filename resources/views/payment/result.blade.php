@extends('payment.layout')

@php
    $pending = in_array($payment->status, ['INITIATED', 'PENDING'], true);
    [$title, $text, $class] = match (true) {
        $payment->status === 'SUCCESS' => ['Payment received', 'Thank you. Your payment is confirmed. Keep the reference below for your records.', 'ok'],
        $pending => ['Payment is processing', 'Your bank has not confirmed this payment yet. Do not pay again; check again in a minute.', ''],
        default  => ['Payment not completed', 'This payment did not go through. If money was debited from your account, contact us with the reference below.', 'bad'],
    };
@endphp

@section('title', $title)

@section('content')
    <h1 class="{{ $class }}">{{ $title }}</h1>
    <p>{{ $text }}</p>
    <dl>
        <dt>Reference</dt><dd>{{ $payment->txn_no }}</dd>
        <dt>Amount</dt><dd>₹{{ number_format((float) $payment->amount, 2) }}</dd>
        @if ($payment->bank_txn_id)
            <dt>Bank ref</dt><dd>{{ $payment->bank_txn_id }}</dd>
        @endif
    </dl>

    @if ($payment->status === 'SUCCESS')
        <a class="btn" href="{{ url('/') }}">Back to website</a>
    @elseif ($pending)
        <a class="btn" href="{{ url()->full() }}">Check status again</a>
    @else
        <a class="btn" href="{{ route('payment.create', array_filter(['account' => $payment->gateway, 'amount' => $payment->amount, 'order_ref' => $payment->order_ref])) }}">Try again</a>
    @endif
@endsection
