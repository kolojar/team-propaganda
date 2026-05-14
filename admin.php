<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <link rel="stylesheet" href="./formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="./formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="./formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>
    <header style="padding-left: 4px; padding-right: 4px; margin-top: 0px; padding-top: 1px; padding-bottom: 0px;" class="formInfoColor">
        <h1>test</h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="?"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="?view=attendants"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="?view=classrooms"><button class="formButton formOkColor">Učebny</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <?php
        session_start();
        require "./assets/config.php";
        if ($_GET["view"] == "attendants") {
        ?>
            <h1>Zájemci</h1>
            <table class="styledTable">
                <tr>
                    <th>Akce</th>
                    <th>Jméno a přijmení</th>
                    <th>Email</th>
                    <th>Zákonný zástupce</th>
                    <th>Zaplaceno</th>
                    <th>Učebna</th>
                </tr>
                <?php
                //Request users
                $stmt = $conn->prepare("SELECT id_users, name,surname, email  password FROM users_teamPropaganda");
                $stmt->execute();
                $stmt->store_result();

                //List all users in table
                for ($i = 0; $i < $stmt->num_rows; $i++) {
                    $stmt->bind_result($id, $name, $surname, $email);
                    $stmt->fetch();
                ?>
                    <tr>
                        <td>
                            <button class="formButton formOkColor">Poslat zprávu</button>
                            <button class="formButton formWarnColor">Upravit</button>
                            <button class="formButton formErrorColor deleteUserButton" userId=<?php echo ($id) ?> userName="<?php echo ($name . " " . $surname) ?>">Odstranit</button>
                        </td>
                        <td><?php echo ($name . " " . $surname) ?></td>
                        <td><?php echo $email ?></td>
                        <td class="parentOfUserCell">?</td>
                        <td>NE</td>
                        <td>?</td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        } else if ($_GET["view"] == "classrooms") {
        ?>
            <h1>Učebny</h1>
        <?php
        } else {
        ?>
            <h1>Hlavní menu</h1>
        <?php
        }
        ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="./formWebScripts/js/formScript.js"></script>
<script type="module" src="./admin.js"></script>

</html>
