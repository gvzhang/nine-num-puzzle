var drag = {
    horizontal:3,
    vertical:3,
    Pointer: function(x, y) {
        this.x = x;
        this.y = y;
    },
    Position: function(left, top) {
        this.left = left;
        this.top = top;
    },
    verification: function () {
        return false;
    },
    beginDrop: function () {

    },
    bindDrop: function(obj){
        var self = this;
        $(obj).find(".item").each(function (i) {
            this.init = function () {
                // 初始化
                this.box = $(this).parent();
                $(this).attr("index", i).css({
                    position: "absolute",
                    left: this.box.offset().left,
                    top: this.box.offset().top
                }).appendTo(obj);
                this.drag();
            },
            this.move = function (callback) {  // 移动
                $(this).stop(true).animate({
                    left: this.box.offset().left,
                    top: this.box.offset().top
                }, 500, function () {
                    if (callback) {
                        callback.call(this);
                    }
                });
            },
            this.collisionCheck = function () {
                var currentItem = this;
                var currentItemIndex = parseInt($(currentItem).attr("index"));

                var puzzleVertical = self.vertical;
                var nearItems = [currentItemIndex - puzzleVertical, currentItemIndex + 1, currentItemIndex + puzzleVertical, currentItemIndex - 1];
                //修复左右边界BUG（上下边界多余值不会存在）
                //如果是左边界，去除左邻格子
                if (currentItemIndex % puzzleVertical == 0) {
                    nearItems.remove(currentItemIndex - 1);
                }
                //如果是右边界，去除右邻格子
                if ((currentItemIndex + 1) % puzzleVertical == 0) {
                    nearItems.remove(currentItemIndex + 1);
                }

                $(this).siblings(".item").each(function () {
                    //如果操作格子为邻近格子
                    //并且操作格子的位置在当前格子内部
                    var thisItemIndex = parseInt($(this).attr("index"));
                    if (
                        nearItems.indexOf(thisItemIndex) != -1 &&
                        currentItem.pointer.x > this.box.offset().left &&
                        currentItem.pointer.y > this.box.offset().top &&
                        (currentItem.pointer.x < this.box.offset().left + this.box.width()) &&
                        (currentItem.pointer.y < this.box.offset().top + this.box.height())
                    ) {
                        this.swap(currentItem);
                    }
                });
            },
            this.swap = function (currentItem) { // 交换位置
                if (this.moveing) return false;

                var saveBox = this.box;
                this.box = currentItem.box;
                currentItem.box = saveBox;
                this.move();
                $(this).attr("index", this.box.index());
                $(currentItem).attr("index", currentItem.box.index());
            },
            this.drag = function () { // 拖拽
                var oldPosition = new self.Position();
                var oldPointer = new self.Pointer();
                var isDrag = false;
                var currentItem = null;
                $(this).mousedown(function (e) {
                    e.preventDefault();
                    var thisValue = $(this).html();
                    //只有空的格子才能拖动
                    if (IsNum(thisValue) == false) {
                        oldPosition.left = $(this).position().left;
                        oldPosition.top = $(this).position().top;
                        oldPointer.x = e.clientX;
                        oldPointer.y = e.clientY;
                        isDrag = true;
                        currentItem = this;
                        self.beginDrop();
                    }
                });
                $(document).mousemove(function (e) {
                    var currentPointer = new self.Pointer(e.clientX, e.clientY);
                    if (!isDrag) return false;
                    $(currentItem).css({
                        "opacity": "0.8",
                        "z-index": 999
                    });
                    var left = currentPointer.x - oldPointer.x + oldPosition.left;
                    var top = currentPointer.y - oldPointer.y + oldPosition.top;
                    $(currentItem).css({
                        left: left,
                        top: top
                    });
                    currentItem.pointer = currentPointer;
                    // 开始交换位置
                    currentItem.collisionCheck();
                });
                $(document).mouseup(function () {
                    if (!isDrag) return false;
                    isDrag = false;
                    currentItem.move(function () {
                        $(this).css({
                            "opacity": "1",
                            "z-index": 0
                        });
                    });
                    self.verification();
                });
            };
            this.init();
        });
    }
};

(function($){
    $.fn.puzzle = function(options){
        return this.each(function(){

            var self = this;  // 保存组件对象

            //默认设置
            var defaults = {
                data: [],
                verifyCallback: function(){
                    alert("拼图成功");
                }
            };

            var params = $.extend(defaults,options);

            //初始化拼图
            var puzzleArr = params.data;
            var initPuzzle = function () {
                if (params.data) {
                    var html = '';
                    for (var i = 0; i < puzzleArr.length; i++) {
                        for (var j = 0; j < puzzleArr[i].length; j++) {
                            html += '<li><div class="item" data-correct="' + puzzleArr[i][j] + '">' + puzzleArr[i][j] + '</div></li>';
                        }
                    }
                    $(self).append('<ul>' + html + '</ul><div style="clear:both"></div>');
                } else {
                    throw ("puzzle数组值不能为空");
                }
            };
            initPuzzle();

            //验证拼图事件
            var computedFn = function () {
                var result = true;
                $(self).find(".item").each(function () {
                    var correctValue = $(this).attr("data-correct");
                    if(correctValue != "") {
                        var thisIndex = $(this).attr("index");
                        if (parseInt(correctValue) != (parseInt(thisIndex) + 1)) {
                            result = false;
                        }
                    }
                });
                return result;
            };

            //绑定拖拉事件
            drag = $.extend(drag, params);
            drag.vertical = parseInt(drag.vertical);
            drag.horizontal = parseInt(drag.horizontal);
            drag.verification = function () {
                if(computedFn()){
                    params.verifyCallback();
                }
            };
            drag.bindDrop(self);
        }) ;
    } ;
})(jQuery) ;