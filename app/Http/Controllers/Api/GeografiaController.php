<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Aldea;
use App\Models\Pais;

// Asumiendo que tienes este modelo

class GeografiaController extends Controller
{
    // Carga todos los países
    public function getPaises()
    {
        // Deberías crear el modelo 'Pais'
         return Pais::orderBy('nombre')->get(['id', 'nombre']);

        // --- Temporal si no tienes modelo 'Pais' ---
        // Voy a devolver los departamentos por ahora
      //  return Departamento::orderBy('nombre')->get(['id', 'nombre']);
    }

    // Carga Departamentos (asumiré que no dependen de País por ahora, como en tu SQL)
    public function getDepartamentos()
    {
        return Departamento::orderBy('nombre')->get(['id', 'nombre']);
    }

    // Carga Municipios filtrados por Departamento
    public function getMunicipios($departamentoId)
    {
        return Municipio::where('departamento_id', $departamentoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    // Carga Aldeas filtradas por Municipio
    public function getAldeas($municipioId)
    {
        return Aldea::where('municipio_id', $municipioId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
}
