$(document).ready(function () {
	$(document)
			.on("click", ".js_git_commit", function () {
				var ansv = prompt("input comment to commit -am \'your_comment\':", "autoCommit");
				if (ansv) {
					var url = moduleUrl + '?_do=commit';
					_aj(url, {text: ansv}, viewConsoleAnswer);
				}
				return false;
			})

	function _aj(url, data, fn, type) {
		$.ajax({
			type: "POST",
			url: url,
			data: data,
			dataType: "json",
			timeout: 15000,
			success: function (result) {
				if (typeof (fn) == 'function')
						fn(result);
			}
		})
	}

	function viewConsoleAnswer(ret) {
		console.log(ret);
	}
})
