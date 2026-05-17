<?php
require "../assets/config.php";
session_start();
if (isset($_POST["id_sites"])) {
    $comp = $conn->query("SELECT JSON_OBJECT('presname', p.name, 'description', description, 'compname', c.name, 'short_info', short_info, 'long_info', long_info, 'id_companies', c.id_companies) json_row FROM companies_teamPropaganda c NATURAL LEFT JOIN sites_teamPropaganda s LEFT JOIN presentations_teamPropaganda p on p.id_presentations = s.id_presentations WHERE id_sites = " . $_POST["id_sites"] . " LIMIT 1;");
    $res = $comp->fetch_assoc()["json_row"];
    if (!$res) {
        http_response_code(400);
        echo "Nebyla nalezena žádná data.";
        die;
    }
    $temp = json_decode($res, true);
    $fields = [];
    $fil = $conn->query("SELECT short FROM `companies_fields_teamPropaganda` NATURAL JOIN fields_teamPropaganda f WHERE id_companies = " . $temp["id_companies"]);
    while ($field = $fil->fetch_assoc()) {
        $fields[] = $field["short"];
    }
    $temp["fields"] = $fields;
    echo json_encode($temp);
    die;
} else {
    http_response_code(400);
    echo "Nebyla poslána žádná data.";
    die;
}
