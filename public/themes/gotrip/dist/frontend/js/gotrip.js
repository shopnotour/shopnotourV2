$.ajaxSetup({
    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
}),
    (window.bravo_format_money = function (e) {
        e = bravo_number_format(
            e / bookingCore.currency_rate,
            bookingCore.booking_decimals,
            bookingCore.decimal_separator,
            bookingCore.thousand_separator
        );
        var t = bookingCore.currency_symbol,
            a = "";
        switch (bookingCore.currency_position) {
            case "right":
                a = e + t;
                break;
            case "left_space":
                a = t + " " + e;
                break;
            case "right_space":
                a = e + " " + t;
                break;
            default:
                a = t + e;
        }
        return a;
    }),
    (window.bravo_number_format = function (e, t, a, o) {
        e = (e + "").replace(/[^0-9+\-Ee.]/g, "");
        var n = isFinite(+e) ? +e : 0,
            r = isFinite(+t) ? Math.abs(t) : 0,
            i = void 0 === o ? "," : o,
            s = void 0 === a ? "." : a,
            l = "";
        return (
            (l = (
                r
                    ? (function (e, t) {
                          var a = Math.pow(10, t);
                          return "" + (Math.round(e * a) / a).toFixed(t);
                      })(n, r)
                    : "" + Math.round(n)
            ).split(".")),
            l[0].length > 3 &&
                (l[0] = l[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, i)),
            (l[1] || "").length < r &&
                ((l[1] = l[1] || ""),
                (l[1] += new Array(r - l[1].length + 1).join("0"))),
            l.join(s)
        );
    }),
    (window.bravo_handle_error_response = function (e) {
        if (401 === e.status) $("#login").modal("show");
    });
var forms = document.getElementsByClassName("needs-validation"),
    validation = Array.prototype.filter.call(forms, function (e) {
        e.addEventListener(
            "submit",
            function (t) {
                !1 === e.checkValidity() &&
                    (t.preventDefault(), t.stopPropagation()),
                    e.classList.add("was-validated");
            },
            !1
        );
    }),
    bookingCoreApp = {
        showSuccess: function (e) {
            var t = {};
            "object" == typeof e ? (t = e) : (t.message = e),
                t.title || (t.title = i18n.success),
                (t.centerVertical = !0),
                bootbox.alert(t);
        },
        showError: function (e) {
            var t = {};
            "object" == typeof e ? (t = e) : (t.message = e),
                t.title || (t.title = i18n.warning),
                (t.centerVertical = !0),
                bootbox.alert(t);
        },
        showAjaxError: function (e) {
            var t = e.responseJSON;
            if (void 0 !== t) {
                if (void 0 !== t.errors) {
                    var a = "";
                    return (
                        _.forEach(t.errors, function (e) {
                            a += e + "<br>";
                        }),
                        this.showError(a)
                    );
                }
                if (t.message) return this.showError(t.message);
            }
            if (e.responseText) return this.showError(e.responseText);
        },
        showAjaxMessage: function (e) {
            e.message && (e.status ? this.showSuccess(e) : this.showError(e));
        },
        showConfirm: function (e) {
            var t = {};
            "object" == typeof e && (t = e),
                (t.buttons = {
                    confirm: {
                        label: '<i class="fa fa-check"></i> ' + i18n.confirm,
                    },
                    cancel: {
                        label: '<i class="fa fa-times"></i> ' + i18n.cancel,
                    },
                }),
                (t.centerVertical = !0),
                bootbox.confirm(t);
        },
    };
function setCookie(e, t, a) {
    const o = new Date();
    o.setTime(o.getTime() + 24 * a * 60 * 60 * 1e3);
    let n = "expires=" + o.toUTCString();
    document.cookie = e + "=" + t + ";" + n + ";path=/";
}
function post_request(e, t) {
    return fetch(bookingCore.url + e, {
        method: "POST",
        cache: "no-cache",
        credentials: "same-origin",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "Content-Type": "application/json",
        },
        redirect: "follow",
        referrerPolicy: "no-referrer",
        body: JSON.stringify(t),
    });
}
jQuery(function (e) {
    "use strict";
    e.fn.bravoAutocomplete = function (t) {
        var a = void 0 !== t.key ? t.key : "id";
        return this.each(function () {
            var o,
                n = e(this),
                r = e(this).closest(".smart-search"),
                i = t.textLoading;
            if (
                (r.append(
                    '<div class="bravo-autocomplete on-message bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4"><div class="list-item"></div><div class="message">' +
                        i +
                        "</div></div>"
                ),
                e(document).on("click.Bst", function (e) {
                    0 !== r.has(e.target).length || r.is(e.target)
                        ? t.dataDefault.length > 0 &&
                          r.find(".bravo-autocomplete").addClass("show")
                        : r.find(".bravo-autocomplete").removeClass("show");
                }),
                t.dataDefault.length > 0)
            ) {
                var s = "";
                for (var l in t.dataDefault) {
                    var c = t.dataDefault[l];
                    s +=
                        '<div class="item text-15 lh-12 fw-500 js-search-option-target -link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="' +
                        c[a] +
                        '" data-text="' +
                        c.title +
                        '"> <i class="' +
                        t.iconItem +
                        '"></i> ' +
                        c.title +
                        " </div>";
                }
                r.find(".bravo-autocomplete .list-item").html(s),
                    r.find(".bravo-autocomplete").removeClass("on-message");
            }
            void 0 !== t.url &&
                t.url &&
                n.keyup(function () {
                    r.find(".bravo-autocomplete").addClass("on-message"),
                        r.find(".bravo-autocomplete .message").html(i),
                        r.find(".child_id").val("");
                    var n = e(this).val();
                    if ((clearTimeout(o), 0 !== n.length))
                        (o = setTimeout(function () {
                            e.ajax({
                                url: t.url,
                                data: { search: n },
                                dataType: "json",
                                type: "get",
                                beforeSend: function () {},
                                success: function (e) {
                                    if (1 === e.status) {
                                        var o = "";
                                        for (var i in e.data) {
                                            var s = e.data[i];
                                            o +=
                                                '<div class="item text-15 lh-12 fw-500 js-search-option-target -link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="' +
                                                s[a] +
                                                '" data-text="' +
                                                s.title +
                                                '"> <i class="' +
                                                t.iconItem +
                                                '"></i> ' +
                                                ((l = s.title),
                                                (c = n),
                                                l.replace(
                                                    new RegExp(
                                                        c + "(?!([^<]+)?>)",
                                                        "gi"
                                                    ),
                                                    '<span class="h-line">$&</span>'
                                                )) +
                                                (s.desc
                                                    ? '<span class="item-desc">' +
                                                      s.desc +
                                                      "</span>"
                                                    : "") +
                                                " </div>";
                                        }
                                        r
                                            .find(
                                                ".bravo-autocomplete .list-item"
                                            )
                                            .html(o),
                                            r
                                                .find(".bravo-autocomplete")
                                                .removeClass("on-message");
                                    }
                                    var l, c;
                                    void 0 === typeof e.message
                                        ? r
                                              .find(".bravo-autocomplete")
                                              .addClass("on-message")
                                        : r
                                              .find(
                                                  ".bravo-autocomplete .message"
                                              )
                                              .html(e.message);
                                },
                            });
                        }, 700)),
                            r.find(".bravo-autocomplete").addClass("show");
                    else if (t.dataDefault.length > 0) {
                        var s = "";
                        for (var l in t.dataDefault) {
                            var c = t.dataDefault[l];
                            s +=
                                '<div class="item text-15 lh-12 fw-500 js-search-option-target -link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="' +
                                c[a] +
                                '" data-text="' +
                                c.title +
                                '"> <i class="' +
                                t.iconItem +
                                '"></i> ' +
                                c.title +
                                " </div>";
                        }
                        r.find(".bravo-autocomplete .list-item").html(s),
                            r
                                .find(".bravo-autocomplete")
                                .removeClass("on-message");
                    } else r.find(".bravo-autocomplete").removeClass("show");
                }),
                r.find(".bravo-autocomplete").on("click", ".item", function () {
                    var t = e(this).attr("data-id"),
                        a = e(this).attr("data-text");
                    t.length > 0 && a.length > 0
                        ? ((a = a.replace(/-/g, "")),
                          (a = d(a, " ")),
                          (a = d(a, "-")),
                          r.find(".parent_text").val(a).trigger("change"),
                          r.find(".child_id").val(t).trigger("change"))
                        : console.log("Cannot select!"),
                        setTimeout(function () {
                            r.find(".bravo-autocomplete").removeClass("show");
                        }, 100);
                });
            var d = function (e, t) {
                return (
                    "]" === t && (t = "\\]"),
                    "\\" === t && (t = "\\\\"),
                    e.replace(
                        new RegExp("^[" + t + "]+|[" + t + "]+$", "g"),
                        ""
                    )
                );
            };
        });
    };
}),
    jQuery(function (e) {
        "use strict";
        function t(e) {
            return e.responseJSON && e.responseJSON.errors
                ? Object.values(e.responseJSON.errors).join("<br>")
                : "";
        }
        e(".g-map-place").each(function () {
            var t = e(this).find(".map").attr("id"),
                a = e(this).find("input[name=map_place]"),
                o = e(this).find('input[name="map_lat"]'),
                n = e(this).find('input[name="map_lgn"]');
            new BravoMapEngine(t, {
                fitBounds: !0,
                center: [51.505, -0.09],
                ready: function (e) {
                    e.searchBox(a, function (e) {
                        o.attr("value", e[0]), n.attr("value", e[1]);
                    });
                },
            });
        }),
            e(".bravo-form-search-slider .effect").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 1,
                        loop: !0,
                        margin: 0,
                        nav: !1,
                        autoplay: !0,
                        autoplayTimeout: 5e3,
                        autoplayHoverPause: !1,
                        animateOut: "fadeOut",
                    });
            }),
            e(".bravo-form-search-all.carousel_v2").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 1,
                        loop: !0,
                        margin: 0,
                        nav: !1,
                        autoplay: !0,
                        autoplayTimeout: 5e3,
                        autoplayHoverPause: !1,
                        animateOut: "fadeOut",
                    });
            }),
            e(".bravo-form-search-tour.carousel_v2  .effect").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 1,
                        loop: !0,
                        margin: 0,
                        nav: !1,
                        autoplay: !0,
                        autoplayTimeout: 5e3,
                        autoplayHoverPause: !1,
                        animateOut: "fadeOut",
                    });
            }),
            e(".bravo-list-tour.carousel").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo-box-category-tour").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !0,
                        margin: 30,
                        nav: !1,
                        dots: !0,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo-client-feedback").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 1,
                        loop: !0,
                        margin: 0,
                        nav: !0,
                        dots: !1,
                    });
            }),
            e(".bravo-list-tour.carousel_simple").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 3,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 3 },
                        },
                    });
            }),
            e(".bravo-list-space").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 3,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 3 },
                        },
                    });
            }),
            e(".bravo-list-hotel").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo-list-car").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo-list-boat").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo-list-event").each(function () {
                e(this)
                    .find(".owl-carousel")
                    .owlCarousel({
                        items: 4,
                        loop: !1,
                        margin: 15,
                        nav: !1,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1e3: { items: 4 },
                        },
                    });
            }),
            e(".bravo_fullHeight").each(function () {
                var t = e(document).height();
                e(document).find(".bravo-admin-bar").length > 0 &&
                    (t -= e(".bravo-admin-bar").height()),
                    e(this).css("min-height", t);
            }),
            e(".form-date-search").each(function () {
                var t = !1;
                e(this).hasClass("is_single_picker") && (t = !0);
                var a = new Date(),
                    o = new Date(
                        a.getFullYear(),
                        a.getMonth(),
                        a.getDate(),
                        0,
                        0,
                        0,
                        0
                    ),
                    n = e(this),
                    r = e(".date-wrapper", n),
                    i = e(".check-in-input", n),
                    s = e(".check-out-input", n),
                    l = e(".check-in-out", n),
                    c = e(".check-in-render", n),
                    d = e(".check-out-render", n),
                    f = {
                        singleDatePicker: t,
                        autoApply: !0,
                        disabledPast: !0,
                        customClass: "",
                        widthSingle: 300,
                        onlyShowCurrentMonth: !0,
                        minDate: o,
                        opens: "center",
                        locale: {
                            format: "YYYY-MM-DD",
                            direction: bookingCore.rtl ? "rtl" : "ltr",
                            firstDay: daterangepickerLocale.first_day_of_week,
                        },
                    };
                "object" == typeof daterangepickerLocale &&
                    (f.locale = _.merge(daterangepickerLocale, f.locale)),
                    l.daterangepicker(f, function (e, t, a) {
                        i.val(e.format(bookingCore.date_format)),
                            c.html(e.format(bookingCore.date_format)),
                            s.val(t.format(bookingCore.date_format)),
                            d.html(t.format(bookingCore.date_format));
                    }),
                    r.click(function (e) {
                        l.trigger("click");
                    });
            }),
            e(".date-picker").each(function () {
                var t = {
                    singleDatePicker: !0,
                    opens: bookingCore.rtl ? "right" : "left",
                    locale: {
                        format: bookingCore.date_format,
                        direction: bookingCore.rtl ? "rtl" : "ltr",
                        firstDay: daterangepickerLocale.first_day_of_week,
                    },
                };
                "object" == typeof daterangepickerLocale &&
                    (t.locale = _.merge(daterangepickerLocale, t.locale)),
                    e(this).daterangepicker(t);
            }),
            e(".form-date-search-hotel").each(function () {
                var t = new Date(),
                    a = new Date(
                        t.getFullYear(),
                        t.getMonth(),
                        t.getDate(),
                        0,
                        0,
                        0,
                        0
                    ),
                    o = e(this),
                    n = e(".date-wrapper", o),
                    r = e(".check-in-input", o),
                    i = e(".check-out-input", o),
                    s = e(".check-in-out", o),
                    l = e(".check-in-render", o),
                    c = e(".check-out-render", o),
                    d = {
                        singleDatePicker: !1,
                        autoApply: !0,
                        disabledPast: !0,
                        customClass: "",
                        widthSingle: 300,
                        onlyShowCurrentMonth: !0,
                        minDate: a,
                        opens: "center",
                        locale: {
                            format: "YYYY-MM-DD",
                            direction: bookingCore.rtl ? "rtl" : "ltr",
                            firstDay: daterangepickerLocale.first_day_of_week,
                        },
                    };
                "object" == typeof daterangepickerLocale &&
                    (d.locale = _.merge(daterangepickerLocale, d.locale)),
                    s
                        .daterangepicker(d)
                        .on("apply.daterangepicker", function (e, t) {
                            t.endDate.diff(t.startDate, "day") <= 0 &&
                                t.endDate.add(1, "day"),
                                r.val(
                                    t.startDate.format(bookingCore.date_format)
                                ),
                                l.html(
                                    t.startDate.format(bookingCore.date_format)
                                ),
                                i.val(
                                    t.endDate.format(bookingCore.date_format)
                                ),
                                c.html(
                                    t.endDate.format(bookingCore.date_format)
                                ),
                                s.val(
                                    t.startDate.format("YYYY-MM-DD") +
                                        " - " +
                                        t.endDate.format("YYYY-MM-DD")
                                );
                        }),
                    n.click(function (e) {
                        s.trigger("click");
                    });
            }),
            e(".review-form .review-items .rates .fa").each(function () {
                var t = e(this).parent(),
                    a = t.children(),
                    o = e(this).index(),
                    n = t.parent();
                e(this).hover(
                    function () {
                        for (var t = 0; t < a.length && t <= o; t++)
                            e(a[t]).addClass("hovered");
                        e(this).on("click", function (t) {
                            for (var r = 0; r < a.length; r++)
                                r <= o
                                    ? e(a[r]).addClass("selected")
                                    : e(a[r]).removeClass("selected");
                            n.children(".review_stats").val(o + 1);
                        });
                    },
                    function () {
                        a.removeClass("hovered");
                    }
                );
            }),
            (window.ajax_error_to_string = function (e) {
                if (void 0 !== e.responseJSON) {
                    if (e.responseJSON.errors) {
                        var t = [];
                        for (var a in e.responseJSON.errors)
                            t.push(e.responseJSON.errors[a].join("<br/>"));
                        return t.join("<br/>");
                    }
                    if (e.responseJSON.message) return e.responseJSON.message;
                }
            }),
            e(".bravo-form-login [type=submit]").on("click", function (t) {
                t.preventDefault();
                let a = e(this).closest(".bravo-form-login");
                var o = a.find("input[name=redirect]").val();
                e.ajax({
                    url: bookingCore.url + "/login",
                    data: {
                        email: a.find("input[name=email]").val(),
                        password: a.find("input[name=password]").val(),
                        remember: a.find("input[name=remember]").is(":checked")
                            ? 1
                            : "",
                        "g-recaptcha-response": a
                            .find("[name=g-recaptcha-response]")
                            .val(),
                        redirect: a.find("input[name=redirect]").val(),
                    },
                    method: "POST",
                    beforeSend: function () {
                        a.find(".error").hide(),
                            a.find(".icon-arrow-top-right").hide(),
                            a.find(".icon-loading").removeClass("d-none");
                    },
                    dataType: "json",
                    success: function (e) {
                        if (e.two_factor)
                            return (window.location.href =
                                bookingCore.url + "/two-factor-challenge");
                        if (
                            (a.find(".icon-arrow-top-right").show(),
                            a.find(".icon-loading").addClass("d-none"),
                            !0 === e.error)
                        ) {
                            if (void 0 !== e.messages)
                                for (var t in e.messages) {
                                    var n = e.messages[t];
                                    a.find(".error-" + t)
                                        .show()
                                        .text(n[0]);
                                }
                            void 0 !== e.messages.message_error &&
                                a
                                    .find(".message-error")
                                    .show()
                                    .html(
                                        '<div class="alert alert-danger">' +
                                            e.messages.message_error[0] +
                                            "</div>"
                                    );
                        }
                        e.message &&
                            a
                                .find(".message-error")
                                .show()
                                .html(
                                    '<div class="alert alert-danger">' +
                                        e.message +
                                        "</div>"
                                ),
                            "undefined" != typeof BravoReCaptcha &&
                                (BravoReCaptcha.reset("login"),
                                BravoReCaptcha.reset("login_normal")),
                            o.trim("/")
                                ? (window.location.href =
                                      bookingCore.url_root +
                                      a.find("input[name=redirect]").val())
                                : window.location.reload();
                    },
                    error: function (e) {
                        a.find(".icon-arrow-top-right").show(),
                            a.find(".icon-loading").addClass("d-none");
                        var t = ajax_error_to_string(e);
                        "undefined" != typeof BravoReCaptcha &&
                            (BravoReCaptcha.reset("login"),
                            BravoReCaptcha.reset("login_normal")),
                            t &&
                                a
                                    .find(".message-error")
                                    .show()
                                    .html(
                                        '<div class="alert alert-danger">' +
                                            t +
                                            "</div>"
                                    );
                    },
                });
            }),
            e(".bravo-form-register [type=submit]").on("click", function (t) {
                t.preventDefault();
                let a = e(this).closest(".bravo-form-register");
                e.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": a
                            .find('meta[name="csrf-token"]')
                            .attr("content"),
                    },
                }),
                    e.ajax({
                        url: bookingCore.routes.register,
                        data: {
                            email: a.find("input[name=email]").val(),
                            password: a.find("input[name=password]").val(),
                            first_name: a.find("input[name=first_name]").val(),
                            last_name: a.find("input[name=last_name]").val(),
                            phone: a.find("input[name=phone]").val(),
                            term: a.find("input[name=term]").is(":checked")
                                ? 1
                                : "",
                            "g-recaptcha-response": a
                                .find("[name=g-recaptcha-response]")
                                .val(),
                        },
                        type: "POST",
                        beforeSend: function () {
                            a.find(".error").hide(),
                                a
                                    .find(".icon-loading")
                                    .css("display", "inline-block");
                        },
                        success: function (e) {
                            if (
                                (a.find(".icon-loading").hide(), !0 === e.error)
                            ) {
                                if (void 0 !== e.messages)
                                    for (var t in e.messages) {
                                        var o = e.messages[t];
                                        a.find(".error-" + t)
                                            .show()
                                            .text(o[0]);
                                    }
                                void 0 !== e.messages.message_error &&
                                    a
                                        .find(".message-error")
                                        .show()
                                        .html(
                                            '<div class="alert alert-danger">' +
                                                e.messages.message_error[0] +
                                                "</div>"
                                        );
                            }
                            "undefined" != typeof BravoReCaptcha &&
                                (BravoReCaptcha.reset("register"),
                                BravoReCaptcha.reset("register_normal")),
                                void 0 !== e.redirect &&
                                    (window.location.href = e.redirect);
                        },
                        error: function (e) {
                            a.find(".icon-loading").hide(),
                                void 0 !== e.responseJSON &&
                                    void 0 !== e.responseJSON.message &&
                                    a
                                        .find(".message-error")
                                        .show()
                                        .html(
                                            '<div class="alert alert-danger">' +
                                                e.responseJSON.message +
                                                "</div>"
                                        ),
                                "undefined" != typeof BravoReCaptcha &&
                                    (BravoReCaptcha.reset("register"),
                                    BravoReCaptcha.reset("register_normal"));
                        },
                    });
            }),
            e("#register").on("show.bs.modal", function (t) {
                e("#login").modal("hide");
            }),
            e("#login").on("show.bs.modal", function (t) {
                e("#register").modal("hide");
            });
        var a = !1;
        function o(e) {
            var t = parseInt(e.find("[name=adults]").val()),
                a = parseInt(e.find("[name=children]").val()),
                o = e.find(".render .adults .multi").data("html");
            console.log(e, o),
                e.find(".render .adults .multi").html(o.replace(":count", t));
            var n = e.find(".render .children .multi").data("html");
            e.find(".render .children .multi").html(n.replace(":count", a)),
                t > 1
                    ? (e.find(".render .adults .multi").removeClass("d-none"),
                      e.find(".render .adults .one").addClass("d-none"))
                    : (e.find(".render .adults .multi").addClass("d-none"),
                      e.find(".render .adults .one").removeClass("d-none")),
                a > 1
                    ? (e.find(".render .children .multi").removeClass("d-none"),
                      e.find(".render .children .one").addClass("d-none"))
                    : (e.find(".render .children .multi").addClass("d-none"),
                      e
                          .find(".render .children .one")
                          .removeClass("d-none")
                          .html(
                              e
                                  .find(".render .children .one")
                                  .data("html")
                                  .replace(":count", a)
                          ));
        }
        function n(e) {
            var t = e.closest(".form-select-seat-type"),
                a = e.attr("id"),
                o = parseInt(e.val()),
                n = t.find("[id=" + a + "_render]"),
                r = n.find(".multi").data("html"),
                i = e.attr("min");
            console.log(n),
                o > i
                    ? (n
                          .find(".multi")
                          .removeClass("d-none")
                          .html(r.replace(":count", o)),
                      n.find(".one").addClass("d-none"))
                    : (n.find(".multi").addClass("d-none"),
                      n.find(".one").removeClass("d-none"));
        }
        e(".bravo-subscribe-form").submit(function (o) {
            if ((o.preventDefault(), !a)) {
                e(this).addClass("loading");
                var n = e(this);
                return (
                    n.find(".form-mess").html(""),
                    e.ajax({
                        url: n.attr("action"),
                        type: "post",
                        data: e(this).serialize(),
                        dataType: "json",
                        success: function (e) {
                            (a = !1),
                                n.removeClass("loading"),
                                e.message &&
                                    n
                                        .find(".form-mess")
                                        .html(
                                            '<span class="' +
                                                (e.status
                                                    ? "text-success"
                                                    : "text-danger") +
                                                '">' +
                                                e.message +
                                                "</span>"
                                        ),
                                e.status && n.find("input").val("");
                        },
                        error: function (e) {
                            console.log(e),
                                (a = !1),
                                n.removeClass("loading"),
                                t(e)
                                    ? n
                                          .find(".form-mess")
                                          .html(
                                              '<span class="text-danger">' +
                                                  t(e) +
                                                  "</span>"
                                          )
                                    : e.responseText &&
                                      n
                                          .find(".form-mess")
                                          .html(
                                              '<span class="text-danger">' +
                                                  e.responseText +
                                                  "</span>"
                                          );
                        },
                    }),
                    !1
                );
            }
        }),
            e(".bravo-more-menu").on("click", function (t) {
                e(this).trigger("bravo-trigger-menu-mobile");
            }),
            e(".bravo-menu-mobile .b-close").on("click", function (t) {
                e(".bravo-more-menu").trigger("bravo-trigger-menu-mobile");
            }),
            e(document).on("click", ".bravo-effect-bg", function () {
                e(".bravo-more-menu").trigger("bravo-trigger-menu-mobile");
            }),
            e(document).on(
                "bravo-trigger-menu-mobile",
                ".bravo-more-menu",
                function () {
                    e(this).toggleClass("active"),
                        e(this).hasClass("active")
                            ? (e(".bravo-menu-mobile").addClass("active"),
                              e("body")
                                  .css("overflow", "hidden")
                                  .append(
                                      "<div class='bravo-effect-bg'></div>"
                                  ))
                            : (e(".bravo-menu-mobile").removeClass("active"),
                              e("body")
                                  .css("overflow", "initial")
                                  .find(".bravo-effect-bg")
                                  .remove());
                }
            ),
            e(".bravo-menu-mobile .g-menu ul li .fa").on("click", function (t) {
                t.preventDefault(), e(this).closest("li").toggleClass("active");
            }),
            e(".bravo-menu-mobile").each(function () {
                var t = e(this).find(".user-profile").height(),
                    a = e(window).height();
                e(this)
                    .find(".g-menu")
                    .css("max-height", a - t - 15);
            }),
            e(".bravo-more-menu-user").on("click", function (t) {
                e(
                    ".bravo_user_profile > .container-fluid > .row > .col-md-3"
                ).addClass("active"),
                    e("body")
                        .css("overflow", "hidden")
                        .append("<div class='bravo-effect-user-bg'></div>");
            }),
            e(document).on(
                "click",
                ".bravo-effect-user-bg,.bravo-close-menu-user",
                function () {
                    e(
                        ".bravo_user_profile > .container-fluid > .row > .col-md-3"
                    ).removeClass("active"),
                        e("body")
                            .css("overflow", "initial")
                            .find(".bravo-effect-user-bg")
                            .remove();
                }
            ),
            e('[data-toggle="tooltip"]').tooltip(),
            e(".dropdown-toggle").dropdown(),
            e(".select-guests-dropdown .btn-minus").on("click", function (t) {
                t.stopPropagation();
                var a = e(this).closest(".form-select-guests"),
                    n = a.find(
                        ".select-guests-dropdown [name=" +
                            e(this).data("input") +
                            "]"
                    ),
                    r = parseInt(n.attr("min")),
                    i = parseInt(n.val());
                i <= r || (n.val(i - 1), o(a));
            }),
            e(".select-guests-dropdown .btn-add").on("click", function (t) {
                t.stopPropagation();
                var a = e(this).closest(".form-select-guests"),
                    n = a.find(
                        ".select-guests-dropdown [name=" +
                            e(this).data("input") +
                            "]"
                    ),
                    r = parseInt(n.attr("max")),
                    i = parseInt(n.val());
                i >= r || (n.val(i + 1), o(a));
            }),
            e(".select-guests-dropdown input").keyup(function (t) {
                o(e(this).closest(".form-select-guests"));
            }),
            e(".select-guests-dropdown input").change(function (t) {
                o(e(this).closest(".form-select-guests"));
            }),
            e(".select-guests-dropdown .dropdown-item-row").on(
                "click",
                function (e) {
                    e.stopPropagation();
                }
            ),
            e(".select-seat-type-dropdown .btn-minus").on(
                "click",
                function (t) {
                    t.stopPropagation();
                    var a = e(this).closest(".form-select-seat-type"),
                        o = e(this).data("input-attr");
                    void 0 === o && (o = "name");
                    var r = a.find(
                            ".select-seat-type-dropdown [" +
                                o +
                                "=" +
                                e(this).data("input") +
                                "]"
                        ),
                        i = parseInt(r.attr("min")),
                        s = parseInt(r.val());
                    s <= i || (r.val(s - 1), n(r));
                }
            ),
            e(".select-seat-type-dropdown .btn-add").on("click", function (t) {
                t.stopPropagation();
                var a = e(this).closest(".form-select-seat-type"),
                    o = e(this).data("input-attr");
                void 0 === o && (o = "name");
                var r = a.find(
                        ".select-seat-type-dropdown [" +
                            o +
                            "=" +
                            e(this).data("input") +
                            "]"
                    ),
                    i = parseInt(r.attr("max")),
                    s = parseInt(r.val());
                s >= i || (r.val(s + 1), n(r));
            }),
            e(".select-seat-type-dropdown .seat_class").on(
                "click",
                function (t) {
                    t.stopPropagation();
                    // var a = e(this).closest(".form-select-seat-type"),
                    //     o = e(this).data("input-attr");
                    // void 0 === o && (o = "name");
                    // var r = a.find(".select-seat-type-dropdown [" + o + "=" + e(this).data("input") + "]"),
                    //     i = parseInt(r.attr("max")),
                    //     s = parseInt(r.val());
                    // s >= i || (r.val(s + 1), n(r));
                    e(".seat_class_render").html(e(this).val());
                }
            ),
            e(".select-seat-type-dropdown input").on("keyup", function (t) {
                n(e(this));
            }),
            e(".select-seat-type-dropdown input").on("change", function (t) {
                n(e(this));
            }),
            e(".select-seat-type-dropdown .dropdown-item-row").on(
                "click",
                function (e) {
                    e.stopPropagation();
                }
            ),
            e(".smart-search .smart-search-hotel").each(function () {
                var t = e(this),
                    a = t.attr("data-default"),
                    o = [];
                a.length > 0 && (o = JSON.parse(a));
                var n = t.data("url"),
                    r = t.data("key"),
                    i = {
                        url:
                            n ||
                            bookingCore.url +
                                "/location/search/searchForSelect2",
                        dataDefault: o,
                        textLoading: t.attr("data-onLoad"),
                        iconItem: "icon-location-2 text-light-1 text-20 pt-4",
                        key: r || "id",
                    };
                t.bravoAutocomplete(i);
            }),
            e(".smart-search .smart-search-tour").each(function () {
                var t = e(this),
                    a = t.attr("data-default"),
                    o = [];
                a.length > 0 && (o = JSON.parse(a));
                var n = t.data("url"),
                    r = t.data("key"),
                    i = {
                        url:
                            n ||
                            bookingCore.url +
                                "/location/search/searchForSelect2",
                        dataDefault: o,
                        textLoading: t.attr("data-onLoad"),
                        iconItem: "icon-location-2 text-light-1 text-20 pt-4",
                        key: r || "id",
                    };
                t.bravoAutocomplete(i);
            }),
            e(".smart-search .smart-search-flight").each(function () {
                var t = e(this),
                    a = t.attr("data-default"),
                    o = [];
                a.length > 0 && (o = JSON.parse(a));
                var n = t.data("url"),
                    r = t.data("key"),
                    i = {
                        url:
                            n ||
                            bookingCore.url +
                                "/location/search/searchForSelect2Flight",
                        dataDefault: o,
                        textLoading: t.attr("data-onLoad"),
                        iconItem: "icon-location-2 text-light-1 text-20 pt-4",
                        key: r || "id",
                    };
                t.bravoAutocomplete(i);
            }),
            e(".smart-search .smart-search-flight").each(function () {
                var t = e(this),
                    a = t.attr("data-default"),
                    o = [];
                a.length > 0 && (o = JSON.parse(a));
                var n = t.data("url"),
                    r = t.data("key"),
                    i = {
                        url:
                            n ||
                            bookingCore.url +
                                "/location/search/searchForSelect2Flight",
                        dataDefault: o,
                        textLoading: t.attr("data-onLoad"),
                        iconItem: "icon-location-2 text-light-1 text-20 pt-4",
                        key: r || "id",
                    };
                t.bravoAutocomplete(i);
            }),
            e(".smart-search .smart-select").each(function () {
                var t = e(this),
                    a = t.attr("data-default"),
                    o = [];
                a.length > 0 && (o = JSON.parse(a));
                var n = {
                    dataDefault: o,
                    iconItem: "",
                    textLoading: t.attr("data-onLoad"),
                };
                t.bravoAutocomplete(n);
            }),
            e(document).on("click", ".service-wishlist", function (t) {
                t.preventDefault();
                var a = e(this);
                e.ajax({
                    url: bookingCore.url + "/user/wishlist",
                    data: {
                        object_id: a.attr("data-id"),
                        object_model: a.attr("data-type"),
                    },
                    dataType: "json",
                    type: "POST",
                    beforeSend: function () {
                        a.addClass("loading");
                    },
                    success: function (e) {
                        a.attr("class", "service-wishlist " + e.class);
                    },
                    error: function (t) {
                        401 === t.status && e("#login").modal("show");
                    },
                });
            }),
            e(".bravo-video-popup").on("click", function (t) {
                let a = e(this).data("src"),
                    o = e(this).data("target");
                e(o)
                    .find(".bravo_embed_video")
                    .attr(
                        "src",
                        a + "?autoplay=0&amp;modestbranding=1&amp;showinfo=0"
                    ),
                    e(o).on("hidden.bs.modal", function () {
                        e(o).find(".bravo_embed_video").attr("src", "");
                    });
            });
        var r = !1;
        e(".bravo-contact-block-form").submit(function (a) {
            if ((a.preventDefault(), !r)) {
                e(this).addClass("loading");
                var o = e(this);
                return (
                    o.find(".form-mess").html(""),
                    e.ajax({
                        url: o.attr("action"),
                        type: "post",
                        data: e(this).serialize(),
                        dataType: "json",
                        success: function (e) {
                            (r = !1),
                                o.removeClass("loading"),
                                e.message &&
                                    o
                                        .find(".form-mess")
                                        .html(
                                            '<span class="' +
                                                (e.status
                                                    ? "text-success"
                                                    : "text-danger") +
                                                '">' +
                                                e.message +
                                                "</span>"
                                        ),
                                e.status &&
                                    (o.find("input").val(""),
                                    o.find("textarea").val("")),
                                "undefined" != typeof BravoReCaptcha &&
                                    BravoReCaptcha.reset("contact");
                        },
                        error: function (e) {
                            console.log(e),
                                (r = !1),
                                o.removeClass("loading"),
                                t(e)
                                    ? o
                                          .find(".form-mess")
                                          .html(
                                              '<span class="text-danger">' +
                                                  t(e) +
                                                  "</span>"
                                          )
                                    : e.responseText &&
                                      o
                                          .find(".form-mess")
                                          .html(
                                              '<span class="text-danger">' +
                                                  e.responseText +
                                                  "</span>"
                                          ),
                                "undefined" != typeof BravoReCaptcha &&
                                    BravoReCaptcha.reset("contact");
                        },
                    }),
                    !1
                );
            }
        }),
            e(".btn-submit-enquiry").on("click", function (t) {
                t.preventDefault();
                let a = e(this).closest(".enquiry_form_modal_form");
                e.ajax({
                    url: bookingCore.url + "/booking/addEnquiry",
                    data: a.find("textarea,input,select").serialize(),
                    dataType: "json",
                    type: "post",
                    beforeSend: function () {
                        a.find(".message_box").html("").hide(),
                            a
                                .find(".icon-loading")
                                .css("display", "inline-block");
                    },
                    success: function (e) {
                        if (e.errors)
                            for (var t in ((e.message = ""), e.errors))
                                e.message += e.errors[t].join("<br>") + "<br>";
                        e.message &&
                            (e.status
                                ? a
                                      .find(".message_box")
                                      .append(
                                          '<div class="text text-success">' +
                                              e.message +
                                              "</div>"
                                      )
                                      .show()
                                : a
                                      .find(".message_box")
                                      .append(
                                          '<div class="text text-danger">' +
                                              e.message +
                                              "</div>"
                                      )
                                      .show()),
                            a.find(".icon-loading").hide(),
                            e.status && a.find("textarea").val(""),
                            "undefined" != typeof BravoReCaptcha &&
                                BravoReCaptcha.reset("enquiry_form");
                    },
                    error: function (e) {
                        "undefined" != typeof BravoReCaptcha &&
                            BravoReCaptcha.reset("enquiry_form"),
                            a.find(".icon-loading").hide();
                    },
                });
            }),
            e(".review_upload_file").change(function () {
                var t = e(this),
                    a = e(this)
                        .closest(".review_upload_wrap")
                        .find(".review_upload_photo_list");
                t.isLoading = !0;
                for (var o = 0; o < t.get(0).files.length; o++) {
                    var n = new FormData();
                    n.append("type", "image"),
                        n.append("file", t.get(0).files[o]),
                        t.showErr ||
                            e.ajax({
                                url: bookingCore.url + "/media/private/store",
                                data: n,
                                dataType: "json",
                                type: "post",
                                contentType: !1,
                                processData: !1,
                                success: function (o) {
                                    if (
                                        (t.val(""),
                                        0 === o.status &&
                                            bookingCoreApp.showError(o),
                                        o.data)
                                    )
                                        if (
                                            e(
                                                ".review_upload_photo_list > .col-md-2"
                                            ).length > 5
                                        )
                                            bookingCoreApp.showError(
                                                "Maximum upload 6 pictures"
                                            );
                                        else {
                                            var n = e(
                                                    '<div class="col-md-2 mb-2"/>'
                                                ),
                                                r = e(
                                                    '<div class="review_upload_item"/>'
                                                );
                                            n.append(r);
                                            var i = e("<input/>");
                                            i.attr("type", "hidden"),
                                                i.attr(
                                                    "name",
                                                    t.data("name") + "[]"
                                                ),
                                                i.val(JSON.stringify(o.data)),
                                                r.append(i),
                                                r.css({
                                                    "background-image":
                                                        "url(" +
                                                        o.data.download +
                                                        ")",
                                                }),
                                                t.data("multiple")
                                                    ? a.append(n)
                                                    : a.html(n);
                                        }
                                },
                                error: function (e) {
                                    bookingCoreApp.showAjaxError(e), t.val("");
                                },
                            });
                }
                e(this).val("");
            }),
            e(".review_upload_item").on("click", function (t) {
                var a = e(t.target).data("target");
                e(a + " .fotorama").fotorama();
            }),
            e(".bc_popup")
                .modal("show")
                .on("hidden.bs.modal", function () {
                    setCookie(
                        e(this).attr("id"),
                        1,
                        parseInt(e(this).data("days"))
                    );
                });
        [].slice
            .call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(function (e) {
                return new bootstrap.Tooltip(e);
            });
    }),
    jQuery(function (e) {
        "use strict";
        var t = e(".dropdown-notifications"),
            a = t.find("a[data-toggle]").find(".notification-icon"),
            o = parseInt(a.html()),
            n = t.find("ul.dropdown-list-items");
        if (bookingCore.pusher_api_key && bookingCore.pusher_cluster)
            var r = new Pusher(bookingCore.pusher_api_key, {
                encrypted: !0,
                cluster: bookingCore.pusher_cluster,
            });
        e(document).on("click", ".markAsRead", function (t) {
            t.stopPropagation(), t.preventDefault();
            var a = e(this).data("id"),
                o = e(this).attr("href");
            e.ajax({
                url: bookingCore.markAsRead,
                data: { id: a },
                method: "post",
                success: function (e) {
                    window.location.href = o;
                },
            });
        }),
            e(document).on("click", ".markAllAsRead", function (o) {
                o.stopPropagation(),
                    o.preventDefault(),
                    e.ajax({
                        url: bookingCore.markAllAsRead,
                        method: "post",
                        success: function (o) {
                            e(".dropdown-notifications")
                                .find("li.notification")
                                .removeClass("active"),
                                a.text(0),
                                t.find(".notif-count").text(0);
                        },
                    });
            });
        var i = function (e) {
            var r = n.html(),
                i =
                    '<li class="notification active"><div class="media">   <a class="markAsRead p-0" data-id="' +
                    e.idNotification +
                    '" href="' +
                    e.link +
                    '">    <div class="media-left">      <div class="media-object">' +
                    e.avatar +
                    '      </div>    </div>    <div class="media-body">      ' +
                    e.message +
                    '      <div class="notification-meta">        <small class="timestamp">about a few seconds ago</small>      </div>    </div>  </a></div></li>';
            n.html(i + r), (o += 1), a.text(o), t.find(".notif-count").text(o);
        };
        bookingCore.isAdmin > 0 &&
            bookingCore.pusher_api_key &&
            r
                .subscribe("admin-channel")
                .bind("App\\Events\\PusherNotificationAdminEvent", i);
        bookingCore.currentUser > 0 &&
            bookingCore.pusher_api_key &&
            r
                .subscribe("user-channel-" + bookingCore.currentUser)
                .bind("App\\Events\\PusherNotificationPrivateEvent", i);
        e(".tabs-box").length &&
            e(".tabs-box .tab-buttons .tab-btn").on("click", function (t) {
                t.preventDefault();
                var a = e(e(this).attr("data-tab"));
                if (e(a).is(":visible")) return !1;
                a
                    .parents(".tabs-box")
                    .find(".tab-buttons")
                    .find(".tab-btn")
                    .removeClass("active-btn"),
                    e(this).addClass("active-btn"),
                    a
                        .parents(".tabs-box")
                        .find(".tabs-content")
                        .find(".tab")
                        .fadeOut(0),
                    a
                        .parents(".tabs-box")
                        .find(".tabs-content")
                        .find(".tab")
                        .removeClass("active-tab animated fadeIn"),
                    e(a).fadeIn(300),
                    e(a).addClass("active-tab animated fadeIn");
            });
    });
(forms = document.getElementsByClassName("needs-validation")),
    (validation = Array.prototype.filter.call(forms, function (e) {
        e.addEventListener(
            "submit",
            function (t) {
                !1 === e.checkValidity() &&
                    (t.preventDefault(), t.stopPropagation()),
                    e.classList.add("was-validated");
            },
            !1
        );
    }));
$(".bravo-theme-gotrip-login-form [type=submit]").on("click", function (e) {
    e.preventDefault();
    let t = $(this).closest(".bravo-theme-gotrip-login-form");
    var a = t.find("input[name=redirect]").val();
    $.ajax({
        url: bookingCore.url + "/login",
        data: {
            email: t.find("input[name=email]").val(),
            password: t.find("input[name=password]").val(),
            remember: t.find("input[name=remember]").is(":checked") ? 1 : "",
            "g-recaptcha-response": t.find("[name=g-recaptcha-response]").val(),
            redirect: t.find("input[name=redirect]").val(),
        },
        method: "POST",
        beforeSend: function () {
            t.find(".error").hide(),
                t.find(".icon-arrow-top-right").hide(),
                t.find(".icon-loading").removeClass("d-none");
        },
        dataType: "json",
        success: function (e) {
            if (e.two_factor)
                return (window.location.href =
                    bookingCore.url + "/two-factor-challenge");
            if (
                (t.find(".icon-arrow-top-right").show(),
                t.find(".icon-loading").addClass("d-none"),
                !0 === e.error)
            ) {
                if (void 0 !== e.messages)
                    for (var o in e.messages) {
                        var n = e.messages[o];
                        t.find(".error-" + o)
                            .show()
                            .text(n[0]);
                    }
                void 0 !== e.messages.message_error &&
                    t
                        .find(".message-error")
                        .show()
                        .html(
                            '<div class="alert alert-danger">' +
                                e.messages.message_error[0] +
                                "</div>"
                        );
            }
            e.message &&
                t
                    .find(".message-error")
                    .show()
                    .html(
                        '<div class="alert alert-danger">' +
                            e.message +
                            "</div>"
                    ),
                "undefined" != typeof BravoReCaptcha &&
                    (BravoReCaptcha.reset("login"),
                    BravoReCaptcha.reset("login_normal")),
                a.trim("/")
                    ? (window.location.href =
                          bookingCore.url_root +
                          t.find("input[name=redirect]").val())
                    : window.location.reload();
        },
        error: function (e) {
            t.find(".icon-arrow-top-right").show(),
                t.find(".icon-loading").addClass("d-none");
            var a = ajax_error_to_string(e);
            "undefined" != typeof BravoReCaptcha &&
                (BravoReCaptcha.reset("login"),
                BravoReCaptcha.reset("login_normal")),
                a &&
                    t
                        .find(".message-error")
                        .show()
                        .html(
                            '<div class="alert alert-danger">' + a + "</div>"
                        );
        },
    });
}),
    $("input[value!='']").addClass("has-value"),
    $(".mobile-footer .menu-item-has-children > a").on("click", function (e) {
        e.preventDefault();
        var t = $(this).parent();
        $(".mobile-footer .menu-item-has-children")
            .not(t)
            .find(".subnav")
            .removeClass("active"),
            t.find(".subnav").toggleClass("active");
    }),
    $(document).ready(function () {
        "use strict";
        $(document).on(
            "click",
            ".searchMenu-loc .js-search-option",
            function (e) {
                e.preventDefault(),
                    $(this).closest(".searchMenu-loc").addClass("has-val");
            }
        ),
            $(".searchMenu-loc").each(function () {
                let e = $(this);
                e.find(".js-search-get-id").val() && e.addClass("has-val");
            }),
            $(".searchMenu-loc .clear-loc").on("click", function (e) {
                e.preventDefault(),
                    $(this)
                        .closest(".searchMenu-loc")
                        .removeClass("has-val")
                        .find("input")
                        .val("");
            }),
            $(".gotrip-dropdown .menu-item-has-children > a").on(
                "click",
                function (e) {
                    e.preventDefault();
                    let t = $(this).parent();
                    $(".gotrip-dropdown .menu-item-has-children")
                        .not(t)
                        .removeClass("show"),
                        t.toggleClass("show");
                }
            ),
            $(".bravo-faq-lists .js-accordion .accordion__button").on(
                "click",
                function (e) {
                    e.preventDefault();
                    document
                        .querySelectorAll(".js-accordion .accordion__item")
                        .forEach((e) => {
                            e
                                .querySelector(".accordion__content")
                                .removeAttribute("style"),
                                e.classList.remove("is-active");
                        });
                }
            ),
            $(".gotrip-detail-book-mobile").click(function () {
                $("html, body").animate(
                    { scrollTop: $(".bravo_single_book").offset().top - 30 },
                    1e3
                );
            }),
            $(".gotrip-detail-hotel-book-mobile").click(function () {
                $("html, body").animate(
                    { scrollTop: $(".hotel_rooms_form").offset().top - 60 },
                    1e3
                );
            });
    });
document.addEventListener("DOMContentLoaded", () => {
    // Attach a single click listener to the whole document
    document.addEventListener("click", (e) => {
        // Check if the clicked element has the class "show-hide-details"
        if (e.target.classList.contains("show-hide-details")) {
            e.preventDefault();

            const id = e.target.dataset.flightId; // get flight id from data attribute
            showHideDetails(id);
        }
    });
});

function showHideDetails(id) {
    const detailsDiv = document.getElementById(`showHideDetailsDiv-${id}`);
    const toggleLink = document.getElementById(`showHideDetailsA-${id}`);

    if (!detailsDiv || !toggleLink) return; // safety check

    if (
        detailsDiv.style.display === "none" ||
        detailsDiv.style.display === ""
    ) {
        detailsDiv.style.display = "block";
        toggleLink.textContent = "Hide Details";
    } else {
        detailsDiv.style.display = "none";
        toggleLink.textContent = "Show Details";
    }
}
