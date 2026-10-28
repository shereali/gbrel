<?php

namespace App\Support;

use DOMDocument;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Reads tabular buyer lead rows from CSV, TSV, Excel (.xlsx), or SpreadsheetML/HTML (.xls) files
 * into an array of associative rows keyed by header column names.
 */
class LeadSpreadsheetReader
{
    /**
     * Parse an uploaded file into associative rows keyed by header name.
     *
     * @return array<int, array<string, string>>
     */
    public static function readUploadedFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if (! $path || ! is_file($path)) {
            throw new RuntimeException('Uploaded spreadsheet file could not be read.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        return self::readFile($path, $ext);
    }

    /**
     * Parse a file path into associative rows keyed by header name.
     *
     * @return array<int, array<string, string>>
     */
    public static function readFile(string $path, ?string $extension = null): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('Spreadsheet file does not exist.');
        }

        $ext = strtolower($extension ?? pathinfo($path, PATHINFO_EXTENSION));
        $head = (string) file_get_contents($path, false, null, 0, 8);

        // Native .xlsx is a ZIP archive starting with PK\x03\x04
        if (str_starts_with($head, "PK\x03\x04") || $ext === 'xlsx') {
            if (str_starts_with($head, "PK\x03\x04")) {
                return self::rowsToAssociative(self::readXlsx($path));
            }
        }

        $raw = (string) file_get_contents($path);
        if ($raw === '') {
            return [];
        }

        $text = self::normalizeEncoding($raw);

        // XML Spreadsheet 2003 (.xls exported by some CRMs) or HTML table .xls
        $trimmed = ltrim($text);
        if (str_starts_with($trimmed, '<?xml') && str_contains($trimmed, '<Workbook')) {
            return self::rowsToAssociative(self::readXmlSpreadsheet2003($trimmed));
        }
        if (preg_match('/^<(!DOCTYPE|html|table)\b/i', $trimmed)) {
            return self::rowsToAssociative(self::readHtmlTable($trimmed));
        }

        // Binary BIFF8 .xls magic header: D0 CF 11 E0 A1 B1 1A E1
        if (str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            throw new RuntimeException('Legacy binary .xls (Excel 97-2003) detected. Please save the file as .xlsx or .csv in Excel and upload again.');
        }

        return self::rowsToAssociative(self::readDelimitedText($text));
    }

    /**
     * Convert raw 2D grid rows (first row = headers) into associative rows.
     *
     * @param  array<int, array<int, string>>  $grid
     * @return array<int, array<string, string>>
     */
    public static function rowsToAssociative(array $grid): array
    {
        if (count($grid) < 2) {
            return [];
        }

        // Find first non-empty row as header
        $headerRow = null;
        $startIndex = 0;
        foreach ($grid as $idx => $row) {
            $nonEmpty = array_filter($row, fn ($cell) => trim((string) $cell) !== '');
            if ($nonEmpty !== []) {
                $headerRow = $row;
                $startIndex = $idx + 1;
                break;
            }
        }

        if (! $headerRow) {
            return [];
        }

        $headers = [];
        foreach ($headerRow as $colIndex => $headerText) {
            $clean = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerText));
            $headers[$colIndex] = $clean !== '' ? $clean : 'Column_'.($colIndex + 1);
        }

        $result = [];
        $count = count($grid);
        for ($i = $startIndex; $i < $count; $i++) {
            $row = $grid[$i];
            $assoc = [];
            $hasData = false;
            foreach ($headers as $colIndex => $headerName) {
                $val = isset($row[$colIndex]) ? trim((string) $row[$colIndex]) : '';
                if ($val !== '') {
                    $hasData = true;
                }
                $assoc[$headerName] = $val;
            }
            if ($hasData) {
                $result[] = $assoc;
            }
        }

        return $result;
    }

    /**
     * Convert UTF-16LE / UTF-16BE (common in Meta Lead Ads CSV/TSV downloads) or UTF-8 BOM to clean UTF-8.
     */
    private static function normalizeEncoding(string $raw): string
    {
        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = substr($raw, 2);
            $text = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE')
                : iconv('UTF-16LE', 'UTF-8//IGNORE', $raw);
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = substr($raw, 2);
            $text = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($raw, 'UTF-8', 'UTF-16BE')
                : iconv('UTF-16BE', 'UTF-8//IGNORE', $raw);
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $text = substr($raw, 3);
        } else {
            $text = $raw;
        }

        return str_replace(["\r\n", "\r"], "\n", (string) $text);
    }

    /**
     * Parse CSV / TSV / semicolon-separated text into a 2D array of rows.
     *
     * @return array<int, array<int, string>>
     */
    private static function readDelimitedText(string $text): array
    {
        $firstLine = strtok($text, "\n") ?: '';
        $delimiter = self::detectDelimiter($firstLine);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $text);
        rewind($stream);

        $rows = [];
        while (($data = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            if ($data === [null]) {
                continue;
            }
            $rows[] = array_map(fn ($v) => trim((string) $v), $data);
        }
        fclose($stream);

        return $rows;
    }

    private static function detectDelimiter(string $firstLine): string
    {
        $candidates = [
            "\t" => substr_count($firstLine, "\t"),
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
        ];
        arsort($candidates);
        $best = array_key_first($candidates);

        return ($candidates[$best] ?? 0) > 0 ? $best : ',';
    }

    /**
     * Read the first worksheet of an .xlsx file using native ZipArchive + SimpleXMLElement.
     *
     * @return array<int, array<int, string>>
     */
    private static function readXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Could not open .xlsx archive.');
        }

        try {
            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml !== false) {
                $sst = @simplexml_load_string($sharedXml);
                if ($sst instanceof SimpleXMLElement) {
                    foreach ($sst->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string) $si->t;
                        } elseif (isset($si->r)) {
                            $parts = [];
                            foreach ($si->r as $run) {
                                $parts[] = (string) ($run->t ?? '');
                            }
                            $sharedStrings[] = implode('', $parts);
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
                        $sheetXml = $zip->getFromName($name);
                        break;
                    }
                }
            }

            if ($sheetXml === false) {
                throw new RuntimeException('No worksheet found inside .xlsx file.');
            }

            $sheet = @simplexml_load_string($sheetXml);
            if (! ($sheet instanceof SimpleXMLElement) || ! isset($sheet->sheetData)) {
                return [];
            }

            $rows = [];
            foreach ($sheet->sheetData->row as $rowNode) {
                $rowCells = [];
                $maxCol = -1;
                $nextSequentialCol = 0;

                foreach ($rowNode->c as $cell) {
                    $ref = (string) ($cell['r'] ?? '');
                    $colIndex = $ref !== '' ? self::columnIndexFromRef($ref) : $nextSequentialCol;
                    $nextSequentialCol = $colIndex + 1;
                    if ($colIndex > $maxCol) {
                        $maxCol = $colIndex;
                    }

                    $type = (string) ($cell['t'] ?? '');
                    $value = '';
                    if ($type === 's') {
                        $idx = (int) ($cell->v ?? 0);
                        $value = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        if (isset($cell->is->t)) {
                            $value = (string) $cell->is->t;
                        } elseif (isset($cell->is->r)) {
                            $parts = [];
                            foreach ($cell->is->r as $run) {
                                $parts[] = (string) ($run->t ?? '');
                            }
                            $value = implode('', $parts);
                        }
                    } else {
                        $value = isset($cell->v) ? (string) $cell->v : '';
                    }

                    $rowCells[$colIndex] = trim($value);
                }

                if ($maxCol >= 0) {
                    $denseRow = [];
                    for ($c = 0; $c <= $maxCol; $c++) {
                        $denseRow[$c] = $rowCells[$c] ?? '';
                    }
                    $rows[] = $denseRow;
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    /**
     * Convert an Excel cell reference like "A1", "C5", "AA12" into a 0-based column index.
     */
    private static function columnIndexFromRef(string $ref): int
    {
        if (! preg_match('/^([A-Z]+)/i', $ref, $m)) {
            return 0;
        }
        $letters = strtoupper($m[1]);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return max(0, $index - 1);
    }

    /**
     * Read XML Spreadsheet 2003 format.
     *
     * @return array<int, array<int, string>>
     */
    private static function readXmlSpreadsheet2003(string $xmlString): array
    {
        $xml = @simplexml_load_string($xmlString);
        if (! ($xml instanceof SimpleXMLElement)) {
            return [];
        }

        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $rowNodes = $xml->xpath('//ss:Worksheet[1]//ss:Table/ss:Row') ?: $xml->xpath('//Row') ?: [];
        $rows = [];

        foreach ($rowNodes as $rowNode) {
            $rowCells = [];
            $colIndex = 0;
            foreach ($rowNode->children() as $cell) {
                if ($cell->getName() !== 'Cell') {
                    continue;
                }
                $attrs = $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet');
                if (isset($attrs['Index'])) {
                    $colIndex = max(0, ((int) $attrs['Index']) - 1);
                }
                $dataVal = '';
                foreach ($cell->children() as $child) {
                    if ($child->getName() === 'Data') {
                        $dataVal = trim((string) $child);
                        break;
                    }
                }
                $rowCells[$colIndex] = $dataVal;
                $colIndex++;
            }
            if ($rowCells !== []) {
                $maxCol = max(array_keys($rowCells));
                $dense = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $dense[$c] = $rowCells[$c] ?? '';
                }
                $rows[] = $dense;
            }
        }

        return $rows;
    }

    /**
     * Read HTML table exported as .xls.
     *
     * @return array<int, array<int, string>>
     */
    private static function readHtmlTable(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $rows = [];
        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $row = [];
            foreach ($tr->childNodes as $cell) {
                if ($cell->nodeName === 'td' || $cell->nodeName === 'th') {
                    $row[] = trim($cell->textContent);
                }
            }
            if ($row !== []) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

