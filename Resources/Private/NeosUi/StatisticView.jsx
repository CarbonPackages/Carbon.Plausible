import React, { useState, useEffect, useMemo } from "react";
import { Button, Icon } from "@neos-project/react-ui-components";
import { neos } from "@neos-project/neos-ui-decorators";
import Dialog from "carbon-neos-editor-styling/Dialog";
import { selectors } from "@neos-project/neos-ui-redux-store";
import backend from "@neos-project/neos-ui-backend-connector";
import { connect } from "react-redux";
import * as stylex from "@stylexjs/stylex";

const styles = stylex.create({
    center: {
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        margin: "var(--spacing-GoldenUnit)",
        gap: "var(--spacing-Full)",
    },
    iframe: {
        height: 1600,
        width: 1,
        minWidth: "100%",
        display: "block",
        margin: "var(--spacing-GoldenUnit) auto",
    },
});

function StatisticView({ labels, focusedNodePath }) {
    const [open, setOpen] = useState(false);
    const [sharedLink, setSharedLink] = useState(null);
    const [iframeSrc, setIframeSrc] = useState(null);
    const [checked, setChecked] = useState(true);

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

    const checkPlausible = (embedUrl) => {
        fetch(embedUrl)
            .then((response) => {
                if (response.ok) {
                    setChecked(true);
                    return;
                }
                setChecked(false);
            })
            .catch(() => {
                setChecked(false);
            });
    };

    useEffect(() => {
        if (!sharedLink) {
            return;
        }
        const binder = sharedLink.includes("?") ? "&" : "?";
        setIframeSrc(
            `${sharedLink}${binder}embed=true&theme=dark&background=transparent`,
        );
        checkPlausible(sharedLink);
    }, [sharedLink]);

    if (!sharedLink) {
        return null;
    }

    return (
        <>
            <Button
                style="lighter"
                onClick={() => setOpen(true)}
                title={labels.openEmbedStats}
            >
                <Icon icon="chart-pie" padded="right" />
                <span>Plausible</span>
            </Button>
            <Dialog
                open={open}
                setOpen={setOpen}
                showCloseButton
                style={{
                    maxWidth: 1400,
                    width: checked ? "var(--dialog-max-width)" : null,
                }}
            >
                {open && checked && (
                    <>
                        <iframe
                            plausible-embed="true"
                            src={iframeSrc}
                            scrolling="no"
                            frameBorder="0"
                            loading="lazy"
                            {...stylex.props(styles.iframe)}
                        ></iframe>
                        <script
                            async
                            src="https://plausible.io/js/embed.host.js"
                        ></script>
                    </>
                )}
                {open && !checked && (
                    <div {...stylex.props(styles.center)}>
                        <p>{labels.blockedIframe}</p>
                        <Button
                            style="brand"
                            onClick={() => openPopup(sharedLink)}
                        >
                            <Icon icon="chart-pie" padded="right" />
                            <span>{labels.openEmbedStatsInNewWindow}</span>
                        </Button>
                    </div>
                )}
            </Dialog>
        </>
    );
}

function openPopup(href) {
    const width = Math.min(window.innerWidth, 1400);
    const height = Math.min(window.innerHeight, 1600);
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    window.open(
        href,
        "_blank",
        `noopener=yes,directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=yes,width=${width},height=${height},left=${left},top=${top}`,
    );
}

const neosifier = neos((globalRegistry) => {
    const labels = {};
    ["openEmbedStats", "blockedIframe", "openEmbedStatsInNewWindow"].forEach(
        (key) => {
            labels[key] = globalRegistry
                .get("i18n")
                .translate(`Carbon.Plausible:Main:${key}`);
        },
    );
    return { labels };
});

const connector = connect((state) => ({
    focusedNodePath: selectors.CR.Nodes.focusedNodePathSelector(state),
}));

export default neosifier(connector(StatisticView));
