jQuery,jQuery(document).ready(function(o){var r=document.querySelector("#page header");jQuery("#ast-scroll-top")&&jQuery("#ast-scroll-top").length&&(ast_scroll_top=function(){var o=jQuery("#ast-scroll-top"),e=o.css("content"),t=o.data("on-devices");if(e=e.replace(/[^0-9]/g,""),"both"==t||"desktop"==t&&"769"==e||"mobile"==t&&""==e){var l=window.pageYOffset||document.body.scrollTop;r&&r.length?l>r.offsetHeight+100?o.show():o.hide():300<jQuery(window).scrollTop()?o.show():o.hide()}else o.hide()},ast_scroll_top(),jQuery(window).on("scroll",function(){ast_scroll_top()}),jQuery("#ast-scroll-top").on("click",function(o){o.preventDefault(),jQuery("html,body").animate({scrollTop:0},200)}))});!function(){var e,t;function o(e){var t=document.body.className;t=t.replace(e,""),document.body.className=t}function r(e){e.style.display="block",setTimeout(function(){e.style.opacity=1},1)}function s(e){e.style.opacity="",setTimeout(function(){e.style.display=""},200)}function l(e){if(document.body.classList.contains("ast-header-break-point")){var t=document.querySelector(".main-navigation"),a=document.querySelector(".main-header-bar");if(null!==a&&null!==t){var n=t.offsetHeight,o=a.offsetHeight;if(n&&!document.body.classList.contains("ast-no-toggle-menu-enable"))var s=parseFloat(n)-parseFloat(o);else s=parseFloat(o);e.style.maxHeight=Math.abs(s)+"px"}}}e="iPhone"==navigator.userAgent.match(/iPhone/i)?"iphone":"",t="iPod"==navigator.userAgent.match(/iPod/i)?"ipod":"",document.body.className+=" "+e,document.body.className+=" "+t;for(var a=document.querySelectorAll("a.astra-search-icon:not(.slide-search)"),n=0;a.length>n;n++)a[n].onclick=function(e){if(e.preventDefault(),e=e||window.event,this.classList.contains("header-cover"))for(var t=document.querySelectorAll(".ast-search-box.header-cover"),a=0;a<t.length;a++)for(var n=t[a].parentNode.querySelectorAll("a.astra-search-icon"),o=0;o<n.length;o++)n[o]==this&&(r(t[a]),t[a].querySelector("input.search-field").focus(),l(t[a]));else if(this.classList.contains("full-screen")){var s=document.getElementById("ast-seach-full-screen-form");s.classList.contains("full-screen")&&(r(s),c="full-screen",document.body.className+=" "+c,s.querySelector("input.search-field").focus())}var c};for(var c=document.getElementsByClassName("close"),i=(n=0,c.length);n<i;++n)c[n].onclick=function(e){e=e||window.event;for(var t=this;;){if(t.parentNode.classList.contains("ast-search-box")){s(t.parentNode),o("full-screen");break}if(t.parentNode.classList.contains("site-header"))break;t=t.parentNode}};document.onkeydown=function(e){if(27==e.keyCode){var t=document.getElementById("ast-seach-full-screen-form");null!=t&&(s(t),o("full-screen"));for(var a=document.querySelectorAll(".ast-search-box.header-cover"),n=0;n<a.length;n++)s(a[n])}},window.addEventListener("resize",function(){if("BODY"===document.activeElement.tagName&&"INPUT"!=document.activeElement.tagName){var e=document.querySelectorAll(".ast-search-box.header-cover");if(!document.body.classList.contains("ast-header-break-point"))for(var t=0;t<e.length;t++)e[t].style.maxHeight="",e[t].style.opacity="",e[t].style.display=""}})}();