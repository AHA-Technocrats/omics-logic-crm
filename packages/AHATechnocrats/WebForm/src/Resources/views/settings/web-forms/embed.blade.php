(function () {
    var src = @json(route('admin.settings.web_forms.preview', $webForm->form_id));
    var title = @json(strip_tags((string) ($webForm->title ?? 'Web Form')));
    var script = document.currentScript;
    var iframe = document.createElement('iframe');

    iframe.src = src;
    iframe.title = title;
    iframe.loading = 'lazy';
    iframe.style.cssText = 'display:block;width:100%;max-width:100%;min-height:720px;height:720px;border:0;';
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowtransparency', 'true');
    iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');

    if (script && script.parentNode) {
        script.parentNode.insertBefore(iframe, script);
    } else if (document.body) {
        document.body.appendChild(iframe);
    } else {
        document.write(iframe.outerHTML);
    }
})();
