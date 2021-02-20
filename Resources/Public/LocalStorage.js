function disableTracking() {
    localStorage.plausible_ignore = true;
}

function enableTracking() {
    delete localStorage.plausible_ignore;
}

function trackingIsDisabled() {
    return localStorage.plausible_ignore === "true";
}

function disableAndForward() {
    var timeout = trackingIsDisabled() ? 0 : 5000;
    disableTracking();
    setTimeout(function () {
        window.location = "/";
    }, timeout);
}
