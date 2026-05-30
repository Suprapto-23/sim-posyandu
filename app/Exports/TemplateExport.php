<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateExport extends DefaultValueBinder implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithColumnFormatting, WithCustomValueBinder
{
    private string $type;

    private array $config = [
        'balita' => [
            'label' => 'Balita',
            'title' => 'TEMPLATE IMPORT DATA BALITA',
            'note' => 'Jangan mengubah nama kolom pada baris 3. Isi data mulai baris 4. Format tanggal disarankan YYYY-MM-DD. Kolom NIK harus tetap berbentuk teks 16 digit.',
            'headers' => [
                'nik_balita',
                'nama_lengkap',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'nama_ibu',
                'nik_ibu',
                'nama_ayah',
                'berat_lahir_kg',
                'panjang_lahir_cm',
                'alamat_lengkap',
            ],
            'sample' => [
                '3326090101230001',
                'Alya Putri',
                'P',
                'Pekalongan',
                '2023-01-01',
                'Siti Aminah',
                '3326094101900001',
                'Ahmad Fauzi',
                '3.2',
                '49',
                'Dukuh Bantarkulon RT 01 RW 02',
            ],
        ],

        'remaja' => [
            'label' => 'Remaja',
            'title' => 'TEMPLATE IMPORT DATA REMAJA',
            'note' => 'Jangan mengubah nama kolom pada baris 3. Isi data mulai baris 4. Format tanggal disarankan YYYY-MM-DD. Kolom NIK dan nomor HP harus tetap berbentuk teks.',
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
                '3326091208100001',
                'Rafi Maulana',
                'L',
                'Pekalongan',
                '2010-08-12',
                'SMP Negeri 1 Lebakbarang',
                '8A',
                'Budi Santoso',
                '081234567890',
                'Dukuh Bantarkulon RT 02 RW 01',
            ],
        ],

        'lansia' => [
            'label' => 'Lansia',
            'title' => 'TEMPLATE IMPORT DATA LANSIA',
            'note' => 'Jangan mengubah nama kolom pada baris 3. Isi data mulai baris 4. Format tanggal disarankan YYYY-MM-DD. Tekanan darah memakai format 120/80.',
            'headers' => [
                'nik',
                'nama_lengkap',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat_lengkap',
                'berat_badan',
                'tinggi_badan',
                'lingkar_perut',
                'tekanan_darah',
                'gula_darah',
                'kolesterol',
                'asam_urat',
                'tingkat_kemandirian',
                'riwayat_penyakit',
                'keluhan',
                'telepon_keluarga',
                'golongan_darah',
            ],
            'sample' => [
                '3326091503600001',
                'Slamet Riyadi',
                'L',
                'Pekalongan',
                '1960-03-15',
                'Dukuh Bantarkulon RT 03 RW 02',
                '62',
                '160',
                '84',
                '130/85',
                '125',
                '190',
                '6.4',
                'mandiri',
                'Hipertensi ringan',
                'Kadang pusing saat pagi',
                '081234567891',
                'O',
            ],
        ],
    ];

    public function __construct(?string $type = 'balita')
    {
        $type = strtolower(trim((string) $type));

        $this->type = array_key_exists($type, $this->config)
            ? $type
            : 'balita';
    }

    public function array(): array
    {
        $data = $this->config[$this->type];
        $columnCount = count($data['headers']);

        return [
            $this->padRow([$data['title']], $columnCount),
            $this->padRow([$data['note']], $columnCount),
            $this->padRow($data['headers'], $columnCount),
            $this->padRow($data['sample'], $columnCount),
        ];
    }

    public function title(): string
    {
        return $this->config[$this->type]['label'];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $column = $cell->getColumn();

        if (in_array($column, $this->textColumns(), true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->textColumns() as $column) {
            $formats[$column] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '047857'],
                ],
            ],
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['rgb' => '64748B'],
                ],
            ],
            3 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '047857'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            4 => [
                'font' => [
                    'bold' => false,
                    'color' => ['rgb' => '334155'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $data = $this->config[$this->type];
                $headers = $data['headers'];
                $columnCount = count($headers);
                $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(34);
                $sheet->getRowDimension(3)->setRowHeight(28);
                $sheet->getRowDimension(4)->setRowHeight(26);

                $sheet->freezePane('A4');

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '047857'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
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
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle("A1:{$lastColumn}1000")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                foreach ($this->textColumns() as $column) {
                    $sheet->getStyle("{$column}4:{$column}1000")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $sheet->getStyle("{$column}4:{$column}1000")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                foreach (range(1, $columnCount) as $index) {
                    $column = Coordinate::stringFromColumnIndex($index);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $this->applyDropdown($sheet, $headers, 'jenis_kelamin', '"L,P"');

                if ($this->type === 'lansia') {
                    $this->applyDropdown(
                        $sheet,
                        $headers,
                        'tingkat_kemandirian',
                        '"mandiri,bantuan_sebagian,ketergantungan_penuh"'
                    );

                    $this->applyDropdown($sheet, $headers, 'golongan_darah', '"A,B,AB,O"');
                }
            },
        ];
    }

    private function textColumns(): array
    {
        $headers = $this->config[$this->type]['headers'] ?? [];
        $columns = [];

        foreach ($headers as $index => $header) {
            $header = strtolower((string) $header);

            if (
                str_contains($header, 'nik') ||
                str_contains($header, 'no_hp') ||
                str_contains($header, 'telepon') ||
                str_contains($header, 'hp')
            ) {
                $columns[] = Coordinate::stringFromColumnIndex($index + 1);
            }
        }

        return array_values(array_unique($columns));
    }

    private function applyDropdown(Worksheet $sheet, array $headers, string $headerName, string $formula): void
    {
        $index = array_search($headerName, $headers, true);

        if ($index === false) {
            return;
        }

        $column = Coordinate::stringFromColumnIndex($index + 1);

        for ($row = 4; $row <= 1000; $row++) {
            $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Pilih nilai sesuai daftar yang tersedia.');
            $validation->setFormula1($formula);
        }
    }

    private function padRow(array $row, int $columnCount): array
    {
        return array_pad($row, $columnCount, '');
    }
}