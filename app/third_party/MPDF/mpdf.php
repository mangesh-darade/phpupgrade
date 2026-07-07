<?php
/**
 * mPDF 6.x legacy API shim — loads mPDF 8.x from Composer vendor.
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('mPDF', false)) {
    class mPDF extends \Mpdf\Mpdf
    {
        public $debug = false;

        public function __construct(
            $mode = '',
            $format = 'A4',
            $default_font_size = 0,
            $default_font = '',
            $mgl = 15,
            $mgr = 15,
            $mgt = 16,
            $mgb = 16,
            $mgh = 9,
            $mgf = 9,
            $orientation = 'P'
        ) {
            if (is_string($format) && preg_match('/^(.+)-([PL])$/i', $format, $m)) {
                $format = $m[1];
                $orientation = strtoupper($m[2]);
            }

            parent::__construct([
                'mode' => $mode,
                'format' => $format,
                'default_font_size' => $default_font_size,
                'default_font' => $default_font,
                'margin_left' => $mgl,
                'margin_right' => $mgr,
                'margin_top' => $mgt,
                'margin_bottom' => $mgb,
                'margin_header' => $mgh,
                'margin_footer' => $mgf,
                'orientation' => $orientation,
            ]);
        }

        public function SetHeader($header = '', $side = 'O', $write = false)
        {
            if (is_string($header) && $header !== '') {
                $this->SetHTMLHeader($header, $side, $write);
                return;
            }
            if (is_array($header)) {
                parent::SetHeader($header, $side, $write);
            }
        }
    }
}
