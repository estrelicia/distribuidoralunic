<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

/**
 * Excel en Windows guarda muchos CSV como application/vnd.ms-excel.
 * WordPress solo acepta text/csv y corta la importación de productos.
 */
class Csv_Import {

    public function register(): void {
        add_filter('wp_check_filetype_and_ext', [$this, 'accept_spreadsheet_csv'], 20, 5);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string>|null $mimes
     * @return array<string, mixed>
     */
    public function accept_spreadsheet_csv($data, $file, $filename, $mimes, $real_mime) {
        unset($file, $mimes);
        if (!is_array($data) || !current_user_can('manage_woocommerce')) {
            return $data;
        }
        $name = is_string($filename) ? $filename : '';
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'csv') {
            return $data;
        }
        $accepted = [
            'text/csv',
            'text/plain',
            'text/html',
            'text/x-csv',
            'text/comma-separated-values',
            'application/csv',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ];
        if (!in_array((string) $real_mime, $accepted, true)) {
            return $data;
        }
        $data['ext'] = 'csv';
        $data['type'] = 'text/csv';
        $data['proper_filename'] = false;
        return $data;
    }
}
