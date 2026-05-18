<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();
if (isset($_SESSION["userId"])) {
    header("Location: ./admin.php");
    die;
}

if (isset($_POST["password"]) && isset($_POST["email"])) {
    $pass = hash("sha256", $_POST["password"]);
    $stmt = $conn->prepare("SELECT id_users FROM `password_user_teamPropaganda` NATURAL JOIN users_teamPropaganda WHERE password = ? AND email = ?");
    $stmt->bind_param("ss", $pass, $_POST["email"]);
    if (!$stmt->execute()) {
        http_response_code(400);
        echo "Nepodařilo se získat data z databáze.";
        die;
    }
    $stmt->store_result();
    $stmt->bind_result($_SESSION["userId"]);
    if (!isset($_SESSION["user"])) {
        http_response_code(400);
        echo "Nesprávný email nebo heslo.";
        die;
    }


    die;
}

?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Přihlásit se k admin panelu</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="formBackground" form-box-holder>
    <img class="formBackgroundLogo" src="../assets/PurkynkaVERBrnoOrgBila.png">
    <form-box>
        <p class="formHeader">Přihlášení do správy systému akcí</p>
        <form id="form">
            <form-input type="email" label="Přihlašovací E-mail" id="email" placeholder="Zadejte Váš E-mail"></form-input>
            <form-input type="password" label="Heslo" id="password" placeholder="Zadejte Váše heslo"></form-input>
            <div class="formButtonBoxHolder formCenter">
                <button type="submit" class="formButton purkynkaButton">Přihlásit se</button>
            </div>
        </form>
        <form-status-message></form-status-message>
    </form-box>
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast, SetWaitStatusForms
        } from "../formWebScripts/js/formScript.js";
        import {
            FormDialogManager
        } from "../formWebScripts/js/formDialogScript.js";

        const dialogManager = new FormDialogManager();
        let sent = false
        document.getElementById("form").addEventListener("submit", (e) => { sendToPHP(e) });
        async function sendToPHP(e) {
            e.preventDefault();
            if (!sent) {
                SetWaitStatusForms("Probíhá odesílání požadavku na server, čekejte prosím...")
                sent = true
                const data = new FormData();
                data.append("email", document.getElementById("email").getValue());
                data.append("password", document.getElementById("password").getValue());

                const [ok, res] = await SendPOSTDataToServerAsync("./index.php", data);

                if (ok) {
                    if (res == "vpoho") {
                        window.location.href = "./admin.php";
                    }
                    else {
                        SendToast("Odpověď serveru", res, "error");
                        setTimeout(async () => {
                            await dialogManager.OpenAlert("Přihlásit se", "Komunikace se serverem se nezdařila, zkuste to prosím znovu a později.")
                            window.location.reload()
                        }, 2000)
                    }
                }
                else {
                    SendToast("Odpověď serveru", res, "error")
                    setTimeout(async () => {
                        await dialogManager.OpenAlert("Přihlásit se", "Zadány neplané údaje, zkuste to prosím znovu.")
                        window.location.reload()
                    }, 2000)
                }
            }
        }
        document.getElementById("email").addEventListener("change", () => { sent = false })
    </script>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
</body>