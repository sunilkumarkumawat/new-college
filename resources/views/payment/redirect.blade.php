@extends('payment.layout')

@section('title', 'Redirecting to your bank')

@section('content')
    {{-- Used only for banks that want a POST form instead of a plain redirect (BankRedirect with fields) --}}
    <h1>Redirecting to your bank</h1>
    <p>Please wait. If nothing happens, press the button.</p>
    <form id="bank" method="post" action="{{ $bank->url }}">
        @foreach ($bank->fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button type="submit">Continue to bank</button>
    </form>
    <script>document.getElementById('bank').submit();</script>
@endsection
