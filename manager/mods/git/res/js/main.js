/* global G */

var myGitMod = {
	// где консолька
	tplConsole: $('.js_myConsole'),
	// ее размер
	tplConsoleHeight: 170,
	//
	// инициализируем "приложение"
	init: function () {
		var self = this;
		// фикс консольки
		self.tplConsole.css({'min-height': self.tplConsoleHeight});
		self.tplConsole.parent().css({height:self.tplConsoleHeight});
		self._scroolConsoleToBottom();
		// прослушки
		self._binds();
	},
	// добавляем в "консоль" инфу
	_appendConsoleAnswer: function (res) {
		var self = this;

		if (!res.success) {
			res.success = ['ERROR$ ' + res.error.message];
		}

		//var tpl = $('<div>');
		var tpl = self.tplConsole;

		var currDate = new Date;
		currDate = '<b>' + currDate.toString() + '</b><br>'
		tpl.append('<p class="hr"></p>').append(currDate);

		for (var _t in res.success) {
			var line = res.success[_t] + '';
			line = line.replace(/</g, '&lt;');
			line = line.replace(/>/g, '&gt;');
			// если это команда
			if (line.indexOf('~$') == 0) {
				line = '<b>' + line + '</b>';
			}
			tpl.append(line + '<br>');
		}

		//$('.js_myConsole').append(tpl);

		// var e = $('.js_myConsole').parent(); e.animate({scrollTop: '0px' }, '500', 'swing');
		setTimeout(function () {
			self._scroolConsoleToBottom();
		}, 0);

	},
	// прокрутка окна консоли вниз
	_scroolConsoleToBottom: function () {
		var self = this;
		var tpl = self.tplConsole;
		var e = tpl.parent();
		var scrollTo = tpl[0].offsetHeight;
		e.animate({scrollTop: scrollTo}, '500', 'swing');

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
