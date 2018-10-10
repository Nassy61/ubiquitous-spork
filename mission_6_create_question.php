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

/*
//(テーブルを作り直すとき用)テーブルを削除
$sql = "DROP TABLE questions";
$stmt = $pdo->query($sql);

$sql = "DROP TABLE workbooks";
$stmt = $pdo->query($sql);
*/


/*
問題集一覧の編集機能
*/

//問題集一覧のテーブルworkbooksを作成
$workbook_create = $_POST['workbook_create'];
$workbook_comment = $_POST['workbook_comment'];
if (!empty($workbook_create)){
	$sql = "CREATE TABLE workbooks"
	. "("
	. "id INT,"
	. "name TEXT,"
	. "creator TEXT,"
	. "comment TEXT"
	. ");";
	$stmt = $pdo->query($sql);
}

//問題集IDの取得
$sql = "SELECT MAX(id) FROM workbooks";
$results = $pdo->query($sql);
$last_number = 0;
foreach($results as $row){
	$last_number = $row['MAX(id)'];
}
$number = $last_number + 1;

//テーブルworkbooksに新規問題集を追加
if (!empty($workbook_create)){
	$sql = "INSERT INTO workbooks (id, name, creator, comment) VALUES ('$number', '$workbook_create', '$username', '$workbook_comment')";
	$results = $pdo->query($sql);
}


/*
編集フォームの表示
*/

//フォーム入力は、編集なら編集用と表示用の変数に値を入れ、表示なら表示用の変数のみに値を入れる
$edit_number = $_POST['edit_number'];
if (!empty($edit_number)){
	$show_number = $edit_number;
	$sql = "SELECT * FROM workbooks WHERE id ='$edit_number'";
	$results = $pdo->query($sql);
	foreach ($results as $row){
		$edit_name = $row['name'];
		$show_name = $row['name'];
	}
} elseif (!empty($_POST['show_number'])){
	$show_number = $_POST['show_number'];
	$sql = "SELECT * FROM workbooks WHERE id ='$show_number'";
	$results = $pdo->query($sql);
	foreach ($results as $row){
		$show_name = $row['name'];
	}
}

/*
問題集の中身の編集機能
*/

//問題集の中身のテーブルquestionsを作成
$question = $_POST['question'];
$answer = $_POST['answer'];
if (!empty($question) and !empty($answer)){
	$sql = "CREATE TABLE questions"
	. "("
	. "workbook INT,"
	. "id INT,"
	. "question TEXT,"
	. "answer TEXT"
	. ");";
	$stmt = $pdo->query($sql);
}

//問題IDの取得
$workbook = $edit_number;	//その問題が所属する問題集ID
$sql = "SELECT MAX(id) FROM questions WHERE workbook = '$workbook' ";
if ($results = $pdo->query($sql)){
	$last_number = 0;
	foreach($results as $row){
		$last_number = $row['MAX(id)'];
	}
	$id = $last_number + 1;
}

//問題集の中身の新規追加
if (!empty($question) and !empty($answer)){
	$sql = "INSERT INTO questions (workbook, id, question, answer) VALUES ('$workbook', '$id', '$question', '$answer')";
	$results = $pdo->query($sql);
}

//問題集の中身の編集
$edit_id = $_POST['edit_id'];
$edit_question = $_POST['edit_question'];
$edit_answer = $_POST['edit_answer'];
if (!empty($edit_question)){
	$sql = "UPDATE questions SET question = '$edit_question', answer = '$edit_answer' WHERE workbook = '$edit_number' AND id = '$edit_id' ";
	$results = $pdo->query($sql);
}

//指定された問題集の中身の表示
if (!empty($show_number)){
	echo
		"<table>"
		. "<caption>「" . $show_name . "」問題一覧</caption>"
		. "<tr>"
			. "<th>問題ID</th>"
			. "<th>問題</th>"
			. "<th>解答</th>";
			if (!empty($edit_number) and empty($_POST['editmode'])){
				echo "<th></th>";
			} elseif(!empty($_POST['editmode'])){
				echo "<th></th>";
			}
		echo "</tr>";
	
	$sql = "SELECT * FROM questions WHERE workbook = ' " . $show_number . " ' ORDER BY id ASC";
	if ($results = $pdo->query($sql)){
		foreach($results as $row){
			$editmode = (int)$_POST['editmode'];
			$id_check = (int)$row['id'];
			echo '<tr>';
			echo '<td>' . $row['id'] . '</td>';
			if ($editmode == $id_check){
				echo '<td>'
				. "<form method = 'post' action = 'mission_6_create_question.php'>"
				. "<input type = 'hidden' name = 'edit_id' value = ' " . $row['id'] . " ' >"
				. "<input type = 'text'  name = 'edit_question' value = ' " . $row['question'] . " '>"
				. '</td>';
				echo '<td>'
				. "<input type = 'text'  name = 'edit_answer' value = ' " . $row['answer'] . " '>"
				. '</td>';
				echo '<td>'
				. "<input type = 'submit' value = '編集を確定'>"
				. "<input type = 'hidden' name = 'edit_number' value = '$edit_number'>"
				. "</form>"
				. '</td>';
			} elseif(!empty($_POST['editmode'])){
				echo '<td>' . $row['question'] . '</td>';
				echo '<td>' . $row['answer'] . '</td>';
				echo '<td></td>';
			} else{
				echo '<td>' . $row['question'] . '</td>';
				echo '<td>' . $row['answer'] . '</td>';
			}
			if (!empty($edit_number) and empty($_POST['editmode'])){
				echo '<td>'
				. "<form method = 'post' action = 'mission_6_create_question.php'>"
				. "<button type = 'submit'  name = 'editmode' value = ' " . $row['id'] . " '>編集</button>"
				. "<input type = 'hidden' name = 'edit_number' value = '$edit_number'>"
				. "</form>"
				. '</td>';
			}
			echo '</tr>';
		}
	}
	if (!empty($edit_number)){
		echo "<form method = 'post' action = 'mission_6_create_question.php'>";
		echo '<td></td>';
		echo '<td>';
		echo "<input type = 'text' name = 'question' placeholder = '問題' />";
		echo '</td><td>';
		echo "<input type = 'text' name = 'answer' placeholder = '解答' />";
		echo '</td><td colspan="2">';
		echo "<input type = 'hidden' name = 'edit_number' value = '" . $edit_number . "'/>";
		echo "<input type = 'submit' value = '新規追加' />";
		echo "</form>";
		echo '</td>';
	}
	echo "</table>";
	echo "<hr>";
}

?>

<table>
	<caption>問題集一覧</caption>
	<tr>
		<th>問題集ID</th>
		<th>問題集名</th>
		<th>作成者</th>
		<th>説明</th>
		<th></th>
		<th></th>
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
	echo '<td>' . $row['creator'] . '</td>';
	echo '<td>' . $row['comment'] . '</td>';
	echo '<td>'
	. "<form method = 'post' action = 'mission_6_create_question.php'>"
	. "<button type = 'submit'  name = 'show_number' value = '$x'>表示</button>"
	. "</form>"
	. '</td>';
		echo '<td>'
	. "<form method = 'post' action = 'mission_6_create_question.php'>"
	. "<button type = 'submit'  name = 'edit_number' value = '$x'>編集</button>"
	. "</form>"
	. '</td>';
	echo '</tr>';
	$x ++;
}

?>
</table>

<hr>

<p>新規問題集の作成</p>
<form method = 'post' action = 'mission_6_create_question.php'>
	
<input type = 'text' name = 'workbook_create' placeholder = '問題集名' />
<input type = 'text' name = 'workbook_comment' placeholder = '説明' />
<input type = 'submit' value = '作成' />

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