<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TemplateExport extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithEvents, WithColumnFormatting, WithCustomValueBinder
{
    private string $type;

    private array $config = [
        'balita' => [
            'title' => 'TEMPLATE IMPORT DATA BALITA / ANAK',
            'note' => 'Isi data mulai baris ke-4. Jangan mengubah nama kolom pada baris ke-3.',
            'headers' => [
                'nama_lengkap',
                'nik_balita',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'nama_ibu',
                'nik_ibu',
                'berat_lahir_kg',
                'panjang_lahir_cm',
                'alamat_lengkap',
            ],
            'sample' => [
                'Ahmad Fauzan',
                '3326123456789001',
                'L',
                'Pekalongan',
                '2021-05-17',
                'Siti Aminah',
                '3326123456789002',
                '3.2',
                '49',
                'Dusun Krajan RT 01 RW 02',
            ],
            'text_columns' => ['B', 'G'],
        ],

        'remaja' => [
            'title' => 'TEMPLATE IMPORT DATA REMAJA',
            'note' => 'Isi data mulai baris ke-4. Jangan mengubah nama kolom pada baris ke-3.',
            'headers' => [
                'nik',
                'nama_lengkap',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'nama_sekolah',
                'kelas',
                'nama_ortu',
                'no_hp_ortu',
                'alamat_lengkap',
            ],
            'sample' => [
                '3326123456789011',
                'Nadia Putri',
                'P',
                'Pekalongan',
                '2010-08-20',
                'SMP Negeri 1',
                'VIII',
                'Budi Santoso',
                '081234567890',
                'Dusun Krajan RT 03 RW 01',
            ],
            'text_columns' => ['A', 'I'],
        ],

        'lansia' => [
            'title' => 'TEMPLATE IMPORT DATA LANSIA',
            'note' => 'Isi data mulai baris ke-4. Jangan mengubah nama kolom pada baris ke-3.',
            'headers' => [
                'nik',
                'nama_lengkap',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'riwayat_penyakit',
                'alamat_lengkap',
            ],
            'sample' => [
                '3326123456789021',
                'Slamet Riyadi',
                'L',
                'Pekalongan',
                '1958-03-12',
                'Hipertensi',
                'Dusun Krajan RT 04 RW 02',
            ],
            'text_columns' => ['A'],
        ],
    ];

    public function __construct(string $type)
    {
        $this->type = array_key_exists($type, $this->config) ? $type : 'balita';
    }

    /**
     * Paksa semua string ditulis sebagai teks.
     * Ini mencegah NIK berubah jadi 3.32612E+15, karena Excel sok jenius.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        $data = $this->config[$this->type];

        return [
            [$data['title']],
            [$data['note']],
            $data['headers'],
            $data['sample'],
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->config[$this->type]['text_columns'] as $column) {
            $formats[$column] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data = $this->config[$this->type];

                $columnCount = count($data['headers']);
                $lastColumn = $this->columnLetter($columnCount);

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '064E3B'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D1FAE5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => '92400E'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '059669'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'A7F3D0'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                ]);

                foreach ($data['text_columns'] as $column) {
                    $sheet->getStyle("{$column}4:{$column}1000")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $sheet->getStyle("{$column}4:{$column}1000")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getStyle("A1:{$lastColumn}1000")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(26);
                $sheet->getRowDimension(4)->setRowHeight(24);

                $sheet->freezePane('A4');

                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function columnLetter(int $columnNumber): string
    {
        $letter = '';

        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $letter = chr(65 + $modulo) . $letter;
            $columnNumber = intdiv($columnNumber - $modulo, 26);
        }

        return $letter;
    }
}