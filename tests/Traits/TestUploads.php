<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;

trait TestUploads
{
    protected function assertInvalidationFile(
        string $field,
        string $extension,
        int $maxSize,
        string $rule,
        array $ruleParams = []
    ) {
        $routes = [
            [
                'method' => 'POST',
                'route' => $this->routeStore()
            ],
            [
                'method' => 'PUT',
                'route' => $this->routeUpdate()
            ]
        ];

        foreach ($routes as $route) {
            $file = UploadedFile::fake()->create("$field.$extension");
            $response = $this->json($route['method'], $route['route'], [
                $field => $file
            ]);
            $this->assertInvalidationFields($response, [$field], $rule, $ruleParams);

            $response = $this->json($route['method'], $route['route'], [
                $field => $file
            ]);

            $file = UploadedFile::fake()->create("$field.$extension")->size($maxSize + 1);
            $this->assertInvalidationFields($response, [$field], 'max.file', ['max' => $maxSize]);
        }
    }
}
