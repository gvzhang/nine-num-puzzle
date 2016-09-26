/**
 * Created by ASUS on 2016/9/26.
 */
$(function () {
    var isComplete = false;
    var timeBegin;
    var refreshInterval;
    var totalStepNum = 0;

    var refreshTimer = function () {
        var now = new Date().getTime();
        var timeAll = now - timeBegin;
        var formaterTime = parseTime(timeAll);
        $("#timer_show").text(preZeroFill(formaterTime.minute, 3) + ":" + preZeroFill(formaterTime.second, 2) + ":" + formaterTime.miliSecond);
    };

    //将PHP传来的字符串还原成数组
    var beginDropFn = function () {
        if(isComplete === false) {
            if (timeBegin == null) {
                timeBegin = new Date().getTime();
            }
            refreshInterval = setInterval(refreshTimer, 100);
        }
    };

    //交换位置结束后
    var endSwapFn = function () {
        if(isComplete === false) {
            $("#step_number").text(++totalStepNum);
        }
    };

    var verifyCallbackFn = function () {
        isComplete = true;
        alert("八数组排序成功");
        clearInterval(refreshInterval);
    };

    //初始化拼图
    $(".main_content").puzzle({
        data: puzzle_data,
        horizontal: horizontal,
        vertical: vertical,
        beginDrop: beginDropFn,
        endSwap: endSwapFn,
        verifyCallback: verifyCallbackFn
    });
});