<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();
if (!isset($_SESSION["userId"])) {
    header("Location: loginAdmin.php");
    die;
}
if (isset($_POST["template"], $_POST["name"], $_POST["message"])) {
    if ($_POST["message"] != null && $_POST["name"] != null) {
        $template = $_POST["template"];
        $name = $_POST["name"];
        $message = $_POST["message"];
        $file = fopen("../templates/$name", "w");
        fwrite($file, $message);
        fclose($file);
        echo "succ";
        exit;
    } else {
        echo "Neplatné použití funkce.";
        die;
    }
}
if (isset($_POST["file"])) {
    unlink("../templates/" . $_POST["file"]);
    echo "succ";
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Šablony</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>

    </style>
</head>

<body>
    <header>
        <?php $result = setupTitlebarAdmin($conn, "presets.php") ?>
    </header>
    <main>
        <table>
            <tr>
                <th>Název souboru</th>
                <th>Akce</th>
            </tr>
            <?php
            $files = array_diff(scandir("../templates/"), array('.', '..'));
            foreach ($files as $file) {
                echo "<tr>
                        <td>$file</td>
                        <td class='formButtonHolderTable'>
                            <button file='$file' class='delete purkynkaButton' form-icon='!delete'></button><button file='$file' class='templateChange purkynkaButton' form-icon='!openFile'></button>
                        </td>
                    </tr>";
            }
            ?>
        </table>
        <form-input type="text" label="Název souboru:" id="name" placeholder="Zadejte název souboru"></form-input>
        <form-input type="textarea" label="Zpráva:" id="message" placeholder="Zadejte zprávu"></form-input>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox">
                <button id="save" class="purkynkaButton" form-icon="!save"></button>
            </div>
        </div>
    </main>
    <script>
        console.log("html")
    </script>
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
        const dialogManager = new FormDialogManager()
        let selectedTemplate = "none";

        let temp = document.getElementsByClassName("templateChange")
        for (let t of temp) {
            t.addEventListener("click", (e) => {
                templateChange(t.getAttribute("file"))
            })
        }

        let del = document.getElementsByClassName("delete")
        for (let d of del) {
            d.addEventListener("click", (e) => {
                delet(d.getAttribute("file"))
            })
        }

        document.getElementById("save").addEventListener("click", async (e) => {
            await save()
        });

        async function templateChange(template) {
            console.log('TADY')
            if (!await dialogManager.ShowConfirmAsync("Otevřít šablonu?", "Opradu si přejete změnit šablonu?<br>Neuložené změny budou smazány.")) {
                //document.getElementById(selectedTemplate).selected = true;
                selectedTemplate = template;
                SendToast("Otevření šablony zrušeno", "Otevření šablony zrušeno.", "info")
                return;
            }
            if (template == "none") {
                message.setValue("");
            } else {
                const progress = dialogManager.ShowProgress("Otevřít šablonu", "Probíhá načítání dat ze serveru, čekejte prosím...")
                var request = new XMLHttpRequest();
                request.open('GET', "../templates/" + template, true);
                request.onload = async function() {
                    if (request.status >= 200 && request.status < 400) {
                        document.getElementById("message").setValue(request.responseText);
                    } else {
                        SendToast("Nelze otevřít šablonu!", "Nepodařilo se načíst informace.", "error")
                        progress?.CloseDialog()
                        await dialogManager.ShowAlertAsync("Otevřít šablonu", "Informace nemohly být načteny, opakujte akci později.<br>Důvod: " + request.responseText)
                    }
                };
                request.onerror = async function() {
                    SendToast("Nelze otevřít šablonu!", "Nepodařilo se načíst informace.", "error")
                    progress?.CloseDialog()
                    await dialogManager.ShowAlertAsync("Otevřít šablonu", "Informace nemohly být načteny, opakujte akci později.<br>Důvod: Neznámá chyba.")
                };
                request.send();
                document.getElementById("name").setValue(template);
                SendToast("Šablona otevřena", "Informace o šabloně načteny.", "ok")
                progress?.CloseDialog()
            }
        }

        async function save() {
<<<<<<< HEAD
            if (!await dialogManager.ShowConfirmAsync("Uložit šablonu?", "Opradu si přejete uložit šablonu?<br>Předchozí obsah bude smazán.")) {
=======
            if (!await dialogManager.OpenConfirm("Uložit šablonu?", "Opradu si přejete uložit šablonu?<br>Předchozí obsah bude smazán.")) {
>>>>>>> 304dbe9 (update #24)
                SendToast("Ukládání šablony zrušeno", "Ukládání šablony zrušeno.", "info")
                return
            }
            const data = new FormData();
            data.append("template", selectedTemplate)
            data.append("name", document.getElementById("name").value)
            data.append("message", document.getElementById("message").value)

            console.log(data);
            const wait = dialogManager.ShowProgress("Odesílání dat na server", "Čekejte prosím", () => {}, 0)
            const [ok, res] = await SendPOSTDataToServerAsync("./presets.php", data);
            wait.CloseDialog()

            if (ok) {
                SendToast("Odpověď serveru:", res, "ok")
                window.location.reload()
            } else SendToast("Odpověď serveru", res, "error")
        }

        async function delet(file) {
            if (!await dialogManager.ShowConfirmAsync("Smazat šablonu?", "Opravdu si přejete smazat tento soubor?<br>Tato akce nelze vzít zpět.")) {
                SendToast("Mazání šablony zrušeno", "Mazání šablony zrušeno.", "info")
                return
            }
            const data = new FormData();
            data.append("file", file)

            const [ok, res] = await SendPOSTDataToServerAsync("./presets.php", data);

            if (ok) {
                SendToast("Odpověď serveru", res, "ok")
                location.reload()
            } else SendToast("Odpověď serveru", res, "error")
        }
    </script>
</body>

</html>
