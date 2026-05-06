<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uživatelé</title>
    <link rel="stylesheet" href="../formWebScripts/css/sharedStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../formWebScripts/css/tableStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <h1 style="color: white">Akce: <?php echo setupTitlebarAction($conn,false,true); ?></h1>
        <div class="formButtonBoxHolder">
            <div class="formButtonBox formJustifyLeft">
                <a href="./admin.php"><button class="formButton formOkColor">Hlavní menu</button></a>
                <a href="./attendants.php"><button class="formButton formOkColor">Zájemci</button></a>
                <a href="./classrooms.php"><button class="formButton formOkColor">Učebny</button></a>
                <a href="./schools.php"><button class="formButton formOkColor">Školy</button></a>
                <a href="./messages.php"><button class="formButton formOkColor">Zprávy</button></a>
                <a href="./payments.php"><button class="formButton formOkColor">Platby</button></a>
            </div>
            <div class="formButtonBox formJustifyRight">
                <a href="./users.php"><button class="formButton formInfoColor">Správa uživatelů</button></a>
                <a href="./events.php"><button class="formButton formWarnColor">Změnit událost</button></a>
                <a href="./logout.php"><button class="formButton formErrorColor">Odhlásit se</button></a>
            </div>
        </div>
    </header>
    <main>
        <h1>Uživatelé</h1>
        <table class='styledTable styledTableAuto'>
            <tr>
                <th>Akce</th>
                <th>Jméno a přijmení</th>
                <th>Email</th>
                <th>Role</th>
                <th>Naposledy přihlášen</th>
            </tr>
            <?php
            ////Get highlighted schools
            //$highlightSchools = [];
            //if(isset($_GET['schools'])) {
            //    $highlightSchools = explode(',',$_GET["schools"]);
            //}
            
            //Request users
            $stmt = $conn->prepare(
                "SELECT id_users, name,surname, email,role,lastLogin FROM users",
            );
            $stmt->execute();
            $stmt->store_result();

            //List all users in table
            for ($i = 0; $i < $stmt->num_rows; $i++) {
                $stmt->bind_result($id, $name, $surname, $email, $role, $lastLogin);
                $stmt->fetch();
                $lastLoginFormat =  DateTime::createFromFormat('Y-m-d H:i:s', $lastLogin)->format("d. m. Y H:i:s");

                //Put in table
                echo "<tr class='clickHighlightRow'>
                        <td>
                            <a href='./user.php?user=$id'><button class='formButton formWarnColor'>Upravit</button></a>
                            <button class='formButton formErrorColor deleteUserButton' userId=$id>Odstranit</button>
                        </td>
                        <td>$name $surname</td>
                        <td><a href='mailto:$email'>$email</td>
                        <td>$role</td>
                        <td>$lastLoginFormat</td>
                    </tr>";
            }
            ?>
            <h1></h1>
        </table>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='./sharedScripts.js'></script>

</html>