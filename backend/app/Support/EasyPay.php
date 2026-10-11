<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * EasyPay (easypaybd.online) merchant API: bKash / Nagad / Rocket payments confirmed by the merchant's SIM listener.
 * Docs: https://www.easypaybd.online/docs/
 *
 * The webhook carries no signature, so a payment is only ever trusted after verify() asks EasyPay directly.
 */
class EasyPay
{
    public static function configured(): bool
    {
        return (string) config('services.easypay.api_key') !== '';
    }

    /**
     * @param  array{amount: float|int|string, customer_name: string, customer_phone: string, customer_email?: string|null, callback_url: string, webhook_url?: string|null}  $payment
     * @return array{transaction_id: string, checkout_url: string, raw: array<string, mixed>}
     */
    public static function createPayment(array $payment): array
    {
        $body = array_filter([
            'amount' => (float) $payment['amount'],
            'currency' => 'BDT',
            'customer_name' => $payment['customer_name'],
            'customer_email' => $payment['customer_email'] ?? null,
            'customer_phone' => $payment['customer_phone'],
            'callback_url' => $payment['callback_url'],
            'webhook_url' => $payment['webhook_url'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $data = self::post('create-payment', $body, 20);
        if (empty($data['transaction_id']) || empty($data['checkout_url'])) {
            throw new RuntimeException('EasyPay did not return a checkout link.');
        }

        return ['transaction_id' => (string) $data['transaction_id'], 'checkout_url' => (string) $data['checkout_url'], 'raw' => $data];
    }

    /**
     * @return array{completed: bool, amount: float|null, trx_id: string|null, payment_method: string|null, sender_number: string|null, payment_status: string|null, raw: array<string, mixed>}
     */
    public static function verify(string $transactionId, int $timeout = 10): array
    {
        $data = self::post('verify-payment', ['transaction_id' => $transactionId], $timeout);
        $paymentStatus = strtolower((string) ($data['payment_status'] ?? $data['status'] ?? ''));

        return [
            'completed' => $paymentStatus === 'completed',
            'payment_status' => $paymentStatus ?: null,
            'amount' => isset($data['amount']) && is_numeric($data['amount']) ? (float) $data['amount'] : null,
            'trx_id' => isset($data['trx_id']) ? (string) $data['trx_id'] : null,
            'payment_method' => isset($data['payment_method']) ? (string) $data['payment_method'] : null,
            'sender_number' => isset($data['sender_number']) ? (string) $data['sender_number'] : null,
            'raw' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private static function post(string $endpoint, array $body, int $timeout): array
    {
        if (! self::configured()) {
            throw new RuntimeException('EasyPay API key is not set (EASYPAY_API_KEY).');
        }

        $base = rtrim((string) config('services.easypay.base_url'), '/');
        try {
            $response = Http::withHeaders(['X-API-Key' => (string) config('services.easypay.api_key')])
                ->acceptJson()->asJson()->timeout($timeout)
                ->post("{$base}/{$endpoint}", $body);
        } catch (ConnectionException $e) {
            throw new RuntimeException('EasyPay could not be reached: '.$e->getMessage(), 0, $e);
        }

        $data = $response->json();
        if ($response->failed() || ! is_array($data)) {
            throw new RuntimeException('EasyPay '.$endpoint.' failed with HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300));
        }

        // Some responses wrap the payload in "data".
        return isset($data['data']) && is_array($data['data']) ? [...$data, ...$data['data']] : $data;
    }
}
