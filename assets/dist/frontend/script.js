!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var o in n)e.o(n,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:n[o]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.apiFetch,n=e.n(t);document.querySelector('[data-component="form-user_login"]').addEventListener("submit",(e=>{const t=e.target,o=t.querySelector('[data-component="messages"]'),a=t.querySelector('button[type="submit"]');if(a.classList.add("cbd-field--button__is-loading"),o.innerHTML="",t.dataset.action){const e=new FormData(t);"string"==typeof t.dataset.redirect&&e.append("redirect",t.dataset.redirect),n()({path:t.dataset.action,method:t.method,body:e}).then((e=>{a.classList.remove("cbd-field--button__is-loading"),a.disabled=!1,"string"==typeof e.redirect?window.location.replace(e.redirect):"boolean"==typeof e.reload&&window.location.reload()})).catch((e=>{e.message&&(o.innerHTML="<div>"+e.message+"</div>"),a.classList.remove("cbd-field--button__is-loading"),a.disabled=!1}))}a.disabled=!0,e.preventDefault()}),!1)}();
//# sourceMappingURL=script.js.map