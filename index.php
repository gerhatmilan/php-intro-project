<?php
include_once('storages/user_storage.php');
include_once('storages/poll_storage.php');
include_once('storages/auth.php');

function redirect($page) {
    header("Location: ${page}");
    exit();
}

function get_percentage($option, $options_array) {
    $option_vote_count = $options_array[$option];
    $vote_count = 0;
    foreach ($options_array as $k => $v) {
        $vote_count += $v;
    }

    return floatval($option_vote_count / $vote_count * 100.0);
}

session_start();
$user_storage = new UserStorage();
$poll_storage = new PollStorage();
$auth = new Auth($user_storage);
$polls = $poll_storage->findAll();
date_default_timezone_set('Europe/Budapest');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/index.css">
    <title>Főoldal</title>
</head>
<body>
    <div id="main">
        <?php if ($auth->is_authenticated()) : ?>
            <div id="menu">
                <span id="menuUsername"><?= $auth->authenticated_user()['username'] ?></span>
                <?php if ($auth->authorize()) : ?>
                    <a href="create_poll.php">Új szavazás</a>
                <?php endif; ?>
                <a href="logout.php">Kijelentkezés</a>
            </div>
        <?php else : ?>
            <div id="menu">
                <a href="login.php">Bejelentkezés</a>
                <a href="registration.php">Regisztráció</a>
            </div>
        <?php endif; ?>

        <h1>Szavazólapok</h1>
        <p>Az oldalon szavazatokat lehet leadni az aktív szavazólapokra. Szavazólaponként egy szavazat leadására van lehetőség. Az egyes szavazólapok lejárati határidejéig a már leadott szavazatok módosíthatóak. A szavazás anonim, viszont a szavazatok leadása regisztrációhoz kötött.</p>
        <h2>Aktív szavazólapok</h2>
        <?php foreach(array_reverse($poll_storage->get_active_polls()) as $k => $v) : ?>
            <div class="pollDiv">
                <div>
                    <?php if($auth->authorize()) : ?>
                        <a href="delete_poll.php?id=<?= $v["id"] ?>" class="delete"></a>
                    <?php endif ?>
                    <span class="questionId">#<?= $v["id"] ?></span>
                    <div class="primaryColor question"><?= $v["question"] ?></div>
                </div>
                <hr>
                <div>
                    <div>
                        Létrehozás ideje: <span class="primaryColor"><?= $v["createdAt"] ?></span>
                    </div>
                    <div>
                        Leadási határidő: <span class="primaryColor"><?= $v["deadline"] ?></span>
                    </div>                   
                </div>
                <div>
                    <?php if ($auth->is_authenticated() && $poll_storage->user_voted($polls[$v["id"]], $auth->authenticated_user())) : ?>
                        <a href="update_vote.php?id=<?= $v["id"] ?>" class="btn btn-primary">Szavazat frissítése</a>
                    <?php else : ?>
                        <a href="vote.php?id=<?= $v["id"] ?>" class="btn btn-primary">Szavazok</a>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
        <h2>Lezárt szavazólapok</h2>
        <?php foreach(array_reverse($poll_storage->get_closed_polls()) as $k => $v) : ?>
            <div class="pollDiv closedPoll">
                <div>
                    <?php if($auth->authorize()) : ?>
                        <span><a href="delete_poll.php?id=<?= $v["id"] ?>" class="delete"></a></span>
                    <?php endif ?>
                    <span class="questionId">#<?= $v["id"] ?> </span>
                    <span class="primaryColor question"><?= $v["question"] ?></span>
                </div>
                <hr>
                <div>
                    <div>
                        Létrehozás ideje: <span class="primaryColor"><?= $v["createdAt"] ?></span>
                    </div>
                    <div>
                        Leadási határidő: <span class="primaryColor"><?= $v["deadline"] ?></span>
                    </div>                   
                </div>
                <hr>
                <div>
                    <div>Eredmény:</div>
                    <?php foreach($v["answers"] as $key => $val) : ?>
                        <div class="result"><span class="primaryColor"><?= $key ?>:</span> <?= $val ?> szavazat
                            <?php if (get_percentage($key, $v["answers"]) !== 0.0) : ?>
                                (<?= number_format(get_percentage($key, $v["answers"]), 1, ".", "") ?>%)
                            <?php endif ?>
                            </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>