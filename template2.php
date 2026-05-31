<?php
require_once 'vendor/autoload.php';


$table = [
    "cjStart" => "8:00",
    "mStart" => "9:30",
    "class" => [
        "U12" => [
            ["Bína", "Robert"],
            ["Pipalová", "Tereza"],
            ["Fulytka", "Denys"],
            ["Plchová", "Gabriela"],
            ["Götzová", "Madeline Olivia"],
            ["Procházka", "Jan"],
            ["Hájková", "Sofie"],
            ["Rossi", "Kryštof"],
            ["Konečný", "Petr"],
        ],
        "U54" => [
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
            ["Vejrosta", "Dominik"],
        ],
        "U43" => [
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
        ],
    ],
];

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('./assets/template2.docx');

$templateProcessor->setValue("timeC", $table["cjStart"]);
$templateProcessor->setValue('timeM', $table["mStart"]);
//$idk = new PageBreak();
//$templateProcessor->setComplexBlock("np", $idk);

$templateProcessor->cloneBlock("block", count($table["class"]), true, true);
$index = 1;
foreach ($table["class"] as $key => $class) {
    $templateProcessor->setValue("class#" . $index, $key);

    $val = array();

    for ($i = 0; $i < count($class); $i += 2) {
        $val[] = [
            "sur#" . $index => $class[$i][0],
            "name#" . $index => $class[$i][1],
            "surn#" . $index => (($class[$i + 1]) ? $class[$i + 1][0] : ""),
            "namen#" . $index => (($class[$i + 1]) ? $class[$i + 1][1] : "")
        ];
    }

    $templateProcessor->cloneRowAndSetValues('sur#' . $index, $val);
    //$section = $phpWord->addSection()->addPageBreak();
    //$templateProcessor->setComplexBlock("np#" . $index, $section);
    $index++;
}
//\PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_TCPDF);
//\PhpOffice\PhpWord\Settings::setPdfRendererPath('./vendor/tecnickcom/tcpdf');
//$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, "PDF");

header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="test.docx"');
//header("Content-Disposition: attachment; filename=test.pdf");
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$templateProcessor->saveAs('php://output');
exit;
