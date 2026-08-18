(function () {
  'use strict';

  const one = (selector, root = document) => root.querySelector(selector);
  const all = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function initHeader(root = document) {
    all('.porumbesti-header-wrap:not([data-ready])', root).forEach((header) => {
      header.dataset.ready = 'true';
      const syncHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
      window.addEventListener('scroll', syncHeader, { passive: true });
      syncHeader();
      const toggle = one('.porumbesti-nav-toggle', header);
      const nav = one('.porumbesti-main-nav', header);
      let setNavigation = () => {};
      const submenuParents = all('.porumbesti-menu .menu-item-has-children', header);
      const topLevelParents = submenuParents.filter((item) => item.parentElement?.classList.contains('porumbesti-menu'));
      const closeSubmenu = (item, restoreFocus = false) => {
        window.clearTimeout(item.porumbestiCloseTimer);
        all('.menu-item-has-children.is-submenu-open', item).reverse().forEach((child) => {
          window.clearTimeout(child.porumbestiCloseTimer);
          child.classList.remove('is-submenu-open', 'opens-left');
          one(':scope > [data-porumbesti-submenu-toggle]', child)?.setAttribute('aria-expanded', 'false');
        });
        item.classList.remove('is-submenu-open');
        item.classList.remove('opens-left');
        const button = one(':scope > [data-porumbesti-submenu-toggle]', item);
        button?.setAttribute('aria-expanded', 'false');
        if (restoreFocus) button?.focus();
      };
      const closeAllSubmenus = (except = null) => topLevelParents.forEach((item) => {
        if (item !== except) closeSubmenu(item);
      });
      const closeSiblingSubmenus = (item) => {
        Array.from(item.parentElement?.children || []).forEach((sibling) => {
          if (sibling !== item && sibling.classList.contains('menu-item-has-children')) closeSubmenu(sibling);
        });
      };
      const positionSubmenu = (item) => {
        item.classList.remove('opens-left');
        if (window.matchMedia('(max-width: 1040px)').matches || item.parentElement?.classList.contains('porumbesti-menu')) return;
        const submenu = one(':scope > .sub-menu', item);
        if (submenu && submenu.getBoundingClientRect().right > window.innerWidth - 16) item.classList.add('opens-left');
      };
      const openSubmenu = (item) => {
        window.clearTimeout(item.porumbestiCloseTimer);
        closeSiblingSubmenus(item);
        item.classList.add('is-submenu-open');
        one(':scope > [data-porumbesti-submenu-toggle]', item)?.setAttribute('aria-expanded', 'true');
        positionSubmenu(item);
      };

      submenuParents.forEach((item, index) => {
        const button = one(':scope > [data-porumbesti-submenu-toggle]', item);
        const submenu = one(':scope > .sub-menu', item);
        const parentLink = one(':scope > a', item);
        if (!button || !submenu) return;
        if (!submenu.id) submenu.id = `porumbesti-submenu-${header.dataset.elementorId || 'header'}-${index}`;
        button.setAttribute('aria-controls', submenu.id);
        parentLink?.setAttribute('aria-haspopup', 'true');

        button.addEventListener('click', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            openSubmenu(item);
          } else if (item.classList.contains('is-submenu-open')) {
            closeSubmenu(item);
          } else {
            openSubmenu(item);
          }
        });
        button.addEventListener('keydown', (event) => {
          if (!['ArrowDown', 'ArrowUp', 'ArrowRight', 'ArrowLeft'].includes(event.key)) return;
          event.preventDefault();
          if (event.key === 'ArrowLeft') {
            closeSubmenu(item, true);
            return;
          }
          openSubmenu(item);
          const links = all('a', submenu);
          (event.key === 'ArrowUp' ? links[links.length - 1] : links[0])?.focus();
        });
        item.addEventListener('pointerenter', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) openSubmenu(item);
        });
        item.addEventListener('pointerleave', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            item.porumbestiCloseTimer = window.setTimeout(() => closeSubmenu(item), 220);
          }
        });
        item.addEventListener('focusin', (event) => {
          if (event.target !== button) openSubmenu(item);
        });
        item.addEventListener('focusout', (event) => {
          if (!item.contains(event.relatedTarget)) item.porumbestiCloseTimer = window.setTimeout(() => closeSubmenu(item), 80);
        });
      });

      if (toggle && nav) {
        setNavigation = (open) => {
          nav.classList.toggle('is-open', open);
          toggle.setAttribute('aria-expanded', String(open));
          toggle.setAttribute('aria-label', toggle.dataset[open ? 'labelClose' : 'labelOpen'] || 'Meniu');
          document.body.classList.toggle('porumbesti-nav-open', open && window.matchMedia('(max-width: 1040px)').matches);
          const icon = one('.dashicons', toggle);
          icon?.classList.toggle('dashicons-menu-alt3', !open);
          icon?.classList.toggle('dashicons-no-alt', open);
          if (!open) closeAllSubmenus();
          if (open) window.requestAnimationFrame(() => one('a', nav)?.focus());
        };
        toggle.addEventListener('click', () => {
          setNavigation(!nav.classList.contains('is-open'));
        });
        all('a', nav).forEach((link) => link.addEventListener('click', () => {
          if (window.matchMedia('(max-width: 1040px)').matches) setNavigation(false);
        }));
      }
      const lang = one('.porumbesti-lang', header);
      const langTrigger = one('.porumbesti-lang-trigger', header);
      if (lang && langTrigger) {
        let langTimer;
        const setLanguage = (open) => {
          window.clearTimeout(langTimer);
          lang.classList.toggle('is-open', open);
          langTrigger.setAttribute('aria-expanded', String(open));
        };
        langTrigger.addEventListener('click', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            setLanguage(true);
          } else {
            setLanguage(!lang.classList.contains('is-open'));
          }
        });
        lang.addEventListener('pointerenter', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) setLanguage(true);
        });
        lang.addEventListener('pointerleave', () => {
          if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) langTimer = window.setTimeout(() => setLanguage(false), 220);
        });
        lang.addEventListener('focusin', (event) => {
          if (event.target !== langTrigger) setLanguage(true);
        });
        lang.addEventListener('focusout', (event) => { if (!lang.contains(event.relatedTarget)) setLanguage(false); });
      }

      header.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const openItems = all('.menu-item-has-children.is-submenu-open', header);
        const openItem = event.target.closest?.('.menu-item-has-children.is-submenu-open') || openItems[openItems.length - 1];
        if (openItem) { closeSubmenu(openItem, true); return; }
        if (lang?.classList.contains('is-open')) { lang.classList.remove('is-open'); langTrigger?.setAttribute('aria-expanded', 'false'); langTrigger?.focus(); return; }
        if (nav?.classList.contains('is-open')) { setNavigation(false); toggle?.focus(); }
      });
      document.addEventListener('click', (event) => {
        if (!header.contains(event.target)) {
          closeAllSubmenus();
          lang?.classList.remove('is-open');
          langTrigger?.setAttribute('aria-expanded', 'false');
          if (nav?.classList.contains('is-open')) setNavigation(false);
        }
      });
      window.addEventListener('resize', () => {
        all('.menu-item-has-children.is-submenu-open', header).forEach(positionSubmenu);
        if (window.matchMedia('(min-width: 1041px)').matches && nav?.classList.contains('is-open')) setNavigation(false);
      }, { passive: true });
    });
  }

  function initFilters(root = document) {
    all('.porumbesti-document-widget:not([data-ready]), .porumbesti-document-library:not([data-ready])', root).forEach((widget) => {
      widget.dataset.ready = 'true';
      let activeFilter = 'all';
      let term = '';
      const items = all('[data-porumbesti-category]', widget);
      const empty = one('.porumbesti-no-results', widget);

      const apply = () => {
        let visible = 0;
        items.forEach((item) => {
          const categories = (item.dataset.porumbestiCategory || '').split(' ');
          const title = (item.dataset.porumbestiTitle || item.textContent).toLowerCase();
          const show = (activeFilter === 'all' || categories.includes(activeFilter)) && (!term || title.includes(term));
          item.hidden = !show;
          if (show) visible += 1;
        });
        if (empty) empty.hidden = visible > 0;
      };

      all('[data-porumbesti-filter]', widget).forEach((button) => {
        button.addEventListener('click', () => {
          activeFilter = button.dataset.porumbestiFilter;
          all('[data-porumbesti-filter]', widget).forEach((other) => other.classList.toggle('is-active', other === button));
          apply();
        });
      });
      const search = one('[data-porumbesti-doc-search]', widget);
      if (search) search.addEventListener('input', () => { term = search.value.trim().toLowerCase(); apply(); });
    });
  }

  function initContact(root = document) {
    all('.porumbesti-contact-form:not([data-ready])', root).forEach((form) => {
      form.dataset.ready = 'true';
      form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const status = one('.porumbesti-form-status', form);
        const button = one('[type="submit"]', form);
        if (!form.reportValidity()) return;
        const original = button.textContent;
        button.disabled = true;
        button.textContent = window.porumbestiWidgets?.i18n?.sending || 'Se trimite…';
        status.className = 'porumbesti-form-status';
        const data = new FormData(form);
        if (!data.has('action')) data.append('action', 'porumbesti_contact');
        if (!data.has('nonce')) data.append('nonce', window.porumbestiWidgets?.nonce || '');
        try {
          const response = await fetch(window.porumbestiWidgets?.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body: data, credentials: 'same-origin' });
          const payload = await response.json();
          if (!response.ok || !payload.success) throw new Error(payload?.data?.message || window.porumbestiWidgets?.i18n?.error);
          status.textContent = payload.data.message;
          status.classList.add('is-success');
          form.reset();
        } catch (error) {
          status.textContent = error.message || window.porumbestiWidgets?.i18n?.error || 'A apărut o eroare.';
          status.classList.add('is-error');
        } finally {
          button.disabled = false;
          button.textContent = original;
        }
      });
    });
  }

  function initAccessibility(root = document) {
    all('.porumbesti-a11y:not([data-ready])', root).forEach((widget) => {
      widget.dataset.ready = 'true';
      const panel = one('.porumbesti-a11y-panel', widget);
      const toggle = one('[data-porumbesti-a11y-toggle]', widget);
      const top = one('[data-porumbesti-top]', widget);
      let state;
      try { state = JSON.parse(localStorage.getItem('porumbesti-a11y') || '{}'); } catch (e) { state = {}; }
      state = Object.assign({ scale: 100, contrast: false, grayscale: false, underline: false }, state);

      const apply = () => {
        document.documentElement.style.fontSize = `${state.scale}%`;
        document.body.classList.toggle('porumbesti-high-contrast', state.contrast);
        document.body.classList.toggle('porumbesti-grayscale', state.grayscale);
        document.documentElement.classList.toggle('porumbesti-grayscale-root', state.grayscale);
        document.body.classList.toggle('porumbesti-underline', state.underline);
        const scaleLabel = one('[data-porumbesti-scale-label]', widget);
        if (scaleLabel) scaleLabel.textContent = `${state.scale}%`;
        all('[data-porumbesti-a11y]', widget).forEach((button) => {
          const on = Boolean(state[button.dataset.porumbestiA11y]);
          button.classList.toggle('is-on', on);
          button.setAttribute('aria-pressed', String(on));
        });
        localStorage.setItem('porumbesti-a11y', JSON.stringify(state));
      };
      toggle?.addEventListener('click', () => {
        panel.hidden = !panel.hidden;
        toggle.setAttribute('aria-expanded', String(!panel.hidden));
      });
      all('[data-porumbesti-scale]', widget).forEach((button) => button.addEventListener('click', () => {
        state.scale = Math.max(90, Math.min(130, state.scale + (button.dataset.porumbestiScale === 'up' ? 10 : -10)));
        apply();
      }));
      all('[data-porumbesti-a11y]', widget).forEach((button) => button.addEventListener('click', () => { const key = button.dataset.porumbestiA11y; state[key] = !state[key]; apply(); }));
      one('[data-porumbesti-reset]', widget)?.addEventListener('click', () => { state = { scale: 100, contrast: false, grayscale: false, underline: false }; apply(); });
      top?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
      window.addEventListener('scroll', () => top?.classList.toggle('is-visible', window.scrollY > 500), { passive: true });
      apply();
    });
  }

  function initSearch(root = document) {
    all('[data-porumbesti-search-modal]:not([data-ready])', root).forEach((modal) => {
      modal.dataset.ready = 'true';
      one('[data-porumbesti-search-close]', modal)?.addEventListener('click', () => { modal.hidden = true; });
      modal.addEventListener('click', (event) => { if (event.target === modal) modal.hidden = true; });
    });
  }

  const documentExtension = (link) => {
    const match = (link.getAttribute('href') || '').match(/\.(csv|docx?|od[st]|pdf|pptx?|rtf|xlsx?|zip)(?:[?#]|$)/i);
    return match ? match[1].toUpperCase() : '';
  };

  function decorateDocumentLink(link, extension, listItem = null) {
    if (link.dataset.porumbestiDownloadReady) return;
    link.dataset.porumbestiDownloadReady = 'true';
    const downloadLabel = window.porumbestiWidgets?.i18n?.downloadFile || 'Download document';
    const icon = document.createElement('span');
    icon.className = `dashicons ${listItem ? 'dashicons-media-document porumbesti-file-icon' : 'dashicons-download'}`;
    icon.setAttribute('aria-hidden', 'true');

    if (!listItem) {
      link.classList.add('porumbesti-inline-download');
      link.prepend(icon);
      return;
    }

    listItem.classList.add('porumbesti-download-item');
    listItem.dataset.fileType = extension;
    link.before(icon);
    const detail = document.createElement('small');
    detail.className = 'porumbesti-file-detail';
    detail.textContent = `${extension} · ${downloadLabel}`;
    link.appendChild(detail);
    const action = document.createElement('span');
    action.className = 'dashicons dashicons-download porumbesti-file-action';
    action.setAttribute('aria-hidden', 'true');
    listItem.appendChild(action);
  }

  function initDocumentLists(root = document) {
    all('.porumbesti-single-content ul:not([data-porumbesti-download-ready])', root).forEach((list) => {
      list.dataset.porumbestiDownloadReady = 'true';
      const items = all(':scope > li', list);
      const documents = items.map((item) => {
        const link = one(':scope > a', item);
        const extension = link ? documentExtension(link) : '';
        return extension ? { item, link, extension } : null;
      }).filter(Boolean);
      if (!documents.length) return;

      list.classList.add('porumbesti-download-list');
      list.closest('.porumbesti-single')?.classList.add('has-document-list');
      documents.forEach((documentItem) => decorateDocumentLink(documentItem.link, documentItem.extension, documentItem.item));
      items.filter((item) => !item.classList.contains('porumbesti-download-item')).forEach((item) => item.classList.add('porumbesti-download-heading'));
    });

    all('.porumbesti-single-content a:not([data-porumbesti-download-ready])', root).forEach((link) => {
      const extension = documentExtension(link);
      if (extension) decorateDocumentLink(link, extension);
    });
  }

  const lightboxCandidateSelector = [
    '.porumbesti-gallery a',
    '.porumbesti-legacy-gallery img',
    '.porumbesti-legacy-media > img',
    '.porumbesti-single-image img',
    '.porumbesti-single-content img',
    '.wp-block-image img',
    '.wp-caption img',
    '.porumbesti-richtext img',
    '.porumbesti-media-frame img',
    '.porumbesti-person-photo img',
    '.porumbesti-cta-image img',
  ].join(', ');
  let lightbox = null;
  let lightboxItems = [];
  let lightboxIndex = 0;
  let lightboxPreviousFocus = null;

  const isImageUrl = (url) => /^(?:data:image\/|blob:)|\.(?:avif|gif|jpe?g|png|webp)(?:[?#]|$)/i.test(url || '');

  function largestImageSource(image) {
    const candidates = (image.getAttribute('srcset') || '').split(',').map((candidate) => {
      const parts = candidate.trim().split(/\s+/);
      return { url: parts[0] || '', width: parseInt(parts[1], 10) || 0 };
    }).filter((candidate) => candidate.url);
    candidates.sort((left, right) => right.width - left.width);
    return candidates[0]?.url || image.currentSrc || image.src || '';
  }

  function resolveLightboxTrigger(candidate) {
    if (candidate.matches('a')) return candidate;
    const link = candidate.closest('a');
    if (!link) return candidate;
    return link.matches('[data-porumbesti-lightbox]') || isImageUrl(link.getAttribute('href')) ? link : null;
  }

  function lightboxItem(trigger) {
    const image = trigger.matches('img') ? trigger : one('img', trigger);
    if (!image) return null;
    const href = trigger.matches('a') ? trigger.getAttribute('href') || '' : '';
    const source = (trigger.hasAttribute('data-porumbesti-lightbox') || isImageUrl(href)) ? href : largestImageSource(image);
    if (!source) return null;
    const figureCaption = trigger.closest('figure')?.querySelector('figcaption')?.textContent?.trim() || '';
    const visibleCaption = one(':scope > span', trigger)?.textContent?.trim() || '';
    const caption = trigger.dataset.porumbestiLightboxCaption || figureCaption || visibleCaption || image.alt || '';
    return { trigger, source, caption, alt: image.alt || caption };
  }

  function collectLightboxItems(root) {
    const triggers = [];
    all(lightboxCandidateSelector, root).forEach((candidate) => {
      const trigger = resolveLightboxTrigger(candidate);
      if (trigger && !triggers.includes(trigger)) triggers.push(trigger);
    });
    return triggers.map(lightboxItem).filter(Boolean);
  }

  function lightboxGroupRoot(trigger) {
    return trigger.closest('.porumbesti-gallery, .porumbesti-legacy-gallery, .porumbesti-single, .porumbesti-content-media, .porumbesti-person, .porumbesti-cta') || trigger.parentElement;
  }

  function formatCounter(position, total) {
    return (window.porumbestiWidgets?.i18n?.imageCounter || 'Image %1$d of %2$d')
      .replace('%1$d', String(position))
      .replace('%2$d', String(total));
  }

  function showLightboxItem(index) {
    if (!lightbox || !lightboxItems.length) return;
    lightboxIndex = (index + lightboxItems.length) % lightboxItems.length;
    const item = lightboxItems[lightboxIndex];
    const image = one('[data-porumbesti-lightbox-image]', lightbox);
    const caption = one('[data-porumbesti-lightbox-text]', lightbox);
    const counter = one('[data-porumbesti-lightbox-counter]', lightbox);
    const hasMultiple = lightboxItems.length > 1;
    image.src = item.source;
    image.alt = item.alt;
    caption.textContent = item.caption;
    caption.hidden = !item.caption;
    counter.textContent = formatCounter(lightboxIndex + 1, lightboxItems.length);
    all('[data-porumbesti-lightbox-nav]', lightbox).forEach((button) => { button.hidden = !hasMultiple; });
  }

  function closeLightbox() {
    if (!lightbox || lightbox.hidden) return;
    lightbox.hidden = true;
    document.body.classList.remove('porumbesti-lightbox-open');
    one('[data-porumbesti-lightbox-image]', lightbox)?.removeAttribute('src');
    lightboxPreviousFocus?.focus();
  }

  function ensureLightbox() {
    if (lightbox) return lightbox;
    lightbox = document.createElement('div');
    lightbox.className = 'porumbesti-lightbox';
    lightbox.hidden = true;
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', window.porumbestiWidgets?.i18n?.openImage || 'Image viewer');
    lightbox.innerHTML = `
      <button type="button" class="porumbesti-lightbox-close" data-porumbesti-lightbox-close><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>
      <button type="button" class="porumbesti-lightbox-nav is-previous" data-porumbesti-lightbox-nav="previous"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span></button>
      <figure class="porumbesti-lightbox-stage">
        <img data-porumbesti-lightbox-image alt="">
        <figcaption data-porumbesti-lightbox-text></figcaption>
        <small data-porumbesti-lightbox-counter aria-live="polite"></small>
      </figure>
      <button type="button" class="porumbesti-lightbox-nav is-next" data-porumbesti-lightbox-nav="next"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>`;
    document.body.appendChild(lightbox);
    const close = one('[data-porumbesti-lightbox-close]', lightbox);
    const previous = one('[data-porumbesti-lightbox-nav="previous"]', lightbox);
    const next = one('[data-porumbesti-lightbox-nav="next"]', lightbox);
    close.setAttribute('aria-label', window.porumbestiWidgets?.i18n?.closeLightbox || 'Close');
    previous.setAttribute('aria-label', window.porumbestiWidgets?.i18n?.previousImage || 'Previous image');
    next.setAttribute('aria-label', window.porumbestiWidgets?.i18n?.nextImage || 'Next image');
    close.addEventListener('click', closeLightbox);
    previous.addEventListener('click', () => showLightboxItem(lightboxIndex - 1));
    next.addEventListener('click', () => showLightboxItem(lightboxIndex + 1));
    lightbox.addEventListener('click', (event) => { if (event.target === lightbox) closeLightbox(); });
    lightbox.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeLightbox();
      if (event.key === 'ArrowLeft') showLightboxItem(lightboxIndex - 1);
      if (event.key === 'ArrowRight') showLightboxItem(lightboxIndex + 1);
      if (event.key !== 'Tab') return;
      const focusable = all('button:not([hidden])', lightbox);
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    return lightbox;
  }

  function openLightbox(items, index, trigger) {
    if (!items.length) return;
    ensureLightbox();
    lightboxItems = items;
    lightboxPreviousFocus = trigger;
    showLightboxItem(index);
    lightbox.hidden = false;
    document.body.classList.add('porumbesti-lightbox-open');
    one('[data-porumbesti-lightbox-close]', lightbox)?.focus();
  }

  function initLightbox(root = document) {
    collectLightboxItems(root).forEach((item) => {
      const trigger = item.trigger;
      if (trigger.dataset.porumbestiLightboxReady) return;
      trigger.dataset.porumbestiLightboxReady = 'true';
      if (trigger.matches('img')) {
        trigger.setAttribute('role', 'button');
        trigger.tabIndex = 0;
      }
      const openLabel = window.porumbestiWidgets?.i18n?.openImage || 'Open image';
      trigger.setAttribute('aria-label', item.caption ? `${openLabel}: ${item.caption}` : openLabel);
      const activate = (event) => {
        event.preventDefault();
        const groupItems = collectLightboxItems(lightboxGroupRoot(trigger));
        const index = Math.max(0, groupItems.findIndex((entry) => entry.trigger === trigger));
        openLightbox(groupItems, index, trigger);
      };
      trigger.addEventListener('click', activate);
      trigger.addEventListener('keydown', (event) => {
        if (!trigger.matches('img') || !['Enter', ' '].includes(event.key)) return;
        activate(event);
      });
    });
  }

  function openSearch() {
    const modal = one('[data-porumbesti-search-modal].is-modal');
    if (!modal) return;
    modal.hidden = false;
    setTimeout(() => one('input[type="search"]', modal)?.focus(), 20);
  }

  function init(root = document) {
    initHeader(root); initFilters(root); initContact(root); initAccessibility(root); initSearch(root); initDocumentLists(root); initLightbox(root);
  }

  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-porumbesti-search]')) openSearch();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') all('[data-porumbesti-search-modal].is-modal').forEach((modal) => { modal.hidden = true; });
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); openSearch(); }
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init()); else init();
  window.addEventListener('load', () => init());
  window.setTimeout(() => init(), 500);
  window.addEventListener('elementor/frontend/init', () => {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', ($scope) => init($scope[0]));
  });
})();
