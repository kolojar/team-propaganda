<?php
require "../assets/config.php";
session_start();
if (!isset($_SESSION["userId"])) {
    if (isset($_SESSION["login"])) {
        echo "login";
        $_SESSION["userId"] = $conn->query("SELECT id_users FROM users_teamPropaganda WHERE `email` = '" . $_SESSION["login"] . "'");
        $_SESSION["login"] = null;
    } else if (isset($_SESSION["signup"])) {
        echo "signup";
        $stmt = $conn->prepare("INSERT INTO users_teamPropaganda (name, surname, email, id_schools) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_SESSION["name"], $_SESSION["surname"], $_SESSION["signup"], $_SESSION["id_schools"]);
        $stmt->execute();
        $_SESSION["userId"] = $stmt->insert_id;
        $_SESSION["name"] = null;
        $_SESSION["surname"] = null;
        $_SESSION["signup"] = null;
        $_SESSION["id_schools"] = null;
    } else {
        echo "none";
        header("Location: ./loginForm.html");
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
</head>

<body>
    <button id="logoff">Odhlásit</button>
    <script type="module">
        import {
            SendPOSTDataToServerAsync
        } from "../formWebScripts/js/serverComunication.js";
        import {
            SendToast
        } from "../formWebScripts/js/formScript.js";

        document.getElementById("logoff").addEventListener("click", async (e) => {
            let data = new FormData();
            data.append("none", null);
            let [ok, res] = await SendPOSTDataToServerAsync("../logoff.php", null)
            if (ok) {
                SendToast("Odpověď serveru", res, "ok");
                window.location = "./loginForm.html"
            } else SendToast("Odpověď serveru", res, "error");
        })
    </script>
</body>

</html>
