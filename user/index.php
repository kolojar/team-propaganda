<?php
require "../assets/config.php";
?>
<!DOCTYPE html>
<html lang="cz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uživatelský panel</title>    
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="./user.css">
    <style>
        ul {
            margin-top: 0px;
        }
    </style>
</head>
<body>
    <header>
        <div class="formButtonBoxHolder" style="margin-top: 0px;">
            <div class="formButtonBox formJustifyLeft">
                <h1 class="headerName" onclick="window.location.href = './index.php'">JMENO</h1>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="../logoff.php"><button class="formButton purkynkaButton">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <fieldset>
            <legend>Informace o Vás</legend>
            <form-input id="name" label="Jméno:" type="text"></form-input><br>
            <form-input id="surname" label="Přijmení:" type="text"></form-input>
            <div class="formButtonBoxHolder">
                <div class="formButtonBox formJustifyLeft">
                    <button class="formButton purkynkaButton" id="btnChangeEmail">Převést účet na jiný Email</button>
                </div>
                <div class="formButtonBox formJustifyRight formCollapsable" collapsed>
                    <button class="formButton purkynkaButton btnCancel">Zrušit provedené změny</button>
                    <button class="formButton purkynkaButton btnSave">Uložit změny</button>
                </div>
            </div>
        </fieldset>
        <br>
        <fieldset>
            <legend>Informace o zájemci: JMENO PRIJMENI</legend>
            <form-input id="name" label="Jméno:" type="text"></form-input><br>
            <form-input id="surname" label="Přijmení:" type="text"></form-input>
            <span>Přihlášené akce:</span>
            <ul>
                <li>DOD</li>
                <li><a href="">Připrava na kruyz</a> </li>
            </ul>
            <div class="formButtonBoxHolder">
                <div class="formButtonBox formJustifyLeft">
                    <button class="formButton purkynkaButton">Přihlásit na další akce</button>
                </div>
                <div class="formButtonBox formJustifyRight formCollapsable" collapsed>
                    <button class="formButton purkynkaButton btnCancel">Zrušit provedené změny</button>
                    <button class="formButton purkynkaButton btnSave">Uložit změny</button>
                </div>
            </div>
        </fieldset>
    </main>
    <footer>
        
    </footer>
    <script type="module" src="../formWebScripts/js/formScript.js"></script>
    <script src="./index.js" type="module"></script>
</body>
</html>