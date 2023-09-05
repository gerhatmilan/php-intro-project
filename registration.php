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

    // email
    if (!isset($post['email'])) {
        $errors["email"] = "Email cím megadása kötelező!";
    }
    else if (trim($post["email"]) === "") {
        $errors["email"] = "Email cím megadása kötelező!";
    }
    else if (!filter_var($post["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Hibás email cím formátum!";
    }

    // password
    if (!isset($post['password'])) {
        $errors["password"] = "Jelszó megadása kötelező!";
    }
    else if (trim($post["password"]) === "") {
        $errors["password"] = "Jelszó megadása kötelező!";
    }

    // password confirmation
    if (!isset($post['passwordConfirmation'])) {
        $errors["passwordConfirmation"] = "Jelszó megerősítése szükséges!";
    }
    else if (trim($post["password"]) === "") {
        $errors["passwordConfirmation"] = "Jelszó megerősítése szükséges!";
    }
    else if ($post["password"] !== $post["passwordConfirmation"]) {
        $errors["passwordConfirmation"] = "A megadott jelszavak nem egyeznek!";
    }

  $data = $post;
  return count($errors) === 0;
}

// main
$user_storage = new UserStorage();
$auth = new Auth($user_storage);
$errors = [];
$data = [];
if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {
    if ($auth->user_exists($data['username'])) {
        $errors['global'] = "A felhasználó már létezik!";
    } else {
        $auth->register($data);
        redirect('login.php');
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
    <title>Regisztráció</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Regisztráció</h1>
    <div class="formSection">
        <form action="" method="post" novalidate>
            <label for="username" class="form-check-label">Felhasználónév:</label>
            <input type="text" name="username" id="username" class="form-control" value="<?= $_POST['username'] ?? "" ?>">
            <?php if (isset($errors['username'])) : ?>
                <span class="error"><?= $errors['username'] ?></span><br>
            <?php endif; ?>

            <label for="email" class="form-check-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= $_POST['email'] ?? "" ?>">
            <?php if (isset($errors['email'])) : ?>
                <span class="error"><?= $errors['email'] ?></span><br>
            <?php endif; ?>

            <label for="password" class="form-check-label">Jelszó:</label>
            <input type="password" name="password" id="password" class="form-control" value="<?= $_POST['password'] ?? "" ?>">
            <?php if (isset($errors['password'])) : ?>
                <span class="error"><?= $errors['password'] ?></span><br>
            <?php endif; ?>


            <label for="passwordConfirmation" class="form-check-label">Jelszó megerősítése:</label>
            <input type="password" name="passwordConfirmation" id="passwordConfirmation" class="form-control" value="<?= $_POST['passwordConfirmation'] ?? "" ?>">
            <?php if (isset($errors['passwordConfirmation'])) : ?>
                <span class="error"><?= $errors['passwordConfirmation'] ?></span><br>
            <?php endif; ?>

            <?php if (isset($errors['global'])) : ?>
                <span class="error"><?= $errors['global'] ?></span><br>
            <?php endif; ?>
            
            <div class="flex-parent jc-center">
                <a href="login.php" class="btn btn-primary">Bejelentkezés</a>
                <button type="submit" class="btn btn-primary">Regisztráció</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>