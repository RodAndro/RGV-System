<?php

require dirname(__DIR__).'/Backend/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

$root = dirname(__DIR__).'/Backend';
$documents = [
    [
        'source' => 'Complete System Documentation.md',
        'output' => 'Complete System Documentation.pdf',
        'title' => 'Complete System Documentation',
    ],
    [
        'source' => 'Complete Database Schema.md',
        'output' => 'Complete Database Schema.pdf',
        'title' => 'Complete Database Schema',
    ],
    [
        'source' => 'System Features and Role Permissions.md',
        'output' => 'System Features and Role Permissions.pdf',
        'title' => 'System Features and Role Permissions',
    ],
];

$environment = new Environment(['html_input' => 'allow']);
$environment->addExtension(new CommonMarkCoreExtension);
$environment->addExtension(new GithubFlavoredMarkdownExtension);
$converter = new MarkdownConverter($environment);

foreach ($documents as $document) {
    $markdownPath = __DIR__.'/'.$document['source'];
    $markdown = file_get_contents($markdownPath);
    if ($markdown === false) {
        throw new RuntimeException("Unable to read {$document['source']}.");
    }

    $content = $converter->convert($markdown)->getContent();
    $content = preg_replace_callback(
        '/<h([1-6])>(.*?)<\/h\1>/is',
        static function (array $matches): string {
            $heading = html_entity_decode(strip_tags($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $heading), '-'));

            return '<h'.$matches[1].' id="'.$slug.'">'.$matches[2].'</h'.$matches[1].'>';
        },
        $content,
    );

    $html = '<!doctype html><html lang="en"><head><meta charset="UTF-8"><title>'.
        htmlspecialchars($document['title'], ENT_QUOTES, 'UTF-8').
        '</title><style>
        @page { size: A4; margin: 20mm 16mm 18mm 16mm; }
        body { font-family: "DejaVu Sans", sans-serif; color: #23323a; font-size: 9pt; line-height: 1.42; }
        h1 { color: #0c6571; font-size: 28pt; line-height: 1.16; margin: 118pt 0 20pt; text-align: center; }
        h1 + p { color: #40535b; font-size: 10pt; line-height: 1.8; text-align: center; }
        h2 { color: #0c6571; font-size: 16pt; margin: 18pt 0 8pt; page-break-after: avoid; }
        h3 { color: #245c68; font-size: 11pt; margin: 14pt 0 6pt; page-break-after: avoid; }
        h4 { color: #245c68; font-size: 9.5pt; margin: 10pt 0 5pt; page-break-after: avoid; }
        p { margin: 5pt 0 8pt; }
        ul, ol { margin: 4pt 0 9pt; padding-left: 18pt; }
        li { margin: 2pt 0; }
        table { border-collapse: collapse; width: 100%; margin: 8pt 0 12pt; font-size: 7.5pt; }
        thead { display: table-header-group; }
        th { background: #e8f1f2; color: #153f48; text-align: left; }
        th, td { border: 0.5pt solid #aebdc1; padding: 4pt 5pt; vertical-align: top; }
        tr { page-break-inside: avoid; }
        code { font-family: "DejaVu Sans Mono", monospace; font-size: 8pt; color: #244b55; }
        pre { background: #f2f6f7; border: 0.5pt solid #d5e0e2; padding: 7pt; font-size: 7.5pt; white-space: pre-wrap; }
        blockquote { border-left: 3pt solid #69a6ab; margin: 8pt 0; padding: 3pt 9pt; color: #465b62; }
        hr { border: 0; border-top: 1pt solid #c5d2d5; margin: 16pt 0; }
        a { color: #0c6571; text-decoration: none; }
        .page-break { page-break-before: always; }
        </style></head><body>'.$content.'</body></html>';

    $options = new Options;
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('chroot', $root);

    $pdf = new Dompdf($options);
    $pdf->setPaper('A4', 'portrait');
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();

    $canvas = $pdf->getCanvas();
    $font = $pdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
    $gray = [0.30, 0.40, 0.43];
    $canvas->page_text(46, $canvas->get_height() - 24, 'RGV Multi-Tech Services | 14 November 2026', $font, 7.5, $gray);
    $canvas->page_text($canvas->get_width() - 104, $canvas->get_height() - 24, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7.5, $gray);

    $outputPath = __DIR__.'/'.$document['output'];
    $bytes = file_put_contents($outputPath, $pdf->output());
    if ($bytes === false) {
        throw new RuntimeException("Unable to write {$document['output']}.");
    }

    printf("%s: %d pages, %d bytes\n", $document['output'], $canvas->get_page_count(), $bytes);
}
