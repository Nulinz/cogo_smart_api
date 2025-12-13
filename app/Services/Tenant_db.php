<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Tenant_db
{
    public static function connect(string $dbName)
    {
        Config::set('database.connections.tenant.database', $dbName);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public static function main()
    {
        // Reconnect to default MySQL DB
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    public static function create_tenant_db(string $dbName)
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS {$dbName}");

        // 2️⃣ Dynamically set tenant DB
        config(['database.connections.tenant.database' => $dbName]);

        // 3️⃣ Reset tenant connection
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => '/database/migrations/tenant',
            '--force' => true,
        ]);
    }
}
