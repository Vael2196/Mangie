<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ValidTurnstile implements ValidationRule
{
    public function __construct(
        private readonly ?string $ipAddress = null
    ) {
    }

    /**
     * Validate a Cloudflare Turnstile response token.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (!config('services.turnstile.enabled')) {
            return;
        }

        $secret = (string) config(
            'services.turnstile.secret_key'
        );

        if ($secret === '') {
            $fail(
                'Human verification is temporarily unavailable. Please try again later.'
            );

            return;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(5)
                ->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    array_filter([
                        'secret' => $secret,
                        'response' => (string) $value,
                        'remoteip' => $this->ipAddress,
                    ])
                );
        } catch (\Throwable) {
            $fail(
                'Human verification could not be completed. Please try again.'
            );

            return;
        }

        $expectedHostname = (string) config(
            'services.turnstile.hostname'
        );

        $valid = $response->successful()
            && $response->json('success') === true
            && $response->json('action') === 'register'
            && (
                $expectedHostname === ''
                || $response->json('hostname')
                    === $expectedHostname
            );

        if (!$valid) {
            $fail(
                'Human verification failed. Please try again.'
            );
        }
    }
}
