<?php

it('switches the locale and persists it in the session', function () {
    $this->from('/dashboard')
        ->put('/locale', ['locale' => 'en'])
        ->assertRedirect('/dashboard')
        ->assertSessionHas('app_locale', 'en');
});

it('rejects an unsupported locale', function () {
    $this->from('/dashboard')
        ->put('/locale', ['locale' => 'de'])
        ->assertSessionHasErrors('locale');
});

it('rejects a missing locale', function () {
    $this->from('/dashboard')
        ->put('/locale', [])
        ->assertSessionHasErrors('locale');
});

it('registers the switch route with a name', function () {
    expect(route('locale.update'))->toEndWith('/locale');
});
