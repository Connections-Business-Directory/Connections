'use strict';
// https://github.com/Leaflet/Leaflet/issues/2091#issuecomment-527632256
async function fetchImage(url, callback, headers, abort) {
  let _headers = {};
  if (headers) {
    headers.forEach(h => {
      _headers[h.header] = h.value;
    });
  }
  const controller = new AbortController();
  const signal = controller.signal;
  if (abort) {
    abort.subscribe(() => {
      controller.abort();
    });
  }
  const f = await fetch(url, {
    method: "GET",
    headers: new Headers(
    	{ 'User-Agent': 'PostmanRuntime/7.22.0' }
    	),
    mode: "no-cors",
    signal: signal
  });
  const blob = await f.blob();
  callback(blob);
}

L.TileLayer.WMSHeader = L.TileLayer.extend({
  initialize: function (url, options, headers, abort) {
    L.TileLayer.prototype.initialize.call(this, url, options);
    this.headers = headers;
    this.abort = abort;
  },
  createTile(coords, done) {
    const url = this.getTileUrl(coords);
    const img = document.createElement("img");
    img.setAttribute("role", "presentation");

    fetchImage(
      url,
      resp => {
        const reader = new FileReader();
        reader.onload = () => {
          img.src = reader.result;
        };
        reader.readAsDataURL(resp);
        done(null, img);
      },
      this.headers,
      this.abort
    );
    return img;
  }
});

L.TileLayer.withHeader = function (url, options, headers, abort) {
  return new L.TileLayer.WMSHeader(url, options, headers, abort);
};
