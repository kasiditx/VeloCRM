<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Livewire\LivewireServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Livewire is available for testing
        if (!app()->providerIsLoaded(LivewireServiceProvider::class)) {
            app()->register(LivewireServiceProvider::class);
        }
    }
}
