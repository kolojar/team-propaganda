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
            background-color: #B000B0;
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
            echo '<img class="icon" src="data:image/jpeg;base64,' . base64_encode($site["icon"]) . '" >';
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

        repositionPins()
        window.addEventListener('resize', repositionPins);
    </script>
</body>

</html>
