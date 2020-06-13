<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthCheckController
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
