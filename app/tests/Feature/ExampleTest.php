<?php

it('renders the interactive landing page', function () {
    $response = $this->get('/');

    $response
        ->assertStatus(200)
        ->assertSee('data-landing-tilt', false)
        ->assertSee('data-landing-shader', false)
        ->assertSee('Ready to get your work organised?');
});
