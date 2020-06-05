<?php
session_start();   //セッション(ブラウザが開いている間に情報を保存)を開始
require('../dbconnect.php');

if (!empty($_POST)) {   //フォームを送信した時(エンターキーによる誤送信と区別)に以下の処理を行う
	if ($_POST['name'] === '') {
		$error['name'] = 'blank';
	}
	if ($_POST['email'] === '') {
		$error['email'] = 'blank';
	}
	if (strlen($_POST['password']) < 4) {
		$error['password'] = 'length';
	}
	if ($_POST['password'] === '') {
		$error['password'] = 'blank';
	}
	$fileName = $_FILES['image']['name'];
	if (!empty($fileName)) {
		$ext = substr($fileName, -3);
		if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
			$error['image'] = 'type';
		}
	}
	//アカウントの重複をチェック
	if (empty($error)) {
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
		$member->execute(array($_POST['email']));
		$record = $member->fetch();
		if ($record['cnt'] > 0) {
			$error['email'] ='duplicate';
		}
	}
	if (empty($error)) {
		$image = date('YmHis') . $_FILES['image']['name'];   //画像ファイル名を被らせないための処理
		move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);   //画像を仮アップロードから本アップロードにする処理
		$_SESSION['join'] = $_POST;   //他ページで値を参照させるための処理
		$_SESSION['join']['image'] = $image;
		header('Location: check.php');
		exit();
	}
}

if ($_REQUEST['action'] === 'rewrite' && isset($_SESSION['join'])) {   //書き直し時、?action=rewriteが付与されていて且つセッションが正しく設定されている時に
	$_POST = $_SESSION['join'];   //入力内容を引き継がせる処理
}
?>

<!DOCTYPE html>
<html lang="ja">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>会員登録</title>

		<link rel="stylesheet" href="../style.css" />
	</head>

	<body>
		<div id="wrap">
			<div id="head">
				<h1>会員登録</h1>
			</div>

			<div id="content">
				<p>次のフォームに必要事項をご記入ください。</p>
				<form action="" method="post" enctype="multipart/form-data">
					<dl>
						<dt>ニックネーム<span class="required">必須</span></dt>
						<dd>
							<input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES); ?>" />
							<?php if ($error['name'] === 'blank') : ?>
								<p class="error">* ニックネームを入力してください</p>
							<?php endif; ?>
						</dd>
						<dt>メールアドレス<span class="required">必須</span></dt>
						<dd>
							<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES); ?>" />
							<?php if ($error['email'] === 'blank') : ?>
								<p class="error">* メールアドレスを入力してください</p>
							<?php endif; ?>
							<?php if ($error['email'] === 'duplicate') : ?>
								<p class="error">* 指定されたメールアドレスはすでに登録されています</p>
							<?php endif; ?>
						<dt>パスワード<span class="required">必須</span></dt>
						<dd>
							<input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>" />
							<?php if ($error['password'] === 'length') : ?>
								<p class="error">* パスワードは４文字以上で入力してください</p>
							<?php endif; ?>
							<?php if ($error['password'] === 'blank') : ?>
								<p class="error">* パスワードを入力してください</p>
							<?php endif; ?>
						</dd>
						<dt>写真など</dt>
						<dd>
							<input type="file" name="image" size="35" value="test" />
							<?php if ($error['image'] === 'type') : ?>
								<p class="error">* 写真などは、「.gif」、「.jpg」または「.png」の画像を指定してください</p>
							<?php endif; ?>
							<?php if (!empty($error)) : ?>
								<p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
							<?php endif; ?>
						</dd>
					</dl>
					<div><input type="submit" value="入力内容を確認する" /></div>
				</form>
			</div>
	</body>

</html>