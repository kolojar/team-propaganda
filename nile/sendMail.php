<?php
require "../assets/config.php";
//if (!isset($_SESSION["userId"])) {
//    header("Location: ./loginForm.html");
//    exit();
//}
if (isset($_POST["subject"]) && isset($_POST["message"]) && isset($_POST["userIds"])) {
    //echo $_POST["subject"], $_POST["message"], $_POST["userIds"], $_POST["datetime"];
    $sent = 0;
    if (isset($_POST["datetime"])) {
        if (!isset($_POST["global"])) {
            $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message, send) VALUES (?, ?, ?)");
        } else {
            $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message, send, isGlobal) VALUES (?, ?, ?, 1)");
        }
        $stmt->bind_param("sss", $_POST["subject"], $_POST["message"], $_POST["datetime"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST["subject"], $_POST["message"]);
        foreach (json_decode($_POST["userIds"]) as $uid) {
            $stmt2 = $conn->prepare("SELECT email FROM users_teamPropaganda WHERE id_users = ?");
            $stmt2->bind_param("i", $uid);
            if ($stmt2->execute()) echo "sent $uid\n";
            else echo "not sent $uid\n";
            $stmt2->store_result();
            //
            //add variable checking in messages;
            //
            $message = $_POST["message"];
            $stmt2->bind_result($email);
            $stmt2->fetch();
            sendMail($email, $_POST["subject"], $message);
        }
        $sent = 1;
    }
    if ($stmt->execute())
        echo ("vpoho\n");
    else echo ("vprdeli\n");
    $emailId = $stmt->insert_id;
    $stmt->prepare("INSERT INTO email_send_user_teamPropaganda (id_users, id_email_send, sent) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $uid, $emailId, $sent);
    foreach ($_POST["userIds"] as $uid) {
        if (!$stmt->execute()) echo "nope $uid\n";
    }
    $stmt->close();
    exit;
}
?>

<html>
<style>
    html {
        margin: 8px;
    }

    * {
        user-select: none;
    }
</style>

<head>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

</head>

<body>
    <form id="form">
        <fieldset id="users">
            <table>
                <tr>
                    <th>×</th>
                    <th>Jméno</th>
                    <th>E-mail</th>
                </tr>
                <?php
                $res = $conn->query("SELECT id_users, name, surname, email FROM users_teamPropaganda WHERE isNILE = 1;");
                while ($row = $res->fetch_object()) {
                    echo "<tr><td><input type='checkbox' name='users' value='$row->id_users'/></td><td>$row->surname $row->name</td><td>$row->email</td></tr>";
                }
                ?>
            </table>
        </fieldset>
        <input type="checkbox" id="checkall" name="checkall">
        <label for="checkall">Vybrat všechny</label><br>
        <input type="checkbox" id="global" name="global"><label for="global">Globální oznámení</label><br>
        <label for="subject">Předmět</label>
        <input type="text" id="subject" name="subject" required><br>
        <label for="message">Zpráva</label><br>
        <textarea name="message" id="message" required></textarea><br>
        <label for="templates">Předvolby</label>
        <select id="templates">
            <option value="none" id="option-none">nový</option>
            <?php
            $files = array_diff(scandir("../klal/templates/"), array('.', '..'));
            foreach ($files as $file) {
                echo "<option value='$file' id='option-$file'>$file</option>";
            }
            ?>
        </select><br>
        <input type="checkbox" checked id="now" name="now">
        <label for="now">Odeslat ihned</label><br>
        <input type="date" id="date" name="date" disabled today>
        <input type="number" id="hour" name="hour" min=0 max=23 value=12 disabled><br>
        <input type="submit">
    </form>
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast
        } from "../formWebScripts/js/formScript.js";

        let selectedTemplate = "none";

        document.getElementById("now").addEventListener("change", () => {
            document.getElementById("date").disabled = (document.getElementById("date").disabled == true) ? false : true;
            document.getElementById("hour").disabled = (document.getElementById("hour").disabled == true) ? false : true;
        });

        document.getElementById("global").addEventListener("change", () => {
            document.getElementById("checkall").disabled = (document.getElementById("checkall").disabled == true) ? false : true;
            document.getElementById("users").disabled = (document.getElementById("users").disabled == true) ? false : true;
        })

        document.getElementById("templates").addEventListener("change", (e) => {
            let template = document.getElementById("templates").value;
            if (!confirm("Opradu si přejete změnit template?\nVšechny změny budou smazány.")) {
                template = selectedTemplate;
                //document.getElementById(selectedTemplate).selected = true;
                selectedTemplate = template;
                return;
            }
            if (template == "none") {
                document.getElementById("message").value = "";
            } else {
                var request = new XMLHttpRequest();
                request.open('GET', "./templates/" + template, true);
                request.onload = function() {
                    if (request.status >= 200 && request.status < 400) {
                        document.getElementById("message").value = request.responseText;
                    } else {
                        SendToast("Odpověď serveru", request.responseText, "error")
                    }
                };
                request.onerror = function() {
                    SendToast("Odpověď serveru", "connection error", "error")
                };
                request.send();
            }
        })

        for (let user of document.getElementsByName("users")) {
            user.addEventListener("change", () => {
                document.getElementById("checkall").indeterminate = true;
                let same = true;
                let sameCheck;
                for (let check of document.getElementsByName("users")) {
                    if (sameCheck == null || sameCheck == undefined) {
                        sameCheck = check.checked;
                    }
                    if (check.checked != sameCheck) {
                        same = false;
                        break;
                    }
                }
                if (same) {
                    document.getElementById("checkall").indeterminate = false;
                    document.getElementById("checkall").checked = sameCheck;
                }
            })
        }

        document.getElementById("checkall").addEventListener("change", () => {
            for (let check of document.getElementsByName("users")) {
                check.checked = document.getElementById("checkall").checked;
            }
        })

        document.getElementById("form").addEventListener('submit', (e) => {
            sendToPHP(e)
        })

        async function sendToPHP(e) {
            e.preventDefault();
            let userIds = [];
            for (let user of document.getElementsByName("users")) {
                if (user.checked) {
                    userIds.push(user.value);
                }
            }
            if (userIds.length == 0 && !document.getElementById("global").checked) {
                SendToast("Chyba", "Nebyl vybrán žádný příjemce.", "error")
                return;
            }
            if (document.getElementById("global").checked && document.getElementById("now").checked) {
                SendToast("Chyba", "Globální zprávu nelze odeslat ihned", "error");
                return
            }

            const data = new FormData();
            data.append("subject", document.getElementById("subject").value)
            data.append("message", document.getElementById("message").value)
            if (!document.getElementById("now").checked) {
                data.append("datetime", document.getElementById("date").value + " " + document.getElementById("hour").value)
            }
            if (document.getElementById("global").checked) {
                data.append("global", true)
                data.append("userIds", "")

            } else {
                data.append("userIds", JSON.stringify(userIds))
            }

            const [ok, res] = await SendPOSTDataToServerAsync("./sendMail.php", data);

            if (ok) SendToast("Odpověď serveru:", res, "ok")
            else SendToast("Odpověď serveru", res, "error")

        }
    </script>
</body>

</html>
