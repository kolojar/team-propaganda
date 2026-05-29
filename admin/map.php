<?php
require "../assets/config.php";
require "./adminFunctions.php";
session_start();
if (!isset($_SESSION["adminId"])) {
    //header("location: ../adminLogin.php")
}

if (isset($_POST["sitePos"])) {
    $checks = $conn->prepare("SELECT id_sites, posX, posY, floor FROM sites_teamPropaganda WHERE id_company_days = ?");
    if (!$checks->bind_param("i", $_POST["companyDayId"]) || !$checks->execute()) {
        echo "Nelze získat informace o firmách.";
        die();
    }
    $checksResult = $checks->get_result();
    $site = json_decode($_POST["sitePos"], true);
    $stmt = $conn->prepare("UPDATE `sites_teamPropaganda` SET `posX` = ?, `posY` = ?, `floor` = ? WHERE `sites_teamPropaganda`.`id_sites` = ?");
    while ($check = $checksResult->fetch_assoc()) {
        if ($site["id" . $check["id_sites"]]["posX"] == 0) {
            $site["id" . $check["id_sites"]]["posX"] = null;
        }
        if ($site["id" . $check["id_sites"]]["posY"] == 0) {
            $site["id" . $check["id_sites"]]["posY"] = null;
        }
        //logToConsole($site["id" . $check["id_sites"]]);
        //logToConsole($site["id" . $check["id_sites"]]["floor"]);
        if ($site["id" . $check["id_sites"]]["posX"] != $check["posX"] || $site["id" . $check["id_sites"]]["posY"] != $check["posY"]) {
            if (!$stmt->bind_param("ddii", $site["id" . $check["id_sites"]]["posX"], $site["id" . $check["id_sites"]]["posY"], $site["id" . $check["id_sites"]]["floor"], $check["id_sites"]) || !$stmt->execute()) {
                http_response_code(400);
                echo "Chyba při aktualizaci pozice na mapě.";
                $checksResult->close();
                $checks->close();
                $stmt->close();
                exit;
            }
        }
    }
    $checksResult->close();
    $checks->close();
    $stmt->close();
    http_response_code(200);
    echo "Místa na mapě uloženy.";
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
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Správa mapy dne firem</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">

</head>
<header>
    <?php
    $result = setupTitlebarAdmin($conn, "map.php");
    $companyDaysId = $result->companyDayId;
    ?>
</header>

<body>
    <main style="position: relative;">
        <?php
        $floor = 0;
        if (isset($_GET["floor"])) {
            $floor = $_GET["floor"];
        }
        echo "<img id='map' src='../assets/maps/map$floor.jpg' style='width: 80%; height: auto; display: block;' class='map'>";

        $sites = $conn->query("SELECT * FROM sites_teamPropaganda NATURAL RIGHT JOIN companies_teamPropaganda WHERE id_company_days=" . $companyDaysId);
        $offset = 15;
        while ($site = $sites->fetch_assoc()) {
            echo "<div class='";
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
            echo "</div>";
            $offset += 10;
        }
        ?>


        <div class="formCenter" style="width:20%; height: 10%; float: right; margin-top: 2%;">

        </div>
    </main>
    <footer>
        <div class="formButtonBoxHolder">
            <?php echo "<h1 style='display: inline'>Patro: $floor</h1>"; ?>
            <div class="formButtonBox">
                <?php
                $disabled = $floor > 0 ? "" : "disabled";
                echo '<button class="purkynkaButton" id="downBtn" ' . $disabled . '>↓</button>';
                $disabled = $floor < 4 ? "" : "disabled";
                echo '<button class="purkynkaButton" id="upBtn" ' . $disabled . '>↑</button>';
                ?>
            </div>
            <div class="formButtonBox formJustifyRight">
                <button id="save" class="purkynkaButton">Uložit plánek</button>
            </div>
        </div>
    </footer>
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast, MakeElementDraggable, DraggableElement
        } from "../formWebScripts/js/formScript.js";
        import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
        const dialogManager = new FormDialogManager()
        let get = new URLSearchParams(window.location.search)

        for (let site of document.getElementsByClassName("site")) {
            MakeElementDraggable(site, null, true)
        }
        //let elementToMove = null;
        //
        //function dragStart(element) {
        //    //console.log(element)
        //    elementToMove = element
        //    elementToMove.style.zIndex = "10"
        //}
        //
        //function dragEnd() {
        //    if (elementToMove.style.left > window.innerWidth * 0.8) {
        //
        //    }
        //    elementToMove.style.zIndex = "0"
        //    elementToMove = null
        //}
        //
        //let lastMousePosition = {
        //    x: 0,
        //    y: 0
        //}
        //document.getElementsByTagName("html").item(0).addEventListener("mousemove", (e) => {
        //    dragMove(e)
        //})
        //document.getElementsByTagName("html").item(0).addEventListener("touchmove", (e) => {
        //    dragMove(e)
        //})
        //
        //function dragMove(event) {
        //    lastMousePosition.x = event.clientX + scrollX
        //    lastMousePosition.y = event.clientY + scrollY
        //    if (elementToMove != null) {
        //        elementToMove.style.top = lastMousePosition.y + "px"
        //        elementToMove.style.left = lastMousePosition.x + "px"
        //    }
        //}

        document.getElementById("save").addEventListener("click", async (e) => {
            if (! await dialogManager.OpenConfirm("Uložit plánek", "Opravdu chcete uložit pozice na mapě?")) {
                SendToast("Uložit plánek", "Uložení plánku zrušeno.", "info")
                return;
            }
            const progress = dialogManager.ShowProgress("Uložit změny", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
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
            console.log(siteList["id1"], siteList["id2"])
            const json = JSON.stringify(siteList)
            console.log(json)
            data.append("sitePos", json)
            data.append("companyDayId", <?php echo $result->companyDayId ?>)

            let [ok, res] = await SendPOSTDataToServerAsync("./map.php", data)

            if (ok) {
                SendToast("Plánek uložen uspěšně", "Data uložena.", "ok");
                setTimeout(() => {
                    window.location.reload()
                }, 1000);
            } else {
                SendToast("Nastala chyba při ukládání dat", "Nelze uložit pozice na mapě.", "error")
                progress.CloseDialog()
                await dialogManager.OpenAlert("Uložit změny", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + reason, true, true)
            }

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

                window.location.href = "?floor=" + (Number(floor) + 1)
            })
        }

        if (document.getElementById("downBtn")) {
            document.getElementById("downBtn").addEventListener("click", () => {
                let floor = get.get("floor")
                if (!floor) floor = 0
                window.location.href = "?floor=" + (floor - 1)
            })
        }

        repositionPins();
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>