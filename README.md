# Twin Assist

トップページの制作プレビューです。PHP CMS実装前の静的UIであり、公開済みサイトではありません。

## 起動

Node.js 24で動作確認済み。外部パッケージのインストールは不要です。

```sh
cd /workspace/twin
npm run dev
```

既定ポートは3000。変更時は `PORT=3001 npm run dev` を使用します。プロセスは新しい環境で再起動してください。

## 現在の範囲

レスポンシブTOP、PC固定サイド、スマホ固定CTA、メニュー、用途選択、検索の空状態、相談窓口未登録の案内を実装。ヒーローと案内欄には生成したライフスタイル写真を使用しています。実店舗・実車・実在の顧客の写真ではなく、在庫画像としては使用していません。実素材提供後に差し替えます。検索の実在庫連携・LINE・送信フォーム・CMS・各下層ページは未実装です。現在のページはnoindexにしています。

## プレビュー画像

- [PC](previews/top-desktop.png)
- [スマホ](previews/top-mobile.png)

## 検証

Chromiumで320/375/390/430/768/1280/1440/1920pxの横はみ出し、メニュー開閉、用途選択、検索空状態、相談ダイアログ、Escape操作、JavaScriptエラーを確認しました。Safari/Firefoxは未確認です。

`node scripts/check-preview.cjs` は環境にPlaywrightと `/usr/bin/chromium` がある場合の検証用です。起動済みのポート3000を使用します。

- [要件定義](docs/Twin_Assist_Requirements.md)
