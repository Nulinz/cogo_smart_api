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
    // public function handle(Request $request, Closure $next): Response
    // {
    //     // Get database name from request
    //     // $db = $request->db_name;

    //       $payload = JWTAuth::parseToken()->getPayload();

    //     // //  $payload = auth()->payload();

    //     //  $payload = auth()->payload(); // Safe, no double validation

        
    //     //  $token = JWTAuth::getToken();

    //     // $payload = JWTAuth::manager()
    //     //     ->getJWTProvider()
    //     //     ->decode($token);

    //     // $db = $payload['db_name'] ?? null;

    //     $db = $payload->get('db_name') ?? null;

    //         //  \Log::info("payload db_name: ".$db);



    //     // $dbName = $payload->get('db_name');

    //     // $db = $payload->get('db_name');

     
      

    //     // dd($db);

    //     if (! $db) {
    //         return response()->json(['error' => 'Missing database name'], 400);
    //     }

    //     // Set tenant connection dynamically
    //     Config::set('database.connections.tenant.database', $db);

    //     // // Refresh DB connection
    //     // DB::purge('tenant');
    //     // DB::reconnect('tenant');

    //       // 🔥 VERY IMPORTANT
    //     DB::disconnect('tenant');
    //     DB::purge('tenant');
    //     DB::reconnect('tenant');

    //     // 🔥 SET DEFAULT CONNECTION (CRITICAL)
    //     DB::setDefaultConnection('tenant');

    //     return $next($request);
    // }

     public function handle(Request $request, Closure $next): Response
    {
        try {

            config([
                    'auth.defaults.guard' => 'tenant'
                ]);
            $token = JWTAuth::getToken();

                if (!$token) {
                    return response()->json(['error' => 'Token not provided'], 401);
                }

                // ✅ SAFE decode (no DB hit)
                $payload = JWTAuth::manager()
                    ->getJWTProvider()
                    ->decode($token);

                $db = $payload['db_name'] ?? null;
                $sub = $payload['sub'] ?? null;

                // \Log::info("Payload DB: ".$db." | Sub: ".$sub);

                if (!$db) {
                    return response()->json(['error' => 'Invalid token (no DB)'], 401);
                }

                // 🔥 RESET AUTH CACHE
                app('auth')->forgetGuards();

                // 🔥 SWITCH DB
                config([
                    'database.default' => 'tenant',
                    'database.connections.tenant.database' => $db,
                ]);

                DB::disconnect('tenant');
                DB::purge('tenant');
                DB::reconnect('tenant');
                DB::setDefaultConnection('tenant');

                // 🔥 ADD THIS (MISSING PIECE)
                app('cache')->forgetDriver('database');

                $testUser = \App\Models\User::on('tenant')->find($sub);

                // \Log::info('Manual user check', [
                //     'user_found' => $testUser ? true : false,
                //     'user_id' => $testUser->id ?? null
                // ]);

               
                app('auth')->forgetGuards();

                // \Log::info([
                //     'provider_connection' => app('auth')->guard('tenant')->getProvider()
                //         ->createModel()->getConnectionName(),
                //     'actual_db' => DB::connection()->getDatabaseName(),
                // ]);

                // 🔥 AUTH AFTER DB READY
                $user = JWTAuth::setToken($token)->authenticate();

                if (!$user) {
                    return response()->json(['error' => 'User not found'], 401);
                }

                auth()->setUser($user);

                 // Save values for use in controller
                $request->merge([
                    'tenant_db' => $db,
                    // 'tenant_user_id' => $sub,
                ]);

                
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
