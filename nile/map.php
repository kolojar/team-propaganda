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
    </style>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
</head>

<body>
    <img id="map" src="assets/img.png" width="100%" class="map">
    <?php
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
            echo "data-pct-x='" . $site["posX"] . "' data-pct-y='" . $site["posY"] . "'";
        }
        echo ">";
        if ($site["icon"] != null) {
            echo '<img class="icon" id="s' . $site["id_sites"] . '" src="data:image/jpeg;base64,' . base64_encode($site["icon"]) . '" >';
        }
        echo "</button>";
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
        let dm = new FormDialogManager()

        function repositionPins() {
            console.log("repos")
            const map = document.getElementById("map");
            const pins = document.getElementsByClassName("site");

            const mapWidth = map.clientWidth;
            const mapHeight = map.clientHeight;

            for (let pin of pins) {
                const pctX = pin.getAttribute("data-pct-x");
                const pctY = pin.getAttribute("data-pct-y");

                if (pctX && pctY && pctX != 0 && pctY != 0) {
                    pin.style.left = ((pctY / 100) * mapWidth) + "px";
                    pin.style.top = ((pctX / 100) * mapHeight) + "px";
                }
            }
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

        repositionPins()
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>
