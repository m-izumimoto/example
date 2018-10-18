<?php

//Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

// // POSTメソッドで渡される値を取得、表示
// $inputString = file_get_contents('php://input');
// error_log($inputString);

// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));

// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

// LINE Messaging APIがリクエストに付与した署名を取得
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

// 署名が正当かチェック。政党であればリクエストをパースし配列へ
$events = $bot->parseEventRequest(file_get_contents('php://input'),$signature);

// 配列に格納された各イベントをループで処理
foreach ($events as $event){
  // テキストを返信
  // $bot->replyText($event->getReplyToken(),'TextMessage');
  // テキストを返信し次のイベントの処理へ
  // replyTextMessage($bot,$event->getReplyToken(),'TextMessage');
  // 画像を返信する
  // replyImageMessage($bot, $event->getReplyToken(),'https://' . $_SERVER[HTTP_HOST] . '/imgs/original.jpg' , 'https://' . $_SERVER[HTTP_HOST] . '/imgs/preview.jpg');
  // Buttonsテンプレートメッセージを返信
  replyButtonsTemplate($bot,$event->getReplyToken(),
  'SUBLINE',
  'https://' .  $_SERVER[HTTP_HOST] . '/imgs/original.jpg',
  'お天気お知らせ',
  '今日の天気予報は晴れです',
  new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder ('解約','end'),
  new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder ('電話番号取得','tel_number'),
  new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ('メンバー追加','add_member')

);
}

//テキストを返信。引数はLINEBot、返信先、テキスト
function replyTextMessage($bot,$replyToken,$text) {
  // 返信を行いメッセージを取得
  // TextMessageBuilderの引数はテキスト
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));

  //レスポンスが異常な場合
  if(!$response->isSucceeded()){
    //エラー内容を出力
    error_log('Failed! '. $response->getHTTPStatus . ' '.$response->getRawBody());
  }
}

//画像を返信。引数はLINEBot、返信先、画像URL、サムネイルURL
function replyImageMessage($bot,$replyToken,$originalImageUrl,$previewImageUrl){
  // ImageMessageBuilderの引数は画像URL、サムネイルURL
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
  if(!$response->isSucceeded()){
    error_log('Failed! '. $response->getHTTPStatus . ' '.$response->getRawBody());
  }
}

// Buttonsテンプレートを送信。引数はLINEBot、返信先、代替テキスト
// 画像URL、タイトル、本文、アクション(可変長引数)
function replyButtonsTemplate($bot, $replyToken, $alternativeText,$imageUrl,$title,$text, ...$actions) {
  // アクションを格納する配列
  $actionArray = array();
  // アクションをすべて追加
  foreach($actions as $value) {
    array_push($actionArray, $value);
  }
  //TemplateMessageBuilderの引数は代替テキスト、ButtonTemplateBuilder
  $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($alternativeText,
    // ButtonTemplateBuilderの引数はタイトル、本文
    // 画像URL、アクションの配列
    new \LINE\LINEBot\MessageBuilder\ButtonTemplateBuilder($title,$text,$imageURL,$actionArray));
  $response = $bot->replyMessage($replyToken, $builder);
  if(!$response->isSucceeded()){
    error_log('Failed! '. $response->getHTTPStatus . ' '.$response->getRawBody());
  }

}

?>
