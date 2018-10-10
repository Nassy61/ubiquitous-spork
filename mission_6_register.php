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

<?php

//データベースに接続
$dsn = 'データベース名';
$user_database = 'ユーザー名';
$password_database = 'パスワード';
$pdo = new PDO($dsn, $user_database, $password_database);

//登録情報のテーブルを作成
$sql = "CREATE TABLE register"
. "("
. "id INT,"
. "username char(32),"
. "email TEXT,"
. "time TEXT,"
. "password TEXT,"
. "certification TEXT"
. ");";
$stmt = $pdo->query($sql);

//定着度のテーブルを作成
$sql = "CREATE TABLE state"
. "("
. "username TEXT,"
. "workbook_id INT,"
. "question_id INT, "
. "state TEXT"
. ");";
$stmt = $pdo->query($sql);


//idの取得
$sql = "SELECT MAX(id) FROM register";
$results = $pdo->query($sql);
$last_id = 0;
foreach($results as $row){
	$last_id = $row['MAX(id)'];
}
$id = $last_id + 1;

//フォーム入力情報の取得
$username = $_POST['username'];
$email = $_POST['email'];
$time = date("Y-m-d H:i:s");
$password = $_POST['password'];

//既に使われているユーザー名かのチェック
$sql = "SELECT * FROM register WHERE username = '$username'";
$results = $pdo->query($sql);
foreach ($results as $row){
	$check = $row;
}

$yet = TRUE;	//全未入力時のみFALSEにする
if (empty($username) and empty($email) and empty($password)){	//全未入力→何もしないor認証
	$yet = FALSE;
	
	if (!empty($_GET['token'])) {
		$input_token = $_GET['token'];
		$input_id = $_GET['id'];
	
	 	$sql = 'SELECT * FROM register';
	 	$results = $pdo->query($sql);
	 	foreach($results as $row){
	 		if ($row['id'] == $input_id){
	 			
	 			//今の時刻と仮登録時刻を比べる
	 			$jikansa = (strtotime($time) - strtotime($row['time']))/(60*60);
	 			
	 			if ($row['certification'] == $input_token and $jikansa <= 24){	//トークンが一致かつ24時間以内ならcertificationを'ok'に変える
	 				$sql = "update register set certification='ok' where id=$input_id";
	 				$result = $pdo->query($sql);
	 				echo $row['username'] . 'さんの本登録が完了しました。';
	 				
	 				//定着度のテーブルstateにデータを追加
	 				$sql = "ALTER TABLE questions ADD [COLUMN] ". $row['id'];
	 				$results = $pdo->query($sql);
	 				
	 			} else{
	 				echo '本登録に失敗しました。'; 
	 			}
	 		}
	 	}
	}
}

elseif (!empty($username) and !empty($email) and !empty($password) and empty($check)){	//全入力→仮登録&メール送信
	$sql = $pdo->prepare("INSERT INTO register (id,username,email,time,password,certification) VALUES (:id,:username,:email,:time,:password,:certification)");
	$sql->bindParam(':id', $id, PDO::PARAM_STR);
	$sql->bindParam(':username', $username, PDO::PARAM_STR);
	$sql->bindParam(':email', $email, PDO::PARAM_STR);
	$sql->bindParam(':time', $time, PDO::PARAM_STR);
	$sql->bindParam(':password', $password, PDO::PARAM_STR);
	$token = uniqid();
	$sql->bindParam(':certification', $token, PDO::PARAM_STR);
	$sql->execute();
	
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");
	$mail_title = '【TECH-BACE山梨】会員登録用URLのお知らせ';
	$mail_body = $username . 'さん、ご登録ありがとうございます。' . "\r\n"
	. '以下のアドレスにアクセスすることで、登録が完了します。' . "\r\n"
	. 'http://tt-305.99sv-coco.com/mission_6_register.php' . '?id=' . $id . '&token=' . $token;
	if (mb_send_mail($email, $mail_title, $mail_body)){
		echo 'メールをお送りしました。24時間以内にメールに記載されたURLから本登録してください。';
	}else {
		echo 'メールの送信に失敗しました。';
	}
	
}

?>

<p>登録フォーム</p>

<form method = 'post' action = 'mission_6_register.php'>

<input type = 'text' name = 'username' placeholder = 'ユーザー名' />
<?php
if ($yet and empty($username)){
	echo 'ユーザー名を入力してください。';
}
if (!empty($check)){
	echo 'そのユーザー名は既に使われています。';
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
<input type = 'submit' value = '送信' />
<?php
if ($yet and empty($password)){
	echo 'パスワードを入力してください。';
}
?>

</form>

<hr>
<table>
	<caption>登録者一覧</caption>
	<tr>
		<th>会員ID</th>
		<th>ユーザー名</th>
		<th>登録状態</th>
	</tr>

<?php

//テーブルの表示
$sql = 'SELECT * FROM register ORDER BY id ASC';
$results = $pdo->query($sql);

foreach($results as $row){
	echo '<tr>';
	echo '<td>' . $row['id'] . '</td>';
	echo '<td>' . $row['username'] . '</td>';
		
	if ($row['certification'] == 'ok') {
		echo '<td>本登録済み</td>';
	} else{
		echo '<td>仮登録状態</td>';
	}
	echo '</tr>';
}

echo '</table>';

?>

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