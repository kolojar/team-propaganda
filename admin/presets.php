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
                        <td>
                            <button file='$file' class='delete purkynkaButton formButtonInline' form-icon>
                                <svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"black\"><path d=\"M267.33-120q-27.5 0-47.08-19.58-19.58-19.59-19.58-47.09V-740H160v-66.67h192V-840h256v33.33h192V-740h-40.67v553.33q0 27-19.83 46.84Q719.67-120 692.67-120H267.33Zm425.34-620H267.33v553.33h425.34V-740Zm-328 469.33h66.66v-386h-66.66v386Zm164 0h66.66v-386h-66.66v386ZM267.33-740v553.33V-740Z\"/></svg>
                            </button><button file='$file' class='templateChange purkynkaButton formButtonInline' form-icon>
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"black\"><path d=\"M226.67-80q-27 0-46.84-19.83Q160-119.67 160-146.67v-666.66q0-27 19.83-46.84Q199.67-880 226.67-880H560l240 240v260h-66.67v-220H520v-213.33H226.67v666.66H620V-80H226.67ZM878-74.33 753.33-199v113h-66.66v-227.33H914v66.66H800L924.67-122 878-74.33Zm-651.33-72.34V-813.33v666.66Z\"/></svg>
                            </button>
                        </td>
                    </tr>";
            }
            ?>
        </table>
        <form-input type="text" label="Název souboru:" id="name" placeholder="Zadejte název souboru"></form-input>
        <form-input type="textarea" label="Zpráva:" id="message" placeholder="Zadejte zprávu"></form-input>
        <button id="save" class="purkynkaButton">Uložit</button>
        <button id="savenew" class="purkynkaButton">Uložit jako nový dokument</button>
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