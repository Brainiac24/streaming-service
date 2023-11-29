window.Example = {
    token: window.localStorage.example_token || 0,
    widgetId: !1,
    loading: !1,
    init: function(e) {
        e = { uuid: this.uuid, token: this.token, session: e.session, type: e.type || "event", element: e.element || !1 };
        var t = this,
            n = '<div id="example_' + e.type + "_" + e.session + '"><iframe id="example_iframe_' + e.type + "_" + e.session + '" width="100%" height="100%" frameborder="0"></iframe></div>';
        e.element ? (document.getElementById(e.element).innerHTML = n) : document.write(n);
        var s = new XMLHttpRequest();
        s.open("GET", "https://ion.example.com/api/v1/auth/register/guest?token=" + e.token + "&session=" + e.session + "&type=" + e.type),
            (s.onload = function() {
                var n;
                200 === s.status ?
                    ((n = JSON.parse(s.responseText)),
                        (t.token = window.localStorage.example_token = n.data.token),
                        (document.getElementById("example_iframe_" + e.type + "_" + e.session).src = n.data.url)) :
                    console.log("Request failed.  Returned status of " + s.status),
                    (t.loading = !1);
            }),
            this.loading ?
            setTimeout(function() {
                s.send();
            }, 100) :
            (s.send(), (this.loading = !0));
    },
};