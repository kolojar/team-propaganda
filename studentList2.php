<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$phpWord = new PhpWord();

$section = $phpWord->addSection([
    'marginTop' => 800,
    'marginLeft' => 800,
    'marginRight' => 800,
    'marginBottom' => 800,
]);

$section->addText(
    'SEZNAM ŽÁKŮ',
    ['size' => 33, 'bold' => true],
    ['alignment' => 'center']
);

$section->addText(
    'UČEBNA: U16',
    ['size' => 24, 'color' => 'FF0000'],
    ['alignment' => 'center']
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
    'StudentTable',
    [
        'borderSize' => 6,
        'borderColor' => '000000',
        'cellMargin' => 50,
    ]
);

$table = $section->addTable('StudentTable');

$table->addRow();
$table->addCell(2500)->addText('PŘÍJMENÍ', ['bold' => true, 'color' => '000000', 'size' => 15]);
$table->addCell(2500)->addText('JMÉNO', ['bold' => true, 'color' => '000000', 'size' => 15]);
$table->addCell(400, ['borderTopStyle' => \PhpOffice\PhpWord\SimpleType\Border::NIL, 'vMerge' => true]);
$table->addCell(2500)->addText('PŘÍJMENÍ', ['bold' => true, 'color' => '000000', 'size' => 15]);
$table->addCell(2500)->addText('JMÉNO', ['bold' => true, 'color' => '000000', 'size' => 15]);

$students = [
    ["Bína", "Robert"],
    ["Pipalová", "Tereza"],
    ["Fulytka", "Denys"],
    ["Plchová", "Gabriela"],
    ["Götzová", "Madeline Olivia"],
    ["Procházka", "Jan"],
    ["Hájková", "Sofie"],
    ["Rossi", "Kryštof"],
    ["Konečný", "Petr"],
    ["Rožnovský", "Štěpán"],
    ["Košťál", "Martin"],
    ["Sedlák", "Petr"],
    ["Lakomá", "Veronika"],
    ["Shylo", "Kyryl"],
    ["Mikl", "Dalibor"],
    ["Siekliková", "Sofie"],
    ["Nedomová", "Kateřina"],
    ["Slavíček", "Jakub"],
    ["Slezáková", "Eliška"],
    ["Svoboda", "David"],
    ["Trochta", "Jakub"],
    ["Vejrosta", "Dominik"]
];

for ($i = 0; $i < count($students); $i += 2) {
    $table->addRow();

    $table->addCell(2500)->addText($students[$i][0], ["size" => 12]);
    $table->addCell(2500)->addText($students[$i][1], ["size" => 12]);

    $table->addCell(400, ['borderTopStyle' => \PhpOffice\PhpWord\SimpleType\Border::NIL, 'vMerge' => true]);
    if ($students[$i + 1]) {
        $table->addCell(2500)->addText($students[$i + 1][0], ["size" => 12]);
        $table->addCell(2500)->addText($students[$i + 1][1], ["size" => 12]);
    }
}
$section = $phpWord->addSection();
$section->addPageBreak();
$writer = IOFactory::createWriter($phpWord, 'Word2007');
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="test2.docx"');
//header("Content-Disposition: attachment; filename=test.pdf");
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$writer->save('php://output');
exit;
