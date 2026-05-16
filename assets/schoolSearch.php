<?php
session_start();
require "../assets/config.php";

//Make SQL Query
//$stmt = $conn->prepare("SELECT schools.id_schools, schools.name, schools.address,
//-- Get score (search order)
//(
// -- Find best name match using FULLTEXT search
// MATCH(schools.name) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +
// -- Find best address match using FULLTEXT search
// MATCH(schools.address) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +
// -- Find best parital name match
// (schools.name COLLATE utf8mb4_general_ci LIKE ?) +
// -- Find best parital address match
// (schools.address COLLATE utf8mb4_general_ci LIKE ?)
//) AS score
//FROM schools
//WHERE
//-- Do matching again for each entry for comparsion
//MATCH(schools.name) AGAINST(? IN NATURAL LANGUAGE MODE) OR MATCH(schools.address) AGAINST(? IN NATURAL LANGUAGE MODE) OR schools.name COLLATE utf8mb4_general_ci LIKE ? OR schools.address COLLATE utf8mb4_general_ci LIKE ?
//-- Order by score and get 20 best
//ORDER BY score DESC LIMIT 20;");
$stmt = $conn->prepare("SELECT s.id_schools, s.name, s.address,
-- Get score (search order)
(
-- Exact match
(LOWER(s.name) LIKE CONCAT(?, '%')) * 5 +

-- FULLTEXT search
MATCH(s.name) AGAINST(? IN NATURAL LANGUAGE MODE) * 3 +
MATCH(s.address) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +

-- Partial word matching
(LOWER(s.name) LIKE CONCAT('%', ?, '%')) * 2 +
(LOWER(s.address) LIKE CONCAT('%', ?, '%'))
) AS score
FROM schools_teamPropaganda s
WHERE
-- Do matching again for each entry for comparsion
MATCH(s.name) AGAINST(? IN NATURAL LANGUAGE MODE)
OR MATCH(s.address) AGAINST(? IN NATURAL LANGUAGE MODE)
OR LOWER(s.name) LIKE CONCAT('%', ?, '%')
OR LOWER(s.address) LIKE CONCAT('%', ?, '%')
-- Order by score and get 20 best
ORDER BY score DESC LIMIT 20");

//Execute SQL
//$queryQuestions = '%' . $_POST["query"] . '%';
$_POST["query"] = strtolower(trim($_POST["query"]));
$stmt->bind_param("sssssssss", $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"], $_POST["query"]);
if (!$stmt->execute()) {
    http_response_code(400);
    echo "Entry could not be fetched";
    die();
}
if (!$stmt->store_result()) {
    http_response_code(400);
    echo "Entry could not be fetched";
    die();
}

//Fetch all schools
$jsonRecords = [];
for ($i = 0; $i < $stmt->num_rows; $i++) {
    $stmt->bind_result($id, $name, $address, $_);
    $stmt->fetch();
    $jsonRecords[] = [
        "id" => $id,
        "name" => $name,
        "address" => $address,
    ];
}

//Generate JSON
http_response_code(201);
echo json_encode($jsonRecords);
die();
?>