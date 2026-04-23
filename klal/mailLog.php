<?php
require "../assets/config.php";
//if (!isset($_SESSION["userId"])) {
//    header("login.php");
//    exit();
//}

$res = $conn->query("SELECT * FROM email_send_user_teamPropaganda NATURAL JOIN email_send_teamPropaganda NATURAL JOIN users_teamPropaganda WHERE isNILE = 0 ORDER BY send DESC");

?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
    <table>
        <tr>
            <th>
                Odesláno
            </th>
            <th>
                Čas odeslání
            </th>
            <th>
                Příjemce
            </th>
            <th>
                Předmět
            </th>
            <th>
                Zpráva
            </th>
        </tr>
        <?php
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo "<tr><td><input type='checkbox' onclick='return false;' ";
                if ($row["sent"] == 1) {
                    echo "checked";
                }
                echo "></td>";
                echo "<td>" . $row["send"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["subject"] . "</td>";
                echo "<td>" . htmlspecialchars($row["message"]) . "</td>";
            }
        }
        ?>
    </table>
    <script>
    </script>
</body>

</html>
