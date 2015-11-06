/* global G */

var myModManager = {
	// показывает-скрывает окошко с визуальностью прогрузки
	_myLoader: function (operation, isRepeated) {
		var self = this;
		var loader = $('.js_myLoader');

		// пытаемся вставить в бади, если нету
		if (!loader.length) {
			if (isRepeated) {
				console.log('cant insert loader');
				return false;
			}
			$('body').append($('<div>', {class: 'js_myLoaderWrap'}));
			$('.js_myLoaderWrap').append($('<div>', {class: 'js_myLoader'}));
			return self._myLoader(operation, 1);
		}

		// координаты крутилки
		var e = event || {};
		var posX = e.pageX || 20;
		var posY = e.pageX || 20;
		var myCss = {left: posX, top: posY};

		// исполнение
		if (operation) {
			loader.parent().show();
			loader.css(myCss);
		} else {
			loader.parent().hide();
		}
	},
	// аякса
	aj: function (data, fn, type) {
		var self = this;
		self._myLoader(1);
		$.ajax({
			type: "POST",
			url: G.moduleUrl,
			data: data,
			dataType: "json",
			timeout: 150000,
			success: function (result) {
				self._myLoader(0);
				if (typeof (fn) == 'function')
					fn(result);
			},
			error: function (result) {
				self._myLoader(0);
				if (typeof (fn) == 'function')
					fn({error: {message: 'ajax fail'}, response: result.responseText});
			}
		});
	},
	// упрощенная версия роутинга. пока считаем что всегда получаем для самих себя ссылки
	getJSRoute: function (_do, params) {
		var ret = [];

		_do ? ret['_do'] = _do : '';

		params ? ret['params'] = params : '';

		if (ret['_do']) {
			ret = '?' + http_build_query(ret);
		}
		else {
			ret = '';
		}
		var link = G.moduleUrl + ret;

		return link;
	}
}

console.log('modUrl = ', G.moduleUrl);

// Generate URL-encoded query string
function http_build_query(formdata, numeric_prefix, arg_separator) {

	var value, key, tmp = [],
			that = this;

	var _http_build_query_helper = function (key, val, arg_separator) {
		var k, tmp = [];
		if (val === true) {
			val = '1';
		} else if (val === false) {
			val = '0';
		}
		if (val != null) {
			if (typeof val === 'object') {
				for (k in val) {
					if (val[k] != null) {
						tmp.push(_http_build_query_helper(key + '[' + k + ']', val[k], arg_separator));
					}
				}
				return tmp.join(arg_separator);
			} else if (typeof val !== 'function') {
				return that.urlencode(key) + '=' + that.urlencode(val);
			} else {
				throw new Error('There was an error processing for http_build_query().');
			}
		} else {
			return '';
		}
	};

	if (!arg_separator) {
		arg_separator = '&';
	}
	for (key in formdata) {
		value = formdata[key];
		if (numeric_prefix && !isNaN(key)) {
			key = String(numeric_prefix) + key;
		}
		var query = _http_build_query_helper(key, value, arg_separator);
		if (query !== '') {
			tmp.push(query);
		}
	}

	return tmp.join(arg_separator);
}
function urlencode(str) {

	str = (str + '').toString();

	return encodeURIComponent(str)
			.replace(/!/g, '%21')
			.replace(/'/g, '%27')
			.replace(/\(/g, '%28')
			.replace(/\)/g, '%29')
			.replace(/\*/g, '%2A')
			.replace(/%20/g, '+');
}