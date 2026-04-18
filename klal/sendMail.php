<?php
require "../assets/config.php";
//if (!isset($_SESSION["userId"])) {
//    header("login.php");
//    exit();
//}

if (isset($_POST["subject"]) && isset($_POST["message"]) && isset($_POST["userIds"])) {
    //echo $_POST["subject"], $_POST["message"], $_POST["userIds"], $_POST["datetime"];
    $sent = 0;
    if (isset($_POST["datetime"])) {
        $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message, send) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_POST["subject"], $_POST["message"], $_POST["datetime"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO email_send_teamPropaganda (subject, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST["subject"], $_POST["message"]);
        foreach ($_POST["userIds"] as $uid) {
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
            echo "email $email\n";
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
        if ($stmt->execute()) echo "uid in $uid\n";
        else echo "nope $uid\n";
    }
    exit;
}
?>

<html>
<style>
    * {
        user-select: none;
    }
</style>

<head>
</head>

<body>
    <form id="form">
        <table id="users">
            <tr>
                <th>×</th>
                <th>Jméno</th>
                <th>E-mail</th>
            </tr>
            <?php
            $res = $conn->query("SELECT id_users, name, surname, email FROM users_teamPropaganda;");
            while ($row = $res->fetch_object()) {
                echo "<tr><td><input type='checkbox' name='users' onchange='userChange()' value='$row->id_users'/></td><td>$row->surname $row->name</td><td>$row->email</td></tr>";
            }
            ?>
        </table>
        <input type="checkbox" id=checkall name="checkall" onchange="checkallChange()">
        <label for="checkall">Vybrat všechny</label><br>
        <label for="subject">Předmět</label>
        <input type="text" id="subject" name="subject" required><br>
        <label for="message">Zpráva</label><br>
        <textarea name="message" id="message" required></textarea><br>
        <label for="templates">Předvolby</label>
        <select id="templates" onchange="templateChange(this.value)">
            <option value="none" id="option-none">nový</option>
            <?php
            $files = array_diff(scandir("./templates/"), array('.', '..'));
            foreach ($files as $file) {
                echo "<option value='$file' id='option-$file'>$file</option>";
            }
            ?>
        </select><br>
        <input type="checkbox" checked id="now" name="now" onchange="nowChange()">
        <label for="now">Odeslat ihned</label><br>
        <input type="date" id="date" name="date" disabled>
        <input type="number" id="hour" name="hour" min=0 max=23 value=12 disabled><br>
        <input type="submit">
    </form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        let selectedTemplate = "none";

        function nowChange() {
            date.disabled = (date.disabled == true) ? false : true;
            hour.disabled = (hour.disabled == true) ? false : true;
        }

        function templateChange(template) {
            if (!confirm("Opradu si přejete změnit template?\nVšechny změny budou smazány.")) {
                templates.value = selectedTemplate;
                //document.getElementById(selectedTemplate).selected = true;
                selectedTemplate = template;
                return;
            }
            if (template == "none") {
                message.value = "";
            } else {
                $.get("./templates/" + template, function(data) {
                    message.value = data;
                });
            }
        }

        function userChange() {
            checkall.indeterminate = true;
            let same = true;
            let sameCheck;
            for (check of document.getElementsByName("users")) {
                if (sameCheck == null || sameCheck == undefined) {
                    sameCheck = check.checked;
                    //console.log(sameCheck)
                }
                if (check.checked != sameCheck) {
                    same = false;
                    break;
                }
            }
            if (same) {
                checkall.indeterminate = false;
                checkall.checked = sameCheck;
            }


        }

        function checkallChange() {
            for (let check of document.getElementsByName("users")) {
                check.checked = checkall.checked;
            }
        }

        form.addEventListener('submit', (e) => {
            sendToPHP(e)
        })

        function sendToPHP(e) {
            e.preventDefault();
            let userIds = [];
            for (user of document.getElementsByName("users")) {
                if (user.checked) {
                    userIds.push(user.value);
                }
            }
            if (userIds.length == 0) {
                alert("Nebyl vybrán žádný příjemce.")
                return;
            }
            let data = {
                //datetime: (now.checked) ? "now" : date.value + " " + hour.value,
                subject: subject.value,
                message: message.value,
                userIds: userIds
            }

            if (!now.checked) {
                data.datetime = date.value + " " + hour.value;
            }
            console.log(data);

            $.ajax({
                url: './sendMail.php',
                method: "POST",
                data: data,
                success: function(response) {
                    console.log('Odpověď serveru:', response);
                    alert("Data úpěšně odeslána.")
                },
                error: function(err) {
                    console.error(err);
                    alert('Došlo k chybě při odesílání dat.');
                }
            });

        }
    </script>
</body>

</html>
