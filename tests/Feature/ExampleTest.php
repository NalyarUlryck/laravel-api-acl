<?php

use function Pest\Laravel\get;

it('should return status code 200', fn () => get('/')->assertStatus(200));
