<?php

require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

$phpWord = new PhpWord();

$section = $phpWord->addSection([
    'marginTop' => 800,
    'marginLeft' => 800,
    'marginRight' => 800,
    'marginBottom' => 800,
]);

$section->addText(
    'SEZNAM ŽÁKŮ',
    [
        'name' => 'Arial',
        'size' => 33,
        'color' => '000000',
    ],
    [
        'alignment' => Jc::CENTER,
        'spaceAfter' => 300,
    ]
);

$section->addText(
    'UČEBNA: U16',
    [
        'name' => 'Arial',
        'size' => 24,
        'bold' => true,
        'color' => 'FF4D4D',
    ],
    [
        'alignment' => Jc::CENTER,
        'spaceAfter' => 300,
    ]
);

$section->addText(
    'Český jazyk a literatura: xx:xx',
    ['size' => 18, 'color' => '000000'],
    ['spaceAfter' => 100]
);

$section->addText(
    'Matematika: xx:xx',
    ['size' => 18, 'color' => '000000'],
    ['spaceAfter' => 300]
);

$phpWord->addTableStyle(
    'StudentsTable',
    [
        'borderSize' => 6,
        'borderColor' => '000000',
        'cellMargin' => 50,
    ]
);

$table = $section->addTable('StudentsTable');

$table->addRow();

$table->addCell(2500)->addText(
    'PŘÍJMENÍ',
    ['bold' => true, 'color' => '000000', 'size' => 15]
);

$table->addCell(2500)->addText(
    'JMÉNO',
    ['bold' => true, 'color' => '000000', 'size' => 15]
);

$table->addCell(5000)->addText(
    'PODPIS',
    ['bold' => true, 'color' => '000000', 'size' => 15]
);

$students = [
    ['Bína', 'Robert'],
    ['Fulytka', 'Denys'],
    ['Götzová', 'Madeline Olivia'],
    ['Hájková', 'Sofie'],
    ['Konečný', 'Petr'],
    ['Košťál', 'Martin'],
    ['Lakomá', 'Veronika'],
    ['Mikl', 'Dalibor'],
    ['Nedomová', 'Kateřina'],
    ['Pipalová', 'Tereza'],
    ['Plchová', 'Gabriela'],
    ['Procházka', 'Jan'],
    ['Rossi', 'Kryštof'],
    ['Rožnovský', 'Štěpán'],
    ['Sedlák', 'Petr'],
    ['Shylo', 'Kyryl'],
    ['Siekliková', 'Sofie'],
    ['Slavíček', 'Jakub'],
    ['Slezáková', 'Eliška'],
    ['Svoboda', 'David'],
    ['Trochta', 'Jakub'],
    ['Vejrosta', 'Dominik'],
    ['Hašek', 'Jaroslav'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
    ['a', 'a'],
];

foreach ($students as $student) {
    $table->addRow();

    $table->addCell(2500)->addText($student[0], ["size" => 12]);
    $table->addCell(3000)->addText($student[1], ["size" => 12]);
    $table->addCell(5000)->addText('');
}

//$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
\PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_TCPDF);
\PhpOffice\PhpWord\Settings::setPdfRendererPath('./vendor/tecnickcom/tcpdf');
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, "PDF");

header("Content-Description: File Transfer");
//header('Content-Disposition: attachment; filename="test.docx"');
header("Content-Disposition: attachment; filename=test.pdf");
//header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$writer->save('php://output');
exit;
