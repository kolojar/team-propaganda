<?php
require "./assets/config.php";

$now = new DateTime("now",  new DateTimeZone('Europe/Prague'));
$now->format("d-m-H-i");
if ((int)$now->format("H") % 2 == 1) {
    sendMail("matej.koralka@email.cz", "odd hour", "This is a CRON test with a time restriction.");
}

$stmt = $conn->prepare("SELECT id_users, email, subject, message, id_email_send FROM email_send_teamPropaganda NATURAL JOiN email_send_user_teamPropaganda NATURAL JOIN users_teamPropaganda WHERE send <= ? AND sent = 0");
$date = $now->format("Y-m-d H");
$stmt->bind_param("s", $date);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $stmt = $conn->query("SELECT id_files FROM email_send_files_teamPropaganda WHERE id_email_send = " . $row["id_email_send"]);
        $files = [];
        while ($file = $stmt->fetch_assoc()) {
            $files[] = $file["id_files"];
        }
        //echo $row["email"], $row["subject"], $row["message"], json_encode($files), $row["id_users"];
        sendMail($row["email"], $row["subject"], $row["message"], json_encode($files), $row["id_users"], "./files/");
        $stmt = $conn->prepare("UPDATE email_send_user_teamPropaganda SET sent = 1 WHERE id_users = ? AND id_email_send = ?");
        $stmt->bind_param("ii", $row["id_users"], $row["id_email_send"]);
        $stmt->execute();
        $stmt->close();
    }
}
$res->close();

$stmt = $conn->prepare("SELECT * FROM email_send_teamPropaganda WHERE send = ? AND isGlobal = 1 OR isGlobal = 2 OR isGlobal = 3");
$stmt->bind_param("s", $date);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        if ($row["isGlobal"] == 1) {
            $users = $conn->query("SELECT * FROM users_teamPropaganda WHERE isNile = 0 AND role = 'user'");
        } else if ($row["isGlobal"] == 2) {
            $users = $conn->query("SELECT * FROM users_teamPropaganda WHERE isNile = 1 AND role = 'user'");
        } else if ($row["isGlobal"] == 3) {
            $users = $conn->query("SELECT * FROM users_teamPropaganda WHERE role = 'user'");
        }
        $stmt = $conn->prepare("INSERT INTO email_send_user_teamPropaganda (id_users, id_email_send, sent) VALUES (?, ?, 1)");
        if (isset($users) && $users->num_rows > 0) {
            while ($user = $users->fetch_assoc()) {
                $stmt2 = $conn->query("SELECT id_files FROM email_send_files_teamPropaganda WHERE id_email_send = " . $row["id_email_send"]);
                $files = [];
                while ($file = $stmt2->fetch_assoc()) {
                    $files[] = $file["id_files"];
                }
                $stmt2->close();
                sendMail($user["email"], $row["subject"], $row["message"], json_encode($files), $user["id_users"], "./files/");
                $stmt->bind_param("ii", $user["id_users"], $row["id_email_send"]);
                $stmt->execute();
            }
        }
        $stmt->close();
        $conn->query("UPDATE email_send_teamPropaganda SET isGlobal = isGlobal + 3 WHERE id_email_send = " . $row["id_email_send"]);
    }
}
