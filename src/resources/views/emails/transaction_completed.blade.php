<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>取引完了通知</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      background-color: #ff5555;
      color: white;
      padding: 20px;
      text-align: center;
      border-radius: 8px 8px 0 0;
    }

    .content {
      background-color: #f9f9f9;
      padding: 30px;
      border-radius: 0 0 8px 8px;
    }

    .item-info {
      background-color: white;
      padding: 20px;
      border-radius: 4px;
      margin: 20px 0;
    }

    .button {
      display: inline-block;
      padding: 12px 24px;
      background-color: #2196f3;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      margin-top: 20px;
    }

    .footer {
      text-align: center;
      margin-top: 30px;
      color: #666;
      font-size: 12px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>取引が完了しました</h1>
  </div>
  <div class="content">
    <p>こんにちは、</p>
    <p>以下の商品の取引が完了しました。</p>

    <div class="item-info">
      <h2>{{ $itemName }}</h2>
      <p><strong>購入者:</strong> {{ $buyerName }}</p>
      <p><strong>取引完了日時:</strong> {{ $transaction->getCompletedAt()->format('Y年m月d日 H:i') }}</p>
    </div>

    <p>取引チャット画面から購入者を評価することができます。</p>

    <a href="{{ url('/transactions/' . $transaction->getId()) }}" class="button">取引チャット画面を開く</a>

    <div class="footer">
      <p>このメールは自動送信されています。</p>
      <p>フリマアプリ</p>
    </div>
  </div>
</body>

</html>
