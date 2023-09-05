<?php
include_once('storages/user_storage.php');
include_once('storages/poll_storage.php');
include_once('storages/vote_storage.php');
include_once('storages/auth.php');

function redirect($page) {
    header("Location: ${page}");
    exit();
}

function validate($post, &$data, &$errors) {
    // not an admin user (just in case)
    if (!$GLOBALS["auth"]->authorize()) {
        $errors["global"] = "Ez a funkció az ön számára nem elérhető!";
    }

    $data = $post;
    return count($errors) === 0;
}

session_start();
$user_storage = new UserStorage();
$poll_storage = new PollStorage();
$vote_storage = new VoteStorage();
$auth = new Auth($user_storage);

$data = [];
$errors = [];

if (!$auth->authorize()) {
    redirect("index.php");
}

if ($_POST) {
    if (validate($_POST, $data, $errors)) {
        $vote_storage->delete($_GET["id"]);
        $poll_storage->delete($_GET["id"]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/index.css">
    <title>Szavazás törlése</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Szavazás törlése</h1>
    <div id="deletePanel">
        <div class="formSection">
            <p>Biztosan törölni akarod?</p>
            <div class="flex-parent jc-center">
                <form action="" method="post" novalidate>
                    <div class="flex-parent jc-center">
                        <button type="submit" class="btn btn-primary" name="submit">Igen</button>                     
                        <a href="index.php" class="btn btn-primary">Mégse</a><br>
                    </div>
                    <?php if (count($errors) === 0 && count($data) > 0) : ?>
                        <span class="success">Szavazás törlése sikeres!</span><br>
                    <?php endif; ?>
                    <?php if (isset($errors["global"])) : ?>
                    <span class="error"><?= $errors["global"] ?></span><br>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>