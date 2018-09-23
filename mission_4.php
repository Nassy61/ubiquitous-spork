<!DOCTYPE html>
<html>
<head>
	<meta charset = 'UTF-8'>
</head>

<body>

<?php

//データベースに接続
$dsn = 'データベース名';
$user_database = 'ユーザー名';
$password_database = 'パスワード';
$pdo = new PDO($dsn, $user_database, $password_database);

//テーブルを作成
$sql = "CREATE TABLE bbs"
. "("
. "id INT,"
. "name char(32),"
. "comment TEXT,"
. "time TEXT,"
. "pass TEXT"
. ");";
$stmt = $pdo->query($sql);

//フォーム入力情報の取得
$sql = "SELECT MAX(id) FROM bbs";
$results = $pdo->query($sql);
$last_number = 0;
foreach($results as $row){
	$last_number = $row['MAX(id)'];
}
$number = $last_number + 1;
$name = $_POST['name'];
$comment = $_POST['comment'];
$time = date("Y/m/d H:i:s");
$pass = $_POST['pass'];

$editmode = $_POST['editmode'];	//編集対象id
$editmode_pass = $_POST['editmode_pass'];
$edit_number = $_POST['edit_number'];

$delete = $_POST['delete'];		//削除対象id
$delete_pass = $_POST['delete_pass'];

//編集モードへの切り替え
if (!empty($editmode)){
	$sql = "SELECT * FROM bbs";
	$results = $pdo->query($sql);
	foreach($results as $row){
		if ($editmode == $row['id']){
				if ($editmode_pass == $row['pass']){		//パス一致なら編集モードへ切り替え
					$edit_name = $row['name'];
					$edit_comment = $row['comment'];
					$edit_pass = $editmode_pass;
				}
				else{
					echo 'パスワードが違います。';
				}
		}
	}
}

//編集モード
elseif (!empty($edit_number)){
	$sql = "update bbs set name='$name',comment='$comment',time='$time',pass='$pass' where id = $edit_number";
	$edit_results = $pdo->query($sql);
}
//削除モード
elseif (!empty($delete)){
	$sql = "SELECT * FROM bbs";
	$results = $pdo->query($sql);
	foreach($results as $row){
		if ($delete == $row['id']){
			if ($delete_pass == $row['pass']){
				$sql = "delete from bbs where id=$delete";
				$results = $pdo->query($sql);
			}
			else{
				echo 'パスワードが違います。';
			}
		}
	}
}

//通常モード
elseif (!empty($comment)){
	$sql = $pdo->prepare("INSERT INTO bbs (id, name, comment, time, pass) VALUES (:id, :name, :comment, :time, :pass)");
	$sql->bindParam(':id', $number, PDO::PARAM_STR);
	$sql->bindParam(':name', $name, PDO::PARAM_STR);
	$sql->bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql->bindParam(':time', $time, PDO::PARAM_STR);
	$sql->bindParam(':pass', $pass, PDO::PARAM_STR);
	$sql->execute();
}

?>

<form method = 'post' action = 'mission_4.php'>

<input type = 'text' name = 'name' placeholder = '名前' value = "<?php echo $edit_name; ?>" /> <br>
<input type = 'text' name = 'comment' placeholder = 'コメント' value = "<?php echo  $edit_comment; ?>" /> <br>
<input type = 'text' name = 'pass' placeholder = 'パスワード' value = "<?php echo $edit_pass; ?>" />
<input type = 'hidden' name = 'edit_number' value = "<?php echo $editmode; ?>" />
<input type = 'submit' value = '送信' /> <br>

</form>
<form method = 'post' action = 'mission_4.php'>

<input type = 'text' name = 'delete' placeholder = '削除対象番号'/> <br>
<input type = 'text' name = 'delete_pass' placeholder = 'パスワード' />
<input type = 'submit' value = '削除' />

</form>

<form method = 'post' action = 'mission_4.php'>

<input type = 'text' name = 'editmode' placeholder = '編集対象番号'/> <br>
<input type = 'text' name = 'editmode_pass' placeholder = 'パスワード' />
<input type = 'submit' value = '編集' />

</form>

<?php

//テーブルの表示
$sql = 'SELECT * FROM bbs ORDER BY id ASC';
$results = $pdo->query($sql);
foreach($results as $row){
		echo $row['id'] . ' ';
		echo $row['name'] . ' ';
		echo $row['comment'] . ' ';
		echo $row['time'] . '<br>';
}

?>

</body>
</html>