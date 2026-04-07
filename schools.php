<?php
session_start();
require "./assets/config.php";
?>
<h1>Školy</h1>
<table class="styledTable">
    <tr>
        <th>Akce</th>
        <th>Název</th>
        <th>Adresa</th>
        <th>Zákonný zástupce</th>
        <th>Zaplaceno</th>
        <th>Učebna</th>
    </tr>
    <?php
    //Request users
    $stmt = $conn->prepare("SELECT name, address  password FROM schools_temaPropaganda");
    $stmt->execute();
    $stmt->store_result();

    //List all users in table
    for ($i = 0; $i < $stmt->num_rows; $i++) {
        $stmt->bind_result($name, $address);
        $stmt->fetch();
    ?>
        <tr>
            <td>
                <button class="formButton formWarnColor">Upravit</button>
                <button class="formButton formErrorColor">Odstranit</button>
            </td>
            <td><?php echo $name ?></td>
            <td><?php echo $address ?></td>
            <td>?</td>
            <td>NE</td>
            <td>?</td>
        </tr>
    <?php
    }
    ?>
</table>
