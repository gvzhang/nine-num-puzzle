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