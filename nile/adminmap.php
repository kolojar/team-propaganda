<?php
require "../assets/config.php";
session_start();
if (!isset($_SESSION["adminId"])) {
    //header("location: ../adminLogin.php")
}

if (isset($_POST["sitePos"])) {
    $checks = $conn->query("SELECT id_sites, posX, posY, floor FROM sites_teamPropaganda");
    $site = json_decode($_POST["sitePos"], true);
    $stmt = $conn->prepare("UPDATE `sites_teamPropaganda` SET `posX` = ?, `posY` = ?, `floor` = ? WHERE `sites_teamPropaganda`.`id_sites` = ?;");
    while ($check = $checks->fetch_assoc()) {
        if ($site["id" . $check["id_sites"]]["posX"] == 0) {
            $site["id" . $check["id_sites"]]["posX"] = null;
        }
        if ($site["id" . $check["id_sites"]]["posY"] == 0) {
            $site["id" . $check["id_sites"]]["posY"] = null;
        }
        if ($site["id" . $check["id_sites"]]["posX"] != $check["posX"] || $site["id" . $check["id_sites"]]["posY"] != $check["posY"]) {
            $stmt->bind_param("ddii", $site["id" . $check["id_sites"]]["posX"], $site["id" . $check["id_sites"]]["posY"], $site["id" . $check["id_sites"]]["floor"], $check["id_sites"]);
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
            border-radius: 50%;
        }

        .round .icon {
            width: 2.5vw;
        }

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

        .panel {
            width: 10vw;
            height: 4vw;

            border: 4px solid black;
            border-radius: 1vw;
            background: white;

            position: absolute;
            bottom: 2vh;
            right: -1vw;
            transform: translate(-50%, -50%);
        }

        .arrow-btn {
            width: 3vw;
            height: 3vw;

            border: none;
            background: transparent;

            font-size: 2.5vw;
            cursor: pointer;

            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        #upBtn {
            right: 0.6vw;
        }

        #downBtn {
            left: 0.6vw;
        }

        .display {
            width: 2vw;
            height: 2vw;

            border: 3px solid black;
            background: #f5f5f5;

            font-size: 1.6vw;
            font-weight: bold;
            text-align: center;
            line-height: 2vw;

            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">

</head>

<body>
    <?php
    $floor = 0;
    if (isset($_GET["floor"])) {
        $floor = $_GET["floor"];
    }
    echo "<img id='map' src='./assets/map$floor.jpg' style='width: 80%; height: auto; display: block;' class='map'>";

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
            echo "data-pct-x='" . $site["posX"] . "' data-pct-y='" . $site["posY"] . "' floor='" . $site["floor"] . "'";
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
    <div class="panel">
        <?php
        if ($floor < 4) echo '<button class="arrow-btn" id="upBtn">↑</button>';
        echo '<div class="display" id="floorDisplay">' . $floor . '</div>';
        if ($floor > 0) echo '<button class="arrow-btn" id="downBtn">↓</button>';
        ?></div>
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast
        } from "../formWebScripts/js/formScript.js";
        let get = new URLSearchParams(window.location.search)

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
            //console.log(element)
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
                //console.log(relativeX, relativeY, Number(site.style.top.slice(0, -2)))

                if (relativeX < 0 || relativeX > 100 || relativeY < 0 || relativeY > 100) {
                    relativeX = 0;
                    relativeY = 0;
                }

                let floor = site.getAttribute("floor");
                if (!floor) {
                    floor = get.get("floor")
                    if (!floor) floor = 0
                }
                siteList["id" + site.id] = {
                    posX: relativeX,
                    posY: relativeY,
                    floor: floor
                };
            }
            //console.log(siteList["id1"], siteList["id2"])
            data.append("sitePos", JSON.stringify(siteList))

            let [ok, res] = await SendPOSTDataToServerAsync("./adminmap.php", data)

            if (ok) {
                SendToast("Odpověď serveru", res, "ok");
                window.location.reload()
            } else SendToast("Odpověď serveru", res, "error")

        })


        function repositionPins() {
            //console.log("repos")
            const map = document.getElementById("map");
            const pins = document.getElementsByClassName("site");

            const mapWidth = map.clientWidth;
            const mapHeight = map.clientHeight;
            let offset = 15;
            let gfloor = get.get("floor")
            if (!gfloor) {
                gfloor = 0
            }

            for (let pin of pins) {
                const pctX = pin.getAttribute("data-pct-x");
                const pctY = pin.getAttribute("data-pct-y");
                const floor = pin.getAttribute("floor");

                if (pctX && pctY && pctX != 0 && pctY != 0 && floor) {
                    pin.style.left = ((pctY / 100) * mapWidth) + "px";
                    pin.style.top = ((pctX / 100) * mapHeight) + "px";
                    if (Number(floor) != gfloor) {
                        pin.style.zIndex = -100;
                        continue
                    }
                } else if (pctX == 0 || pctY == 0 || !pctX || !pctY || !floor) {
                    pin.style.left = (0.895 * window.innerWidth) + "px";
                    pin.style.top = offset + "%"
                    offset += 10
                }
            }
        }

        if (document.getElementById("upBtn")) {
            document.getElementById("upBtn").addEventListener("click", () => {
                let floor = get.get("floor")
                if (!floor) floor = 0

                window.location.href = "./adminmap.php?floor=" + (Number(floor) + 1)
            })
        }

        if (document.getElementById("downBtn")) {
            document.getElementById("downBtn").addEventListener("click", () => {
                let floor = get.get("floor")
                if (!floor) floor = 0
                window.location.href = "./adminmap.php?floor=" + (floor - 1)
            })
        }

        repositionPins();
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>
