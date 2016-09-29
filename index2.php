<?php
set_time_limit(0);

require("lib/puzzle2.php");
$horizontal = 3;
$vertical = 3;
$puzzle = new Puzzle($horizontal, $vertical);
$puzzleArr = $puzzle->getPuzzle();
$timeBegin = 0;
$timeEnd = 0;
$solutionPath = [];
$solutionPathText = [];
$availableWarning = "无解八数码";
if ($puzzle->hasSolution()) {
    $availableWarning = "";

    $timeBegin = microtime(true);
    $solutionPath = $puzzle->computeSolution();
    $timeEnd = microtime(true);

    $formatTest = function ($record) {
        return Puzzle::$operationText[$record];
    };
    $solutionPathText = array_map($formatTest, $solutionPath);
}
$puzzleArr = json_encode($puzzleArr);
$solutionPath = json_encode($solutionPath);
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>八数码拼盘</title>
    <link rel="stylesheet" type="text/css" href="css/puzzle.css"/>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="js/jq.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/puzzle.js"></script>
    <script type="text/javascript">
        //将PHP传来的字符串还原成数组
        var puzzleData = JSON.parse('<?=$puzzleArr?>');
        var horizontal = "<?=$horizontal?>";
        var vertical = "<?=$vertical?>";
        var solutionPath = JSON.parse("<?=$solutionPath?>");
        var isAuto = "<?=isset($_GET["auto"]) ? $_GET["auto"] : ""?>";
    </script>
    <script type="text/javascript" src="js/public.js"></script>
</head>
<body>
<div class="spend_timer">
    <div class="step_number">
        拼图计时：<span class="num" id="timer_show">000:00:0</span>&nbsp;&nbsp;
        步数：<span id="step_number">0</span>
    </div>
    <?php if (count($solutionPathText) > 0) { ?>
        <div class="timer_show">
            使用了<?= round($timeEnd - $timeBegin, 3) ?>秒生成<a href="javascript:$('#solution').show();">解决路径</a>
        </div>
        <div class="solution" id="solution"><?= implode(",", $solutionPathText) ?></div>
    <?php } ?>
    <?php if (!empty($availableWarning)) { ?>
        <div class="warning" id="warning"><?= $availableWarning ?></div>
    <?php } ?>
    <div class="clearfix"></div>
</div>
<div class="main_content"></div>
</body>
</html>