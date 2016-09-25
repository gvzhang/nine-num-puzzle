<?php
    require("puzzle.php");
    $horizontal = 3;
    $vertical = 3;
    $puzzle = new Puzzle($horizontal, $vertical);
    $puzzleArr = json_encode($puzzle->getPuzzleArr());
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>八数码拼盘</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="js/jq.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/puzzle.js"></script>
    <script type="text/javascript">
        $(function () {
            //将PHP传来的字符串还原成数组
            var puzzle_data = JSON.parse('<?=$puzzleArr?>');

            var beginDropFn = function () {
                console.log("开始拼图");
            };

            var verifyCallbackFn = function () {
                alert("拼图成功");
            };

            $(".main_content").puzzle({
                data: puzzle_data,
                horizontal: "<?=$horizontal?>",
                vertical: "<?=$vertical?>",
                beginDrop: beginDropFn,
                verifyCallback: verifyCallbackFn
            });
        });
    </script>
</head>
<body>
<div class="main_content"></div>
</body>
</html>