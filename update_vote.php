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
    // options
    if (!isset($post["answers"])) {
        $errors["global"] = "Opció kiválasztása kötelező!";
    }

    // deadline over (just in case)
    if ($GLOBALS["polls"][$_GET["id"]]["deadline"] < date("Y-m-d")) {
        $errors["global"] = "A szavazás határideje lejárt!";
    }
    

    $data = $post;
    return count($errors) === 0;
}

session_start();
$user_storage = new UserStorage();
$poll_storage = new PollStorage();
$vote_storage = new VoteStorage();
$auth = new Auth($user_storage);
date_default_timezone_set('Europe/Budapest');

$data = [];
$errors = [];
$polls = $poll_storage->findAll();
$votes = $vote_storage->findAll();

if (!$auth->is_authenticated()) {
    redirect('login.php');
}
if ($polls[$_GET["id"]]["deadline"] < date("Y-m-d")) {
    redirect('index.php');
}

if ($_POST) {
    if (validate($_POST, $data, $errors)) {
        $update_poll_record = $polls[$_GET["id"]]; // poll_storage update record
        $update_vote_record = $votes[$_GET["id"]]; // vote storage update record
        $update_array = $update_vote_record[$auth->authenticated_user()["id"]];
        
        foreach($update_poll_record["answers"] as $k => $v) {
            if (in_array($k, $update_array) && !in_array($k, $data["answers"])) {
                // if the option was checked by the user previously, but they unchecked it when updating their vote 

                $update_poll_record["answers"][$k]--; // decrease total vote count 
                if (($key = array_search($k, $update_array)) !== false) {
                    unset($update_array[$key]); 
                } // delete the option in the array that holds the voted options by the user
            }
            else if (!in_array($k, $update_array) && in_array($k, $data["answers"])) {
                // if the option was not checked by the user previously, but they checked it when updating their vote 

                $update_poll_record["answers"][$k]++; // increase total vote count
                $update_array[] = $k; // add the option to the array that holds the voted options by the user
            }
        }

        $poll_storage->update($_GET["id"], $update_poll_record);

        $update_vote_record[$auth->authenticated_user()["id"]] = $update_array;
        $vote_storage->update($_GET["id"], $update_vote_record);
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
    <title>Szavazás</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Szavazás</h1>
    <div class="formSection">
        <form action="" method="post" novalidate>
            <p class="primaryColor"><?= $polls[$_GET["id"]]["question"] ?></p>
            <input type="text" name="hidden" hidden>
            <div>
                <?php if ($polls[$_GET["id"]]["isMultiple"] === "true") : ?>
                    <?php foreach($polls[$_GET["id"]]["options"] as $k => $v) : ?>
                        <div class="form-check">
                            <input id="<?= $k ?>" class="form-check-input" type="checkbox" name="answers[]" value="<?= $v ?>"
                            <?php if ($vote_storage->option_voted_by_user($polls[$_GET["id"]], $v, $auth->authenticated_user()) || isset($_POST['answers']) && in_array($v, $_POST['answers'])) : ?>
                                checked
                            <?php endif ?>>
                            <label for="<?= $k ?>" class="form-check-label"><?= $v ?></label>
                        </div>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php foreach($polls[$_GET["id"]]["options"] as $k => $v) : ?>
                        <div class="form-check">
                            <input id="<?= $k ?>" class="form-check-input" type="radio" name="answers[]" value="<?= $v ?>"
                            <?php if ($vote_storage->option_voted_by_user($polls[$_GET["id"]], $v, $auth->authenticated_user()) || isset($_POST['answers']) && in_array($v, $_POST['answers'])) : ?>
                                checked
                            <?php endif ?>>
                            <label for="<?= $k ?>" class="form-check-label"><?= $v ?></label>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
            <div>Létrehozás ideje: <span class="primaryColor"><?= $polls[$_GET["id"]]["createdAt"] ?></div>
            <div>Leadási határidő: <span class="primaryColor"><?= $polls[$_GET["id"]]["deadline"] ?></div>
           
            <?php if (isset($errors['global'])) : ?>
                <div class="error"><?= $errors['global'] ?></div>
            <?php endif; ?>
            <?php if (count($errors) === 0 && isset($data['answers'])) : ?>
                <div class="success">Szavazata sikeresen módosult!</div>
            <?php endif; ?>

            <div class="flex-parent jc-center">
                <button class="btn btn-primary" type="submit">Szavazat frissítése</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>