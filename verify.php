<?php
session_start();
require "./assets/config.php";
//user already logged in
if (isset($_SESSION["userId"]) && !(isset($_SESSION["verify"]) || isset($_POST["verify"]))) {
    header("Location: ./user/main.php");
    exit();
}
//login
if (isset($_POST["login"])) {
    $_SESSION["login"] = $_POST["login"];
    $stmt = $conn->prepare("SELECT * FROM users_teamPropaganda WHERE email = ?");
    $stmt->bind_param("s", $_SESSION["login"]);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    if ($res->num_rows > 0) {
        echo "vpoho";
        verify($_SESSION["login"]);
        exit;
    } else {
        echo "Email nenalezen.";
        $_SESSION["login"] = null;
        exit;
    }
} else if (
    isset($_POST["email"]) &&
    isset($_POST["nameA"]) &&
    isset($_POST["surnameA"]) &&
    isset($_POST["id_schools"]) &&
    isset($_POST["nameU"]) &&
    isset($_POST["surnameU"]) &&
    isset($_POST["phone"])
) { //sign in
    $_SESSION["signup"] = $_POST["email"];
    $_SESSION["nameA"] = $_POST["nameA"];
    $_SESSION["surnameA"] = $_POST["surnameA"];
    $_SESSION["id_schools"] = $_POST["id_schools"];
    $_SESSION["nameU"] = $_POST["nameU"];
    $_SESSION["surnameU"] = $_POST["surnameU"];
    $_SESSION["phone"] = $_POST["phone"];

    $stmt = $conn->prepare("SELECT * FROM users_teamPropaganda WHERE email = ?");
    $stmt->bind_param("s", $_SESSION["signup"]);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    if ($res->num_rows == 0) {
        verify($_POST["email"]);
        exit;
    } else {
        http_response_code(400);
        echo "Uživatel již přihlášen";
        $_SESSION["signup"] = null;
        $_SESSION["nameA"] = null;
        $_SESSION["surnameA"] = null;
        $_SESSION["id_schools"] = null;
        $_SESSION["nameU"] = null;
        $_SESSION["surnameU"] = null;
        $_SESSION["phone"] = null;

        exit;
    }
} else if (isset($_POST["verify"])) {
    $_SESSION["verify"] = $_POST["verify"];
    verify($_SESSION["verify"]);
    exit;
} else if (!$_SESSION["login"] && !$_SESSION["signup"] && !$_SESSION["verify"]) { //trying to go around
    header('Location: ./user/loginForm.php');
    exit;
}

/**
 * create verify code and call sendMail() with email prepared
 */
function verify(string $email)
{
    $code = rand(10000, 99999);
    //echo $code;
    $_SESSION["verifyCode"] = $code;
    $message = str_replace("\${login_code}", $code, file_get_contents("./assets/AuthEmail.html"));
    if (!sendMail($email, "Ověření Emailu", $message)) {
        http_response_code(400);
        echo "Nepodařilo se odeslat email.";
        die;
    }
}
?>
<!DOCTYPE html>
<html lang="cz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ověřte si účet</title>
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <link rel="stylesheet" href="./formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="./assets/style.css">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
</head>

<body class="formBackground" form-box-holder>
    <img class="formBackgroundLogo" src="../assets/PurkynkaVERBrnoOrgBila.png">
    <form-box>
        <p class="formHeader">Přihlášení do systému akcí</p>
        <form method="post" id="form">
            <form-input icon='../formWebScripts/images/key32.svg' type="text" label="Ověřovací kód:" id="code" placeholder="Zadejte ověřovací kód"></form-input>
            <div class="formButtonBoxHolder formCenter">
                <button type="submit" class="formButton purkynkaButton">Ověřit ověřovací kód</button>
            </div>
        </form>
        <form-status-message></form-status-message>
    </form-box>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
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
        const dialogManager = new FormDialogManager()

        //const fields = document.getElementsByClassName('otp-field');
        //console.log(fields)
        //let index = 0
        //for (let field of fields) {
        //
        //    // Handle entering a digit
        //    field.addEventListener('input', (e) => {
        //        const value = e.target.value;
        //        // Auto-advance cursor
        //        if (value && index < fields.length - 1) {
        //            fields[index + 1].focus();
        //        }
        //    });
        //
        //    // Handle backspace logic
        //    field.addEventListener('keydown', (e) => {
        //        if (e.key === 'Backspace' && !field.value && index > 0) {
        //            fields[index - 1].focus();
        //        } else if (e.key === 'Enter' && index === 4) {
        //            e.preventDefault();
        //            submit(e)
        //        }
        //    });
        //
        //    // Handle pasting the code
        //    field.addEventListener('paste', (e) => {
        //        e.preventDefault();
        //        // Get the pasted data and filter for numbers only
        //        const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
        //
        //        if (pasteData.length > 0) {
        //            // Fill each field starting from the one being pasted into
        //            for (let i = 0; i < pasteData.length; i++) {
        //                const targetIndex = i;
        //                if (targetIndex < fields.length) {
        //                    fields[targetIndex].value = pasteData[i];
        //                }
        //            }
        //
        //            // Focus the next available empty field or the last field
        //            const nextFocusIndex = Math.min(index + pasteData.length, fields.length - 1);
        //            fields[nextFocusIndex].focus();
        //        }
        //    });
        //
        //    // Prevent non-numeric characters from being typed
        //    field.addEventListener('keypress', (e) => {
        //        if (!/[0-9]/.test(e.key)) {
        //            e.preventDefault();
        //        }
        //    });
        //    index++
        //};

        // Simple form submission feedback
        document.getElementById('form').addEventListener('submit', async (e) => {
            await submit(e)
        })

        async function submit(e) {
            e.preventDefault();
            SetWaitStatusForms("Probíhá kontrola kódu, čekejte prosím...")
            //const btn = document.querySelector('.verify-btn');
            //btn.textContent = 'Verifying...';
            //btn.style.opacity = '0.7';

            // Collect the full code
            //let verificationCode = "";
            //for (let field of fields) {
            //    verificationCode += field.value;
            //};

            const data = new FormData();
            data.append("code", document.getElementById("code").value);
            const [ok, res] = await SendPOSTDataToServerAsync("./codeVerify.php", data);

            if (ok) {
                if (res == "true") {
                    window.location.href = "./user/user.php";
                }
                else {
                    SendToast("Odpověď serveru", res, "error");
                    setTimeout(async () => {
                        await dialogManager.ShowAlertAsync("Zaslat ověřovací kód", "Zadány neplané údaje, zkuste to prosím znovu.")
                        window.location.reload()
                    }, 1000)
                }
            } else {
                SendToast("Odpověď serveru", res, "error");
                setTimeout(async () => {
                    await dialogManager.ShowAlertAsync("Zaslat ověřovací kód", "Zadány neplané údaje, zkuste to prosím znovu.")
                    window.location.reload()
                }, 1000)
            }
        };
    </script>
</body>

</html>