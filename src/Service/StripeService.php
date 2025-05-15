<?php
namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private string $secretKey;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(ParameterBagInterface $params, UrlGeneratorInterface $urlGenerator)
    {
        $this->secretKey = $params->get('stripe.secret_key');
        Stripe::setApiKey($this->secretKey);
        $this->urlGenerator = $urlGenerator;
    }

    public function createCheckoutSession(array $products): ?Session
    {
        $lineItems = array_map(fn($product) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $product['name']],
                'unit_amount' => $product['price'] * 100,
            ],
            'quantity' => $product['quantity'],
        ], $products);
    
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'offre_id' => $products[0]['metadata']['offre_id'] ?? null,
                ],
                'success_url' => $this->urlGenerator->generate(
                    'stripe_success',
                    ['session_id' => '{CHECKOUT_SESSION_ID}'],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'cancel_url' => $this->urlGenerator->generate(
                    'stripe_cancel',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]);
    
            return $session;
        } catch (ApiErrorException $e) {
            return null;
        }
    }
    
}
