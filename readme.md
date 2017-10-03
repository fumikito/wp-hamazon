#  WP Hamazon

Contributors: Takahashi_Fumiki, hametuha
Tags: amazon, affiliate, linkshare, rakuten, dmm, phg  
Requires at least: 4.7
Tested up to: 4.8.2
Requires PHP: 5.4
Stable tag: 3.0.0

You can add affiliate link in post content via Amazon, Rakute and etc.

##  Description

WordPressの投稿編集画面にアフィリエイトリンク挿入ショートコードを追加することができます。  
メディアアップローダーボタンの脇に各アフィリエイトサービスのボタンが表示されるようになるので、そちらをクリックしてください。
クリックすると検索画面になるので、該当する商品を探し、ショートコードを挿入してください。

### 対応しているアフィリエイトサービス

* Amazon
* 楽天
* リンクシェア
* PHG
* DMM

##  Installation

1. `wp-hamazon`フォルダーを`/wp-content/plugins/`ディレクトリにアップロードしてください。
1. プラグインを有効化してください。
1. 設定 > アフィリエト設定へ移動し、必要な情報を入力してください。

##  Screenshots

1. このようなボタンが追加されます

##  Changelog

### 2.3.1

* 関数`tmkm_amazon_view`が動かなくなっていたので、直しました。
* HTMLリンクを出力する`hamazon_asin_link`を追加しました。

###  2.3

* ショートコード挿入ボタンをつけました。
* ショートコード内の情報を出力できるようにしました。一言コピーのようなものが入れられます。後方互換は取っているので、いままでのコンテンツはそのまま表示されます。
* サービスにPHG（iTuensアフィリエイト）とDMMを追加しました。

```
// これまで
[tmkm-amazon]00000000[/tmkm-amazon]
// これから
[tmkm-amazon asin='00000000']この本は最高ですよ！[/tmkm-amazon]
```

### 2.2

* 楽天をサービスとして追加
* マークアップをドラスティックに変更しました。これまで利用していた方は注意してください。

### 2.1

* リンクシェアのAPIがリクエストを返さないことがあるので、キャッシュ方法を変更

### 2.0

* リンクシェアを追加

### 1.0

* はじめてのリリース
