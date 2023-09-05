<?php

include_once('storages/poll_storage.php');
include_once('storages/user_storage.php');
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

    // question
    if (!isset($post["question"])) {
        $errors["question"] = "Kérdés megadása kötelező!";
    }
    else if (trim($post["question"]) === "") {
        $errors["question"] = "Kérdés megadása kötelező!";
    }

    // options
    if (!isset($post["options"])) {
        $errors["options"] = "Legalább két választási lehetőség megadása kötelező!";
    }
    else if (trim($post["options"]) === "") {
        $errors["options"] = "Legalább két választási lehetőség megadása kötelező!";
    }
    else if (count(explode("\n", $post['options'])) < 2) {
        $errors["options"] = "Legalább két választási lehetőség megadása kötelező!";
    }
    else {
        if (array_filter(explode("\n", $post['options']), function ($item) {
            return trim($item) === "";
        })) {
            $errors["options"] = "Hibás választási lehetőség!";
        }
    }

    // multiple options
    if (!isset($post["isMultiple"])) {
        $errors["isMultiple"] = "Opció kiválasztása kötelező!";
    }

    // deadline
    if (!isset($post["deadline"])) {
        $errors["deadline"] = "Leadási határidő megadása kötelező!";
    }
    else if (trim($post["deadline"]) === "") {
        $errors["deadline"] = "Leadási határidő megadása kötelező!";
    }
    else if ($post['deadline'] < $post["createdAt"]) {
        $errors["deadline"] = "Helytelen dátum!";
    }

    $data = $post;
    return count($errors) === 0;
}

session_start();
$user_storage = new UserStorage();
$poll_storage = new PollStorage();
$auth = new Auth($user_storage);
$data = [];
$errors = [];
date_default_timezone_set('Europe/Budapest');

if (!$auth->authorize()) {
    redirect("index.php");
}

if ($_POST) {
  if (validate($_POST, $data, $errors)) {
    $data["options"] = explode("\n", $data['options']);
    $answers_array = [];

    foreach ($data["options"] as $k => $v) {
        $data["options"][$k] = trim($v, "\r");
        $answers_array[$data["options"][$k]] = 0;
    }

    $poll_storage->add([
        "question" => $data["question"],
        "options" => $data["options"],
        "isMultiple" => $data["isMultiple"],
        "createdAt" => $data["createdAt"],
        "deadline" => $data["deadline"],
        "answers" => $answers_array,
        "voted" => []
    ]);
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
    <title>Szavazás létrehozása</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Szavazás létrehozása</h1>
    <div class="formSection">
        <form action="" method="post" novalidate>
            <label for="question" class="form-check-label">Kérdés:</label>
            <input type="text" name="question" id="question" class="form-control" value="<?= $_POST["question"] ?? "" ?>">
            <?php if (isset($errors['question'])) : ?>
                <span class="error"><?= $errors['question'] ?></span><br>
            <?php endif; ?>

            <label for="options" class="form-check-label">Választási lehetőségek:</label>
            <textarea class="form-control" name="options" id="options" cols="" rows="5"><?= $_POST["options"] ?? "" ?></textarea>
            <?php if (isset($errors['options'])) : ?>
                <span class="error"><?= $errors['options'] ?></span><br>
            <?php endif; ?>
            
            <div>
                <label class="form-check-label">Lehetséges-e több opció megjelölése?</label><br>
                <input class="form-check-input" type="radio" name="isMultiple" id="isMultiple1" value="true" 
                <?php if (isset($_POST['isMultiple']) && $_POST['isMultiple']  === "true") : ?>
                    checked
                <?php endif ?>>
                <label class="form-check-label" for="isMultiple1" class="form-check-label">Igen</label>
                <input class="form-check-input" type="radio" name="isMultiple" id="isMultiple2" value="false" 
                <?php if (isset($_POST['isMultiple']) && $_POST['isMultiple']  === "false") : ?>
                    checked
                <?php endif ?>>
                <label class="form-check-label" for="isMultiple2" class="form-check-label">Nem</label>
            </div>
            <?php if (isset($errors['isMultiple'])) : ?>
                <span class="error"><?= $errors['isMultiple'] ?></span><br>
            <?php endif; ?>

            <label for="createdAt" class="form-check-label">Létrehozás ideje:</label>
            <input readonly type="date" class="form-control" id="createdAt" name="createdAt" value="<?= date('Y-m-d'); ?>">

            <label for="deadline" class="form-check-label">Leadás határideje:</label>
            <input type="date" class="form-control" id="deadline" name="deadline" value="<?= $_POST["deadline"] ?? date('Y-m-d') ?>">
            <?php if (isset($errors['deadline'])) : ?>
                <span class="error"><?= $errors['deadline'] ?></span><br>
            <?php endif; ?>

            <?php if (isset($errors["global"])) : ?>
                <span class="error"><?= $errors["global"] ?></span><br>
            <?php endif; ?>
            <?php if (count($errors) === 0 && count($data) > 0) : ?>
                <span class="success">Szavazás létrehozása sikeres!</span><br>
            <?php endif; ?>
            <div class="flex-parent jc-center">
                <button type="submit" class="btn btn-primary">Létrehozás</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>