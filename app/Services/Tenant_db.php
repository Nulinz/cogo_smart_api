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
        // DB::purge('mysql');
        // DB::reconnect('mysql');

        // Clean tenant connection
        DB::purge('tenant');

        // Reset default connection to main DB
        DB::setDefaultConnection('mysql');

        // Reconnect main DB
        DB::reconnect('mysql');
    }

    public static function create_tenant_db(string $dbName)
    {
        try {
            // 1️⃣ Create DB (cannot be rolled back)
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

            // 2️⃣ Set tenant DB dynamically
            config(['database.connections.tenant.database' => $dbName]);

            // 3️⃣ Reset tenant connection
            DB::purge('tenant');
            DB::reconnect('tenant');

            // 4️⃣ Run tenant migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            return true;

        } catch (\Exception $e) {

            // 🔥 MANUAL ROLLBACK (important)
            try {
                DB::statement("DROP DATABASE IF EXISTS `$dbName`");
            } catch (\Exception $dropEx) {
                \Log::critical('Failed to drop tenant DB after migration failure', [
                    'database' => $dbName,
                    'error'    => $dropEx->getMessage(),
                ]);
            }

            \Log::error('Tenant DB creation failed', [
                'database' => $dbName,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            throw $e;
        }
    }


    // public static function create_tenant_db(string $dbName)
    // {
    //     DB::statement("CREATE DATABASE IF NOT EXISTS {$dbName}");

    //     // 2️⃣ Dynamically set tenant DB
    //     config(['database.connections.tenant.database' => $dbName]);

    //     // 3️⃣ Reset tenant connection
    //     DB::purge('tenant');
    //     DB::reconnect('tenant');

    //     try{
    //         Artisan::call('migrate', [
    //             '--database' => 'tenant',
    //             '--path' => 'database/migrations/tenant',
    //             '--force' => true,
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error('Migration failed for tenant DB '.$dbName.': '.$e->getMessage());

    //          \Log::error('Tenant migration failed', [
    //         'database' => $dbName,
    //         'message'  => $e->getMessage(),
    //         'file'     => $e->getFile(),
    //         'line'     => $e->getLine(),
    //         'trace'    => $e->getTraceAsString(),
    //     ]);

    //         throw $e;
    //     }
    // }
}
