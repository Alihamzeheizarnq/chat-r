<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>Laravel</title>
</head>
<body class="antialiased">

<button onclick="send()">send request</button>
<script>
    var ws    = new WebSocket("ws://127.0.0.1:5200/ws?p=1");
    ws.onopen = function () {
        console.log("Message is sent...");
    };

    function on(event, callback) {
        ws.onmessage = function (evt) {
            let data = JSON.parse(evt.data);

            if (data.event === event) {
                callback(data[0]);
            }

        };
    }

    on('order.1', function (evt) {
        console.log(evt)
    })


    ws.onclose   = function () {
        console.log(ws)
    };


    function send() {
        ws.send(JSON.stringify([
            'message', {
                id: 1
            }
        ]))
    }

    //
    // window.onoffline = (event) => {
    //     ws.send(JSON.stringify([
    //         'message', {
    //             id: 11231312312
    //         }
    //     ]))
    // };
</script>
<script type="module">
    console.log(Echo)
</script>
</body>
</html>
