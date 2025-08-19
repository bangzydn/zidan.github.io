<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DOMPDF Options
    |--------------------------------------------------------------------------
    |
    | All options available in DOMPDF are listed here
    | http://dompdf.github.io/dompdf/dompdf_config.html
    | 
    | Options are passed to DOMPDF as an array
    |
    */

    'options' => [
        'isPhpEnabled' => env('DOMPDF_ENABLE_PHP', false),
        'isRemoteEnabled' => env('DOMPDF_ENABLE_REMOTE', true),
        'isCssFloatEnabled' => env('DOMPDF_ENABLE_CSS_FLOAT', true),
        'isJavascriptEnabled' => env('DOMPDF_ENABLE_JAVASCRIPT', true),
        'isHtml5ParserEnabled' => true,
        'isFontSubsettingEnabled' => true,
        'defaultMediaType' => 'screen',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'serif',
        'dpi' => 96,
        'fontHeightRatio' => 0.9,
        'enableCssFloat' => true,
        'enableJavascript' => true,
        'enablePhp' => false,
        'enableRemote' => true,
        'fontCache' => storage_path('fonts/'),
        'tempDir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enableHtml5Parser' => true,
        'isFontSubsettingEnabled' => true,
        'pdfBackend' => 'CPDF',
        'defaultMediaType' => 'screen',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'serif',
        'dpi' => 96,
        'fontHeightRatio' => 0.9,
        'isPhpEnabled' => false,
        'isRemoteEnabled' => true,
        'isCssFloatEnabled' => true,
        'isJavascriptEnabled' => true,
        'isHtml5ParserEnabled' => true,
        'isFontSubsettingEnabled' => true,
        'defaultMediaType' => 'screen',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'serif',
        'dpi' => 96,
        'fontHeightRatio' => 0.9,
        'enableCssFloat' => true,
        'enableJavascript' => true,
        'enablePhp' => false,
        'enableRemote' => true,
        'fontCache' => storage_path('fonts/'),
        'tempDir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enableHtml5Parser' => true,
        'isFontSubsettingEnabled' => true,
        'pdfBackend' => 'CPDF',
        'defaultMediaType' => 'screen',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'serif',
        'dpi' => 96,
        'fontHeightRatio' => 0.9,
    ],

    /*
    |--------------------------------------------------------------------------
    | DOMPDF Default Options
    |--------------------------------------------------------------------------
    |
    | These options are set for all PDF documents
    |
    */

    'defaults' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
        'dpi' => 96,
        'font' => 'serif',
    ],

    /*
    |--------------------------------------------------------------------------
    | DOMPDF Fonts
    |--------------------------------------------------------------------------
    |
    | Configure fonts for DOMPDF
    |
    */

    'fonts' => [
        'serif' => [
            'normal' => 'Times-Roman',
            'bold' => 'Times-Bold',
            'italic' => 'Times-Italic',
            'bold_italic' => 'Times-BoldItalic'
        ],
        'sans-serif' => [
            'normal' => 'Helvetica',
            'bold' => 'Helvetica-Bold',
            'italic' => 'Helvetica-Oblique',
            'bold_italic' => 'Helvetica-BoldOblique'
        ],
        'monospace' => [
            'normal' => 'Courier',
            'bold' => 'Courier-Bold',
            'italic' => 'Courier-Oblique',
            'bold_italic' => 'Courier-BoldOblique'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DOMPDF Paper Sizes
    |--------------------------------------------------------------------------
    |
    | Configure paper sizes for DOMPDF
    |
    */

    'papers' => [
        'a0' => [0, 0, 841, 1189],
        'a1' => [0, 0, 594, 841],
        'a2' => [0, 0, 420, 594],
        'a3' => [0, 0, 297, 420],
        'a4' => [0, 0, 210, 297],
        'a5' => [0, 0, 148, 210],
        'a6' => [0, 0, 105, 148],
        'a7' => [0, 0, 74, 105],
        'a8' => [0, 0, 52, 74],
        'a9' => [0, 0, 37, 52],
        'a10' => [0, 0, 26, 37],
        'b0' => [0, 0, 1000, 1414],
        'b1' => [0, 0, 707, 1000],
        'b2' => [0, 0, 500, 707],
        'b3' => [0, 0, 353, 500],
        'b4' => [0, 0, 250, 353],
        'b5' => [0, 0, 176, 250],
        'b6' => [0, 0, 125, 176],
        'b7' => [0, 0, 88, 125],
        'b8' => [0, 0, 62, 88],
        'b9' => [0, 0, 44, 62],
        'b10' => [0, 0, 31, 44],
        'c0' => [0, 0, 917, 1297],
        'c1' => [0, 0, 648, 917],
        'c2' => [0, 0, 458, 648],
        'c3' => [0, 0, 324, 458],
        'c4' => [0, 0, 229, 324],
        'c5' => [0, 0, 162, 229],
        'c6' => [0, 0, 114, 162],
        'c7' => [0, 0, 81, 114],
        'c8' => [0, 0, 57, 81],
        'c9' => [0, 0, 40, 57],
        'c10' => [0, 0, 28, 40],
        '4a0' => [0, 0, 1682, 2378],
        '2a0' => [0, 0, 1189, 1682],
        'folio' => [0, 0, 210, 330],
        'quarto' => [0, 0, 215, 275],
        'executive' => [0, 0, 184, 267],
        'letter' => [0, 0, 216, 279],
        'legal' => [0, 0, 216, 356],
        'ledger' => [0, 0, 279, 432],
        'tabloid' => [0, 0, 432, 279],
        'invoice' => [0, 0, 140, 216],
        'statement' => [0, 0, 140, 216],
        'government-letter' => [0, 0, 203, 267],
        'government-legal' => [0, 0, 216, 330],
        'junior-legal' => [0, 0, 203, 127],
        'junior-executive' => [0, 0, 184, 127],
        'foolscap' => [0, 0, 203, 330],
        'monarch' => [0, 0, 184, 267],
        'commercial' => [0, 0, 267, 432],
        'crown' => [0, 0, 203, 254],
        'demy' => [0, 0, 219, 276],
        'medium' => [0, 0, 229, 279],
        'royal' => [0, 0, 254, 381],
        'elephant' => [0, 0, 279, 356],
        'double-demy' => [0, 0, 276, 438],
        'quad-demy' => [0, 0, 438, 558],
        'statement' => [0, 0, 140, 216],
        'executive' => [0, 0, 184, 267],
        'folio' => [0, 0, 210, 330],
        'quarto' => [0, 0, 215, 275],
        '10x14' => [0, 0, 254, 356],
        '11x17' => [0, 0, 279, 432],
    ],

];