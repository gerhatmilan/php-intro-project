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

    // already voted?
    $polls = $GLOBALS["polls"];
    $poll_storage = $GLOBALS["poll_storage"];
    $auth = $GLOBALS["auth"];

    if ($poll_storage->user_voted($polls[$_GET["id"]], $auth->authenticated_user())) {
        $errors["global"] = "Csak egyszer tudsz szavazatot leadni!";
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
        $update_record = $polls[$_GET["id"]]; // poll_storage update record

        // if there is no vote yet for the specific poll 
        if (!isset($votes[$_GET["id"]])) { 
            $vote = []; // vote_storage new record
            $vote["id"] = $_GET["id"];
    
            foreach($data["answers"] as $k => $v) {
                $update_record["answers"][$v]++;
                $vote[$auth->authenticated_user()["id"]][] = $v;
            }
            $update_record["voted"][] = $auth->authenticated_user()["id"];
    
            $poll_storage->update($_GET["id"], $update_record);
            $vote_storage->add($vote);
        }
        // if there is at least one vote for the specific poll
        else { 
            $vote =  $votes[$_GET["id"]];
    
            foreach($data["answers"] as $k => $v) {
                $update_record["answers"][$v]++;
                $vote[$auth->authenticated_user()["id"]][] = $v;
            }
            $update_record["voted"][] = $auth->authenticated_user()["id"];
    
            $poll_storage->update($_GET["id"], $update_record);
            $vote_storage->update($_GET["id"], $vote);
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
    <title>Szavazás</title>
</head>
<body>
    <a href="index.php" class="close"></a>
    <h1>Szavazás</h1>
    <div class="formSection">
        <form action="
        <?php if (in_array($auth->authenticated_user()["id"], $poll_storage->findById($_GET["id"])["voted"])) : ?>
            update_vote.php?id=<?= $_GET["id"] ?>
        <?php endif ?>" 
        method="post" novalidate>
            <p class="primaryColor"><?= $polls[$_GET["id"]]["question"] ?></p>
            <input type="text" name="hidden" hidden>
            <div>
                <?php if ($polls[$_GET["id"]]["isMultiple"] === "true") : ?>
                    <?php foreach($polls[$_GET["id"]]["options"] as $k => $v) : ?>
                        <div class="form-check">
                            <input id="<?= $k ?>" class="form-check-input" type="checkbox" name="answers[]" value="<?= $v ?>"
                            <?php if (isset($_POST['answers']) && in_array($v, $_POST['answers'])) : ?>
                                checked
                            <?php endif ?>>
                            <label for="<?= $k ?>" class="form-check-label"><?= $v ?></label>
                        </div>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php foreach($polls[$_GET["id"]]["options"] as $k => $v) : ?>
                        <div class="form-check">
                            <input id="<?= $k ?>" class="form-check-input" type="radio" name="answers[]" value="<?= $v ?>"
                            <?php if (isset($_POST['answers']) && in_array($v, $_POST['answers'])) : ?>
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
                <div class="success">Sikeres szavazat!</div>
            <?php endif; ?>

            <div class="flex-parent jc-center">
                <button class="btn btn-primary" type="submit">
                    <?php if (in_array($auth->authenticated_user()["id"], $poll_storage->findById($_GET["id"])["voted"])) : ?>
                        Szavazat frissítése
                    <?php else : ?>
                        Szavazat leadása
                    <?php endif ?>
                </button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>