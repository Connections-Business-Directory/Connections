jQuery((function(t){const e=[];let n=0;t.fn.mapBlock=function(a){const o={};t.each(t(this).get(0).attributes,(function(t,e){"data"===e.name.substring(0,4)&&(o[e.name]=e.value.replace(/_/g," "))})),t.each(o,(function(t,e){const n=t.replace("data-","").split("-");1===n.length&&(a[n[0]]=e)}));const i=t(this).attr("id");t("#"+i).on("appear",{id:n,data:a},(function(n,a){a.each((function(){const a=n.data.id,o=n.data.data,i=t(this).attr("id");if(void 0===e[a]){e[a]=L.map(i).setView(L.latLng(o.center.split(",")),1),e[a].attributionControl.setPrefix("");const n={};t.each(t(this).children("map-tilelayer"),(function(o,i){const r=t(i),s=/^google/.test(r.data("id"))?L.gridLayer.googleMutant({type:r.data("type"),attribution:r.html()}):L.tileLayer(r.data("url"),{attribution:r.html(),subdomains:r.data("subdomains"),minZoom:r.data("minzoom"),maxZoom:r.data("maxzoom")});t.isEmptyObject(n)&&s.addTo(e[a]);const d=t("map-control-layers").find('[data-id="'+r.data("id")+'"]');d.length&&(n[d.html()]=s)}));const r=[],s={},d=L.latLngBounds([]);let c=0;if(t.each(t(this).children("map-layergroup"),(function(n,o){const i=t(o).data("id"),l=[];t.each(t(this).children("map-marker"),(function(e,n){const a=t(n),o=a.data("latitude"),i=a.data("longitude"),r=a.find("map-marker-popup");0===r.length?l.push(L.marker([o,i])):l.push(L.marker([o,i]).bindPopup(r.html())),c++})),r[i]=L.featureGroup(l).addTo(e[a]),d.extend(r[i].getBounds());const u=t("map-control-layers").find('[data-mapID="'+i+'"]');u.length&&(s[u.html()]=r[i])})),t.each(t(this).children("map-marker"),(function(n,o){const i=[],r=t(o),s=r.data("latitude"),l=r.data("longitude"),u=r.find("map-marker-popup");0===u.length?i.push(L.marker([s,l])):i.push(L.marker([s,l]).bindPopup(u.html()));const p=L.featureGroup(i).addTo(e[a]);d.extend(p.getBounds()),c++})),!t.isEmptyObject(n)||!t.isEmptyObject(s)){const o=t(this).find("map-control-layers");L.control.layers(n,s,{collapsed:o.data("collapsed"),hideSingleBase:!0}).addTo(e[a])}d.isValid()&&e[a].fitBounds(d,{padding:L.point(20,20)}),1>=c&&e[a].setZoom(o.zoom),e[a].on("overlayadd overlayremove",(function(t){const n=new L.LatLngBounds([]);e[a].eachLayer((function(t){t instanceof L.FeatureGroup&&n.extend(t.getBounds())})),n.isValid()?e[a].fitBounds(n):e[a].fitWorld()}))}}))})),t.inView("#"+i),n++},t("map-block").each((function(){t(this).mapBlock({})})),t.force_appear()})),function(t){const e=[];let n=!1,a=!1;const o={interval:250,force_process:!1},i=t(window),r=[],s=[];function d(){a=!1;for(let a=0,o=e.length;a<o;a++){const o=(n=e[a],t(n).filter((function(){return t(this).is(":appeared")})));if(o.length&&!0!==s[a]&&(o.trigger("appear",[o]),s[a]=!0),r[a]){const t=r[a].not(o);t.length&&(t.trigger("disappear",[t]),s[a]=!1)}r[a]=o}var n}t.expr.pseudos.appeared=t.expr.createPseudo((function(){return function(e){const n=t(e);if(!n.is(":visible"))return!1;const a=i.scrollLeft(),o=i.scrollTop(),r=n.offset(),s=r.left,d=r.top;return d+n.height()>=o&&d-(n.data("in-view-top-offset")||0)<=o+i.height()&&s+n.width()>=a&&s-(n.data("in-view-left-offset")||0)<=a+i.width()}})),t.fn.extend({inView(e,n){return t.inView(this,n),this}}),t.extend({inView(i,s){const c=t.extend({},o,s||{});if(!n){const e=function(){a||(a=!0,setTimeout(d,c.interval))};t(window).on("scroll",e).on("resize",e).on("click",e),n=!0}c.force_process&&setTimeout(d,c.interval),function(t){e.push(t),r.push()}(i)},force_appear(){return!!n&&(d(),!0)}})}(jQuery);
//# sourceMappingURL=script.js.map