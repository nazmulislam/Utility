<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Excel;

trait CellStyleTrait
{
    private $cellStyle = [
                        'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'rotation' => 0,
                                'startColor' => [
                                    'rgb' => 'ffffff'
                                ],
                                'endColor' => [
                                    'rgb' => 'ffffff'
                                ]
                        ],
                        'font' => [
                            'name' => 'Calibri',
                            'bold' => false,
                            'italic' => false,
                            'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE,
                            'strikethrough' => false,
                            'color' => [
                                'rgb' => '000000'
                            ]
                        ],
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ],
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ],
                            'left' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ],
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'quotePrefix'    => true
                    ];
    

    
  
}