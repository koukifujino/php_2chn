<?php

date_default_timezone_set("Asia/Tokyo");

// h()関数の定義例
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


$comment_array = array();
$pdo = null;
$statment = null;
$error_messages = array();

//DB接続
$dsn = 'mysql:dbname=test_db;host=run-php-db;';
$user = 'test';
$password = 'test';
try {
    $pdo = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo $e->getMessage();
}



//フォームを打ち込んだ時
if (!empty($_POST["submitButton"])) {

    //名前のチェック
    if (empty($_POST["username"])) {
        echo "名前を入力してください";
        $error_messages["username"] = "名前を入力してください";
    }
    //コメントのチェック
    if (empty($_POST["comment"])) {
        echo "コメントを入力してください";
        $error_messages["comment"] = "コメントを入力してください";
    }

    if (empty($error_messages)) {
        $postDate = date("Y-m-d H:i:s");
        try {
            $statment = $pdo->prepare("INSERT INTO bbs_table (user_name, comment, postDate) VALUES (:username, :comment, :postDate)");
            $statment->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
            $statment->bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
            $statment->bindParam(':postDate', $postDate, PDO::PARAM_STR);

            $statment->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

//dbからコメント取得
$sql = "SELECT `id`, `user_name`, `comment`, `postDate` FROM `bbs_table`;";
$comment_array = $pdo->query($sql);

// クエリの実行に失敗した場合のエラー処理
if (!$comment_array) {
    $error_info = $pdo->errorInfo();
    echo "クエリの実行に失敗しました：{$error_info[2]}";
    exit;
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 class="title">PHPで2チャンネル風の掲示板アプリ</h1>
    <hr>
    <div class="boardWrapper">
        <section>
            <?php foreach ($comment_array as $comment) : ?>
                <article>
                    <div class="wrapper">
                        <div class="nameArea">
                            <span>名前：</span>
                            <p class="username"><?php echo h($comment["user_name"]); ?></p>
                            <time>：<?php echo h($comment["postDate"]); ?></time>
                        </div>
                        <p class="comment"><?php echo h($comment["comment"]); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        <form class="formWrapper" method="POST">
            <div>
                <input type="submit" value="書き込む" name="submitButton">
                <label for="">名前：</label>
                <input type="text" name="username">
                <div>
                    <textarea class="commentTextArea" name="comment"></textarea>
                </div>
            </div>
        </form>
    </div>
</body>

</html>