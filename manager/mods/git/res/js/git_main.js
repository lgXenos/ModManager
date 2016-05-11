/* global G, myModManager */

var myGitMod = {
	// где консолька
	tplConsole: $('.js_myConsole'),
	// ее размер
	tplConsoleHeight: 220,
	//
	// инициализируем "приложение"
	init: function () {
		var self = this;
		// фикс консольки
		self.tplConsole.css({'min-height': self.tplConsoleHeight});
		self.tplConsole.parent().css({height: self.tplConsoleHeight});
		self._scroolConsoleToBottom();
		// прослушки
		self._binds();
		// фикс урла чтоб по Ф5 не отправлялось
		history.pushState(null, null, G.moduleUrl);
	},
	// добавляем в "консоль" инфу
	_appendConsoleAnswer: function (res) {
		var self = this;
		var tpl = self.tplConsole;

		if (!res) {
			tpl.html('');
			return;
		}

		if (!res.success) {
			var errMsg = 'unknow error';
			if (res.error) {
				if (res.error.message)
					errMsg = ['ERROR$ ' + res.error.message];
			}
			if (res.response) {
				errMsg = ['ERROR$ ' + res.response];
			}
			res.success = errMsg;
		}

		var currDate = new Date;
		currDate = '<b>' + currDate.toString() + '</b><br>';
		tpl.append('<p class="hr"></p>').append(currDate);

		for (var _t in res.success) {
			var line = res.success[_t] + '';
			line = line.replace(/</g, '&lt;');
			line = line.replace(/>/g, '&gt;');
			// если это команда
			if (line.indexOf('~$') == 0) {
				line = '<b>' + line + '</b>';
			}
			// если это ошибка
			if (line.indexOf('ERROR$') == 0) {
				line = '<font color="#faa">' + line + '</font>';
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
	// добавление попап-меню в элемент
	_addPopupActionMenu: function (that, isRepeated) {
		// 
		var self = this;

		var branchName = that.attr('data-name');
		if (branchName == '') {
			return;
		}

		var branchType = that.attr('data-type');
		var popupClass = 'popupMenu';
		var menuBlock = that.find('.' + popupClass);
		if (!menuBlock.length) {
			that.append($('<ul>', {class: popupClass}));
		}
		setTimeout(function () {
			menuBlock = that.find('.' + popupClass);
			// не перезаписываем менюшки
			if (menuBlock.html()) {
				return;
			}
			switch (branchType) {
				case'local':
				{
					self._appendLocalActionsToPopup(menuBlock, branchName);
					break;
				}
				case'remote':
				{
					self._appendRemoteActionsToPopup(menuBlock, branchName);
					break;
				}
				default:
				{
					menuBlock.append('unknown type "' + branchType + '"');
				}
			}
		}, 0);
	},
	// создать и получить ноду из параметров
	__getNodeFromParams: function (params) {
		var tag = $('<' + (params.tag || 'div') + '>');
		// есть ли класс
		if (params.class) {
			tag.addClass(params.class);
		}
		// есть ли класс
		if (params.html) {
			tag.html(params.html);
		}
		// есть ли data-аттрибуты
		if (params.data) {
			for (var index in params.data) {
				var value = params.data[index];
				tag.attr('data-' + index, value);
			}
		}
		// есть ли обычные аттрибуты
		if (params.attr) {
			tag.attr(params.attr);
		}

		if (params.coverTag) {
			var cover = $('<' + params.coverTag + '>');
			cover.append(tag);
			return cover;
		}

		return tag;
	},
	// выдает куда=то возникшую ошибку
	_outError: function (txt) {
		this._appendConsoleAnswer({error: {message: txt}});
	},
	// набьем пунктов в меню local-ветки
	_appendLocalActionsToPopup: function (menuBlock, branchName) {
		var self = this;

		// git checkout {...}
		menuBlock.append(
				self.__getNodeFromParams({
					tag: 'a',
					coverTag: 'li',
					html: 'checkout ',
					attr: {href: myModManager.getJSRoute('checkout', {branch_name: branchName})},
					data: {method: 'checkout', name: branchName}
				})
				);

		// git branch -D {...}
		menuBlock.append(
				self.__getNodeFromParams({
					tag: 'a',
					coverTag: 'li',
					class: 'js_promptBefore',
					html: 'branch -D[elete] ',
					attr: {href: myModManager.getJSRoute('delete_local', {branch_name: branchName})},
					data: {method: 'checkout', name: branchName}
				})
				);

		// git merge {...}
		menuBlock.append(
				self.__getNodeFromParams({
					tag: 'li',
					class: 'js_magicButton',
					html: 'merge from ' + branchName,
					data: {method: 'merge_local', name: branchName}
				})
				);
	},
	// набьем пунктов в меню remote-ветки
	_appendRemoteActionsToPopup: function (menuBlock, branchName) {
		var self = this;

		// git pull origin {...}
		menuBlock.append(
				self.__getNodeFromParams({
					tag: 'li',
					class: 'js_magicButton',
					html: 'pull origin ',
					data: {method: 'pull', name: branchName}
				})
				);

		// git push origin :{...}
		menuBlock.append(
				self.__getNodeFromParams({
					tag: 'a',
					class: 'js_show_loading js_promptBefore',
					coverTag: 'li',
					html: '[delete] push origin :...',
					attr: {href: myModManager.getJSRoute('delete_remote', {branch_name: branchName})},
					data: {method: 'checkout', name: branchName}})
				);
	},
	// слушатели
	_binds: function () {
		var self = this;
		$(document)
				// кнопка коммита текущих правок
				.on("click", ".js_git_commit", function () {
					var ansv = prompt("input comment to commit -am \'your_comment\':", "autoCommit");
					if (ansv) {
						var fn = function (res) {
							self._appendConsoleAnswer(res);
						};
						myModManager.aj({_do: 'commit', text: ansv}, fn);
					}
					return false;
				})
				// кнопка добавления новой ветки
				.on("click", ".js_git_add_branch", function () {
					var ansv = prompt("input branch-name: branch -b {имя_ветки}:", "master");
					if (ansv) {
						var fn = function (res) {
							self._appendConsoleAnswer(res);
						};
						window.location = myModManager.getJSRoute('add_branch', {text: ansv});
					}
					return false;
				})
				// долгие операции имеют класс псевдо-загрузки
				.on("click", ".js_show_loading", function () {
					myModManager._myLoader(1);
				})
				// с пасхой
				.on("dblclick", ".etcMenuButton", function () {
					self._appendConsoleAnswer();
				})
				// обработка "волшебных" кнопок попапов
				.on("click", ".js_magicButton", function () {
					var t = $(this);
					var method = t.attr('data-method');
					var branchName = t.attr('data-name');
					if (!branchName) {
						self._outError('no valid branch name');
					}
					var fn = function (res) {
						self._appendConsoleAnswer(res);
					};
					myModManager.aj({_do: method, branch_name: branchName}, fn);
					return false;
				})
				// долгие операции имеют класс псевдо-загрузки
				.on("mouseover", ".js_showBranchActionsPopup", function () {
					self._addPopupActionMenu($(this));
				})
				// долгие операции имеют класс псевдо-загрузки
				.on("change", ".js_repsChng", function () {
					var that = $(this);
					var url = myModManager.getJSRoute('change_rep', {rep_name: that.val()});
					window.location = url;
				})
				// берем пользовательскую задачу к себе в локалку
				.on("click", ".js_getUserFeature", function () {
					var ansv = prompt("Input feature name:", '1319_css');
					if (ansv) {
						var fn = function (res) {
							self._appendConsoleAnswer(res);
						};
						myModManager.aj({_do: 'get_user_feature', feature_name: ansv}, fn);
					}
					return false;
				})
				// переливаем текущую задачу в девелоп
				.on("click", ".js_acceptUserFeature", function () {
					var fn = function (res) {
						self._appendConsoleAnswer(res);
					};
					myModManager.aj({_do: 'accept_user_feature'}, fn);
				});

	}
};

$(document).ready(function () {
	myGitMod.init();
});
