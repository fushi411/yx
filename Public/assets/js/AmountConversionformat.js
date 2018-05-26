

// 插件测试 金额转化固定格式 -- 一个函数只做一件事
function AmountConversionformat($paramArr){
    // 传入JQ对象
    this.input = $paramArr.input;
    this.show_div = $paramArr.show_div;
    this.symbol = $paramArr.symbol;
    this.input_show_bool = $paramArr.input_show_bool;
    this.show_div.css('font-weight','600');
    this.init = function(){ // 初始化函数 ->input 挂载实践
        var $this = this;
        $this.show_div.hide();

        $this.input.on('blur',function(e){
            var val  = e.target.value,
                func = $this.isEmpty(val);
            func ? $this.noEmptyDo(val) : $this.isEmptyDo(val);
        });

        $this.show_div.on('click',function(){
            $this.input.show();
            $this.input.focus();
            $this.show_div.hide();
        });
    }

    // 判断为空的情况
    this.isEmpty = function(value){
        return value == '' ? 0 : 1 ;
    }

    // 值为空的情况
    this.isEmptyDo = function(value){
        
        this.show_div.html('');
        this.input.show();
        this.show_div.hide();
    }

    // 值不为空的情况
    this.noEmptyDo = function(value){
        var num = this.formatMoney(value,2);
        this.input.hide();
        this.show_div.show();
        this.show_div.html(this.symbol+num);
    }

    // 金钱格式转化
    this.formatMoney = function(number, places, symbol, thousand, decimal) {
        number = number || 0;
        places = !isNaN(places = Math.abs(places)) ? places : 2;
        symbol = symbol !== undefined ? symbol : "$";
        thousand = thousand || ",";
        decimal = decimal || ".";
        var negative = number < 0 ? "-" : "",
            i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return  negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
    }
} 