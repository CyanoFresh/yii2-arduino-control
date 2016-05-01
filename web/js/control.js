var WS;
var WS_opened;
var WebSocketURL;

var LEDState;

const $loader = $('#loader');
const $loaderError = $('.loader-error-text');
const $loaderSpinner = $('.loader-spinner');
const $content = $('#content');
const $ledBtn = $('body').find('[data-type="led"]');

function connect() {
    WS = new WebSocket(WebSocketURL);

    WS.onopen = function () {
        WS_opened = true;

        $loader.fadeOut(function () {
            $content.fadeIn();
        });
    };
    WS.onclose = function () {
        if (WS_opened) {
            $loaderError.html('Disconnected from the server');

            $content.fadeOut(function () {
                $loader.fadeIn(function () {
                    $loaderSpinner.fadeOut(function () {
                        $loaderError.fadeIn();
                    })
                });
            });
        } else {
            $loaderError.html('Cannot connect: <br>No response from the server');
        }

        WS = null;
    };
    WS.onerror = function () {
        $loaderError.html('Error happened');

        $content.fadeOut(function () {
            $loader.fadeIn(function () {
                $loaderSpinner.fadeOut(function () {
                    $loaderError.fadeIn();
                })
            });
        });
    };
    WS.onmessage = onMessage;
}

function onMessage(response) {
    try {
        var data = JSON.parse(response.data);

        log(data);

        switch (data.type) {
            case 'welcome':
                setLedState(data.led);
                break;
            case 'led':
                setLedState(data.on);
                break;
        }
    } catch (e) {
        console.log(e);
    }
}

function log(msg) {
    console.log(msg);
}

function send(msg) {
    if (typeof msg != "string") {
        msg = JSON.stringify(msg);
    }

    log('Sending:' + msg);

    if (WS && WS.readyState == 1) {
        WS.send(msg);
    }
}

function setLedState(state) {
    LEDState = state;

    if (state) {
        if ($ledBtn.hasClass('btn-danger')) {
            $ledBtn.removeClass('btn-danger');
        }

        $ledBtn.addClass('btn-success');

        $ledBtn.find('span.ledStatus').html('on');
    } else {
        if ($ledBtn.hasClass('btn-success')) {
            $ledBtn.removeClass('btn-success');
        }

        $ledBtn.addClass('btn-danger');

        $ledBtn.find('span.ledStatus').html('off');
    }
}

$(document).ready(function () {
    connect();

    $ledBtn.click(function (e) {
        e.preventDefault();

        send({
            type: 'led'
        });
    });
});