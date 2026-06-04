<?php

namespace Tests\Unit;

use App\Services\Routing\GeoUtils;
use Tests\TestCase;

class GeoUtilsTest extends TestCase
{
    /**
     * Test que Haversine calcula correctamente la distancia entre dos puntos.
     * Distancia conocida: Centro de Zitácuaro a punto cercano (~500m)
     */
    public function test_haversine_calculates_distance_correctly(): void
    {
        $lat1 = 19.4357;
        $lng1 = -100.3571;
        $lat2 = 19.4320;
        $lng2 = -100.3550;

        $distance = GeoUtils::haversine($lat1, $lng1, $lat2, $lng2);

        $this->assertGreaterThan(300, $distance);
        $this->assertLessThan(600, $distance);
    }

    /**
     * Test que Haversine retorna 0 para el mismo punto.
     */
    public function test_haversine_returns_zero_for_same_point(): void
    {
        $lat = 19.4357;
        $lng = -100.3571;

        $distance = GeoUtils::haversine($lat, $lng, $lat, $lng);

        $this->assertEquals(0, $distance);
    }

    /**
     * Test que Haversine calcula distancias largas correctamente.
     * Zitácuaro a Morelia (~100km)
     */
    public function test_haversine_calculates_long_distances(): void
    {
        $zitacuaro = [19.4357, -100.3571];
        $morelia = [19.7008, -101.1844];

        $distance = GeoUtils::haversine(
            $zitacuaro[0], $zitacuaro[1],
            $morelia[0], $morelia[1]
        );

        $this->assertGreaterThan(90000, $distance);
        $this->assertLessThan(110000, $distance);
    }

    /**
     * Test que walkingTime calcula correctamente basado en 1.4 m/s.
     */
    public function test_walking_time_calculation(): void
    {
        $distance = 140;
        $expectedTime = 100;

        $time = GeoUtils::walkingTime($distance);

        $this->assertEquals($expectedTime, $time);
    }

    /**
     * Test que walkingTime redondea hacia arriba.
     */
    public function test_walking_time_rounds_up(): void
    {
        $distance = 100;
        $expectedTime = 72;

        $time = GeoUtils::walkingTime($distance);

        $this->assertEquals($expectedTime, $time);
    }

    /**
     * Test que busTime calcula correctamente basado en 20 km/h.
     */
    public function test_bus_time_calculation(): void
    {
        $distance = 1000;
        $expectedTime = 180;

        $time = GeoUtils::busTime($distance);

        $this->assertEquals($expectedTime, $time);
    }

    /**
     * Test que busTime redondea hacia arriba.
     */
    public function test_bus_time_rounds_up(): void
    {
        $distance = 500;
        $expectedTime = 90;

        $time = GeoUtils::busTime($distance);

        $this->assertEquals($expectedTime, $time);
    }

    /**
     * Test que las constantes tienen valores esperados.
     */
    public function test_constants_have_expected_values(): void
    {
        $this->assertEquals(6371000, GeoUtils::EARTH_RADIUS_M);
        $this->assertEquals(20, GeoUtils::BUS_SPEED_KMH);
        $this->assertEquals(1.4, GeoUtils::WALKING_SPEED_MS);
        $this->assertEquals(300, GeoUtils::WAIT_TIME_S);
    }
}
