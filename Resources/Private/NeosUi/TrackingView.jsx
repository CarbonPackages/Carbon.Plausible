import React, { useState, useEffect, useMemo } from "react";
import { neos } from "@neos-project/neos-ui-decorators";
import { Button } from "@neos-project/react-ui-components";

import {
    trackingIsDisabled,
    enableTracking,
    disableTracking,
} from "../Assets/Helper.ts";

function TrackingView({ i18nRegistry }) {
    const [trackingEnabled, setTrackingEnabled] =
        useState(!trackingIsDisabled());

    // Translate labels
    const label = useMemo(() => {
        const labels = {};
        [
            "enableTracking",
            "disableTracking",
            "trackingIsDisabled",
            "trackingIsEnabled",
        ].forEach((key) => {
            labels[key] = i18nRegistry.translate(
                `Carbon.Plausible:Main:${key}`,
            );
        });
        return labels;
    }, []);

    useEffect(() => {
        if (trackingEnabled) {
            enableTracking();
            return;
        }
        disableTracking();
    }, [trackingEnabled]);

    return (
        <Button
            style="neutral"
            hoverStyle="brand"
            title={
                trackingEnabled
                    ? label.trackingIsEnabled
                    : label.trackingIsDisabled
            }
            onClick={() => {
                setTrackingEnabled((prev) => !prev);
            }}
        >
            {trackingEnabled ? label.disableTracking : label.enableTracking}
        </Button>
    );
}

const neosifier = neos((globalRegistry) => ({
    i18nRegistry: globalRegistry.get("i18n"),
}));

export default neosifier(TrackingView);
