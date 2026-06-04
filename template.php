<?php

require 'vendor/autoload.php';


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


$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor("./assets/template.docx");

$templateProcessor->setValue("timeC", $table["cjStart"]);
$templateProcessor->setValue('timeM', $table["mStart"]);

$templateProcessor->cloneBlock("block", count($table["class"]), true, true);
$index = 1;
foreach ($table["class"] as $key => $class) {
    $templateProcessor->setValue("class#" . $index, $key);

    $val = array();
    foreach ($class as $student) {
        $val[] = [
            "sur#" . $index => $student[0],
            "name#" . $index => $student[1],
        ];
    }

    $templateProcessor->cloneRowAndSetValues('sur#' . $index, $val);
    $index++;
}


//header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="test.docx"');
//header("Content-Disposition: attachment; filename=test.pdf");
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$templateProcessor->saveAs('php://output');
exit;
