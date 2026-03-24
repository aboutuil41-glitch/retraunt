<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Food API',
    description: 'API for managing dishes, categories, ingredients, and AI-powered dietary recommendations.',
    version: '1.0.0',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
)]
#[OA\Server(
    url: '/api',
    description: 'API Server',
)]
abstract class Controller {}