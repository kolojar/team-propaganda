<?php
require "../assets/config.php";
session_start();
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
            $stmt->bind_param("ddi", $site["id" . $check["id_sites"]]["posX"], $site["id" . $check["id_sites"]]["posY"], $check["id_sites"]);
            if (!$stmt->execute()) {
                http_response_code(400);
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
            /*padding: 1vw;*/
            border-radius: 50%;
        }

        .round .icon {
            width: 2.5vw;
        }

        /*.square {
            padding: 0.5vw;
        }*/

        .site {
            position: absolute;
            border: none;
            cursor: pointer;
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            width: 4vw;
        }

        .map {
            position: absolute;
            z-index: -1;
            margin: 0;
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
    <img id="map" src="assets/img.png" style="width: 80%; height: auto; display: block;" class="map">
    <?php
    $sites = $conn->query("SELECT * FROM sites_teamPropaganda NATURAL RIGHT JOIN companies_teamPropaganda");
    $offset = 15;
    while ($site = $sites->fetch_assoc()) {
        echo "<button class='";
        if ($site["isClass"] == null) {
            echo "site round";
        } else {
            echo "site square";
        }
        echo "' id='" . $site["id_sites"] . "'";

        if ($site["posX"] != 0 && $site["posY"] != 0) {
            echo "data-pct-x='" . $site["posX"] . "' data-pct-y='" . $site["posY"] . "'";
        }
        if ($site["electricity"]) {
            echo "style=' background-color: #00FF00;";
        } else {
            echo "style=' background-color: #FF0000;";
        }
        echo "transform: translate(-50%, -50%);'>";
        if ($site["icon"] != null) {
            echo '<img class="icon" src="data:image/jpeg;base64,' . base64_encode($site["icon"]) . '" >';
        }
        echo "</button>";
        $offset += 10;
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
            lastMousePosition.x = event.clientX + scrollX
            lastMousePosition.y = event.clientY + scrollY
            if (elementToMove != null) {
                elementToMove.style.top = lastMousePosition.y + "px"
                elementToMove.style.left = lastMousePosition.x + "px"
            }
        }

        document.getElementById("save").addEventListener("click", async (e) => {
            let data = new FormData();
            let siteList = {};
            const map = document.getElementById("map");
            const mapWidth = map.clientWidth;
            const mapHeight = map.clientHeight;
            for (let site of document.getElementsByClassName("site")) {

                let relativeY = ((Number(site.style.left.slice(0, -2)) / mapWidth) * 100);
                let relativeX = ((Number(site.style.top.slice(0, -2)) / mapHeight) * 100);
                console.log(relativeX, relativeY, Number(site.style.top.slice(0, -2)))

                if (relativeX < 0 || relativeX > 100 || relativeY < 0 || relativeY > 100) {
                    relativeX = 0;
                    relativeY = 0;
                }

                siteList["id" + site.id] = {
                    posX: relativeX,
                    posY: relativeY
                };
            }
            console.log(siteList["id1"], siteList["id2"])
            data.append("sitePos", JSON.stringify(siteList))

            let [ok, res] = await SendPOSTDataToServerAsync("./adminmap.php", data)

            if (ok) {
                SendToast("Odpověď serveru", res, "ok");
                window.location.reload()
            } else SendToast("Odpověď serveru", res, "error")

        })


        function repositionPins() {
            console.log("repos")
            const map = document.getElementById("map");
            const pins = document.getElementsByClassName("site");

            const mapWidth = map.clientWidth;
            const mapHeight = map.clientHeight;
            let offset = 15;

            for (let pin of pins) {
                const pctX = pin.getAttribute("data-pct-x");
                const pctY = pin.getAttribute("data-pct-y");

                if (pctX && pctY && pctX != 0 && pctY != 0) {
                    pin.style.left = ((pctY / 100) * mapWidth) + "px";
                    pin.style.top = ((pctX / 100) * mapHeight) + "px";
                } else if (pctX == 0 || pctY == 0 || !pctX || !pctY) {
                    pin.style.left = (0.895 * window.innerWidth) + "px";
                    pin.style.top = offset + "%"
                }
                offset += 10
            }
        }

        repositionPins();
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>
