<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();
if (!isset($_SESSION["userId"])) {
    //header("Location: loginAdmin.php");
}
if (isset($_POST["template"], $_POST["name"], $_POST["message"])) {
    //echo "got ";
    $template = $_POST["template"];
    $name = $_POST["name"];
    $message = $_POST["message"];
    //echo "$template, $name, $message\n";
    if (!isset($_POST["new"]) || $_POST["new"] == false) {
        unlink("../templates/$template");
    }
    $file = fopen("../templates/$name", "w");
    fwrite($file, $message);
    fclose($file);
    echo "succ";
    exit;
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
        <div class="formButtonBox">
            <button id="save" class="purkynkaButton" form-icon="!save"></button>
            <button id="savenew" class="purkynkaButton" form-icon="!saveAs"></button>
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

        document.getElementById("save").addEventListener("click", (e) => {
            save()
        })
        document.getElementById("savenew").addEventListener("click", (e) => {
            save(true)
        })

        function templateChange(template) {
            console.log('TADY')
            if (!confirm("Opradu si přejete změnit template?\nNeuložené změny budou smazány.")) {
                //document.getElementById(selectedTemplate).selected = true;
                selectedTemplate = template;
                return;
            }
            if (template == "none") {
                message.setValue("");
            } else {
                var request = new XMLHttpRequest();
                request.open('GET', "../templates/" + template, true);
                request.onload = function () {
                    if (request.status >= 200 && request.status < 400) {
                        document.getElementById("message").setValue(request.responseText);
                    } else {
                        SendToast("Odpověď serveru", request.responseText, "error")
                    }
                };
                request.onerror = function () {
                    SendToast("Odpověď serveru", "connection error", "error")
                };
                request.send();
                document.getElementById("name").setValue(template);
            }
        }

        async function save(nev = false) {
            const data = new FormData();
            data.append("new", nev)
            data.append("template", selectedTemplate)
            data.append("name", document.getElementById("name").getValue())
            data.append("message", document.getElementById("message").getValue())

            console.log(data);

            const [ok, res] = await SendPOSTDataToServerAsync("./presets.php", data);

            if (ok) SendToast("Odpověď serveru:", res, "ok")
            else SendToast("Odpověď serveru", res, "error")
        }

        async function delet(file) {
            const data = new FormData();
            data.append("file", file)

            const [ok, res] = await SendPOSTDataToServerAsync("./presets.php", data);

            if (ok) SendToast("Odpověď serveru", res, "ok")
            else SendToast("Odpověď serveru", res, "error")
        }
    </script>
</body>

</html>