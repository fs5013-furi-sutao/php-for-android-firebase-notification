<html>
    <head>
        <title>FreeStyle | Firebase Cloud Messaging</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/favicon.ico">
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css">
        <link rel="stylesheet" type="text/css" href="./css/main.css">
    </head>
    <body>
    <style>
pre, code {
    padding:10px 0px;
    box-sizing:border-box;
    -moz-box-sizing:border-box;
    webkit-box-sizing:border-box;
    display:block; 
    white-space: pre-wrap;  
    white-space: -moz-pre-wrap; 
    white-space: -pre-wrap; 
    white-space: -o-pre-wrap; 
    word-wrap: break-word; 
    width:100%; overflow-x:auto;
}

* {
  margin: 0;
  padding: 0;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
 
body {
    width: 100%;
    margin: 0 auto;
  padding: 20px;
  text-align: left;
  font-family: Lato;
  color: #777;
  /* background: #9b59b6; */
}

h1 a {
    text-decoration: none;
    color: #777;
}

h1 a:hover {
    color: #bbb;
}

h2 {
    margin: 1em 0 1em 0;
    color: #555;
}

.tabs {
    height: 100%;
  width: 650px;
  float: none;
  list-style: none;
  position: relative;
  margin: 0 2em 0 10px;
  text-align: left;
}
.tabs li {
  float: left;
  display: block;
}
.tabs input[type="radio"] {
  position: absolute;
  top: -9999px;
  left: -9999px;
}
.tabs label.tab-label {
  display: block;
  padding: 14px 21px;
  border-radius: 2px 2px 0 0;
  font-size: 20px;
  font-weight: normal;
  text-transform: uppercase;
  /*background: #8e44ad;*/
  border-bottom: 1px solid #ccc;
  cursor: pointer;
  position: relative;
  top: 4px;
  -webkit-transition: all 0.2s ease-in-out;
  -moz-transition: all 0.2s ease-in-out;
  -o-transition: all 0.2s ease-in-out;
  transition: all 0.2s ease-in-out;
}
.tabs label.tab-label:hover {
    /*color: #fff;*/
    background: #d1e8ff;
}
.tabs .tab-content {
    height: auto;
  z-index: 2;
  display: none;
  overflow: hidden;
  width: 100%;
  font-size: 17px;
  line-height: 25px;
  padding: 25px;
  position: absolute;
  top: 53px;
  left: 0;
  /*background: #612e76;*/
}
.tabs [id^="tab"]:checked + label {
  top: 0;
  padding-top: 17px;
  border-top: 4px solid #007fff; 
  border-left: 1px solid #ccc;
  border-right: 1px solid #ccc;
  border-bottom: none;
  /*background: #612e76;*/
}
.tabs [id^="tab"]:checked ~ [id^="tab-content"] {
  display: block;
}

form label {
    margin: 2em 0 0 0 !important;
}

form input, form textarea {
    text-align: left !important;
    width: 100% !important;
}

form input[type="checkbox"] {
    color: red !important;
}

.container {
    display: flex;
    justify-content: center;
}

.fl_window {
    width: 24em;
}

.checkbox_label input {
    width: 1em !important;
    margin: 0 1em 2em 0;
}
    </style>
<?php
// エラーレポート有効化
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once __DIR__ . '/firebase.php';
require_once __DIR__ . '/push.php';
require_once __DIR__ . '/config.php';

$firebase = new Firebase();
$push = new Push();

// 独自のユーザー定義フィールド
// optional payload
$payload = array();
$payload['team'] = 'Japan';
$payload['score'] = '5.6';

// 通知タイトル
$title = isset($_GET['title']) ? $_GET['title'] : '';

// 通知メッセージ
$message = isset($_GET['message']) ? $_GET['message'] : '';

// 通知種別 - single user / topic
$push_type = isset($_GET['push_type']) ? $_GET['push_type'] : '';

// 画像ファイルを含めるかどうか
$include_image = isset($_GET['include_image']) ? true : false;

$push->setTitle($title);
$push->setMessage($message);

if ($include_image) {
    $push->setImage('http://freestyles.jp/fshp/wp-content/uploads/2019/03/page_game_obake.jpg');

} else {
    $push->setImage('');
}

$push->setIsBackground(false);
$push->setPayload($payload);

$json = '';
$response = '';

if ($push_type == 'topic') {
    $json = $push->getPush();
    $response = $firebase->sendToTopic(TOPICS_NAME, $json);

} else if ($push_type == 'individual') {
    $json = $push->getPush();
    $regId = isset($_GET['regId']) ? $_GET['regId'] : '';
    $response = $firebase->send($regId, $json);
}

// DB 接続クラスの読み込み
require_once __DIR__ . '/connect.php';

$obj = new connect();
$histories = '';

if (isset($_GET['message'])) {
    $push_type_for_db = '';

    if ($push_type == 'topic') {
        $push_type_for_db = "topics/" . TOPICS_NAME;

    } else if ($push_type == 'individual') {
        $push_type_for_db = $push_type;

    }

    //sql文の発行
    $sql = "INSERT INTO "
        . "            push_notification_history "
        . "            (push_type, title, message) "
        . "     VALUES "
        . "            (?, ?, ?) "
        . ";";
    //クラスの中の関数の呼び出し
    $updated = $obj->plural($sql, array($push_type_for_db, $title, $message));
}
//sql文の発行
$sql = "SELECT "
    . "       id "
    . "     , push_type "
    . "     , title "
    . "     , message "
    . "  FROM "
    . "       push_notification_history"
    . ";";
//クラスの中の関数の呼び出し
$histories = $obj->select($sql);
?>



    <div class="container">
        <main>
            <h1><a href=".">Android 端末へのプッシュ通知システム</a></h1>
            <ul class="tabs">
                <li>
                    <input type="radio" name="tabs" id="tab1" checked />
                    <label class="tab-label" for="tab1">単一端末</label>
                    <div id="tab-content1" class="tab-content">
                        <form class="pure-form pure-form-stacked" method="get">
                            <fieldset>
                                <legend><h2>単一端末に送信する</h2></legend>
                
                                <label for="regId">Firebase Reg Id</label>
                                <input type="text" id="regId" name="regId"     class="pure-input-1-2" placeholder="'firebase registration id' を入力する">
                
                                <label for="title">件名</label>
                                <input type="text" id="individual_title" name="title" class="pure-input-1-2"   placeholder="件名を入力する">
                
                                <label for="message">メッセージ</label>
                                <textarea class="pure-input-1-2" rows="5" name="message"     id="individual_message" placeholder="通知メッセージを入力する"></textarea>
                
                                <label for="include_image" class="pure-checkbox checkbox_label">
                                    <input name="include_image" id="individual_include_image" type="checkbox" >画像を含めて送信する</label>
                                <input type="hidden" name="push_type" value="individual"/>
                                <button type="submit" id="individual_button" class="pure-button pure-button-primary btn_send" disabled>単一端末に送信</button>
                            </fieldset>
                        </form>
                    </div>
                </li>
     
                <li>
                    <input type="radio" name="tabs" id="tab2" />
                    <label class="tab-label" for="tab2">トピック</label>
                    <div id="tab-content2" class="tab-content">
                        <form class="pure-form pure-form-stacked" method="get">
                            <fieldset>
                                <legend><h2>「news」トピックに送信する</h2></legend>
                
                                <label for="topic_title">件名</label>
                                <input type="text" id="topic_title" name="title"     class="pure-input-1-2" placeholder="件名を入力する">
                
                                <label for="topic_message">メッセージ</label>
                                <textarea class="pure-input-1-2" name="message" id="topic_message"   rows="5" placeholder="通知メッセージを入力する"></textarea>
                
                                <label for="include_image1" class="pure-checkbox checkbox_label">
                                    <input id="topic_include_image" name="include_image" type="checkbox">画像を含めて送信する
                                </label>
                                <input type="hidden" name="push_type" value="topic"/>
                                <button type="submit" id="topic_button" class="pure-button pure-button-primary btn_send" disabled>トピックに送信</button>
                            </fieldset>
                        </form>
                    </div>
                </li>
    
                <li>
                    <input type="radio" name="tabs" id="tab3" />
                    <label class="tab-label" for="tab3">送信履歴</label>
                    <div id="tab-content3" class="tab-content">
                        <?php if ($histories != null) { ?>
                            <table class="pure-table pure-table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>送信形式</th>
                                        <th>件名</th>
                                        <th>メッセージ本文</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach ($histories as $row): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['push_type']; ?></td>
                                            <td><?php echo $row['title']; ?></td>
                                            <td><?php echo $row['message']; ?></td>
                                        </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                        <?php }
                            if ($histories == null) {?>
                                <p>送信履歴がありません.</p>
                        <?php } ?>
                    </div>
                </li>
            </ul>
        </main>

        <div class="fl_window">
            <div><img src="https://www.redspark.io/wp-content/uploads/2020/01/0-400x250.png" width="240" alt="Firebase"/></div>
            <br/>

            <label><b>Request:</b></label>
            <div class="json_preview">
                <?php if ($json != '') {?>
                    <pre><?php echo json_encode($json) ?></pre>
                <?php }?>
            </div>

            <br/>

            <label><b>Response:</b></label>
            <div class="json_preview">
                <?php if ($response != '') {?>
                    <pre><?php echo $response ?></pre>
                <?php }?>
            </div>
        </div>    
    </div><!-- .container -->

<script>
const regId = document.querySelector("#regId");
const individualTitle = document.querySelector("#individual_title");
const individualMessage = document.querySelector("#individual_message");
const individualButton = document.querySelector('#individual_button');
const topicTitle = document.querySelector("#topic_title");
const topicMessage = document.querySelector("#topic_message");
const topicButton = document.querySelector('#topic_button');

let cntNotBlankIndividulaForm = 0;
regId.addEventListener('keyup', function() {
    checkIsBlankIndividual();
}, false);

individualTitle.addEventListener('keyup', function() {
    checkIsBlankIndividual();
}, false);

individualMessage.addEventListener('keyup', function() {
    checkIsBlankIndividual();
}, false);

let cntNotBlankTopicForm = 0;
topicTitle.addEventListener('keyup', function() {
    checkIsBlankTopic();
}, false);

topicMessage.addEventListener('keyup', function() {
    checkIsBlankTopic();
}, false);

function checkIsBlankIndividual() {
    cntNotBlankIndividulaForm = 0;
    if (regId.value != '') cntNotBlankIndividulaForm++;
    if (individualTitle.value != '') cntNotBlankIndividulaForm++;
    if (individualMessage.value != '') cntNotBlankIndividulaForm++;
    canSendIndividual(); 
}

function canSendIndividual() {
    if (cntNotBlankIndividulaForm == 3) {
        individualButton.disabled = false;
        return;
    }
    individualButton.disabled = true;
}

function checkIsBlankTopic() {
    cntNotBlankTopicForm = 0;
    if (topicTitle.value != '') cntNotBlankTopicForm++;
    if (topicMessage.value != '') cntNotBlankTopicForm++;
    canSendTopic(); 
}

function canSendTopic() {
    if (cntNotBlankTopicForm == 2) {
        topicButton.disabled = false;
        return;
    }
    topicButton.disabled = true;
}

</script>
    

    </body>
</html>