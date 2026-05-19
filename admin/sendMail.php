<?php
require "../assets/config.php";
//if (!isset($_SESSION["userId"])) {
//    header("Location: ./loginForm.html");
//    exit();
//}
if (isset($_POST["subject"]) && isset($_POST["message"]) && isset($_POST["userIds"])) {
    $sent = 0;
    $attachments = null;
    if (isset($_POST["files"])) {
        $attachments = $_POST["files"];
    }
    if (isset($_POST["datetime"])) {
        $global = 0;
        if (isset($_POST["global"])) {
            $global = $_POST["userIds"] + 1;
        }
        $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message, send, isGlobal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_POST["subject"], $_POST["message"], $_POST["datetime"], $global);
    } else {
        //file_put_contents("php://stdout", "else" . "\n");
        $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST["subject"], $_POST["message"]);
        foreach (json_decode($_POST["userIds"]) as $uid) {
            //file_put_contents("php://stdout", "foreach $uid" . "\n");
            $stmt2 = $conn->prepare("SELECT email FROM users_teamPropaganda WHERE id_users = ?");
            $stmt2->bind_param("i", $uid);
            if (!$stmt2->execute()) {
                http_response_code(400);
                echo "Nepodařilo se zapsat data do databáze u uživatele s id: $uid";
                die;
            }
            $stmt2->store_result();
            //
            //add variable checking in messages;
            //
            $message = $_POST["message"];
            $stmt2->bind_result($email);
            $stmt2->fetch();
            //file_put_contents("php://stdout", "mes $message, ema $email, uid $uid, sub " . $_POST["subject"] . " att $attachments" . "\n");
            $mail = sendMail($email, $_POST["subject"], $message, $attachments, $uid);
            //file_put_contents("php://stdout", "mail $mail" . "\n");
            if (!$mail) {
                http_response_code(400);
                echo "Nepodařilo se odeslat email pro uživatele s id: $uid";
                die;
            }
            $stmt2->close();
        }
        $sent = 1;
    }
    if (!$stmt->execute()) {
        http_response_code(400);
        echo ("Nepodařilo se odeslat data na do databáze.\n");
        exit;
    }
    $emailId = $stmt->insert_id;
    $stmt->prepare("INSERT INTO email_send_user_teamPropaganda (id_users, id_email_send, sent) VALUES (?, ?, ?)");
    if (!isset($_POST["global"])) {
        foreach (json_decode($_POST["userIds"]) as $uid) {
            $stmt->bind_param("iii", $uid, $emailId, $sent);
            if (!$stmt->execute()) {
                http_response_code(400);
                echo "Nepodařilo se uložit data k uživateli s id: $uid";
            };
        }
    }
    $stmt->prepare("INSERT INTO email_send_files_teamPropaganda (id_files, id_email_send) VALUES (?, ?)");
    foreach (json_decode($attachments) as $fileId) {
        $stmt->bind_param("ii", $fileId, $emailId);
        if (!$stmt->execute()) {
            http_response_code(400);
            echo "Nepodařilo se uložit soubory k emailu.";
        };
    }
    $stmt->close();
    exit;
}
if (isset($_GET["isNILE"])) $isNILE = $_GET["isNILE"];
else header("Location: ./accessDenied.php");
?>

<html>
<style>
    html {
        margin: 8px;
    }

    * {
        user-select: none;
    }

    .y {
        color: #F4D572;
    }
</style>

<head>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

</head>

<body>
    <!--<form id="form">-->
    <fieldset id="users">
        <table>
            <tr>
                <th>×</th>
                <th>Jméno</th>
                <th>E-mail</th>
            </tr>
            <?php
            $res = ($isNILE == 2) ? $conn->query("SELECT id_users, name, surname, email FROM users_teamPropaganda WHERE role = 'user';") : $conn->query("SELECT id_users, name, surname, email FROM users_teamPropaganda WHERE isNILE = " . $isNILE . " AND role = 'user';");
            $uid = $_GET["uid"];
            while ($row = $res->fetch_object()) {
                echo "<tr><td><input type='checkbox' name='users' " . (($row->id_users == $uid) ? "checked " : " ") . "value='$row->id_users'/></td><td>$row->name $row->surname</td><td>$row->email</td></tr>";
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
        $files = array_diff(scandir("../templates/"), array('.', '..'));
        foreach ($files as $file) {
            echo "<option value='$file' id='option-$file'>$file</option>";
        }
        ?>
    </select><br>
    <input type="checkbox" checked id="now" name="now">
    <label for="now">Odeslat ihned</label><br>
    <input type="date" id="date" name="date" disabled today>
    <input type="number" id="hour" name="hour" min=0 max=23 value=12 disabled><br>
    <div id="attachments">

    </div>
    <button id="addAttachment" name="addAttachment">+</button>
    <label for="addAttachment">Přidat přílohu</label>
    <input type="submit" id="submit">
    <!--</form>-->
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast
        } from "../formWebScripts/js/formScript.js";
        import {
            FormDialogManager
        } from "../formWebScripts/js/formDialogScript.js";
        let dm = new FormDialogManager();
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

        document.getElementById("submit").addEventListener('click', (e) => {
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
            let files;
            for (let file of document.getElementsByClassName("atch")) {
                if (!files) {
                    files = []
                };
                files.push(file.getAttribute("file"))

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
            if (files != undefined) data.append("files", JSON.stringify(files))
            if (!document.getElementById("now").checked) {
                data.append("datetime", document.getElementById("date").value + " " + document.getElementById("hour").value)
            }
            if (document.getElementById("global").checked) {
                data.append("global", true)
                data.append("userIds", <?php echo $isNILE; ?>)

            } else {
                data.append("userIds", JSON.stringify(userIds))
            }

            const [ok, res] = await SendPOSTDataToServerAsync("./sendMail.php", data);

            if (ok) SendToast("Odpověď serveru:", res, "ok")
            else SendToast("Odpověď serveru", res, "error")

        }

        document.getElementById("addAttachment").addEventListener("click", async (e) => {
            e.preventDefault()
            let options = new Map();
            let files = {};

            <?php
            $files = ($isNILE == 2) ? $conn->query("SELECT id_files, name, isDir FROM `files_teamPropaganda`") : $conn->query("SELECT id_files, name, isDir FROM `files_teamPropaganda` WHERE isNILE = 2 OR isNILE = " . $isNILE);
            while ($file = $files->fetch_assoc()) {
                echo "files['" . $file["id_files"] . "'] = '" . $file["name"] . "'\n";
                if ($file["isDir"] == 1 && is_dir("../files/" . $file["name"])) {
                    echo "options.set('" . $file["name"] . "','" . $file["id_files"] . "')\n"; //but yellow
                } else if ($file["isdir"] == 0 && file_exists("../files/" . $file["name"])) {
                    echo "options.set('" . $file["name"] . "','" . $file["id_files"] . "')\n";
                }
            }
            ?>
            console.log(options)

            let file = await dm.OpenSelect("Příloha", "Vyberte přílohu z nabídky.<br><a href='../admin/fs.php' target='_blank'>Přidat novou přílohu.</a>", null, options)
            console.log(file)
            if (file) {
                let btn = document.createElement("button")
                btn.classList.add("atch")
                btn.setAttribute("file", file)
                btn.innerHTML = files[file]
                btn.addEventListener("click", (e) => {
                    btn.remove();
                })
                document.getElementById("attachments").append(btn)
            }
        })
    </script>
</body>

</html>
