# アプリケーション名

coachtech フリマ

## 概要

## 技術スタック

## 環境構築手順

### 前提条件

以下のソフトウェアがインストールされていることを確認してください：

- Docker Desktop
- Docker Compose

### 1. リポジトリのクローン

```bash
git clone <リポジトリURL>
cd Furima
```

### 2. 環境の起動

プロジェクトのルートディレクトリで以下のコマンドを実行してください：

```bash
# Dockerコンテナをビルドして起動
docker compose up -d --build
```

### 3. 動作確認

ブラウザで以下の URL にアクセスして、アプリケーションが正常に動作することを確認してください：

```
http://localhost
```

正常に動作している場合、「COACHTECH」と表示されます。

### 4. 開発時の操作

#### コンテナの停止

```bash
# コンテナを停止（バックグラウンドで実行中の場合）
docker compose stop

# コンテナを停止して削除
docker compose down
```

#### コンテナの再起動

```bash
# コンテナを再起動
docker compose restart
```

#### ログの確認

```bash
# 全サービスのログを確認
docker compose logs

# 特定のサービスのログを確認
docker compose logs nginx
docker compose logs php
```

#### コンテナ内での作業

```bash
# PHPコンテナに入る
docker compose exec php bash

# Nginxコンテナに入る
docker compose exec nginx bash
```

### 5. ファイル構成

```
Furima/
├── docker/
│   ├── nginx/
│   │   └── default.conf    # Nginx設定ファイル
│   └── php/
│       ├── Dockerfile      # PHPコンテナの設定
│       └── php.ini         # PHP設定ファイル
├── src/
│   └── index.php          # メインのPHPファイル
├── docker-compose.yml     # Docker Compose設定
└── README.md             # このファイル
```

### 6. トラブルシューティング

#### ポート 80 が既に使用されている場合

他のアプリケーションがポート 80 を使用している場合は、`docker-compose.yml`の`ports`セクションを変更してください：

```yaml
ports:
  - "8080:80" # ホストの8080ポートを使用
```

この場合、アクセス URL は `http://localhost:8080` になります。

#### コンテナが起動しない場合

```bash
# コンテナの状態を確認
docker compose ps

# ログを確認してエラーの詳細を確認
docker compose logs

# コンテナを完全に削除して再ビルド
docker compose down --volumes --remove-orphans
docker compose up --build
```

### 7. 開発のヒント

- `src/` ディレクトリ内のファイルを編集すると、コンテナ内に自動的に反映されます
- PHP ファイルを変更した場合は、ブラウザでリロードするだけで変更が反映されます
- Nginx 設定を変更した場合は、コンテナの再起動が必要です

## ライセンス

このプロジェクトのライセンス情報をここに記載してください。
