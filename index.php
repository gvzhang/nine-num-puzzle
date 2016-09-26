<?php
    set_time_limit(0);

    require("lib/puzzle.php");
    $horizontal = 3;
    $vertical = 3;
    $puzzle = new Puzzle($horizontal, $vertical);
    $puzzleArr = $puzzle->getPuzzle();
    $solutionPath = [];
    $availableWarning = "无解八数组";
    if($puzzle->hasSolution()){
        $availableWarning = "";
        $solutionPath = $puzzle->computeSolution();
    }
    echo $availableWarning."<br />";
    var_dump($solutionPath);exit;
    $puzzleArr = json_encode($puzzleArr);
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
        var puzzle_data = JSON.parse('<?=$puzzleArr?>');
        var horizontal = "<?=$horizontal?>";
        var vertical = "<?=$vertical?>";
    </script>
    <script type="text/javascript" src="js/public.js"></script>
</head>
<body>
<div class="spend_timer">
    <div class="timer_show">
        耗时：<span class="num" id="timer_show">000:00:0</span>
    </div>
    <div class="step_number">
        步数：<span id="step_number">0</span>
    </div>
    <div class="warning" id="warning"><?=$availableWarning?></div>
    <div class="clearfix"></div>
</div>
<div class="main_content"></div>
</body>
</html>