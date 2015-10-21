var connections = 0; // count active connections

self.addEventListener("connect", function (e) {

	var port = e.ports[0];
	connections++;

	var eventReceived=JSON.stringify(e);
	port.addEventListener("message", function (e) {
		port.postMessage("Hello " + e.data + " (port #" + connections + ")\nData: "+eventReceived);
	}, false);

	port.start();
}, false);