import domReady from "@wordpress/dom-ready";

function debounce(fn, wait = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
    };
}

domReady(() => {
    const reblock = document.querySelector("#reblock-" + reblock_obj.postId);
    let lastHeight = Math.round(document.documentElement.scrollHeight);
    const ro = new ResizeObserver(([entry]) => {
        const h = Math.round(entry.contentRect.height);
        if (h === lastHeight) return;
        lastHeight = h;
        postHeight(h);
    });

    const postHeight = debounce((newHeight) => {
        window.parent.postMessage({ type: "reblock", id: reblock_obj.postId, height: newHeight }, "*");
    }, 100);

    if (reblock) {
        postHeight(lastHeight);
        ro.observe(document.documentElement);
    }

    window.addEventListener("message", (event) => {
        const msg = event.data;
        if (msg?.type !== "reblock" || !msg.id) return;

        const iframe = document.querySelector(`iframe[data-reblock='${msg.id}']`);
        if (iframe) iframe.style.height = `${msg.height}px`;
    });

    window.addEventListener("beforeunload", () => {
        ro.disconnect();
    });
});
