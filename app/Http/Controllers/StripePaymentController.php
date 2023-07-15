<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Stripe;

class StripePaymentController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripe()
    {
        return view('stripe');
    }

   /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripePost(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $customer = Stripe\Customer::create(array(
            "address" => [
                "line1" => "Virani Chowk",
                "postal_code" => "360001",
                "city" => "Rajkot",
                "state" => "GJ",
                "country" => "IN",
            ],
            "email" => "demo@gmail.com",
            "name" => "Zahid Hasan",
            "source" => $request->stripeToken
        ));

        Stripe\PaymentIntent::create([
            "amount" => 100 * 100,
            "currency" => "usd",
            "customer" => $customer->id,
            "description" => "Test payment from User",
        ]);

        Session::flash('success', 'Payment successful!');

        return back();
    }

    public function handleWebhook(Request $request)
    {
        Session::put('webhook', 'Webhook called');
        // Retrieve the request's body and verify the signature
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = 'whsec_VqrVEtD4GwC3AnEyI2Nozer8qsNORBoV'; // Replace with your actual webhook secret key

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            // Handle the event based on its type
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    // Handle successful payment intent
                    break;
                case 'payment_intent.failed':
                    // Handle failed payment intent
                    break;
                // Add more cases for other event types you want to handle
                default:
                    // Unexpected event type
                    break;
            }

            return new Response('Yes!! Webhook handled successfully', 200);
        } catch (\Exception $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }
    }

}
