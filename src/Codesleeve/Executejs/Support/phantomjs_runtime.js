var print = function(message) { console.log(message); };

phantom.onError = function(msg, trace)
{
	console.error(msg);
	phantom.exit(1);
};

phantom.injectJs("{{SCRIPT_PATH}}");