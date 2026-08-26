<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class RouteHealthCheckTest extends TestCase
{
    public function test_all_get_routes_and_views()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $routes = Route::getRoutes();
        $errors = [];
        $tested = 0;

        foreach ($routes as $route) {
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            $uri = $route->uri();

            // Skip parameterized routes or logout
            if (preg_match('/\{[^\}]+\}/', $uri) || in_array($uri, ['logout', 'logout2', 'storage/{path}'])) {
                continue;
            }

            $tested++;

            try {
                $response = $this->actingAs($user)
                    ->withSession([
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_group' => $user->user_group,
                        'user_cabang' => $user->user_cabang,
                        'cabang_aktif' => $user->user_cabang,
                    ])
                    ->get('/' . ltrim($uri, '/'));

                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $errors[] = [
                        'uri' => $uri,
                        'name' => $route->getName(),
                        'action' => $route->getActionName(),
                        'status' => $status,
                        'exception' => $response->exception ? $response->exception->getMessage() . ' in ' . $response->exception->getFile() . ':' . $response->exception->getLine() : 'Server Error 500',
                    ];
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'status' => 'EXCEPTION',
                    'exception' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
                ];
            }
        }

        if (!empty($errors)) {
            $msg = "Encountered " . count($errors) . " errors during route health check (Tested $tested routes):\n\n";
            foreach ($errors as $idx => $err) {
                $msg .= ($idx + 1) . ") URI: /" . $err['uri'] . " (Route: " . $err['name'] . ", Action: " . $err['action'] . ")\n";
                $msg .= "   Status: " . $err['status'] . "\n";
                $msg .= "   Error: " . $err['exception'] . "\n\n";
            }
            $this->fail($msg);
        }

        $this->assertTrue(true, "All $tested GET routes responded successfully without server errors!");
    }
}
