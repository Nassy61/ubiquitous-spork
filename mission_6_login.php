<!DOCTYPE html>
<html>
<head>
	<meta charset = 'UTF-8'>
	<link href="mission_6.css" rel="stylesheet" type="text/css">
</head>

<body>

<header>
<h1>暗記サポート</h1>
<p>効率的な暗記を助けるwebアプリです。</p>
</header>

<div id="content">

<div id="main">

<body>

<?php
session_start();

//データベースに接続
$dsn = 'データベース名';
$user_database = 'ユーザー名';
$password_database = 'パスワード';
$pdo = new PDO($dsn, $user_database, $password_database);

//フォーム入力情報の取得
$username = $_POST['username'];
$email = $_POST['email'];
$time = date("Y-m-d H:i:s");
$password = $_POST['password'];

//ユーザー名とパスワードを照合、合っていればメインページに移動
$sql = "SELECT * FROM register WHERE username='$username'";
$results = $pdo->query($sql);
foreach($results as $row){
	if($row['username'] == $username){
		if($row['certification'] <> 'ok'){
			echo 'メール認証が完了していません。';
		} else{
			if($row['email'] <> $email){
				echo 'メールアドレスが間違っています。';
			} else{
				if($row['password'] <> $password){
					echo 'パスワードが間違っています。';
				} else{	//全て正しければメインページへ
					$_SESSION['username'] = $username;
					$_SESSION['id'] = $row['id'];
					header('location: mission_6_main.php');
					exit();
				}
			}
		}
	}
}

?>

<p>ログインフォーム</p>
<form method = 'post' action = 'mission_6_login.php'>

<input type = 'text' name = 'username' placeholder = 'ユーザー名' />
<?php
if ($yet and empty($username)){
	echo 'ユーザー名を入力してください。';
}
?>
<br>

<input type = 'text' name = 'email' placeholder = 'メールアドレス' />
<?php
if ($yet and empty($email)){
	echo 'メールアドレスを入力してください。';
} 
?>
<br>

<input type = 'text' name = 'password' placeholder = 'パスワード' />
<input type = 'submit' value = 'ログイン' />
<?php
if ($yet and empty($password)){
	echo 'パスワードを入力してください。';
}

?>


</form>

</div>

<nav>
<a href="http://tt-305.99sv-coco.com/mission_6_main.php" class="square_btn">トップページ　　　</a><br>
<a href="http://tt-305.99sv-coco.com/mission_6_create_question.php" class="square_btn">問題集の作成・編集</a><br>
<a href="http://tt-305.99sv-coco.com/mission_6_training.php" class="square_btn">演習　　　　　　　</a><br>
<a href="http://tt-305.99sv-coco.com/mission_6_login.php" class="square_btn">ログイン　　　　　</a><br>
<a href="http://tt-305.99sv-coco.com/mission_6_logout.php" class="square_btn">ログアウト　　　　</a><br>
<a href="http://tt-305.99sv-coco.com/mission_6_register.php" class="square_btn">新規会員登録　　　</a><br>
</nav>

</div>

<footer>
<p>制作者：Nassy</p>
</footer>

</body>
</html>