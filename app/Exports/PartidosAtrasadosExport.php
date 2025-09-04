<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PartidosAtrasadosExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $datos;
    protected $torneo;
    protected $categoria;

    public function __construct($datos, $torneo, $categoria)
    {
        $this->datos = $datos;
        $this->torneo = $torneo;
        $this->categoria = $categoria;
    }

    public function array(): array
    {
        return array_map(function($item) {
            return [
                $item['Jugador'],
                $item['Categoria'] ?? 'Sin categoría',
                $item['Grupo'] ?? 'Sin grupo',
                $item['Semanas Transcurridas'],
                $item['Partidos Jugados'],
                $item['Partidos Pendientes'],
                $item['Partidos Atrasados'],
                $item['Fecha Inicio Torneo']
            ];
        }, $this->datos);
    }

    public function headings(): array
    {
        return [
            'Jugador',
            'Categoría',
            'Grupo',
            'Semanas Transcurridas',
            'Partidos Jugados',
            'Partidos Pendientes',
            'Partidos Atrasados',
            'Fecha Inicio Torneo'
        ];
    }

    public function title(): string
    {
        return 'Jugadores Atrasados - ' . $this->categoria;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ]);

        // Formatear columnas numéricas para mostrar ceros (ahora D, E, F, G)
        $lastRow = count($this->datos) + 1;
        $sheet->getStyle("D2:G{$lastRow}")->getNumberFormat()->setFormatCode('0');

        // Colorear filas según el estado
        foreach ($this->datos as $index => $item) {
            $rowNumber = $index + 2; // +2 porque empezamos en fila 2 (después del header)
            
            if (isset($item['Color']) && $item['Color'] == 'Rojo') {
                $sheet->getStyle("A{$rowNumber}:H{$rowNumber}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'] // Rojo claro
                    ]
                ]);
            } elseif (isset($item['Color']) && $item['Color'] == 'Amarillo') {
                $sheet->getStyle("A{$rowNumber}:H{$rowNumber}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'] // Amarillo claro
                    ]
                ]);
            }
        }

        return [];
    }
}