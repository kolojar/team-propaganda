<?php
require "../assets/config.php";

if (!isset($_SESSION["adminId"])) {
    //header("location: ../adminLogin.php")
}

if (isset($_POST["sitePos"])) {
    $checks = $conn->query("SELECT id_sites, posX, posY FROM sites_teamPropaganda");
    $site = json_decode($_POST["sitePos"], true);
    $stmt = $conn->prepare("UPDATE `sites_teamPropaganda` SET `posX` = ?, `posY` = ? WHERE `sites_teamPropaganda`.`id_sites` = ?;");
    while ($check = $checks->fetch_assoc()) {
        if ($site["id" . $check["id_sites"]]["posX"] == 0) {
            $site["id" . $check["id_sites"]]["posX"] = null;
        }
        if ($site["id" . $check["id_sites"]]["posY"] == 0) {
            $site["id" . $check["id_sites"]]["posY"] = null;
        }
        if ($site["id" . $check["id_sites"]]["posX"] != $check["posX"] || $site["id" . $check["id_sites"]]["posY"] != $check["posY"]) {
            $stmt->bind_param("iii", $site["id" . $check["id_sites"]]["posX"], $site["id" . $check["id_sites"]]["posY"], $check["id_sites"]);
            if (!$stmt->execute()) {
                echo "error UPDATE";
                exit;
            }
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        html,
        body {
            margin: 0;
        }

        * {
            user-select: none;
            -webkit-user-drag: none;
        }

        .round {
            padding: 1vw;
            border-radius: 50%;
        }

        .square {
            padding: 0.5vw;
        }

        .site {
            position: absolute;
            border: none;
            cursor: pointer;
            display: grid;
            place-items: center;
            aspect-ratio: 1;
        }

        .map {
            position: absolute;
            z-index: -1;
        }

        .icon {
            width: 3vw;
            aspect-ratio: 1;
        }

        /*#save {
            float: right;
            margin-right: 6%;
        margin-top: 2%;
        }

        */
    </style>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

</head>

<body>
    <img id="map" src="assets/img.png" width="80%" class="map">
    <?php
    $sites = $conn->query("SELECT * FROM sites_teamPropaganda NATURAL RIGHT JOIN companies_teamPropaganda");
    $offset = 7;
    while ($site = $sites->fetch_assoc()) {
        echo "<button class='";
        if ($site["isClass"] == null) {
            echo "site round";
        } else {
            echo "site square";
        }
        echo "' id='" . $site["id_sites"] . "' style='";
        if ($site["posX"] != null && $site["posY"] != null) {
            echo "top:" . $site["posX"] * 0.8 . "px; left:" . $site["posY"] * 0.8 . "px;";
        } else {
            echo "top:" . $offset . "%; left: 88%;";
        }
        if ($site["electricity"]) {
            echo " background-color: #00FF00;";
        } else {
            echo " background-color: #FF0000;";
        }
        echo "'>";
        if ($site["icon"] != null) {
            echo '<img class="icon" src="data:image/jpeg;base64,' . base64_encode($site["icon"]) . '" >';
        }
        echo "</button>";
        $offset += 5;
    }


    ?>
    <div class="formCenter" style="width:20%; height: 10%; float: right; margin-top: 2%;">
        <button id="save" class="formButton formOkColor">Uložit plánek</button>
    </div>

    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast
        } from "../formWebScripts/js/formScript.js";

        for (let site of document.getElementsByClassName("site")) {
            site.addEventListener("mousedown", (e) => {
                e.preventDefault()
                dragStart(site)
            })
            site.addEventListener("touchstart", (e) => {
                e.preventDefault()
                dragStart(site)
            })
            site.addEventListener("mouseup", (e) => {
                dragEnd()
            })
            site.addEventListener("touchend", (e) => {
                dragEnd()
            })
        }
        let elementToMove = null;

        function dragStart(element) {
            console.log(element)
            elementToMove = element
            elementToMove.style.zIndex = "10"
        }

        function dragEnd() {
            if (elementToMove.style.left > window.innerWidth * 0.8) {

            }
            elementToMove.style.zIndex = "0"
            elementToMove = null
        }

        let lastMousePosition = {
            x: 0,
            y: 0
        }
        document.getElementsByTagName("html").item(0).addEventListener("mousemove", (e) => {
            dragMove(e)
        })
        document.getElementsByTagName("html").item(0).addEventListener("touchmove", (e) => {
            dragMove(e)
        })

        function dragMove(event) {
            lastMousePosition.x = event.clientX + scrollX - 25
            lastMousePosition.y = event.clientY + scrollY - 25
            if (elementToMove != null) {
                elementToMove.style.top = lastMousePosition.y + "px"
                elementToMove.style.left = lastMousePosition.x + "px"
            }
        }

        document.getElementById("save").addEventListener("click", async (e) => {

            let data = new FormData();
            let siteList = {}
            for (let site of document.getElementsByClassName("site")) {
                let top = 0,
                    left = 0;
                if (site.style.top.includes("%") || site.style.left.includes("%")) {
                    top = 0
                    left = 0
                    console.log("%")
                } else if (Number(site.style.top.slice(0, -2)) != NaN && Number(site.style.left.slice(0, -2)) != NaN) {
                    top = site.style.top.slice(0, -2);
                    left = site.style.left.slice(0, -2);
                    console.log("vpoho")
                }
                if (top > document.getElementById("map").height || left > document.getElementById("map").width) {
                    top = 0;
                    left = 0;
                    console.log("0")
                }
                siteList["id" + site.id] = {
                    posX: top / 0.8,
                    posY: left / 0.8
                }
            }
            console.log(siteList)
            data.append("sitePos", JSON.stringify(siteList))

            let [ok, res] = await SendPOSTDataToServerAsync("./adminmap.php", data)

            if (ok) SendToast("Odpověď serveru", res, "ok")
            else SendToast("Odpověď serveru", res, "error")

        })
    </script>
</body>

</html>
