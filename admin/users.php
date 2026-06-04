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
    <meta name="form-locales-main" content="../formWebScripts/locales/">
    <title>Uživatelé</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="pageHolder">
    <header>
        <?php
        $result = setupTitlebarAdmin($conn, "users.php");
        $resultUserType = $result->getUserType(false);
        ?>
    </header>
    <main>
        <h1>Seznam uživatelů systému</h1>
        <?php
        function role($result, $setup) {
            return userRole::from($result["role"])->value;
        }

        setupFilteredTable(
            $conn,
            $result,
            "purkynkaTableStripped purkynkaTableFullLines",
            "id_users, name,surname, email,role,type,last_login",
            "users_teamPropaganda",
            "",
            "",
            "",
            "",
            "",
            [],
            [
                new filterSelector("name", "Jméno", "name", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("surname", "Příjmení", "surname", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("email", "Email", "email", filterSelectorType::TEXT, filterCompareOperator::LIKE),
                new filterSelector("!role", "Role", "role", filterSelectorType::SELECT, filterCompareOperator::EQUALSNULLABLE, false, ["listId" => "roles"]),
                new filterSelector("!type", "Typ", "type", filterSelectorType::SELECT, filterCompareOperator::EQUALSNULLABLE, false, ["listId" => "types"]),
                new filterSelector("last_login", "Minimální datum posledního přihlášení", "lastLoginMin", filterSelectorType::DATETIME, filterCompareOperator::MOREEQUALS),
                new filterSelector("last_login", "Maximální datum posledního přihlášení", "lastLoginMax", filterSelectorType::DATETIME, filterCompareOperator::LESSEQUALS),
            ],
            [
                new filterDisplayer("name", "Jméno", true),
                new filterDisplayer("surname", "Přijmení", true),
                new filterDisplayer("email", "Email", true),
                new filterDisplayer("role", "Role", true),
                new filterDisplayer("type", "Typ", true),
                new filterDisplayer("last_login", "Datum posledního přihlášení", false),
            ]
        );
        ////Get highlighted schools
        //$highlightSchools = [];
        //if(isset($_GET['schools'])) {
        //    $highlightSchools = explode(',',$_GET["schools"]);
        //}
        
        ?>
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