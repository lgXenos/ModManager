/* global G */

var myGitMod = {
	// инициализируем "приложение"
	init: function () {
		var self = this;
		self._binds();
	},
	// добавляем в "консоль" инфу
	_appendConsoleAnswer: function (res) {
		if(!res.success){
			res.success = ['ERROR$ ' + res.error.message];
		}
		
		var tpl = $('<div>').css({display:'none'});
		for(var _t in res.success){
			var line = res.success[_t]+'';
			line = line.replace(/</g,'&lt;');
			line = line.replace(/>/g,'&gt;');
			tpl.append(line + '<br>');
		}
		var currDate = new Date;
		currDate = '<b>' + currDate.toString() + '</b><br>'
		tpl.append('<br class="hr">').prepend( currDate );
		
		$('.js_myConsole').prepend(tpl);
		
		setTimeout(function(){
			tpl.show(500);
		}, 0);
		
	},
	// слушатели
	_binds: function () {
		var self = this;
		$(document)
				.on("click", ".js_git_commit", function () {
					var ansv = prompt("input comment to commit -am \'your_comment\':", "autoCommit");
					if (ansv) {
						var fn = function (res) {
							self._appendConsoleAnswer(res);
						};
						self._aj({_do: 'commit', text: ansv}, fn);
					}
					return false;
				});
	},
	// аякса
	_aj: function (data, fn, type) {
		$.ajax({
			type: "POST",
			url: G.moduleUrl,
			data: data,
			dataType: "json",
			timeout: 15000,
			success: function (result) {
				if (typeof (fn) == 'function')
					fn(result);
			}
		});
	}
};

$(document).ready(function () {
	myGitMod.init();
})
