<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();
//if (!isset($_SESSION["userIds"])) {
//    header("Location: ./loginForm.html");
//    exit;
//}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Soubory</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .y {
            color: #F4D572;
        }
    </style>
</head>

<body>
    <header>
        <?php
        $result = setupTitlebarAdmin($conn, "fs.php");
        $userType = $result->getUserType(true);
        $isNILE = $userType->getIsNILE();
        if ($isNILE == -1) {
            header("Location: ./accessDenied.php");
            die;
        }
        ?>
    </header>
    <main>
        <?php
        if (isset($_GET["file"]) && is_dir("../files/" . $_GET["file"])) {
            echo "<h1>Zobrazení složky: " . $_GET["file"] . "</h1>";
            ?><br>
            <table>
                <tr>
                    <th>Akce</th>
                    <th>Jméno</th>
                    <th>Email</th>
                </tr>
                <?php
                $stmt = $conn->query("SELECT * FROM users_teamPropaganda WHERE type = 'KLAL' AND role = 'USER'");
                if ($stmt->num_rows > 0) {
                    while ($user = $stmt->fetch_assoc()) {
                        echo "<tr><td class='formButtonBoxTable'>";
                        if (file_exists("../files/" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1])) {
                            echo "<button form-icon='!eye' class='purkynkaButton show' file='" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1] . "'></button><button form-icon='!delete'  userId='" . $user["id_users"] . "' class='delete purkynkaButton deleteUserButton'></button><button form-icon='!download' class='download purkynkaButton' file='" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1] . "'></button>";
                        } else {
                            echo "<button form-icon='!addFile' userId='" . $user["id_users"] . "' class='addFile purkynkaButton'></button>";
                        }
                        echo "</td><td>" . $user["name"] . " " . $user["surname"] . "</td><td>" . $user["email"] . "</td></tr>";
                    }
                }
                ?>
            </table>
            <?php
            echo "<button class='purkynkaButton' onclick=\"window.location.href='./fs.php'\">Zpět</button>";
        } else {
            ?>
            <div class="formButtonBoxHolder">
                <div class="formJustifyLeft" style="align-items: center">
                    <h1 style="margin-bottom: 0px; margin-top: 4px;">Prohlížení souborů</h1>
                </div>
                <div class="formJustifyRight">
                    <button id="addFolder" class='purkynkaButton' form-icon="!addFolder"><span>Vytvořit složku</span></button>
                    <button id="addFile" class='purkynkaButton' form-icon="!addFile"><span>Vytvořit soubor</span></button>
                </div>
            </div>
            <table>
                <tr>
                    <th>Akce</th>
                    <th>Název</th>
                </tr>
                <?php
                //$files = array_diff(scandir("../files/"), array('..', '.'));
                $files = ($isNILE == 2) ? $conn->query("SELECT name, isDir FROM `files_teamPropaganda`") : $conn->query("SELECT name, isDir FROM `files_teamPropaganda` WHERE isNILE = 2 OR isNILE = " . $isNILE);
                while ($file = $files->fetch_assoc()) {
                    echo "<tr><td class='formButtonBoxTable'>";
                    if ($file["isDir"] == 1 && is_dir("../files/" . $file["name"])) {
                        echo "<button form-icon='!openFolder' class='purkynkaButton showDir' file='" . $file["name"] . "'></button><button form-icon='!delete' file='../files/" . $file["name"] . "' class='delDir purkynkaButton'></button></td><td form-icon='!folder'><span>" . $file["name"] . "</span></td></tr>";
                    } else if ($file["isdir"] == 0 && file_exists("../files/" . $file["name"])) {
                        echo "<button form-icon='!eye' file='" . $file["name"] . "' class='show purkynkaButton'></button><button form-icon='!delete' file='../files/" . $file["name"] . "' class='delete purkynkaButton'></button><button form-icon='!download' class='download purkynkaButton' file='" . $file["name"] . "'></button></td><td form-icon='!file'><span>" . $file["name"] . "</span></td></tr>";
                    }
                }
                ?>
            </table>
            <?php
        }
        ?>
    </main>
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
        const urlSearchParams = new URLSearchParams(window.location.search)

        let isNILE = urlSearchParams.get("isNILE");

        if (document.getElementById("addFile")) {
            document.getElementById("addFile").addEventListener("click", async (e) => {
                let file = await dm.ShowPromptAsync("Soubor", "Vyberte soubor", null, "file")
                if (file && file[0]) {
                    SendToast("Nahrávání souboru", "Soubor úspěšně nahrán", "ok")
                    let data = new FormData()
                    data.append('files[]', file[0]);
                    data.append("isNILE", isNILE)
                    let [ok, res] = await SendPOSTDataToServerAsync("./addFile.php", data);
                    if (ok) {
                        SendToast("Odpověď serveru", res, "ok");
                        window.location.reload()
                    } else SendToast("Odpověď serveru", res, "error")
                } else {
                    SendToast("Nahrávání souboru", "Soubor se nepodařilo nahrát", "error")
                    return;
                }
            })

            document.getElementById("addFolder").addEventListener("click", async (e) => {
                let name = await dm.ShowPromptAsync("Název", "Zadejte název souborů. <br>Tento název bude použit pro všechny soubory, které budou vloženy do této složky. (To platí i pro příponu.)", null)
                if (name) {
                    let data = new FormData()
                    data.append("name", name)
                    data.append("isNILE", isNILE)
                    let [ok, res] = await SendPOSTDataToServerAsync("./addFile.php", data)
                    if (ok) {
                        SendToast("Odpověď serveru", res, "ok");
                        window.location.reload()
                    } else SendToast("Odpověď serveru", res, "error")
                }
            })
        }

        for (let del of document.getElementsByClassName("delete")) {
            del.addEventListener("click", async (e) => {
                if (await dm.ShowConfirmAsync("Smazání souboru", "Opravdu si přejete smazat tento soubor?")) {
                    let data = new FormData()
                    if (urlSearchParams.has("file")) {
                        data.append("name", "../files/" + urlSearchParams.get("file") + "/" + del.getAttribute("userId") + "." + urlSearchParams.get("file").split(".")[1])
                    } else {
                        data.append("name", del.getAttribute("file"))
                        data.append("isNILE", isNILE)
                    }
                    let [ok, res] = await SendPOSTDataToServerAsync("./deleteFile.php", data)
                    if (ok) {
                        SendToast("Odpověď serveru", res, "ok");
                        window.location.reload()
                    } else SendToast("Odpověď serveru", res, "error")
                }
            })
        }

        for (let neu of document.getElementsByClassName("addFile")) {
            neu.addEventListener("click", async (e) => {
                let id = neu.getAttribute("userId");
                let data = new FormData()
                let file = await dm.ShowPromptAsync("Soubor", "Vyberte soubor. <br>Jméno souboru bude změněno na jméno složky.", null, "file")
                if (file && file[0]) {
                    SendToast("Nahrávání souboru", "Soubor úspěšně nahrán", "ok")
                    data.append("files[]", file[0])
                    data.append("id", id)
                    data.append("place", urlSearchParams.get("file"))
                    console.log(urlSearchParams.get("file"))
                    let [ok, res] = await SendPOSTDataToServerAsync("./addFile.php", data)
                    if (ok) {
                        SendToast("Odpověď serveru", res, "ok");
                        window.location.reload()
                    } else SendToast("Odpověď serveru", res, "error")
                } else {
                    SendToast("Nahrávání souboru", "Soubor se nepodařilo nahrát", "error")
                    return;
                }
            })
        }

        for (let del of document.getElementsByClassName("delDir")) {
            del.addEventListener("click", async (e) => {
                if (await dm.ShowConfirmAsync("Smazání složky", "Opravdu si přejete smazat tuto složku? <br>Všechny soubory v této složce budou smazány také.")) {
                    let data = new FormData()
                    data.append("rmdir", del.getAttribute("file"))
                    data.append("isNILE", isNILE)
                    let [ok, res] = await SendPOSTDataToServerAsync("./deleteFile.php", data)
                    if (ok) {
                        SendToast("Odpověď serveru", res, "ok");
                        window.location.reload()
                    } else SendToast("Odpověď serveru", res, "error")
                }
            })
        }
        for (let show of document.getElementsByClassName("showDir")) {
            show.addEventListener("click", (e) => {
                window.location.href = "./fs.php?file=" + show.getAttribute("file")
            })
        }
        for (let show of document.getElementsByClassName("show")) {
            show.addEventListener("click", (e) => {
                window.open("./showFile.php?file=" + show.getAttribute("file"), "_blank")
            })
        }
        for (let download of document.getElementsByClassName("download")) {
            download.addEventListener("click", (e) => {
                window.location.href = "./downloadFile.php?file=" + download.getAttribute("file")
            })
        }
    </script>
</body>

</html>