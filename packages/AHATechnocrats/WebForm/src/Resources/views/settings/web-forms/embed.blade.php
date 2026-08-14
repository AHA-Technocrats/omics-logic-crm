(function () {
    var src = @json(route('admin.settings.web_forms.preview', $webForm->form_id));
    var title = @json(strip_tags((string) ($webForm->title ?? 'Web Form')));
    var script = document.currentScript;
    var iframe = document.createElement('iframe');

    // Derived from the iframe URL rather than APP_URL, which may be misconfigured.
    var expectedOrigin = (function () {
        try {
            return new URL(src, window.location.href).origin;
        } catch (error) {
            return null;
        }
    })();

    iframe.src = src;
    iframe.title = title;
    iframe.loading = 'lazy';
    iframe.style.cssText = 'display:block;width:100%;max-width:100%;height:600px;border:0;';
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowtransparency', 'true');
    iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');

    // Grow/shrink the iframe to match the form's real height so the parent
    // page never shows dead space below it or clips a tall form.
    window.addEventListener('message', function (event) {
        if (event.source !== iframe.contentWindow) {
            return;
        }

        if (expectedOrigin && event.origin !== expectedOrigin) {
            return;
        }

        var data = event.data || {};

        if (data.type === 'omicslogic-webform-height' && data.height > 0) {
            iframe.style.height = data.height + 'px';
        }
    });

    if (script && script.parentNode) {
        script.parentNode.insertBefore(iframe, script);
    } else if (document.body) {
        document.body.appendChild(iframe);
    } else {
        document.write(iframe.outerHTML);
    }
})();
