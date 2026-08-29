import React, { useState, useEffect, useMemo } from "react";
import { Button, Icon } from "@neos-project/react-ui-components";
import { neos } from "@neos-project/neos-ui-decorators";
import Dialog from "carbon-neos-editor-styling/Dialog";
import { selectors } from "@neos-project/neos-ui-redux-store";
import backend from "@neos-project/neos-ui-backend-connector";
import { connect } from "react-redux";

function StatisticView({ label, focusedNodePath }) {
    const [open, setOpen] = useState(false);
    const [sharedLink, setSharedLink] = useState(null);
    const [iframeSrc, setIframeSrc] = useState(null);

    useMemo(async () => {
        const { uri } = await backend
            .get()
            .endpoints.dataSource("carbon-plausible-statsview", null, {
                node: focusedNodePath,
            });
        if (uri) {
            setSharedLink(uri);
        }
    }, []);

    useEffect(() => {
        if (!sharedLink) {
            return;
        }
        const binder = sharedLink.includes("?") ? "&" : "?";
        setIframeSrc(
            `${sharedLink}${binder}embed=true&theme=dark&background=transparent`,
        );
    }, [sharedLink]);

    if (!sharedLink) {
        return null;
    }

    return (
        <>
            <Button style="lighter" onClick={() => setOpen(true)} title={label}>
                <Icon icon="chart-pie" padded="right" />
                <span>Plausible</span>
            </Button>
            <Dialog
                open={open}
                setOpen={setOpen}
                fullHeight
                showCloseButton
                style={{ maxWidth: 1400, width: "var(--dialog-max-width)" }}
            >
                {open && (
                    <>
                        <iframe
                            plausible-embed="true"
                            src={iframeSrc}
                            scrolling="no"
                            frameBorder="0"
                            loading="lazy"
                            style={{
                                height: 1600,
                                width: 1,
                                minWidth: "100%",
                                display: "block",
                                margin: "var(--spacing-GoldenUnit) auto",
                            }}
                        ></iframe>
                        <script
                            async
                            src="https://plausible.io/js/embed.host.js"
                        ></script>
                    </>
                )}
            </Dialog>
        </>
    );
}

/*


                        */

const neosifier = neos((globalRegistry) => ({
    label: globalRegistry
        .get("i18n")
        .translate(`Carbon.Plausible:Main:openEmbedStats`),
}));

const connector = connect((state) => ({
    focusedNodePath: selectors.CR.Nodes.focusedNodePathSelector(state),
}));

export default neosifier(connector(StatisticView));
