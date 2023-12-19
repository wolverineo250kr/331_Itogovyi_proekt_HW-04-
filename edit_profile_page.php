<?php
// Подключение к базе данных и другие необходимые настройки
require_once 'engine/config.php';
require_once 'engine/dbFunctions.php';

session_start();

$userEmail = $_SESSION['user'];
$userData = getUserObjectByEmail($userEmail);

?>

<html lang="en">

<head>
    <title>Профиль</title>
    <?php
    require_once '_head.php';
    ?>
</head>

<body>
<h1>Профиль</h1>

<div class="jumbotron">
    <div class="container">
        <div class="row">
            <div class="col-md-2">

            </div>
            <div class="col-md-6">
                <form id="editProfileForm" enctype="multipart/form-data">
                    <label for="nickname">Nickname:</label>
                    <input type="text" name="nickname" id="nickname" value="<?php echo $userData['nickname']; ?>"
                           required>
                    <br>

                    <label for="avatar">Avatar:</label>
                    <input type="file" name="avatar" id="avatar">
                    <?php if ($userData['avatar']): ?>
                        <a data-fancybox="gallery" href="<?= $userData['avatar'] ?>">
                            <img src="<?= $userData['avatar'] ?>" style="width: 200px">
                        </a>

                    <?php endif; ?>
                    <br>

                    <label for="is_email_hidden">Скрыть Email:</label>
                    <input type="checkbox" name="is_email_hidden"
                           id="is_email_hidden" <?php echo $userData['is_email_hidden'] ? 'checked' : ''; ?>>
                    <br>
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" value="<?php echo $userData['email']; ?>" required>
                    <br>

                    <button type="submit">Save Changes</button>
                </form>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        require_once '_rightMenu.php';
                        ?>
                    </div>
                </div>

            </div>
        </div>


    </div>
</div>
<script src="/components/fancybox/source/jquery.fancybox.js"></script>
<script src="/js/editProfile.js"></script>
</body>

</html>
