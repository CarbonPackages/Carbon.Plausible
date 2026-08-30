import {
    trackingIsDisabled,
    disableTracking,
    replaceDomainPlaceholders,
} from "./Helper";

const timeout = trackingIsDisabled() ? 0 : 5000;
setTimeout(() => {
    window.location = "/";
}, timeout);

disableTracking();
replaceDomainPlaceholders();
