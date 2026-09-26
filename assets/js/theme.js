(function () {
	'use strict';

	var cfg = window.teCore || {};
	var features = cfg.features || {};
	var endpoints = cfg.endpoints || {};
	var i18n = cfg.i18n || {};

	function qs(sel, root) { return (root || document).querySelector(sel); }
	function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

	function digits(value) {
		if (!cfg.persianDigits) return String(value);
		return String(value).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
	}

	function live(message) {
		var el = qs('#te-live');
		if (el) el.textContent = message || '';
	}

	function lock(on) {
		document.documentElement.classList.toggle('te-locked', !!on && !!qs('dialog[open]'));
		if (!on) document.documentElement.classList.remove('te-locked');
	}

	qsa('dialog').forEach(function (dialog) {
		dialog.addEventListener('close', function () { lock(false); });
		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) dialog.close();
		});
	});

	document.addEventListener('click', function (event) {
		var opener = event.target.closest('[data-te-dialog]');
		if (opener) {
			var dialog = document.getElementById(opener.getAttribute('data-te-dialog'));
			if (dialog && dialog.showModal) {
				event.preventDefault();
				dialog.showModal();
				document.documentElement.classList.add('te-locked');
				opener.setAttribute('aria-expanded', 'true');
				if (opener.getAttribute('data-te-dialog') === 'te-wishlist') renderWishlist();
				if (opener.getAttribute('data-te-dialog') === 'te-compare') renderCompare();
			}
		}
		var closer = event.target.closest('[data-te-close]');
		if (closer) {
			var parent = closer.closest('dialog');
			if (parent) parent.close();
		}
		if (event.target.closest('[data-te-dismiss="announce"]')) {
			try { sessionStorage.setItem('te_core_announce', '1'); } catch (e) {}
			document.documentElement.classList.add('te-announce-off');
		}
		var focus = event.target.closest('[data-te-focus]');
		if (focus) {
			var target = document.getElementById(focus.getAttribute('data-te-focus'));
			if (target) {
				target.focus();
				target.scrollIntoView({ block: 'center' });
			}
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === '/' && !event.metaKey && !event.ctrlKey && !event.altKey) {
			var tag = document.activeElement && document.activeElement.tagName;
			if (tag === 'INPUT' || tag === 'TEXTAREA' || (document.activeElement && document.activeElement.isContentEditable)) return;
			var search = qs('#te-q');
			if (!search) return;
			event.preventDefault();
			search.focus();
		}
	});

	var filters = qs('#te-filters');
	var backdrop = qs('.te-filters-backdrop');
	function setFilters(open) {
		if (!filters) return;
		filters.classList.toggle('is-open', open);
		if (backdrop) {
			backdrop.hidden = !open;
			backdrop.classList.toggle('is-open', open);
		}
		document.documentElement.classList.toggle('te-locked', open);
	}
	document.addEventListener('click', function (event) {
		if (event.target.closest('[data-te-open-filters]')) setFilters(true);
		if (event.target.closest('[data-te-close-filters]')) setFilters(false);
	});
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') setFilters(false);
	});

	function post(url, data) {
		var body = new FormData();
		Object.keys(data).forEach(function (key) { body.append(key, data[key]); });
		body.append('nonce', cfg.nonce || '');
		return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (res) {
			return res.json();
		});
	}

	function paintCart(payload) {
		var body = qs('[data-te-cart-body]');
		if (body && payload.html) body.innerHTML = payload.html;
		qsa('[data-te-cart-count]').forEach(function (el) {
			var n = payload.count || 0;
			el.textContent = payload.display || digits(n);
			el.hidden = !n;
		});
		var dialog = qs('#te-cart');
		if (dialog && dialog.showModal && !dialog.open) {
			dialog.showModal();
			document.documentElement.classList.add('te-locked');
		}
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.te-ajax-cart');
		if (!button || !features.ajaxCart || !endpoints.add) return;
		if (button.classList.contains('product_type_variable') || button.classList.contains('product_type_grouped') || button.classList.contains('product_type_external')) return;
		var id = button.getAttribute('data-product_id');
		if (!id) return;
		event.preventDefault();
		button.classList.add('is-loading');
		button.setAttribute('aria-busy', 'true');
		post(endpoints.add, { product_id: id, quantity: 1 }).then(function (res) {
			button.classList.remove('is-loading');
			button.removeAttribute('aria-busy');
			if (res && res.data && res.data.redirect) {
				window.location.href = res.data.redirect;
				return;
			}
			if (!res || !res.success) {
				live((res && res.data && res.data.message) || i18n.error);
				return;
			}
			paintCart(res.data);
			live(i18n.added);
		}).catch(function () {
			button.classList.remove('is-loading');
			window.location.href = button.href;
		});
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest('form.cart');
		if (!form || !features.ajaxCart || !endpoints.add) return;
		if (qs('[name="variation_id"]', form)) return;
		var idField = qs('[name="add-to-cart"]', form);
		if (!idField) return;
		event.preventDefault();
		var qty = qs('[name="quantity"]', form);
		post(endpoints.add, {
			product_id: idField.value,
			quantity: qty ? qty.value : 1
		}).then(function (res) {
			if (res && res.success) {
				paintCart(res.data);
				live(i18n.added);
			} else {
				form.submit();
			}
		}).catch(function () { form.submit(); });
	});

	document.addEventListener('click', function (event) {
		var qtyBtn = event.target.closest('[data-te-qty]');
		if (qtyBtn && endpoints.qty) {
			var wrap = qtyBtn.closest('[data-key]');
			if (!wrap) return;
			var current = parseInt(wrap.getAttribute('data-qty'), 10);
			if (isNaN(current)) current = 1;
			var next = Math.max(0, current + parseInt(qtyBtn.getAttribute('data-te-qty'), 10));
			post(endpoints.qty, { key: wrap.getAttribute('data-key'), quantity: next }).then(function (res) {
				if (res && res.success) paintCart(res.data);
			});
		}
		var remove = event.target.closest('[data-te-remove]');
		if (remove && endpoints.remove) {
			post(endpoints.remove, { key: remove.getAttribute('data-te-remove') }).then(function (res) {
				if (res && res.success) {
					paintCart(res.data);
					live(i18n.removed);
				}
			});
		}
	});

	var wishKey = 'te_core_wishlist';
	function readIds(key) {
		try { return JSON.parse(localStorage.getItem(key) || '[]') || []; } catch (e) { return []; }
	}
	function writeIds(key, ids) {
		var unique = [];
		ids.forEach(function (id) {
			id = parseInt(id, 10);
			if (id && unique.indexOf(id) === -1) unique.push(id);
		});
		try { localStorage.setItem(key, JSON.stringify(unique.slice(0, 24))); } catch (e) {}
		return unique.slice(0, 24);
	}
	function paintWish() {
		var ids = readIds(wishKey);
		qsa('[data-te-wish]').forEach(function (btn) {
			var on = ids.indexOf(parseInt(btn.getAttribute('data-te-wish'), 10)) !== -1;
			btn.classList.toggle('is-on', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		qsa('[data-te-wish-count]').forEach(function (el) {
			el.textContent = digits(ids.length);
			el.hidden = !ids.length;
		});
	}
	function renderWishlist() {
		var box = qs('[data-te-wish-body]');
		if (!box || !endpoints.cards) return;
		var ids = readIds(wishKey);
		if (!ids.length) {
			box.innerHTML = '<p class="te-empty-inline">' + (i18n.empty || '') + '</p>';
			return;
		}
		fetch(endpoints.cards + '?ids=' + encodeURIComponent(ids.join(',')), { credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (data) { box.innerHTML = (data && data.html) || ''; paintWish(); paintCompare(); });
	}
	if (features.wishlist) {
		paintWish();
		document.addEventListener('click', function (event) {
			var btn = event.target.closest('[data-te-wish]');
			if (!btn) return;
			event.preventDefault();
			var id = parseInt(btn.getAttribute('data-te-wish'), 10);
			var ids = readIds(wishKey);
			var index = ids.indexOf(id);
			if (index === -1) ids.push(id); else ids.splice(index, 1);
			writeIds(wishKey, ids);
			paintWish();
		});
	}

	if (cfg.productId) {
		var recent = readIds('te_core_recent').filter(function (id) { return id !== cfg.productId; });
		recent.unshift(cfg.productId);
		writeIds('te_core_recent', recent);
	}
	qsa('[data-te-recent]').forEach(function (section) {
		var ids = readIds('te_core_recent').filter(function (id) { return id !== cfg.productId; });
		var limit = parseInt(section.getAttribute('data-count'), 10) || 4;
		ids = ids.slice(0, limit);
		if (!ids.length || !endpoints.cards) return;
		section.hidden = false;
		fetch(endpoints.cards + '?ids=' + encodeURIComponent(ids.join(',')), { credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				var body = qs('[data-te-recent-body]', section);
				if (body) body.innerHTML = (data && data.html) || '';
				paintWish();
				paintCompare();
			});
	});

	var input = qs('#te-q');
	var list = qs('#te-suggest');
	var timer = 0;
	var controller = null;
	var active = -1;
	var searchKey = 'te_core_searches';

	function closeSuggest() {
		if (!list) return;
		list.hidden = true;
		list.innerHTML = '';
		if (input) input.setAttribute('aria-expanded', 'false');
		active = -1;
	}
	function setActive(next) {
		var options = qsa('[role="option"]', list);
		options.forEach(function (el) { el.classList.remove('is-active'); });
		if (!options.length) return;
		active = (next + options.length) % options.length;
		options[active].classList.add('is-active');
		input.setAttribute('aria-activedescendant', options[active].id);
	}
	function readSearches() {
		var raw = readIds(searchKey);
		if (!Array.isArray(raw)) return [];
		return raw.map(String).filter(function (item) { return item && item.length >= 2; }).slice(0, 5);
	}
	function rememberSearch(q) {
		q = String(q || '').trim().slice(0, 80);
		if (q.length < 2 || !features.recentSearch) return;
		var items = readSearches().filter(function (item) { return item !== q; });
		items.unshift(q);
		try { localStorage.setItem(searchKey, JSON.stringify(items.slice(0, 5))); } catch (e) {}
	}
	function searchHref(q) {
		var action = (input.form && input.form.getAttribute('action')) || window.location.pathname;
		var url = new URL(action, window.location.origin);
		url.searchParams.set('s', q);
		var type = qs('[name="post_type"]', input.form);
		if (type) url.searchParams.set('post_type', type.value);
		return url.toString();
	}
	function showRecent() {
		if (!features.recentSearch || !list || !input || input.value.trim().length >= 2) return;
		var items = [];
		try { items = JSON.parse(localStorage.getItem(searchKey) || '[]') || []; } catch (e) { items = []; }
		items = items.filter(function (item) { return typeof item === 'string' && item.length >= 2; }).slice(0, 5);
		if (!items.length) { closeSuggest(); return; }
		var html = '<p class="te-suggest__label">' + escapeHtml(i18n.recent || '') + '</p>';
		items.forEach(function (item, i) {
			html += '<a role="option" id="te-opt-' + i + '" data-q="' + escapeHtml(item) + '" href="' + escapeHtml(searchHref(item)) + '"><span>' + escapeHtml(item) + '</span></a>';
		});
		list.innerHTML = html;
		list.hidden = false;
		input.setAttribute('aria-expanded', 'true');
		active = -1;
	}
	if (input && list && (features.predictive || features.recentSearch)) {
		input.addEventListener('focus', function () {
			if (input.value.trim().length < 2) showRecent();
		});
		input.addEventListener('input', function () {
			var q = input.value.trim();
			window.clearTimeout(timer);
			if (q.length < 2) {
				if (features.recentSearch) showRecent();
				else closeSuggest();
				return;
			}
			if (!features.predictive || !endpoints.search) { closeSuggest(); return; }
			timer = window.setTimeout(function () {
				if (controller) controller.abort();
				controller = new AbortController();
				fetch(endpoints.search + '?q=' + encodeURIComponent(q), { signal: controller.signal, credentials: 'same-origin' })
					.then(function (res) { return res.json(); })
					.then(function (data) {
						if (input.value.trim() !== q) return;
						renderSuggest(data, q);
					})
					.catch(function () {});
			}, 160);
		});
		input.addEventListener('keydown', function (event) {
			if (list.hidden) return;
			if (event.key === 'ArrowDown') { event.preventDefault(); setActive(active + 1); }
			if (event.key === 'ArrowUp') { event.preventDefault(); setActive(active - 1); }
			if (event.key === 'Escape') closeSuggest();
			if (event.key === 'Enter' && active >= 0) {
				var options = qsa('[role="option"]', list);
				if (options[active]) {
					event.preventDefault();
					if (options[active].getAttribute('data-q')) rememberSearch(options[active].getAttribute('data-q'));
					window.location.href = options[active].href;
				}
			}
		});
		if (input.form) {
			input.form.addEventListener('submit', function () { rememberSearch(input.value); });
		}
		list.addEventListener('click', function (event) {
			var opt = event.target.closest('[data-q]');
			if (opt) rememberSearch(opt.getAttribute('data-q'));
		});
		document.addEventListener('click', function (event) {
			if (!event.target.closest('.te-search')) closeSuggest();
		});
	}

	function renderSuggest(data, q) {
		var html = '';
		var n = 0;
		(data.categories || []).forEach(function (cat) {
			html += '<a role="option" id="te-opt-' + (n++) + '" href="' + escapeHtml(cat.url) + '"><span class="te-suggest__ph"></span><span>' + escapeHtml(cat.name) + '<small>' + escapeHtml(cat.count || '') + '</small></span></a>';
		});
		(data.products || []).forEach(function (item) {
			var img = item.image ? '<img alt="" width="44" height="44" src="' + escapeHtml(item.image) + '">' : '<span class="te-suggest__ph"></span>';
			html += '<a role="option" id="te-opt-' + (n++) + '" href="' + escapeHtml(item.url) + '">' + img + '<span>' + escapeHtml(item.name) + '<small>' + escapeHtml(item.price || '') + '</small></span></a>';
		});
		if (!n) html = '<p class="te-suggest__empty">' + escapeHtml(i18n.empty || '') + '</p>';
		if (data.view_all) html += '<a role="option" id="te-opt-' + (n++) + '" href="' + escapeHtml(data.view_all) + '"><span>' + escapeHtml(i18n.viewAll || '') + '</span></a>';
		list.innerHTML = html;
		list.hidden = false;
		input.setAttribute('aria-expanded', 'true');
		active = -1;
	}

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function (ch) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
		});
	}

	var quick = qs('#te-quick');
	document.addEventListener('click', function (event) {
		var btn = event.target.closest('[data-te-quick]');
		if (!btn || !features.quickView || !quick || !endpoints.quick) return;
		event.preventDefault();
		var body = qs('[data-te-quick-body]');
		if (body) body.innerHTML = '<p>' + escapeHtml(i18n.loading || '') + '</p>';
		quick.showModal();
		document.documentElement.classList.add('te-locked');
		fetch(endpoints.quick + '?id=' + encodeURIComponent(btn.getAttribute('data-te-quick')), { credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (body) body.innerHTML = (data && data.html) || '';
			})
			.catch(function () {
				if (body) body.innerHTML = '<p>' + escapeHtml(i18n.error || '') + '</p>';
			});
	});

	var compareKey = 'te_core_compare';
	function compareIds() {
		var ids = readIds(compareKey).map(function (id) { return parseInt(id, 10); }).filter(Boolean);
		if (ids.length > 4) {
			ids = ids.slice(0, 4);
			writeIds(compareKey, ids);
		}
		return ids.slice(0, 4);
	}
	function paintCompare() {
		if (!features.compare) return;
		var ids = compareIds();
		qsa('[data-te-compare]').forEach(function (btn) {
			var on = ids.indexOf(parseInt(btn.getAttribute('data-te-compare'), 10)) !== -1;
			btn.classList.toggle('is-on', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		qsa('[data-te-compare-count]').forEach(function (el) {
			el.textContent = digits(ids.length);
			el.hidden = !ids.length;
		});
	}
	function renderCompare() {
		var box = qs('[data-te-compare-body]');
		if (!box) return;
		var ids = compareIds();
		if (!ids.length) {
			box.innerHTML = '<p class="te-empty-inline">' + escapeHtml(i18n.compareEmpty || '') + '</p>';
			return;
		}
		if (!endpoints.compare) return;
		box.setAttribute('aria-busy', 'true');
		fetch(endpoints.compare + '?ids=' + encodeURIComponent(ids.join(',')), { credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				box.innerHTML = (data && data.html) || '';
				box.removeAttribute('aria-busy');
			})
			.catch(function () {
				box.innerHTML = '<p class="te-empty-inline">' + escapeHtml(i18n.error || '') + '</p>';
				box.removeAttribute('aria-busy');
			});
	}
	if (features.compare) {
		paintCompare();
		document.addEventListener('click', function (event) {
			var drop = event.target.closest('[data-te-compare-remove]');
			if (drop) {
				var dropId = parseInt(drop.getAttribute('data-te-compare-remove'), 10);
				writeIds(compareKey, readIds(compareKey).filter(function (id) { return parseInt(id, 10) !== dropId; }));
				paintCompare();
				renderCompare();
				return;
			}
			var btn = event.target.closest('[data-te-compare]');
			if (!btn) return;
			event.preventDefault();
			var id = parseInt(btn.getAttribute('data-te-compare'), 10);
			if (!id) return;
			var ids = compareIds();
			var index = ids.indexOf(id);
			if (index === -1 && ids.length >= 4) {
				live(i18n.compareFull);
				return;
			}
			if (index === -1) ids.push(id);
			else ids.splice(index, 1);
			writeIds(compareKey, ids.slice(0, 4));
			paintCompare();
			live(index === -1 ? i18n.compareAdded : i18n.removed);
		});
	}

	if (features.buybar && 'IntersectionObserver' in window) {
		var bar = qs('.te-buybar');
		var buyForm = qs('form.cart');
		if (bar && buyForm) {
			var buyObserver = new IntersectionObserver(function (entries) {
				var entry = entries[0];
				var past = !entry.isIntersecting && entry.boundingClientRect.top < 0;
				bar.hidden = !past;
			}, { rootMargin: '-72px 0px 0px 0px', threshold: 0 });
			buyObserver.observe(buyForm);
			var buy = qs('[data-te-buybar]', bar);
			if (buy) {
				buy.addEventListener('click', function () {
					if (buy.hasAttribute('data-te-buybar-options')) {
						buyForm.scrollIntoView({ block: 'center' });
						var field = qs('select, input[type="radio"]', buyForm);
						if (field) field.focus();
						return;
					}
					var real = qs('.single_add_to_cart_button', buyForm);
					if (real) real.click();
				});
			}
		}
	}

	if (features.coupon && endpoints.coupon) {
		document.addEventListener('submit', function (event) {
			var form = event.target.closest('[data-te-coupon]');
			if (!form) return;
			event.preventDefault();
			var field = qs('input[name="coupon"]', form);
			post(endpoints.coupon, { coupon: field ? field.value : '' }).then(function (res) {
				if (res && res.success) {
					paintCart(res.data);
					live(i18n.coupon);
				} else {
					live((res && res.data && res.data.message) || i18n.error);
				}
			}).catch(function () { live(i18n.error); });
		});
		document.addEventListener('click', function (event) {
			var removeCoupon = event.target.closest('[data-te-coupon-remove]');
			if (!removeCoupon) return;
			post(endpoints.coupon, { coupon: removeCoupon.getAttribute('data-te-coupon-remove'), remove: '1' }).then(function (res) {
				if (res && res.success) {
					paintCart(res.data);
					live(i18n.removed);
				} else {
					live((res && res.data && res.data.message) || i18n.error);
				}
			});
		});
	}

	if (document.body.classList.contains('te-motion') === false && !document.body.classList.contains('te-reduce')) {
		document.body.classList.add('te-motion');
	}
})();
