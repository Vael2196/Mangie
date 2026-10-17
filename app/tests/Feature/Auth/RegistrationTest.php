<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(
        route('verification.notice', absolute: false)
    );

    $user = User::where(
        'email',
        'test@example.com'
    )->firstOrFail();

    expect($user->hasVerifiedEmail())
        ->toBeFalse();

    Notification::assertSentTo(
        $user,
        VerifyEmail::class
    );
});

test('registration validates a turnstile token on the server', function () {
    Notification::fake();

    config()->set(
        'services.turnstile',
        [
            'enabled' => true,
            'site_key' => 'test-site-key',
            'secret_key' => 'test-secret-key',
            'hostname' => 'mangie.vaell.dev',
        ]
    );

    Http::fake([
        'challenges.cloudflare.com/*' =>
            Http::response([
                'success' => true,
                'hostname' => 'mangie.vaell.dev',
                'action' => 'register',
            ]),
    ]);

    $this->post('/register', [
        'name' => 'Protected User',
        'email' => 'protected@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'valid-token',
    ])->assertRedirect(
        route('verification.notice', absolute: false)
    );

    $this->assertAuthenticated();

    Http::assertSent(
        fn (ClientRequest $request) =>
            $request->url()
                === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret']
                === 'test-secret-key'
            && $request['response']
                === 'valid-token'
    );
});

test('registration rejects a failed turnstile challenge', function () {
    config()->set(
        'services.turnstile',
        [
            'enabled' => true,
            'site_key' => 'test-site-key',
            'secret_key' => 'test-secret-key',
            'hostname' => 'mangie.vaell.dev',
        ]
    );

    Http::fake([
        'challenges.cloudflare.com/*' =>
            Http::response([
                'success' => false,
                'error-codes' => [
                    'invalid-input-response',
                ],
            ]),
    ]);

    $this->post('/register', [
        'name' => 'Robot User',
        'email' => 'robot@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'invalid-token',
    ])->assertSessionHasErrors(
        'cf-turnstile-response'
    );

    $this->assertGuest();
    $this->assertDatabaseMissing(
        'users',
        ['email' => 'robot@example.com']
    );
});

test('registration requires turnstile when it is enabled', function () {
    config()->set(
        'services.turnstile.enabled',
        true
    );

    $this->post('/register', [
        'name' => 'Missing Challenge',
        'email' => 'missing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(
        'cf-turnstile-response'
    );

    $this->assertGuest();
});
