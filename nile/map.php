<?php
require "../assets/config.php";

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

            .icon {
                width: 2.5vw;
            }
        }

        .site {
            position: absolute;
            border: none;
            cursor: pointer;
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            background-color: #B000B0;
            width: 4vw;
        }

        .map {
            position: absolute;
            z-index: -1;
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
    echo "<img id='map' src='./assets/map$floor.jpg' style='width: 100%; height: auto; display: block;' class='map'>";


    $sites = $conn->query("SELECT * FROM sites_teamPropaganda NATURAL RIGHT JOIN companies_teamPropaganda");
    while ($site = $sites->fetch_assoc()) {
        if ($site["posX"] == null || $site["posY"] == null) {
            continue;
        }
        echo "<button class='";
        if ($site["isClass"] == null) {
            echo "site round";
        } else {
            echo "site square";
        }
        echo "' id='" . $site["id_sites"] . "' style='transform: translate(-50%, -50%);'";
        if ($site["posX"] != 0 && $site["posY"] != 0) {
            echo "data-pct-x='" . $site["posX"] . "' data-pct-y='" . $site["posY"] . "' floor='" . $site["floor"] . "'";
        }
        echo ">";
        if ($site["icon"] != null) {
            echo '<img class="icon" id="s' . $site["id_sites"] . '" src="data:image/jpeg;base64,' . base64_encode($site["icon"]) . '" >';
        }
        echo "</button>";
    }


    ?>
    <div class="panel">
        <?php
        if ($floor < 4) echo '<button class="arrow-btn" id="upBtn">↑</button>';
        echo '<div class="display" id="floorDisplay">' . $floor . '</div>';
        if ($floor > 0) echo '<button class="arrow-btn" id="downBtn">↓</button>';
        ?>
    </div>

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
        let dm = new FormDialogManager()
        let get = new URLSearchParams(window.location.search)

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
            let toremove = []
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
                } else toremove.push(pin)
            }

            toremove.forEach((element) => console.log(element));
        }

        let sites = document.getElementsByClassName("site")
        for (let site of sites) {
            site.addEventListener("click", async (e) => {
                let data = new FormData()
                console.log(site.id)
                data.append("id_sites", site.id)
                let [ok, res] = await SendPOSTDataToServerAsync("./siteData.php", data);
                if (!ok) SendToast("Odpověď serveru.", res, "error")
                let result = JSON.parse(res)
                let fields = result["fields"].join(", ")
                let html = (result.presname) ? `<h2>${result.presname}</h2><br><h3>Info:</h3><br>${result.description}<br>` : "";
                html += `<h2>` + ((document.getElementById("s" + site.id)) ? `<img class="icon" src="${document.getElementById("s"+site.id).src}"> ` : ``) + `${result.compname}</h2><br>${fields}<br><h3>Info:</h3><br>${result.short_info}<br><br><h3>Popis</h3><br>${result.long_info}<br>`
                await dm.OpenAlert(result.compname, html)
            })
        }
        if (document.getElementById("upBtn")) {
            document.getElementById("upBtn").addEventListener("click", () => {
                let floor = get.get("floor")
                if (!floor) floor = 0

                window.location.href = "./map.php?floor=" + (Number(floor) + 1)
            })
        }

        if (document.getElementById("downBtn")) {
            document.getElementById("downBtn").addEventListener("click", () => {
                let floor = get.get("floor")
                if (!floor) floor = 0
                window.location.href = "./map.php?floor=" + (floor - 1)
            })
        }


        repositionPins()
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>
