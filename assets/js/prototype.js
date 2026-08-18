(function () {
  'use strict';

  const one = (selector, root = document) => root.querySelector(selector);
  const all = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const navToggle = one('[data-nav-toggle]');
  const nav = one('[data-navigation]');
  if (navToggle && nav) {
    const setNav = (open) => {
      nav.classList.toggle('is-open', open);
      navToggle.setAttribute('aria-expanded', String(open));
      navToggle.setAttribute('aria-label', navToggle.dataset[open ? 'labelClose' : 'labelOpen'] || 'Meniu');
      document.body.classList.toggle('prototype-nav-open', open);
      if (open) window.requestAnimationFrame(() => one('a', nav)?.focus());
    };
    navToggle.addEventListener('click', () => setNav(!nav.classList.contains('is-open')));
    nav.addEventListener('click', (event) => {
      if (event.target.closest('a')) setNav(false);
    });
    document.addEventListener('click', (event) => {
      if (!nav.contains(event.target) && !navToggle.contains(event.target)) setNav(false);
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) {
        setNav(false);
        navToggle.focus();
      }
    });
    window.addEventListener('resize', () => {
      if (window.matchMedia('(min-width: 1101px)').matches) setNav(false);
    }, { passive: true });
  }

  const siteHeader = one('.prototype-header');
  if (siteHeader) {
    const syncHeader = () => siteHeader.classList.toggle('is-scrolled', window.scrollY > 12);
    window.addEventListener('scroll', syncHeader, { passive: true });
    syncHeader();
  }

  all('[data-dialog-open]').forEach((button) => {
    const dialog = document.getElementById(button.dataset.dialogOpen);
    if (!dialog || typeof dialog.showModal !== 'function') return;
    button.addEventListener('click', () => {
      dialog.showModal();
      window.requestAnimationFrame(() => one('input, [autofocus]', dialog)?.focus());
    });
  });
  all('[data-dialog-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  all('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
  });

  const galleryDialog = one('[data-gallery-dialog]');
  const galleryImage = galleryDialog ? one('[data-gallery-image]', galleryDialog) : null;
  const galleryCaption = galleryDialog ? one('[data-gallery-caption]', galleryDialog) : null;
  all('[data-gallery-open]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!galleryDialog || !galleryImage || typeof galleryDialog.showModal !== 'function') return;
      galleryImage.src = button.dataset.gallerySrc || '';
      galleryImage.alt = button.dataset.galleryAlt || '';
      if (galleryCaption) galleryCaption.textContent = button.dataset.galleryAlt || '';
      galleryDialog.showModal();
    });
  });

  const accessToggle = one('[data-accessibility-toggle]');
  const accessPanel = one('[data-accessibility-panel]');
  const stateKeys = ['largeText', 'highContrast', 'grayscale', 'underlinedLinks'];
  const readState = () => {
    try { return JSON.parse(localStorage.getItem('porumbestiAccessibility') || '{}'); } catch (_) { return {}; }
  };
  const writeState = (state) => {
    try { localStorage.setItem('porumbestiAccessibility', JSON.stringify(state)); } catch (_) { /* private mode */ }
  };
  const applyState = (state) => {
    document.body.classList.toggle('is-large-text', Boolean(state.largeText));
    document.body.classList.toggle('is-high-contrast', Boolean(state.highContrast));
    document.body.classList.toggle('is-grayscale', Boolean(state.grayscale));
    document.documentElement.classList.toggle('is-grayscale-root', Boolean(state.grayscale));
    document.body.classList.toggle('has-underlined-links', Boolean(state.underlinedLinks));
    all('[data-accessibility-option]').forEach((button) => {
      button.setAttribute('aria-pressed', String(Boolean(state[button.dataset.accessibilityOption])));
    });
  };
  let accessState = readState();
  applyState(accessState);
  if (accessToggle && accessPanel) {
    const setAccessibilityPanel = (open, restoreFocus = false) => {
      accessPanel.classList.toggle('is-open', open);
      accessToggle.setAttribute('aria-expanded', String(open));
      if (restoreFocus) accessToggle.focus();
    };
    accessToggle.addEventListener('click', () => {
      const open = !accessPanel.classList.contains('is-open');
      setAccessibilityPanel(open);
    });
    all('[data-accessibility-option]').forEach((button) => {
      button.addEventListener('click', () => {
        const key = button.dataset.accessibilityOption;
        accessState[key] = !accessState[key];
        writeState(accessState);
        applyState(accessState);
      });
    });
    one('[data-accessibility-reset]')?.addEventListener('click', () => {
      accessState = Object.fromEntries(stateKeys.map((key) => [key, false]));
      writeState(accessState);
      applyState(accessState);
    });
    document.addEventListener('click', (event) => {
      if (!accessPanel.contains(event.target) && !accessToggle.contains(event.target)) setAccessibilityPanel(false);
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && accessPanel.classList.contains('is-open')) setAccessibilityPanel(false, true);
    });
  }

  all('[data-prototype-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const status = one('.prototype-form-status', form);
      if (status) status.textContent = form.dataset.success || 'Mesajul este valid. Trimiterea va fi activată în WordPress.';
      form.reset();
    });
  });

  const currentYear = new Date().getFullYear();
  all('[data-current-year]').forEach((node) => { node.textContent = String(currentYear); });
})();
