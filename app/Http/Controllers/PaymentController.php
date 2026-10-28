<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Throwable;

/** Same controller for every bank/account; the {account} in the URL picks the gateway. */
class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    /** GET /pay/{account} — payment form. A signed pay link (PaymentService::payLink) locks the amount. */
    // public function create(string $account)
    // {
    //     return view('payment.create', ['bankLabel' => config("payments.accounts.$account.label")]);
    // }

    /** POST /pay/{account} — validate, create payment, send customer to the bank. */
    public function create(Request $request, string $account)
    {
        if($request->isMethod('post')){

        
        $data = $request->validate([
            'customer_name'   => ['required', 'string', 'min:2', 'max:45'],
            'customer_email'  => ['required', 'email', 'max:48'],
            'customer_mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'amount'          => ['required', 'numeric', 'min:1', 'max:1000000'],
            'order_ref'       => ['nullable', 'alpha_dash', 'max:32'],
        ], [], ['customer_name' => 'name', 'customer_email' => 'email', 'customer_mobile' => 'mobile number']);

        if ($request->hasValidSignature()) {   // pay link: amount/ref come from the signed URL, not the form
            $data['amount'] = $request->query('amount');
            $data['order_ref'] = $request->query('order_ref');
        }

        try {
            $bank = $this->payments->start($account, $data + ['user_id' => $request->user()?->id]);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['payment' => 'The bank could not start this payment. Try again in a minute.']);
        }

        return $bank->fields
            ? view('payment.redirect', ['bank' => $bank])   // bank wants a POST form
            : redirect()->away($bank->url);
        }
        return view('payment.create', ['bankLabel' => config("payments.accounts.$account.label")]);
    }

    /** POST /payment/{account}/callback — the bank's page posts the result here through the customer's browser. */
    public function callback(Request $request, string $account)
    {
        $txnNo = rescue(fn () => $this->payments->notification($account, $this->bankData($request))['txn_no']);
        $payment = $txnNo ? rescue(
            fn () => $this->payments->sync($txnNo),              // real status from the bank
            fn () => Payment::firstWhere('txn_no', $txnNo)       // bank unreachable: show current status
        ) : null;

        return $payment
            ? redirect(URL::signedRoute('payment.result', $payment))
            : redirect()->route('payment.create', $account);
    }

    /** POST /payment/{account}/webhook — server-to-server notification. Non-200 makes the bank retry. */
    public function webhook(Request $request, string $account)
    {
        $this->payments->fromBank($account, $this->bankData($request));

        return response('OK');
    }

    /** GET /payment/{payment}/result (signed URL) — re-checks with the bank while still pending. */
    public function result(Payment $payment)
    {
        if (in_array($payment->status, ['INITIATED', 'PENDING'], true)) {
            $payment = rescue(fn () => $this->payments->sync($payment->txn_no), $payment);
        }

        return view('payment.result', ['payment' => $payment]);
    }

    /** Raw body, read before Laravel's TrimStrings middleware changes values (keeps signatures exact). */
    private function bankData(Request $request): array
    {
        if ($request->isJson()) {
            return (array) json_decode($request->getContent(), true);
        }
        parse_str($request->getContent(), $data);

        return $data ?: $request->post();
    }
}
