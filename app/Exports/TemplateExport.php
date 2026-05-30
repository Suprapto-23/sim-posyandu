<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private string $type;

    private array $configs = [
        'balita' => [
            'label' => 'Balita',
            'title' => 'TEMPLATE IMPORT DATA BALITA',
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
            'examples' => [
                [
                    '332609010120230001',
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
        ],

        'remaja' => [
            'label' => 'Remaja',
            'title' => 'TEMPLATE IMPORT DATA REMAJA',
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
            'examples' => [
                [
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
        ],

        'lansia' => [
            'label' => 'Lansia',
            'title' => 'TEMPLATE IMPORT DATA LANSIA',
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
            'examples' => [
                [
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
        ],
    ];

    public function __construct(?string $type = 'balita')
    {
        $type = strtolower(trim((string) $type));

        $this->type = array_key_exists($type, $this->configs)
            ? $type
            : 'balita';
    }

    public function array(): array
    {
        $config = $this->configs[$this->type];
        $headers = $config['headers'];
        $columnCount = count($headers);

        $rows = [
            $this->padRow([$config['title']], $columnCount),
            $this->padRow([
                'Jangan mengubah nama kolom pada baris 3. Isi data mulai baris 4. Format tanggal disarankan YYYY-MM-DD.',
            ], $columnCount),
            $headers,
        ];

        foreach ($config['examples'] as $example) {
            $rows[] = $this->padRow($example, $columnCount);
        }

        return $rows;
    }

    public function title(): string
    {
        return $this->configs[$this->type]['label'];
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
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $headers = $this->configs[$this->type]['headers'];
                $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(26);

                $sheet->freezePane('A4');

                $sheet->getStyle("A1:{$lastColumn}2")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A3:{$lastColumn}4")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D1FAE5');

                $sheet->getStyle("A4:{$lastColumn}4")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');

                $sheet->getStyle("A4:{$lastColumn}4")->getFont()
                    ->getColor()->setRGB('334155');

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