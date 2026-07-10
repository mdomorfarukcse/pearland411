<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Spreadsheet import helper.
 *
 * Parses CSV and XLSX files into a plain PHP array of rows without any
 * third-party dependency. CSV is read with fgetcsv(); XLSX is unzipped and
 * its sheet XML parsed with SimpleXML (both ZipArchive and SimpleXML ship
 * with the standard PHP build used by this project).
 *
 * Old binary .xls (BIFF) files are NOT supported — users should re-save
 * them as .xlsx or .csv, which every spreadsheet application can do.
 */

if (!function_exists('spreadsheet_read_rows')) {
	/**
	 * Read a spreadsheet file into a 2D array (rows of cell strings).
	 *
	 * @param string $path Absolute path to the uploaded file.
	 * @param string $ext  Lower-case extension without the dot (csv|xlsx).
	 * @return array{0:array,1:string|null} [rows, error]. rows is empty on error.
	 */
	function spreadsheet_read_rows($path, $ext)
	{
		$ext = strtolower($ext);

		if ($ext === 'csv' || $ext === 'txt') {
			return spreadsheet_read_csv($path);
		}

		if ($ext === 'xlsx') {
			return spreadsheet_read_xlsx($path);
		}

		if ($ext === 'xls') {
			return array(array(), 'Old .xls files are not supported. Please re-save the file as .xlsx or .csv and try again.');
		}

		return array(array(), 'Unsupported file type ".' . $ext . '". Upload a .csv or .xlsx file.');
	}
}

if (!function_exists('spreadsheet_read_csv')) {
	function spreadsheet_read_csv($path)
	{
		$rows = array();
		if (($handle = @fopen($path, 'r')) === false) {
			return array(array(), 'Could not open the uploaded file.');
		}

		// Strip a UTF-8 BOM if present so the first header is not corrupted.
		$bom = fread($handle, 3);
		if ($bom !== "\xEF\xBB\xBF") {
			rewind($handle);
		}

		while (($data = fgetcsv($handle, 0, ',')) !== false) {
			// Skip fully-empty lines.
			if ($data === array(null) || (count($data) === 1 && trim((string) $data[0]) === '')) {
				continue;
			}
			$rows[] = array_map(function ($v) {
				return is_string($v) ? trim($v) : $v;
			}, $data);
		}
		fclose($handle);

		return array($rows, null);
	}
}

if (!function_exists('spreadsheet_read_xlsx')) {
	function spreadsheet_read_xlsx($path)
	{
		if (!class_exists('ZipArchive')) {
			return array(array(), 'The server cannot read .xlsx files (ZipArchive missing). Please upload a .csv file instead.');
		}

		$zip = new ZipArchive();
		if ($zip->open($path) !== true) {
			return array(array(), 'The .xlsx file could not be opened. It may be corrupt.');
		}

		// Shared strings table (cell text is stored here and referenced by index).
		$sharedStrings = array();
		$ssXml = $zip->getFromName('xl/sharedStrings.xml');
		if ($ssXml !== false) {
			$ss = @simplexml_load_string($ssXml);
			if ($ss !== false) {
				foreach ($ss->si as $si) {
					$sharedStrings[] = spreadsheet_xlsx_si_text($si);
				}
			}
		}

		// Locate the first worksheet. Default to sheet1.xml, but honour the
		// workbook relationships when present.
		$sheetName = 'xl/worksheets/sheet1.xml';
		$sheetXml = $zip->getFromName($sheetName);
		if ($sheetXml === false) {
			// Fall back to the first worksheet file we can find.
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$name = $zip->getNameIndex($i);
				if (strpos($name, 'xl/worksheets/sheet') === 0 && substr($name, -4) === '.xml') {
					$sheetXml = $zip->getFromName($name);
					break;
				}
			}
		}
		$zip->close();

		if ($sheetXml === false || $sheetXml === null) {
			return array(array(), 'No worksheet was found inside the .xlsx file.');
		}

		$sheet = @simplexml_load_string($sheetXml);
		if ($sheet === false) {
			return array(array(), 'The worksheet inside the .xlsx file could not be parsed.');
		}

		$rows = array();
		foreach ($sheet->sheetData->row as $row) {
			$cells = array();
			$maxCol = -1;
			foreach ($row->c as $c) {
				$ref = (string) $c['r'];        // e.g. "B2"
				$colIdx = spreadsheet_col_to_index($ref);
				$type = (string) $c['t'];
				$value = '';

				if ($type === 's') {
					// Shared-string index.
					$idx = (int) $c->v;
					$value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
				} elseif ($type === 'inlineStr') {
					$value = spreadsheet_xlsx_si_text($c->is);
				} else {
					$value = isset($c->v) ? (string) $c->v : '';
				}

				$cells[$colIdx] = trim($value);
				if ($colIdx > $maxCol) {
					$maxCol = $colIdx;
				}
			}

			// Normalise to a dense, 0-indexed row (fill gaps with '').
			$dense = array();
			for ($i = 0; $i <= $maxCol; $i++) {
				$dense[] = isset($cells[$i]) ? $cells[$i] : '';
			}

			// Skip fully-empty rows.
			$nonEmpty = array_filter($dense, function ($v) {
				return $v !== '';
			});
			if (empty($nonEmpty)) {
				continue;
			}

			$rows[] = $dense;
		}

		return array($rows, null);
	}
}

if (!function_exists('spreadsheet_xlsx_si_text')) {
	/**
	 * Extract the plain text of a shared-string / inline-string <si> node,
	 * concatenating any rich-text runs (<r><t>...</t></r>).
	 */
	function spreadsheet_xlsx_si_text($si)
	{
		if ($si === null) {
			return '';
		}
		if (isset($si->t) && count($si->r) === 0) {
			return (string) $si->t;
		}
		$text = '';
		foreach ($si->r as $r) {
			$text .= (string) $r->t;
		}
		if ($text === '' && isset($si->t)) {
			$text = (string) $si->t;
		}
		return $text;
	}
}

if (!function_exists('spreadsheet_col_to_index')) {
	/**
	 * Convert an A1-style cell reference (e.g. "AB12") to a 0-based column index.
	 */
	function spreadsheet_col_to_index($ref)
	{
		if (!preg_match('/^([A-Z]+)/i', $ref, $m)) {
			return 0;
		}
		$letters = strtoupper($m[1]);
		$num = 0;
		$len = strlen($letters);
		for ($i = 0; $i < $len; $i++) {
			$num = $num * 26 + (ord($letters[$i]) - ord('A') + 1);
		}
		return $num - 1;
	}
}
