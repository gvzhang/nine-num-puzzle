var operationBox = null;

var drag = {
    horizontal: 3,
    vertical: 3,
    Pointer: function (x, y) {
        this.x = x;
        this.y = y;
    },
    Position: function (left, top) {
        this.left = left;
        this.top = top;
    },
    verification: function () {
        return false;
    },
    beginDrop: function () {

    },
    endSwap: function () {

    },
    bindDrop: function (obj) {
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

                //操作单元格才允许绑定拖动事件
                var data_correct = $(this).attr("data-correct");
                if (data_correct == "") {
                    operationBox = this;
                    this.drag();
                }
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

                    self.endSwap();
                },
                this.drag = function () { // 拖拽
                    var oldPosition = new self.Position();
                    var oldPointer = new self.Pointer();
                    var isDrag = false;
                    var currentItem = null;
                    $(this).mousedown(function (e) {
                        e.preventDefault();
                        oldPosition.left = $(this).position().left;
                        oldPosition.top = $(this).position().top;
                        oldPointer.x = e.clientX;
                        oldPointer.y = e.clientY;
                        isDrag = true;
                        currentItem = this;
                        self.beginDrop();
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

(function ($) {
    $.fn.puzzle = function (options) {
        return this.each(function () {

            var self = this;  // 保存组件对象
            var OPERATION_UP = 0;
            var OPERATION_DOWN = 1;
            var OPERATION_LEFT = 2;
            var OPERATION_RIGHT = 3;

            //默认设置
            var defaults = {
                data: [],
                solutionPath: [],
                isAuto: false,
                verifyCallback: function () {
                    alert("拼图成功");
                }
            };

            var params = $.extend(defaults, options);

            //初始化拼图
            var puzzleArr = params.data;
            this.initPuzzle = function () {
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
            self.initPuzzle();

            //验证拼图事件
            this.computedFn = function () {
                var result = true;
                $(self).find(".item").each(function () {
                    var correctValue = $(this).attr("data-correct");
                    if (correctValue != "") {
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
            drag.beginDrop = params.begin;
            drag.verification = function () {
                if (self.computedFn()) {
                    if($.isFunction(params.verifyCallback)) {
                        params.verifyCallback();
                    }
                }
            };
            drag.bindDrop(self);

            //自动完成拼图配置
            if (params.isAuto && params.solutionPath) {
                var auto = {
                    queue: [],
                    up: function () {
                        this.queue.push("top");
                    },
                    down: function () {
                        this.queue.push("down");
                    },
                    left: function () {
                        this.queue.push("left");
                    },
                    right: function () {
                        this.queue.push("right");
                    },
                    move: function () {
                        var autoSelf = this;
                        if (autoSelf.queue.length > 0) {
                            var operation = autoSelf.queue[0];
                            var animate = null;
                            switch (operation) {
                                case "top":
                                    animate = {top: $(operationBox).position().top - 140 + "px"};
                                    break;
                                case "down":
                                    animate = {top: $(operationBox).position().top + 140 + "px"};
                                    break;
                                case "left":
                                    animate = {left: $(operationBox).position().left - 220 + "px"};
                                    break;
                                case "right":
                                    animate = {left: $(operationBox).position().left + 220 + "px"};
                                    break;
                            }
                            if (animate != null) {
                                $(operationBox).animate(animate, 1000, function () {
                                    operationBox.pointer = new drag.Pointer(operationBox.offsetLeft + 20, operationBox.offsetTop + 20);
                                    // 开始交换位置
                                    operationBox.collisionCheck();
                                    if (self.computedFn()) {
                                        if($.isFunction(params.verifyCallback)) {
                                            params.verifyCallback();
                                        }
                                    } else {
                                        autoSelf.move();
                                    }
                                });
                            }
                            autoSelf.queue.shift();
                        }
                    }
                };

                //记录操作顺序，然后队列执行
                var solutionPath = params.solutionPath;
                for (var i = 0; i < solutionPath.length; i++) {
                    switch (solutionPath[i]) {
                        case OPERATION_UP:
                            auto.up();
                            break;
                        case OPERATION_DOWN:
                            auto.down();
                            break;
                        case OPERATION_LEFT:
                            auto.left();
                            break;
                        case OPERATION_RIGHT:
                            auto.right();
                            break;
                    }
                }
                params.begin();
                auto.move();
            }
        });
    };
})(jQuery);