<?php

namespace App\Services\Routing;

class GeoUtils
{
    const EARTH_RADIUS_M = 6371000;
    const BUS_SPEED_KMH = 20;
    const WALKING_SPEED_MS = 1.4;
    const WAIT_TIME_S = 300;

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) ** 2;
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return self::EARTH_RADIUS_M * $c;
    }

    public static function walkingTime(float $distanceM): int
    {
        return (int) ceil($distanceM / self::WALKING_SPEED_MS);
    }

    public static function busTime(float $distanceM): int
    {
        $speedMs = self::BUS_SPEED_KMH / 3.6;
        return (int) ceil($distanceM / $speedMs);
    }
}
