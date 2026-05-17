<?php

if (isset($_POST["pas"])) {
    echo hash("sha256", $_POST['pas']);
}
?>
<form action="./genpass.php" method="post">
    <input name="pas"><input type="submit">
</form>
