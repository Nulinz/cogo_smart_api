<?php

namespace App\Services;

use App\Models\Quality;
use App\Models\Transport;
use App\Models\Truck_capacity;
use Illuminate\Support\Facades\Auth;

class Base_ser
{
    public static function create_quality(array $data)
    {

        // return Quality::updateOrCreate(
        //     ['id' => $data['quality_id'] ?? null],
        //     [
        //         'quality' => $data['quality'],
        //         'status' => $data['status'] ?? 'active',
        //         'c_by' => $data['c_by'],
        //     ]
        // );

        if (! empty($data['quality_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Quality::where('id', $data['quality_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Quality::where('id', $data['quality_id'])->update([
                    'quality' => $data['quality'],
                ]);
            }

        } else {
            // CREATE: full insert
            return Quality::create([
                'quality' => $data['quality'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }
    }

    public static function create_transport(array $data)
    {
        if (! empty($data['transport_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Transport::where('id', $data['transport_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Transport::where('id', $data['transport_id'])->update([
                    'transport' => $data['transport'],
                    'phone' => $data['phone'],
                ]);
            }

        } else {
            // CREATE: full insert
            return Transport::create([
                'transport' => $data['transport'],
                'phone' => $data['phone'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
            );
        }
    }

    // Add other common service methods here

    public static function create_truck(array $data)
    {
        if (! empty($data['truck_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Truck_capacity::where('id', $data['truck_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Truck_capacity::where('id', $data['truck_id'])->update([
                    'capacity' => $data['capacity'],
                    'charge' => $data['charge'],
                ]);
            }

        } else {
            // CREATE: full insert
            return Truck_capacity::create([
                'capacity' => $data['capacity'],
                'charge' => $data['charge'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }

    }
}
