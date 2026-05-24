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
    if (!$stmt->bind_param("ss", $pass, $_POST["email"]) || !$stmt->execute() || !$stmt->store_result() || !$stmt->bind_result($_SESSION["userId"]) || !$stmt->fetch() || !$stmt->close()) {
        http_response_code(400);
        echo "Nepodařilo se získat data z databáze.";
        die;
    }
    if (!isset($_SESSION["userId"])) {
        http_response_code(400);
        echo "Nesprávný email nebo heslo.";
        die;
    } else {
        http_response_code(201);
        echo "Přihlášení bylo úspěšné.";
        die;
    }
    die;
}

?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlásit se k admin panelu</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
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
            SendToast,
            SetWaitStatusForms
        } from "../formWebScripts/js/formScript.js";
        import {
            FormDialogManager
        } from "../formWebScripts/js/formDialogScript.js";

        const dialogManager = new FormDialogManager();
        let sent = false
        document.getElementById("form").addEventListener("submit", (e) => {
            sendToPHP(e)
        });
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
                    if (res == "Přihlášení bylo úspěšné.") {
                        window.location.href = "./admin.php";
                    } else {
                        SendToast("Odpověď serveru", res, "error");
                        setTimeout(async () => {
                            await dialogManager.OpenAlert("Přihlásit se", "Komunikace se serverem se nezdařila, zkuste to prosím znovu a později.")
                            window.location.reload()
                        }, 1000)
                    }
                } else {
                    SendToast("Odpověď serveru", res, "error")
                    setTimeout(async () => {
                        await dialogManager.OpenAlert("Přihlásit se", "Zadány neplané údaje, zkuste to prosím znovu.")
                        window.location.reload()
                    }, 1000)
                }
            }
        }
        document.getElementById("email").addEventListener("change", () => {
            sent = false
        })
    </script>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
</body>
