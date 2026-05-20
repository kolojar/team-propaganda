<?php
session_start();
require "../assets/config.php";
require "./adminFunctions.php";
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json">
    <meta name="form-icons-db" content="../assets/formIcons.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uživatelé</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php setupTitlebarAdmin($conn,"users.php") ?>
    </header>
    <main>
        <h1>Uživatelé</h1>
        <table>
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
                "SELECT id_users, name,surname, email,role,lastLogin FROM users_teamPropaganda",
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
                        <td class='formButtonBoxTable'>
                            <a href='./user.php?user=$id'><button form-icon='!edit' class='purkynkaButton'></button></a>
                            <button form-icon='!delete' class='purkynkaButton btnTableDelete' user='$id'></button>
                        </td>
                        <td>$name $surname</td>
                        <td><a href='mailto:$email'>$email</td>
                        <td>$role</td>
                        <td>$lastLoginFormat</td>
                    </tr>";
            }
            ?>
        </table>
    </main>
    <footer>        
        <div class="formButtonBox">
            <a href="./user.php?newUser=1"><button form-icon="!add" class="purkynkaButton"><span>Vytvořit nového uživatele</span></button></a>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type='module' src='../assets/sharedScripts.js'></script>
<script type="module" src="./users.js"></script>
</html>