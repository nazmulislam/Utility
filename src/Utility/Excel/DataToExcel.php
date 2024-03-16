<?php

declare(strict_types=1);
namespace NazmulIslam\Utility\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use NazmulIslam\Utility\Excel\HeaderStyleTrait;
use NazmulIslam\Utility\Excel\HeaderStyleLeftTrait;
use NazmulIslam\Utility\Excel\CellStyleTrait;
use NazmulIslam\Utility\Excel\CellStyleTraitLeft;


class DataToExcel
{
    use HeaderStyleTrait;
    use HeaderStyleLeftTrait;
    use CellStyleTrait;
    use CellStyleTraitLeft;
  
    
    public $objPHPExcel;
    public array $columnHeadings = [];
    public array $excelRows = [];
    public int $headerRowIndex = 1;
    
    public function __construct(array $colmunHeadings, array $rowData, string $sheetTitle, bool $isVertical = false, int $activeSheetIndex = 0)
    {
        
        $this->setExcelColumnHeadings($colmunHeadings);
        $this->setExcelRows($rowData);
  
        $this->createNewExcel();
        if(count($this->columnHeadings) > 0 && count($this->rowData) > 0)
        {
            if($isVertical)
            {
                 $this->addNewSheetWithIndexAndTitle($activeSheetIndex,$sheetTitle)
                 ->writeExcelColumnHeadingsVertical($this->columnHeadings)
                 ->processDataArrayVertical($this->rowData);
            }
            else
            {
                 $this->addNewSheetWithIndexAndTitle($activeSheetIndex,$sheetTitle)
                 ->writeExcelColumnHeadings(rowNumber:1,autosize:true)
                 ->processDataArray();
            }
        }
    }
    
    
    public function setExcelColumnHeadings(array $colmunHeadings): DataToExcel
    {
         $this->columnHeadings = $colmunHeadings;
         return $this;
    }
    public function setExcelRows(array $rows): DataToExcel
    {
         $this->rowData = $rows;
         return $this;
    }
    public function createNewExcel() : DataToExcel
    {
        $this->objPHPExcel = new Spreadsheet();
        return $this;
    }
    
    public function getActiveSheetIndex() :int
    {
        return $this->objPHPExcel->getActiveSheetIndex();
    }
    
    public function addNewSheetWithIndexAndTitle(int $index, string $title) : DataToExcel
    {
        if($index != 0) 
        {
            $this->objPHPExcel->createSheet($index);
        }
        $this->objPHPExcel->setActiveSheetIndex($index);
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        return $this;
    }
    
    private function writeExcelColumnHeadings(int $rowNumber = 1, bool $autosize = true): DataToExcel
    {
        
        $this->headerRowIndex = $rowNumber;
        for ($index = 0; $index < count($this->columnHeadings); $index++)
        {
            if(isset($this->columnHeadings[$index]['columnTitle']) && !empty($this->columnHeadings[$index]['columnTitle']))
            {
                $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index+1, $this->headerRowIndex, $this->columnHeadings[$index]['columnTitle']);
                $this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($index+1)->setAutoSize($autosize);
                $this->setHeaderStyle($index+1, $this->headerRowIndex, $this->columnHeadings[$index]);
            }
        }
        return $this;
    }
                
    public function processData(array $rows): DataToExcel
    {
        $this->rowData = $rows;
        for ($index = 0; $index < count($this->rowData); $index++)
        {
            if(isset($this->rowData[$index]) && is_array($this->rowData[$index]))
            {
                $row = $this->rowData[$index];
                for ($x = 0; $x < count($this->columnHeadings); $x++)
                {
                    if(isset($this->columnHeadings[$x]['key']) && !empty($this->columnHeadings[$x]['key']))
                    {
                        $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($x+1, $index+1+$this->headerRowIndex,(isset($row[$this->columnHeadings[$x]['key']])?$row[$this->columnHeadings[$x]['key']]:'N/A'));
                        $this->setCellStyle($x+1, $index+1+$this->headerRowIndex, $this->columnHeadings[$x]);
                    }
                }
            }        
        }
        return $this;
    }
    
    /*
     * When we have cell data as Array we need to use below function.
     */
    public function processDataArray(): DataToExcel
    {
        
        $rowShowIndex = $this->headerRowIndex;
        $extraRows = 0;
        $extraRowsCount = 0;
        for ($rowIndex = 1; $rowIndex <= count($this->rowData); $rowIndex++)
        {
            
            if(isset($this->rowData[$rowIndex-1]) && is_array($this->rowData[$rowIndex-1]))
            {
                $row = $this->rowData[$rowIndex-1];
                $extraRowsCount = $extraRowsCount + $extraRows;
                $extraRows = 0;
                $rowShowIndex = $rowIndex + $extraRowsCount + $this->headerRowIndex;
                for ($colIndex = 1; $colIndex <= count($this->columnHeadings); $colIndex++)
                {
                    $column = $this->columnHeadings[$colIndex-1];
                    if(isset($column['key']) && !empty($column['key']))
                    {
                        
                        if(isset($row[$column['key']]) && is_array($row[$column['key']]))
                        {
                            for ($y = 0; $y < count($row[$column['key']]); $y++)
                            {
                                $extraRows = count($row[$column['key']]);
                                $cellValue = (isset($row[$column['key']][$y])?$row[$column['key']][$y]:'N/A');
                                $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colIndex, $rowShowIndex+$y,$cellValue);
                                $this->setCellStyle($colIndex, $rowShowIndex+$y, $column);
                            }
                        }
                        else
                        {
                            $cellValue = (isset($row[$column['key']])?$row[$column['key']]:'N/A');
                            $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colIndex, $rowShowIndex,$cellValue);
                            $this->setCellStyle($colIndex, $rowShowIndex, $column);
                        }
                    }
                }
            }        
        }
        return $this;
    }
    
    public function saveExcelWithPath($filename): DataToExcel
    {
        $objWriter = new Xlsx($this->objPHPExcel);
        $objWriter->save($filename);
        return $this;
    }
    
    
    private function writeExcelColumnHeadingsVertical(array $columns, int $columnNumber = 1, bool $autosize = true): DataToExcel
    {
        $this->columnHeadings = $columns;
        for ($index = 0; $index < count($this->columnHeadings); $index++)
        {
            if(isset($this->columnHeadings[$index]['column']) && !empty($this->columnHeadings[$index]['column']))
            {
                $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnNumber,$index+1 , $this->columnHeadings[$index]['column']);
                $this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnNumber)->setAutoSize($autosize);
                $this->setHeaderStyle($columnNumber, $index+1, $this->columnHeadings[$index]);
            }
        }
        return $this;
    }
    
    public function processDataArrayVertical(array $rows): DataToExcel
    {
        $this->rowData = $rows;
        for ($index = 0; $index < count($this->rowData); $index++)
        {
            if(isset($this->rowData[$index]) && is_array($this->rowData[$index]))
            {
                $row = $this->rowData[$index];
                for ($x = 0; $x < count($this->columnHeadings); $x++)
                {
                    if(isset($this->columnHeadings[$x]['key']) && is_array($this->columnHeadings[$x]['key']))
                    {
                        for ($keyLoop = 0; $keyLoop < count($this->columnHeadings[$x]['key']); $keyLoop++)
                        {
                            $key = $this->columnHeadings[$x]['key'][$keyLoop];
                            $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index+1+$this->headerRowIndex+$keyLoop,$x+1,(isset($row[$key])?$row[$key]:$key));
                            $this->setCellStyle($index+1+$this->headerRowIndex+$keyLoop,$x+1, $this->columnHeadings[$x]);
                        }
                    }
                }
            }        
        }
        return $this;
    }
    
    private function setHeaderStyle(int $columnIndex,int $rowIndex,array$cell):DataToExcel
    {
        if(isset($cell['headerStyle']))
        {
            if(is_array($cell['headerStyle']))
            {
                $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnIndex, $rowIndex)->applyFromArray($cell['headerStyle']);
            }
            else 
            {
                $headerStyle = $this->{$cell['headerStyle']};
                if(isset($headerStyle) && is_array($headerStyle))
                {
                    $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnIndex, $rowIndex)->applyFromArray($headerStyle);
                }
            }
        }
        return $this;
    }
    
    private function setCellStyle(int $columnIndex,int $rowIndex,array $cell): DataToExcel
    {
        if(isset($cell['cellStyle']))
        {
            if(is_array($cell['cellStyle']))
            {
                $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnIndex,$rowIndex)->applyFromArray($cell['cellStyle']);
            }
            else 
            {
                $cellStyle = $this->{$cell['cellStyle']};
                if(isset($cellStyle) && is_array($cellStyle))
                {
                    $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnIndex,$rowIndex)->applyFromArray($cellStyle);
                }
            }
        }
        
        return $this;
    }
}