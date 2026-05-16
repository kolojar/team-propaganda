<?php
function setupTitlebarUser(mysqli $conn) {
    //Get name of current user
    $stmt = $conn->prepare("SELECT name, surname FROM users_teamPropaganda WHERE id_users=?");
    $stmt->bind_param("i",$_SESSION["userId"]);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name, $surname);
    $stmt->fetch();

    //Make HTML
    echo "<div class='formButtonBoxHolder' style='margin-top: 0px;'>
        <div class='formButtonBox formJustifyLeft'>
            <h1 class='headerName' onclick='window.location.href = \"./index.php\"'>$name $surname</h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href='../logoff.php'><button class='formButton purkynkaButton'>Odhlásit se</button></a>
        </div>
    </div>";
}
?>