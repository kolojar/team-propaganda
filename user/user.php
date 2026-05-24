<?php
require "../assets/config.php";
session_start();
if (!isset($_SESSION["userId"]) || isset($_SESSION["verify"])) {
    if (isset($_SESSION["login"])) {
        $res = $conn->query("SELECT id_users FROM users_teamPropaganda WHERE `email` = '" . $_SESSION["login"] . "'");
        $_SESSION["userId"] = $res->fetch_assoc()["id_users"];
        $_SESSION["login"] = null;
        header("Location: ./main.php");
    } else if (isset($_SESSION["signup"])) {
        $stmt = $conn->prepare("INSERT INTO users_teamPropaganda (name, surname, email, id_schools) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_SESSION["name"], $_SESSION["surname"], $_SESSION["signup"], $_SESSION["id_schools"]);
        $stmt->execute();
        $_SESSION["userId"] = $stmt->insert_id;
        $_SESSION["name"] = null;
        $_SESSION["surname"] = null;
        $_SESSION["signup"] = null;
        $_SESSION["id_schools"] = null;
    } else if (isset($_SESSION["verify"])) {
        $stmt = $conn->prepare("UPDATE users_teamPropaganda SET email=? WHERE id_users=?;");
        $stmt->bind_param("si", $_SESSION["verify"], $_SESSION["userId"]);
        $stmt->execute();
        $_SESSION["verify"] = null;
    } else {
        header("Location: ./user/");
    }
    die();
}
if (isset($_POST["action"])) {
    if ($_POST["action"] == "update") {
        //Check if values set
        if (!isset($_POST["name"]) || !isset($_POST["surname"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }

        //Make SQL Update
        $stmt = $conn->prepare("UPDATE users_teamPropaganda SET name=?, surname=? WHERE id_users=?");
        $stmt->bind_param("ssi", $_POST["name"], $_POST["surname"], $_SESSION["userId"]);
        if ($stmt->execute()) {
            http_response_code(201);
            echo "Entry updated.";
            die();
        } else {
            http_response_code(400);
            echo "Entry could not be updated.";
            die();
        }
    } else if ($_POST["action"] == "getCompanyDays") {
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die;
        }
        $stmt = $conn->query("SELECT * FROM `company_days_companies_teamPropaganda` WHERE id_companies = " . $_POST["id"]);
        if ($stmt->num_rows == 0) {
            $res = $conn->query("SELECT * FROM company_days_teamPropaganda");
        } else {
            $arr = [];
            while ($row = $stmt->fetch_assoc()) {
                $arr[] = $row["id_company_days"];
            }
            $res = $conn->query("SELECT * FROM company_days_teamPropaganda WHERE id_company_days not in (" . join(", ", $arr) . ")");
        }
        $ret = [];
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $ret[] = [$row["id_company_days"], $row["date"], $row["description"]];
            }
        } else {
            http_response_code(400);
            echo "Nejsou na výběr žádné nové dny firem.";
            die;
        }
        echo json_encode($ret);
    } else if ($_POST["action"] == "addCD") {
        if (!isset($_POST["id"]) || !isset($_POST["idCD"])) {
            http_response_code(400);
            echo "Neplatné použití funkce - chybí parametr";
            die();
        }
        if ($conn->query("INSERT INTO company_days_companies_teamPropaganda (id_companies, id_company_days) VALUES (" . $_POST["id"] . ", " . $_POST["idCD"] . ")")) {
            echo "Data úspěšně uložena do databáze.";
            die;
        } else {
            http_response_code(400);
            echo "Data se nepodařilo zapsat do databáze";
            die;
        }
    } else {
        http_response_code(400);
        echo "Neplatné použití funkce - neplatná akc";
        die();
    }
}
