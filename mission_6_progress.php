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
session_start();
$username = $_SESSION['username'];

//ログイン(=$usernameに値がある)時にはそのまま、非ログイン時はログインページに飛ぶ
if (empty($username)){
	header('location: mission_6_login.php');
}

//データベースに接続
$dsn = 'データベース名';
$user_database = 'ユーザー名';
$password_database = 'パスワード';
$pdo = new PDO($dsn, $user_database, $password_database);

//登録者を配列に入れる
$sql = 'SELECT * FROM register';
$results = $pdo->query($sql);
$users = array();
foreach($results as $row){
	$userid = $row['id'];
	$users[$userid] = $row['username'];
}

//指定された問題の進捗状況を一覧で表示
$show_number = $_POST['show_number']
if(!empty($show_number)){
	$sql = "SELECT * FROM state WHERE workbook_id='$show_number' ";
	$results = $pdo->query($sql);
	foreach($results as $row){
		$workbook_id = $row['workbook_id']
		$workbook_name = $row 
	}
	
	
}

?>

<table>
	<caption>問題集一覧</caption>
	<tr>
		<th>問題集ID</th>
		<th>問題集名</th>
	</tr>
<?php
//問題集一覧の表示
$sql = 'SELECT * FROM workbooks ORDER BY id ASC';
$results = $pdo->query($sql);
$x = 1;
foreach($results as $row){
	echo '<tr>';
	echo '<td>' . $row['id'] . '</td>';
	echo '<td>' . $row['name'] . '</td>';
	echo '<td>'
	. "<form method = 'post' action = 'mission_6_progress.php'>"
	. "<button type = 'submit'  name = 'show_number' value = '$x'>表示</button>"
	. "</form>"
	. '</td>';
	echo '</tr>';
	$x ++;
}

?>
</table>

</div>

<nav>
<li><a href="http://tt-305.99sv-coco.com/mission_6_main.php">トップページ</a></li>
<li><a href="http://tt-305.99sv-coco.com/mission_6_create_question.php">問題集の作成・編集</a></li>
<li><a href="http://tt-305.99sv-coco.com/mission_6_training.php">演習</a></li>
<li><a href="http://tt-305.99sv-coco.com/mission_6_login.php">ログイン</a></li>
<li><a href="http://tt-305.99sv-coco.com/mission_6_logout.php">ログアウト</a></li>
<li><a href="http://tt-305.99sv-coco.com/mission_6_register.php">新規会員登録</a></li>
</nav>

</div>

<footer>
<p>制作者：Nassy</p>
</footer>

</body>
</html>