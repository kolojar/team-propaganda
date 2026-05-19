<?php
session_start();
if (isset($_SESSION["userId"])) {
    header("Location: ./main.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Přihlásit se</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="formBackground" form-box-holder>
    <img class="formBackgroundLogo" src="../assets/PurkynkaVERBrnoOrgBila.png">
    <form-box>
        <p class="formHeader">Přihlášení do systému akcí</p>
        <form method="post" id="form">
            <form-input type="email" label="Přihlašovací E-mail" id="email" placeholder="Zadejte Váš E-mail"></form-input>
            <div class="formButtonBoxHolder formCenter">
                <button type="submit" class="formButton purkynkaButton">Zaslat ověřovací kód</button>
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

        const dialogManager = new FormDialogManager()
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
                data.append("login", document.getElementById("email").getValue());

                const [ok, res] = await SendPOSTDataToServerAsync("../verify.php", data);

                if (ok) {
                    if (res == "vpoho") {
                        window.location.href = "../verify.php";
                    } else {
                        SendToast("Odpověď serveru", res, "error");
                        setTimeout(async () => {
                            await dialogManager.OpenAlert("Zaslat ověřovací kód", "Zadány neplané údaje, zkuste to prosím znovu.")
                            window.location.reload()
                        }, 2000)
                    }
                } else {
                    SendToast("Odpověď serveru", res, "error");
                    setTimeout(async () => {
                        await dialogManager.OpenAlert("Zaslat ověřovací kód", "Zadány neplané údaje, zkuste to prosím znovu.")
                        window.location.reload()
                    }, 2000)
                }
            } else {
                SendToast("Odpověď serveru", res, "error")
                setTimeout(async () => {
                    await dialogManager.OpenAlert("Zaslat ověřovací kód", "Zadány neplané údaje, zkuste to prosím znovu.")
                    window.location.reload()
                }, 2000)
            }
        }
        document.getElementById("email").addEventListener("change", () => {
            sent = false
        })
    </script>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
</body>

</html>
