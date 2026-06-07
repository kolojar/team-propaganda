<?php
require './assets/config.php';
require 'vendor/autoload.php';
session_start();


$table = [
    "cjStart" => null,
    "mStart" => null,
    "class" => [],
];



if (!isset($_GET["seId"])) {
    echo "Nebyl zadán žádný parametr.";
    die;
}
$stmt = $conn->query("SELECT * FROM subevents_teamPropaganda WHERE id_subevents = " . $_GET["seId"]);
if (!$se = $stmt->fetch_assoc()) {
    echo "Nepodařilo se získat data z databáze.";
    die;
}

$table["cjStart"] = $se["time_cjl"];
$table["mStart"] = $se["time_mat"];

$stmt->close();
if (!$classes = $conn->query("SELECT * FROM classrooms_subevents_teamPropaganda NATURAL JOIN classrooms_teamPropaganda WHERE id_subevents = " . $_GET["seId"])) {
    echo "Nepodařilo se získat data z databáze.";
    die;
}

while ($class = $classes->fetch_assoc()) {
    $stmt = $conn->query("SELECT a.name, a.surname FROM attendants_presence_teamPropaganda NATURAL JOIN `registered_attendants_teamPropaganda` NATURAL JOIN attendants_teamPropaganda a WHERE id_subevents = " . $_GET["seId"] . " AND id_classrooms = " . $class["id_classrooms"]);
    if ($stmt->num_rows > 0) {
        $uclass = [];
        while ($user = $stmt->fetch_assoc()) {
            $uclass[] = [$user["surname"], $user["name"]];
        }
    }
    $stmt->close();
    if (isset($uclass)) {
        $table["class"][$class["name"]] = $uclass;
    }
}
$classes->close();

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


header('Content-Disposition: attachment; filename="test.docx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$templateProcessor->saveAs('php://output');
exit;
