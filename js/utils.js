/**
 * Created by ASUS on 2016/9/24.
 */

/**
 * 判断是否是正整数
 * @param num
 * @returns {boolean}
 * @constructor
 */
function IsNum(num) {
    if (!(/(^[1-9]\d*$)/.test(num))) {
        return false;
    } else {
        return true;
    }
}

/**
 * 删除数组指定的某个元素
 * @param val
 */
Array.prototype.remove = function (val) {
    var index = this.indexOf(val);
    if (index > -1) {
        this.splice(index, 1);
    }
};

/**
 * 位数补零
 * @param num
 * @param size
 * @returns {string}
 */
function preZeroFill(num, size) {
    if (num >= Math.pow(10, size)) { //如果num本身位数不小于size位
        return num.toString();
    } else {
        var _str = Array(size + 1).join('0') + num;
        return _str.slice(_str.length - size);
    }
}

/**
 * 格式化时间
 * @param timeValue
 * @returns {{minute: number, second: number, miliSecond: number}}
 */
function parseTime(timeValue) {
    var secondAll = Math.floor(timeValue / 1000);
    var minute = Math.floor(secondAll / 60);
    var second = Math.floor(secondAll - minute * 60);
    var miliSecond = Math.floor((timeValue - secondAll * 1000) / 100);
    return {
        minute: minute,
        second: second,
        miliSecond: miliSecond
    }
}