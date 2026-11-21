<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CleanPublicPrefixFromLinks;


Route::any('/public/{any?}', function () {
    abort(404);
})->where('any', '.*');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';
require __DIR__ . '/backend.php';


Route::get('/auth.md', function () {
    $content = "# Auth.md\n\n" .
               "This document provides instructions for AI agents interacting with our platform.\n\n" .
               "## Agent Registration\n" .
               "- **Register Endpoint**: " . url('/api/v1/agent/register') . "\n" .
               "- **Supported Identity Types**: `agent`, `service_account`\n" .
               "- **Supported Credential Types**: `oauth2_bearer`\n\n" .
               "## OAuth 2.0 Configuration\n" .
               "- **Authorization Server**: " . url('/.well-known/oauth-authorization-server') . "\n" .
               "- **Protected Resource**: " . url('/.well-known/oauth-protected-resource') . "\n\n" .
               "```json\n" .
               "{\n" .
               "  \"agent_auth\": {\n" .
               "    \"register_uri\": \"" . url('/api/v1/agent/register') . "\",\n" .
               "    \"supported_identity_types\": [\"agent\", \"service_account\"],\n" .
               "    \"supported_credential_types\": [\"oauth2_bearer\"],\n" .
               "    \"claim_uri\": \"" . url('/api/v1/agent/claim') . "\",\n" .
               "    \"revocation_uri\": \"" . url('/api/v1/agent/revoke') . "\"\n" .
               "  }\n" .
               "}\n" .
               "```";

    return response($content, 200, [
        'Content-Type' => 'text/markdown; charset=UTF-8'
    ]);
});

// 2. OAuth Protected Resource Metadata
Route::get('/.well-known/oauth-protected-resource', function () {
    return response()->json([
        "resource" => url('/api'),
        "authorization_servers" => [url('/')],
        "scopes_supported" => ["api:access"],
        "bearer_methods_supported" => ["header"]
    ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
});

// 3. OAuth Authorization Server Metadata
Route::get('/.well-known/oauth-authorization-server', function () {
    return response()->json([
        "issuer" => url('/'),
        "authorization_endpoint" => url('/oauth/authorize'),
        "token_endpoint" => url('/oauth/token'),
        "registration_endpoint" => url('/api/v1/agent/register'),
        "code_challenge_methods_supported" => ["S256"],
        "token_endpoint_auth_methods_supported" => ["none", "client_secret_post", "client_secret_basic"],
        "agent_auth" => [
            "register_uri" => url('/api/v1/agent/register'),
            "supported_identity_types" => ["agent", "service_account"],
            "supported_credential_types" => ["oauth2_bearer"]
        ]
    ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
});

Route::middleware(['web', CleanPublicPrefixFromLinks::class])
    ->group(function () {
        require __DIR__ . '/frontend.php';
    });
