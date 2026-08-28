<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class TipoVehiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['codigo' => 'SW', 'nombre' => 'Station Wagon'],
            ['codigo' => 'SD', 'nombre' => 'Sedán'],
            ['codigo' => 'HB', 'nombre' => 'Hatchback'],
            ['codigo' => 'PU', 'nombre' => 'Camioneta/Pickup'],
            ['codigo' => 'SUV', 'nombre' => 'SUV'],
            ['codigo' => 'VN', 'nombre' => 'Furgón'],
            ['codigo' => 'CM', 'nombre' => 'Camión'],
        ];

        foreach ($tipos as $tipo) {
            TipoVehiculo::updateOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }
    }
}
