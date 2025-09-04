<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PartidosExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $datos;
    protected $torneoNombre;
    protected $categoriaNombre;

    public function __construct($datos, $torneoNombre, $categoriaNombre)
    {
        $this->datos = $datos;
        $this->torneoNombre = $torneoNombre;
        $this->categoriaNombre = $categoriaNombre;
    }

    public function array(): array
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Partido',
            'Fecha Inicio',
            'Fecha Final',
            'Resultado',
            'Ganador',
            'Sets Ganador',
            'Games Ganador',
            'Sets Perdedor',
            'Games Perdedor',
            'Estado',
            'Días Vencido'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para headers
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(15); // Grupo
        $sheet->getColumnDimension('B')->setWidth(40); // Partido
        $sheet->getColumnDimension('C')->setWidth(12); // Fecha Inicio
        $sheet->getColumnDimension('D')->setWidth(12); // Fecha Final
        $sheet->getColumnDimension('E')->setWidth(15); // Resultado
        $sheet->getColumnDimension('F')->setWidth(30); // Ganador
        $sheet->getColumnDimension('G')->setWidth(12); // Sets Ganador
        $sheet->getColumnDimension('H')->setWidth(14); // Games Ganador
        $sheet->getColumnDimension('I')->setWidth(12); // Sets Perdedor
        $sheet->getColumnDimension('J')->setWidth(14); // Games Perdedor
        $sheet->getColumnDimension('K')->setWidth(12); // Estado
        $sheet->getColumnDimension('L')->setWidth(12); // Días Vencido

        // Estilo para todo el contenido
        $lastRow = count($this->datos) + 1;
        $sheet->getStyle('A1:L' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Partidos - ' . $this->categoriaNombre;
    }
}