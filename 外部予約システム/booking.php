<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>タクシー予約フォーム</title>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <div style="width:60%; margin: 50px auto; background-color:white; padding:30px; border-radius:8px;">
        <h2>タクシー予約フォーム</h2>

        <form method="post" action="process_booking.php">
            <label>お名前：</label><br>
            <input type="text" name="name" required><br><br>

            <label>メールアドレス：</label><br>
            <input type="email" name="email" required><br><br>

            <label>出発地：</label><br>
            <input type="text" name="pickup" required><br><br>

            <label>目的地：</label><br>
            <input type="text" name="dropoff" required><br><br>

            <label>ご利用日時：</label><br>
            <input type="datetime-local" name="date" required><br><br>

            <label>備考：</label><br>
            <textarea name="message" rows="4" cols="50"></textarea><br><br>

            <button type="submit" style="background-color:#222; color:white; padding:10px 30px; border:none;">送信</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>

</html>