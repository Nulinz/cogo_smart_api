<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class Dynamic_db
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get database name from request
        // $db = $request->db_name;

        $payload = JWTAuth::parseToken()->getPayload();

        // $dbName = $payload->get('db_name');

        $db = $payload->get('db_name');

        // dd($db);

        if (! $db) {
            return response()->json(['error' => 'Missing database name'], 400);
        }

        // Set tenant connection dynamically
        Config::set('database.connections.tenant.database', $db);

        // Refresh DB connection
        DB::purge('tenant');
        DB::reconnect('tenant');

        return $next($request);
    }
}
