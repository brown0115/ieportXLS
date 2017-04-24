<?php
namespace Imtigger\OneExcel\Writer;

use Imtigger\OneExcel\ColumnType;
use Imtigger\OneExcel\Format;
use Imtigger\OneExcel\OneExcelWriterInterface;

class LibXLWriter extends OneExcelWriter implements OneExcelWriterInterface
{
    public static $input_format_supported = [Format::XLSX, Format::XLS];
    public static $output_format_supported = [Format::XLSX, Format::XLS];
    public static $input_output_same_format = true;
    /** @var \ExcelBook $book */
    private $book;
    /** @var \ExcelSheet $sheet */
    private $sheet;
    private $input_format;
    private $output_format;

    public function create($output_format = Format::XLSX)
    {
        $this->checkFormatSupported($output_format);
        $this->output_format = $output_format;
        $this->book = new \ExcelBook(null, null, $this->output_format == Format::XLSX);
        $this->book->setLocale('UTF-8');
        $this->sheet = $this->book->addSheet('Sheet1');
    }

    public function load($filename, $output_format = Format::XLSX, $input_format = Format::AUTO)
    {
        $this->checkFormatSupported($output_format, $input_format);

        $this->input_format = $input_format;
        $this->output_format = $output_format;

        $this->book = new \ExcelBook(null, null, $this->output_format == Format::XLSX);
        $this->book->loadFile($filename);
        $this->book->setLocale('UTF-8');
        $this->sheet = $this->book->getSheet(0);
    }

    public function writeCell($row_num, $column_num, $data, $data_type = ColumnType::STRING)
    {
        $this->sheet->write($row_num - 1, $column_num, $data, null, $this->getColumnFormat($data_type));
    }

    public function writeRow($row_num, $data)
    {
        $this->sheet->writeRow($row_num - 1, $data);
    }

    public function save($path)
    {
        $this->book->save($path);
    }

    public function download($filename)
    {
        header('Content-Type: ' . $this->getFormatMime($this->output_format));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: no-cache');

        $this->save('php://output');
    }

    public function getColumnFormat($internal_format)
    {
        switch ($internal_format) {
            case ColumnType::STRING:
                return -1;
            case ColumnType::NUMERIC:
                return \ExcelFormat::AS_NUMERIC_STRING;
            case ColumnType::FORMULA:
                return \ExcelFormat::AS_FORMULA;
        }
        return -1;
    }
}