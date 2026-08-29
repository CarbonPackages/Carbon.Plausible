import { trackingIsDisabled, disableTracking } from "./Helper";

disableAndForward();

export function disableAndForward() {
    const timeout = trackingIsDisabled() ? 0 : 5000;
    disableTracking();
    setTimeout(() => {
        window.location = "/";
    }, timeout);
}
