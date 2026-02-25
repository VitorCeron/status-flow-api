<?php

namespace Tests\Traits;

use App\Models\Timezone;

trait TimezoneTestTrait 
{
    /**
     * Obter timezone a partir do nome
     * 
     * @param string $timezoneName
     * @return Timezone|null
     */
    private function getTimezoneByName(string $timezoneName): ?Timezone
    {
        // Arrange: Buscar timezone America/Sao_Paulo no banco
        $timezone = Timezone::where('name', $timezoneName)->first();
        $this->assertNotNull($timezone, "Timezone {$timezoneName} não encontrado no banco");

        return $timezone;
    }

    private function getSaoPauloTimezoneName(): string
    {
        return 'America/Sao_Paulo';
    }
}