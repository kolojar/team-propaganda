<?php
require "../assets/config.php";
session_start();
//if (!isset($_SESSION["userIds"])) {
//    header("Location: ./loginForm.html");
//    exit;
//}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

    <style>
        .y {
            color: #F4D572;
        }
    </style>
</head>

<body>
    <?php
    if (isset($_GET["file"]) && is_dir("../files/" . $_GET["file"])) {
        echo $_GET["file"];
    ?><br>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Jméno</th>
                <th>Email</th>
            </tr>
            <?php
            $stmt = $conn->query("SELECT * FROM users_teamPropaganda WHERE isNILE = 0 AND role = 'user'");
            if ($stmt->num_rows > 0) {
                while ($user = $stmt->fetch_assoc()) {
                    echo "<tr><td>";
                    if (file_exists("../files/" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1])) {
                        echo "<button file='" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1] . "' class='show formButton'><svg width='20' height='20' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
 <path d='M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
 <path d='M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
 </svg></button><button userId='" . $user["id_users"] . "' class='delete formButton formErrorColor deleteUserButton'><svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"black\"><path d=\"M267.33-120q-27.5 0-47.08-19.58-19.58-19.59-19.58-47.09V-740H160v-66.67h192V-840h256v33.33h192V-740h-40.67v553.33q0 27-19.83 46.84Q719.67-120 692.67-120H267.33Zm425.34-620H267.33v553.33h425.34V-740Zm-328 469.33h66.66v-386h-66.66v386Zm164 0h66.66v-386h-66.66v386ZM267.33-740v553.33V-740Z\"/></svg>
</button><button class='download' file='" . $_GET["file"] . "/" . $user["id_users"] . "." . explode(".", $_GET["file"])[1] . "'>d</button>";
                    } else {
                        echo "<button userId='" . $user["id_users"] . "' class='addFile formButton'><svg height='20' viewBox='0 0 2 2' xmlns='http://www.w3.org/2000/svg'>
                    <g fill='none' fill-rule='evenodd'>
                        <path d='M0 0h2v2H0z' />
                        <path d='m1.376 0 .499.626V2H.125V0zm-.06.125H.25v1.75h1.5V.67zm-.253.563v.313h.313v.125h-.313v.313H.938v-.313H.625v-.125h.313V.688z' fill='#000' fill-rule='nonzero' />
                    </g>
                </svg></button>";
                    }
                    echo "</td><td>" . $user["name"] . " " . $user["surname"] . "</td><td>" . $user["email"] . "</td></tr>";
                }
            }
            ?>
        </table>
    <?php
        echo "<button onclick=\"window.location.href='./fs.php'\">Zpět</button>";
    } else {
    ?>
        <div style="text-align: right;">
            <button id="addFolder"><svg width="50" height="50" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg">
                    <path d="m764.386 112.941 225.882 338.824H1920v1185.882c0 88.213-67.799 160.913-154.016 168.718l-15.396.694H169.412c-88.214 0-160.913-67.799-168.718-154.016L0 1637.647V112.941zm-60.537 112.941H112.941v1411.765c0 27.708 20.079 50.776 46.354 55.56l10.117.91h1581.176c27.608 0 50.754-19.989 55.557-46.324l.914-10.146V564.706H225.882V451.765H854.4zm312.622 564.706v282.353h282.353v112.941H1016.47v282.353H903.529v-282.353H621.176v-112.94H903.53V790.587z" fill-rule="evenodd" />
                </svg></button>
            <button id="addFile"><svg width="50" height="50" viewBox="0 0 2 2" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M0 0h2v2H0z" />
                        <path d="m1.376 0 .499.626V2H.125V0zm-.06.125H.25v1.75h1.5V.67zm-.253.563v.313h.313v.125h-.313v.313H.938v-.313H.625v-.125h.313V.688z" fill="#000" fill-rule="nonzero" />
                    </g>
                </svg></button>
        </div>
        <table>
            <tr>
                <th>Akce</th>
                <th>Název</th>
            </tr>
            <?php
            $files = array_diff(scandir("../files/"), array('..', '.'));
            foreach ($files as $file) {
                echo "<tr><td>";
                if (is_dir("../files/$file")) {
                    echo "<button file='$file' class='showDir'><svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 48 48'>
<path d='M 5 5 C 3.3545455 5 2 6.3545455 2 8 L 2 37 C 2 39.749516 4.2504839 42 7 42 L 36.804688 42 C 38.920892 42 40.815665 40.660847 41.519531 38.664062 L 47.402344 21.998047 C 48.080614 20.075541 46.611203 18 44.572266 18 L 17.830078 18 C 15.713874 18 13.819101 19.339153 13.115234 21.335938 L 8.0566406 35.667969 A 1.0001 1.0001 0 1 0 9.9433594 36.332031 L 15.001953 22.001953 C 15.426087 20.798738 16.554283 20 17.830078 20 L 44.572266 20 C 45.293328 20 45.755354 20.652537 45.515625 21.332031 L 39.634766 37.998047 C 39.210632 39.201262 38.080483 40 36.804688 40 L 7 40 C 5.3315161 40 4 38.668484 4 37 L 4 8 C 4 7.4454545 4.4454545 7 5 7 L 13.140625 7 C 13.900451 7 14.589267 7.4246825 14.929688 8.1035156 L 16.552734 11.34375 C 17.060303 12.35739 18.101329 13 19.234375 13 L 38 13 C 39.116666 13 40 13.883334 40 15 A 1.0001 1.0001 0 1 0 42 15 C 42 12.802666 40.197334 11 38 11 L 19.234375 11 C 18.853421 11 18.512228 10.789579 18.341797 10.449219 L 18.341797 10.447266 L 16.71875 7.2070312 C 16.04117 5.8558644 14.652799 5 13.140625 5 L 5 5 z'></path>
</svg></button><button file='../files/$file' class='delDir'><svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"black\"><path d=\"M267.33-120q-27.5 0-47.08-19.58-19.58-19.59-19.58-47.09V-740H160v-66.67h192V-840h256v33.33h192V-740h-40.67v553.33q0 27-19.83 46.84Q719.67-120 692.67-120H267.33Zm425.34-620H267.33v553.33h425.34V-740Zm-328 469.33h66.66v-386h-66.66v386Zm164 0h66.66v-386h-66.66v386ZM267.33-740v553.33V-740Z\"/></svg>
</button></td><td class = y>" . $file . "</td></tr>";
                } else {
                    echo "<button file='$file' class='show'><svg width='20' height='20' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
 <path d='M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
 <path d='M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
 </svg></button><button file='../files/$file' class='delete'><svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"black\"><path d=\"M267.33-120q-27.5 0-47.08-19.58-19.58-19.59-19.58-47.09V-740H160v-66.67h192V-840h256v33.33h192V-740h-40.67v553.33q0 27-19.83 46.84Q719.67-120 692.67-120H267.33Zm425.34-620H267.33v553.33h425.34V-740Zm-328 469.33h66.66v-386h-66.66v386Zm164 0h66.66v-386h-66.66v386ZM267.33-740v553.33V-740Z\"/></svg>
</button><button class='download' file='$file'>d</button></td><td>" . $file . "</td></tr>";
                }
            }
            ?>
        </table>
    <?php
    }
    ?>

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

        if (document.getElementById("addFile")) {
            document.getElementById("addFile").addEventListener("click", async (e) => {
                let file = await dm.OpenPrompt("Soubor", "Vyberte soubor", null, "file")
                if (file && file[0]) {
                    SendToast("Nahrávání souboru", "Soubor úspěšně nahrán", "ok")
                    let data = new FormData()
                    data.append('files[]', file[0])
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
                let name = await dm.OpenPrompt("Název", "Zadejte název souborů. <br>Tento název bude použit pro všechny soubory, které budou vloženy do této složky. (To platí i pro příponu.)", null)
                if (name) {
                    let data = new FormData()
                    data.append("name", name)
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
                if (await dm.OpenConfirm("Smazání souboru", "Opravdu si přejete smazat tento soubor?")) {
                    let data = new FormData()
                    if (urlSearchParams.has("file")) {
                        data.append("name", "../files/" + urlSearchParams.get("file") + "/" + del.getAttribute("userId") + "." + urlSearchParams.get("file").split(".")[1])
                    } else {
                        data.append("name", del.getAttribute("file"))
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
                let file = await dm.OpenPrompt("Soubor", "Vyberte soubor. <br>Jméno souboru bude změněno na jméno složky.", null, "file")
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
                if (await dm.OpenConfirm("Smazání složky", "Opravdu si přejete smazat tuto složku? <br>Všechny soubory v této složce budou smazány také.")) {
                    let data = new FormData()
                    data.append("rmdir", del.getAttribute("file"))
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
