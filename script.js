const navToggle = document.querySelector('.nav-toggle');
const siteNav = document.querySelector('.site-nav');

if (navToggle && siteNav) {
  navToggle.addEventListener('click', () => {
    const isOpen = siteNav.classList.toggle('is-open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  siteNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      if (window.matchMedia('(max-width: 720px)').matches) {
        siteNav.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  });
}

const revealItems = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window && revealItems.length) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.14,
      rootMargin: '0px 0px -10% 0px',
    },
  );

  revealItems.forEach((item) => observer.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add('is-visible'));
}

const caseFilter = document.getElementById('case-filter');

if (caseFilter) {
  const categorySelect = document.getElementById('case-category');
  const caseFiles = document.querySelectorAll('.case-file');
  const emptyMessage = document.getElementById('case-filter-empty');

  caseFilter.addEventListener('submit', (event) => {
    event.preventDefault();

    const selectedCategory = categorySelect.value;
    let visibleCount = 0;

    caseFiles.forEach((caseFile) => {
      const caseTag = caseFile.querySelector('.case-tag')?.textContent.trim();
      const isVisible = !selectedCategory || caseTag === selectedCategory;

      caseFile.hidden = !isVisible;
      if (isVisible) {
        visibleCount += 1;
      }
    });

    emptyMessage.hidden = visibleCount !== 0;
  });
}

const contactForm = document.getElementById('contact-form');

if (contactForm) {
  const errorBox = document.getElementById('contact-form-error');
  const successBox = document.getElementById('contact-form-success');
  const emailField = document.getElementById('cf-email');
  const messageField = document.getElementById('cf-message');
  const discoveryFields = contactForm.querySelectorAll('input[name="discovery"]');
  const discoveryOtherField = contactForm.querySelector('[name="discovery_other"]');
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const updateDiscoveryOtherState = () => {
    const isOtherSelected = contactForm.querySelector('input[name="discovery"]:checked')?.value === 'その他';
    discoveryOtherField.disabled = !isOtherSelected;
  };

  discoveryFields.forEach((field) => field.addEventListener('change', updateDiscoveryOtherState));
  updateDiscoveryOtherState();

  contactForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const errors = [];
    [emailField, messageField].forEach((field) => {
      field.closest('.form-field').classList.remove('has-error');
    });

    if (!emailField.value.trim()) {
      errors.push('メールアドレスを入力してください。');
      emailField.closest('.form-field').classList.add('has-error');
    } else if (!emailPattern.test(emailField.value.trim())) {
      errors.push('メールアドレスの形式が正しくありません。');
      emailField.closest('.form-field').classList.add('has-error');
    }

    if (!messageField.value.trim()) {
      errors.push('お問い合わせ内容を入力してください。');
      messageField.closest('.form-field').classList.add('has-error');
    }

    if (errors.length) {
      errorBox.textContent = errors.join(' ');
      errorBox.hidden = false;
      successBox.hidden = true;
      return;
    }

    errorBox.hidden = true;
    // モックアップのため実際の送信は行わず、成功メッセージのみ表示します。
    contactForm.hidden = true;
    successBox.hidden = false;
    successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
}
