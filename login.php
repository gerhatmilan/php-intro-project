<?php
include_once('storages/user_storage.php');
include_once('storages/auth.php');

// functions
function redirect($page) {
    header("Location: ${page}");
    exit();
}

function validate($post, &$data, &$errors) {
    // username
    if (!isset($post["username"])) {
        $errors["username"] = "Felhasználónév megadása kötelező!";
    }
    else if (trim($post["username"]) === "") {
        $errors["username"] = "Felhasználónév megadása kötelező!";
    }

    // password
    if (!isset($post['password'])) {
        $errors["password"] = "Jelszó megadása kötelező!";
    }
    else if (trim($post["password"]) === "") {
        $errors["password"] = "Jelszó megadása kötelező!";
    }

    $data = $post;
    return count($errors) === 0;
}

function login($user) {
    $_SESSION["user"] = $user;
}
  
// main
session_start();
$user_storage = new UserStorage();
$auth = new Auth($user_storage);
$data = [];
$errors = [];

if ($_POST) {
  if (validate($_POST, $data, $errors)) {
    if (!$auth->user_exists($data['username'])) {
        $errors['global'] = "A felhasználó nem létezik!";
    }
    else {
        $auth_user = $auth->authenticate($data['username'], $data['password']);
        if (!$auth_user) {
          $errors['global'] = "A megadott jelszó hibás!";
        } else {
          $auth->login($auth_user);
          redirect('index.php');
        }
    }
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
    <title>Bejelentkezés</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Bejelentkezés</h1>
    <div class="formSection">
        <form action="" method="post" novalidate>
            <label for="username" class="form-check-label">Felhasználónév:</label>
            <input type="text" name="username" id="username" class="form-control" value="<?= $_POST['username'] ?? "" ?>">
            <?php if (isset($errors['username'])) : ?>
                <span class="error"><?= $errors['username'] ?></span><br>
            <?php endif; ?>

            <label for="password" class="form-check-label">Jelszó:</label>
            <input type="password" name="password" id="password" class="form-control" value="<?= $_POST['password'] ?? "" ?>">
            <?php if (isset($errors['password'])) : ?>
                <span class="error"><?= $errors['password'] ?></span><br>
            <?php endif; ?>

            <?php if (isset($errors['global'])) : ?>
                <span class="error"><?= $errors['global'] ?></span><br>
            <?php endif; ?>

            <div class="flex-parent jc-center">
                <button type="submit" class="btn btn-primary">Bejelentkezés</button>
                <a href="registration.php" class="btn btn-primary">Regisztráció</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>