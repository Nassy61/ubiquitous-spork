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
$user_id = $_SESSION['id'];

//ログイン(=$usernameに値がある)時にはそのまま、非ログイン時はログインページに飛ぶ
if (empty($username)){
	header('location: mission_6_login.php');
}

//データベースに接続
$dsn = 'データベース名';
$user_database = 'ユーザー名';
$password_database = 'パスワード';
$pdo = new PDO($dsn, $user_database, $password_database);

//フォーム入力は、演習なら演習用の変数のみに値を入れ、表示なら表示用の変数のみに値を入れる
$training_number = $_POST['training_number'];
$show_number = $_POST['show_number'];
if (!empty($training_number)){
	$sql = "SELECT * FROM workbooks WHERE id ='$training_number'";
	$results = $pdo->query($sql);
	foreach ($results as $row){
		$training_name = $row['name'];
	}
} elseif (!empty($show_number)){
	$sql = "SELECT * FROM workbooks WHERE id ='$show_number'";
	$results = $pdo->query($sql);
	foreach ($results as $row){
		$show_name = $row['name'];
	}
}

//定着度の初期化
$training_start = $_POST['training_start'];
if ($training_start){
	//問題数の取得
	$sql = "SELECT MAX(id) FROM questions WHERE workbook = '$training_start' ";
	$result = $pdo->query($sql);
	foreach ($result as $row){
		$num = $row['MAX(id)'] + 1;
	}
	
	//既に入っている定着度を削除
	$sql = "DELETE FROM state WHERE username = '$username' AND workbook_id = '$training_start' ";
	$results = $pdo->query($sql);
	
	//全ての問題が「未演習」の定着度を書き込み
	$i = 1;
	while ($i < $num){
		$sql = 	"INSERT INTO state (username, workbook_id, question_id, state) VALUES ('$username', '$training_start', '$i', '未演習')";
		$results = $pdo->query($sql);
		$i ++;
	}
}

//演習モード
if (!empty($training_number)){
	$show_answer = $_POST['show_answer'];	//「解答を表示」が押されたかチェック
	
	echo "「" . $training_name . "」演習モード";
	echo "<form method = 'post' action = 'mission_6_training.php'>";
		echo "<input type = 'hidden' name = 'training_number' value = '$training_number'>";	//問題番号の引き継ぎ
		
		if (empty($show_answer)){
			//定着度の更新
			$last_question = $_POST['last_question'];
			if (!empty($last_question)){
				switch ($_POST['response']){	//stateを書き換える
					case 1:
						$state = 'さっぱり';
						break;
					case 2:
						$state = 'まあまあ';
						break;
					case 3:
						$state = '完璧';
						break;
				}
				
				//問題のidを取得
				$last_questionID = $_POST['last_questionID'];
			
				//既に入っている定着度を削除
				$sql = "DELETE FROM state WHERE username = '$username' AND workbook_id = '$training_number' AND question_id = '$last_questionID' ";
				$results = $pdo->query($sql);
	
				//定着度の書き込み
				$sql = 	"INSERT INTO state (username, workbook_id, question_id, state) VALUES ('$username', '$training_number', '$last_questionID', '$state')";
				$results = $pdo->query($sql);
			}

			$sql = "SELECT * FROM state WHERE username = '$username' AND workbook_id= '$training_number' AND state = '未演習' ORDER BY RAND() LIMIT 1";	//「未演習」問題をランダムに取得
			if ($results = $pdo->query($sql)){
			foreach ($results as $row){
				$question_id = $row['question_id'];
			}
			}
			$sql2 = "SELECT *FROM questions WHERE workbook = '$training_number' AND id = '$question_id' ";
			if ($results = $pdo->query($sql2)){
				foreach($results as $row){
					$state = '未演習';
					$question = $row['question'];
					$answer = $row['answer'];
					$id = $row['id'];
				}
			}
			
			if (empty($question)){
				$sql = "SELECT * FROM state WHERE username = '$username' AND workbook_id= '$training_number' AND state = 'さっぱり' ORDER BY RAND() LIMIT 1";	//「さっぱり」問題をランダムに取得
				if($results = $pdo->query($sql)){
				foreach ($results as $row){
					$question_id = $row['question_id'];
				}
				}
				$sql2 = "SELECT *FROM questions WHERE workbook = '$training_number' AND id = '$question_id' ";
				if ($results = $pdo->query($sql2)){
					foreach($results as $row){
						$state = 'さっぱり';
						$question = $row['question'];
						$answer = $row['answer'];
						$id = $row['id'];
					}
				}
				
				if (empty($question)){
					$sql = "SELECT * FROM state WHERE username = '$username' AND workbook_id= '$training_number' AND state = 'まあまあ' ORDER BY RAND() LIMIT 1";	//「まあまあ」問題をランダムに取得
					if($results = $pdo->query($sql)){
					foreach ($results as $row){
						$question_id = $row['question_id'];
					}
					}
					$sql2 = "SELECT *FROM questions WHERE workbook = '$training_number' AND id = '$question_id' ";
					if ($results = $pdo->query($sql2)){
						foreach($results as $row){
							$state = 'まあまあ';
							$question = $row['question'];
							$answer = $row['answer'];
							$id = $row['id'];
						}
					}
					
					if (empty($question)){
						$question = '全て完璧になりました。<br>お疲れ様でした。';
					}
				}
			}
			
			echo "<div class='qbox'>"
					. "<p class='qtitle'>Question</p>"
					. "<p>" . $question .  "</p>"
					. "</div>";
			
			if ($question <> '全て完璧になりました。<br>お疲れ様でした。'){
				echo "<div class='pbox'>"
						. "<p>習得状況　" . $state .  "</p>"
						. "</div>";

				echo "<button class = 'kbox' type='submit' name='show_answer' value='TRUE'>解答を表示</button>";
			}
			
			echo "<input type = 'hidden' name = 'last_question' value = '$question'>";		//問題の引き継ぎ
			echo "<input type = 'hidden' name = 'last_answer' value = '$answer'>";		//解答の引き継ぎ
			echo "<input type = 'hidden' name = 'last_questionID' value = '$id'>";	//問題番号の引き継ぎ
		
		}else{
			echo "<div class = 'qbox'><p class='qtitle'>Question</p><p>" . $_POST['last_question'] . "</p></div>";
			echo "<div class = 'abox'><p class='atitle'>Answer</p><p>" . $_POST['last_answer'] . "</p></div>";
			echo "<button class = 'icon' type='submit' name='response' value='3'>完璧</button>";
			echo "<button class = 'icon' type='submit' name='response' value='2'>まあまあ</button>";
			echo "<button class = 'icon' type='submit' name='response' value='1'>さっぱり</button>";
			
			echo "<input type = 'hidden' name = 'last_question' value = ' ". $_POST['last_question'] . " ' >";
			echo "<input type = 'hidden' name = 'last_answer' value = ' ". $_POST['last_answer'] . " ' >";
			echo "<input type = 'hidden' name = 'last_questionID' value = ' " . $_POST['last_questionID'] . " '>";	//問題番号の引き継ぎ
		}
		
	echo "</form>";
	echo "<hr>";
}

//問題一覧の表示
if (!empty($show_number)){
	echo
		"<table>"
		. "<caption>「" . $show_name . "」問題一覧</caption>"
		. "<tr>"
			. "<th>問題ID</th>"
			. "<th>問題</th>"
			. "<th>解答</th>"
			. "<th>習得状況</th>"
		. "</tr>";
	
	$sql2 = "SELECT * FROM state WHERE username = '$username' AND workbook_id = ' " . $show_number . " ' ";
	if ($results2 = $pdo->query($sql2)){
		$states = array();
		foreach ($results2 as $row){
			$question_id = $row['question_id'];
			$states[$question_id] = $row['state'];
		}
	}
	
	$sql = "SELECT * FROM questions WHERE workbook = ' " . $show_number . " ' ";
	if ($results = $pdo->query($sql)){
		$x = 0;
		foreach($results as $row){
			$question_id = $row['id'];
			echo '<tr>';
			echo '<td>' . $row['id'] . '</td>';
			echo '<td>' . $row['question'] . '</td>';
			echo '<td>' . $row['answer'] . '</td>';
			echo '<td>' . $states[$question_id] . '</td>';
			echo '</tr>';
			$x ++;
		}
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
		<th></th>
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
	echo '<td>'
	. "<form method = 'post' action = 'mission_6_training.php'>"
	. "<button type = 'submit'  name = 'show_number' value = '$x'>表示</button>"
	. "</form>"
	. '</td>';
	
	//初期化されているかどうかの確認
	$check = ' ';
	$sql = "SELECT * FROM state WHERE username = '$username' AND workbook_id = '$x' ";
	if ($results = $pdo->query($sql)){
		foreach ($results as $row){
			$check = $row[0];
		}
	}
	if ($check == $username){
		echo '<td>'
		. "<form method = 'post' action = 'mission_6_training.php'>"
		. "<button type = 'submit'  name = 'training_number' value = '$x'>演習</button>"
		. "</form>"
		. '</td>';
	}else{
		echo '<td></td>';
	}
	
	echo '<td>'
	. "<form method = 'post' action = 'mission_6_training.php'>"
	. "<input type = 'hidden'  name = 'show_number' value = '$x'>"
	. "<button type = 'submit'  name = 'training_start' value = '$x'>習得状況の初期化</button>"
	. "</form>"
	. '</td>';
	$x ++;
}

?>
</table>

<p>「習得状況の初期化」を押すことで、「演習」ボタンが出現します。</p>

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