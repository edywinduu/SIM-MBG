

document.addEventListener('DOMContentLoaded', () => {
  init3DCoverflowCarousel();
  initMenuChips();
  initLandingSmoothScroll();
  loadProfile();
});

function init3DCoverflowCarousel() {
  const cards = document.querySelectorAll('.carousel-card');
  const prevBtn = document.querySelector('#prevBtn');
  const nextBtn = document.querySelector('#nextBtn');
  const stepItems = document.querySelectorAll('.timeline-steps .step-item');
  const progressLine = document.querySelector('#timelineProgress');
  const captionEl = document.querySelector('#carouselCaption');
  const carouselContainer = document.querySelector('.carousel-stage-container');
  const viewport = document.querySelector('#carouselViewport');

  if (!cards || cards.length === 0) return;

  let currentStep = 1;
  const totalSteps = 3;

  const stepCaptions = {
    1: 'Daftar Akun Siswa',
    2: 'Tekan Menu Makanan',
    3: 'Konfirmasi Pesanan'
  };


  function renderCarousel(step) {
    currentStep = step;


    const leftStep = ((currentStep - 2 + totalSteps) % totalSteps) + 1;
    const rightStep = (currentStep % totalSteps) + 1;


    cards.forEach((card) => {
      const cardStep = parseInt(card.getAttribute('data-step'), 10);


      card.classList.remove('active', 'card-center', 'card-left', 'card-right', 'card-hidden');

      if (cardStep === currentStep) {
        card.classList.add('active', 'card-center');
        card.setAttribute('aria-hidden', 'false');
      } else if (cardStep === leftStep) {
        card.classList.add('card-left');
        card.setAttribute('aria-hidden', 'true');
      } else if (cardStep === rightStep) {
        card.classList.add('card-right');
        card.setAttribute('aria-hidden', 'true');
      } else {
        card.classList.add('card-hidden');
        card.setAttribute('aria-hidden', 'true');
      }
    });


    if (progressLine) {
      const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressLine.style.width = `${progressPercent}%`;
    }


    if (stepItems && stepItems.length > 0) {
      stepItems.forEach((item) => {
        const itemStep = parseInt(item.getAttribute('data-step'), 10);
        item.classList.remove('active', 'active-done');

        if (itemStep === currentStep) {
          item.classList.add('active');
          item.setAttribute('aria-selected', 'true');
        } else if (itemStep < currentStep) {
          item.classList.add('active-done');
          item.setAttribute('aria-selected', 'false');
        } else {
          item.setAttribute('aria-selected', 'false');
        }
      });
    }


    if (captionEl) {
      captionEl.style.opacity = '0';
      captionEl.style.transform = 'translateY(4px)';
      setTimeout(() => {
        captionEl.textContent = stepCaptions[currentStep] || 'Tekan Menu Makanan';
        captionEl.style.opacity = '1';
        captionEl.style.transform = 'translateY(0)';
      }, 150);
    }
  }


  function goNext() {
    const next = (currentStep % totalSteps) + 1;
    renderCarousel(next);
  }

  function goPrev() {
    const prev = ((currentStep - 2 + totalSteps) % totalSteps) + 1;
    renderCarousel(prev);
  }


  const AUTO_INTERVAL = 8000;
  let autoTimer = setInterval(goNext, AUTO_INTERVAL);

  function resetAutoTimer() {
    clearInterval(autoTimer);
    autoTimer = setInterval(goNext, AUTO_INTERVAL);
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goNext();
      resetAutoTimer();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goPrev();
      resetAutoTimer();
    });
  }


  cards.forEach((card) => {
    card.addEventListener('click', () => {
      const cardStep = parseInt(card.getAttribute('data-step'), 10);
      if (cardStep && cardStep !== currentStep) {
        renderCarousel(cardStep);
        resetAutoTimer();
      }
    });


    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const cardStep = parseInt(card.getAttribute('data-step'), 10);
        if (cardStep && cardStep !== currentStep) {
          renderCarousel(cardStep);
          resetAutoTimer();
        }
      }
    });
  });


  if (stepItems && stepItems.length > 0) {
    stepItems.forEach((item) => {
      item.addEventListener('click', () => {
        const targetStep = parseInt(item.getAttribute('data-step'), 10);
        if (targetStep) {
          renderCarousel(targetStep);
          resetAutoTimer();
        }
      });
    });
  }


  if (viewport) {
    let touchStartX = 0;
    let touchEndX = 0;

    viewport.addEventListener('touchstart', (e) => {
      if (e.changedTouches && e.changedTouches.length > 0) {
        touchStartX = e.changedTouches[0].screenX;
      }
    }, { passive: true });

    viewport.addEventListener('touchend', (e) => {
      if (e.changedTouches && e.changedTouches.length > 0) {
        touchEndX = e.changedTouches[0].screenX;
        const diffX = touchEndX - touchStartX;
        if (Math.abs(diffX) > 40) {
          if (diffX < 0) {
            goNext();
          } else {
            goPrev();
          }
          resetAutoTimer();
        }
      }
    }, { passive: true });
  }


  document.addEventListener('keydown', (e) => {
    const alurSection = document.querySelector('#alur');
    if (!alurSection) return;
    const rect = alurSection.getBoundingClientRect();
    const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
    if (isVisible) {
      if (e.key === 'ArrowLeft') {
        goPrev();
        resetAutoTimer();
      } else if (e.key === 'ArrowRight') {
        goNext();
        resetAutoTimer();
      }
    }
  });


  if (carouselContainer) {
    carouselContainer.addEventListener('mouseenter', () => clearInterval(autoTimer));
    carouselContainer.addEventListener('mouseleave', resetAutoTimer);
    carouselContainer.addEventListener('focusin', () => clearInterval(autoTimer));
    carouselContainer.addEventListener('focusout', resetAutoTimer);
  }


  renderCarousel(currentStep);
}

function initMenuChips() {
  const chips = document.querySelectorAll('.menu-chip');
  if (!chips || chips.length === 0) return;

  // Inisialisasi floating tooltip tunggal di body untuk performa optimal
  let tooltip = document.querySelector('#menuFloatingTooltip');
  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.id = 'menuFloatingTooltip';
    tooltip.className = 'menu-floating-tooltip arrow-bottom';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.setAttribute('aria-hidden', 'true');
    tooltip.innerHTML = `
      <div class="tooltip-arrow"></div>
      <div class="tooltip-title">
        <span class="tooltip-name"></span>
        <span class="tooltip-badge"></span>
      </div>
      <div class="tooltip-desc"></div>
    `;
    document.body.appendChild(tooltip);
  }

  const tooltipName = tooltip.querySelector('.tooltip-name');
  const tooltipBadge = tooltip.querySelector('.tooltip-badge');
  const tooltipDesc = tooltip.querySelector('.tooltip-desc');
  const tooltipArrow = tooltip.querySelector('.tooltip-arrow');

  function updateTooltipPosition(chip) {
    const rect = chip.getBoundingClientRect();
    const tooltipWidth = 320;
    const tooltipHeight = tooltip.offsetHeight || 90;

    let arrowClass = 'arrow-bottom';
    // Posisikan di atas chip secara default
    let top = rect.top - tooltipHeight - 12;

    // Jika ruang di atas terlalu sempit (misal dekat top viewport), posisikan di bawah chip
    if (top < 12) {
      top = rect.bottom + 12;
      arrowClass = 'arrow-top';
    }

    // Pusatkan secara horizontal terhadap chip
    let left = rect.left + rect.width / 2 - tooltipWidth / 2;

    // Batasi horizontal agar selalu di dalam viewport
    const maxLeft = window.innerWidth - tooltipWidth - 16;
    if (left < 16) left = 16;
    if (left > maxLeft) left = maxLeft;

    tooltip.className = `menu-floating-tooltip ${arrowClass} is-visible`;
    tooltip.style.top = `${top}px`;
    tooltip.style.left = `${left}px`;

    // Arahkan panah ke tengah chip
    if (tooltipArrow) {
      const arrowLeft = rect.left + rect.width / 2 - left;
      const boundedArrowLeft = Math.max(16, Math.min(arrowLeft, tooltipWidth - 16));
      tooltipArrow.style.left = `${boundedArrowLeft}px`;
    }
  }

  function showTooltip(chip) {
    const name = chip.getAttribute('data-menu-name') || '';
    const desc = chip.getAttribute('data-menu-desc') || '';
    const calories = chip.getAttribute('data-menu-calories') || '';

    if (!desc && !name) return;

    tooltipName.textContent = name;
    if (calories) {
      tooltipBadge.textContent = `${calories} kkal`;
      tooltipBadge.style.display = 'inline-block';
    } else {
      tooltipBadge.style.display = 'none';
    }
    tooltipDesc.textContent = desc;
    tooltip.setAttribute('aria-hidden', 'false');

    updateTooltipPosition(chip);
  }

  function hideTooltip() {
    tooltip.classList.remove('is-visible');
    tooltip.setAttribute('aria-hidden', 'true');
  }

  chips.forEach((chip) => {
    chip.addEventListener('mouseenter', () => showTooltip(chip));
    chip.addEventListener('mouseleave', hideTooltip);

    chip.addEventListener('click', () => {
      hideTooltip();
      chip.style.transform = 'scale(0.96)';
      setTimeout(() => {
        chip.style.transform = '';
      }, 150);

      const alurSection = document.querySelector('#alur');
      if (alurSection) {
        alurSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  });

  window.addEventListener('scroll', hideTooltip, { passive: true });
}

function initLandingSmoothScroll() {
  const navLinks = document.querySelectorAll('.student-nav-menu .nav-link');
  const allAnchorLinks = document.querySelectorAll('a[href^="#"]');
  const header = document.querySelector('.main-header');

  function getHeaderHeight() {
    return header ? header.offsetHeight : 85;
  }

  function setActiveNavLink(targetHash) {
    if (!navLinks || navLinks.length === 0) return;
    navLinks.forEach((link) => {
      const href = link.getAttribute('href');
      if (href === targetHash) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  }


  allAnchorLinks.forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      const href = anchor.getAttribute('href');
      if (!href || href === '#' || !href.startsWith('#')) return;

      const targetId = href.substring(1);
      const targetElement = document.getElementById(targetId);

      if (targetElement) {
        e.preventDefault();

        const headerHeight = getHeaderHeight();
        let targetY = 0;

        if (targetId === 'beranda') {
          targetY = 0;
        } else {
          const rect = targetElement.getBoundingClientRect();
          targetY = window.pageYOffset + rect.top - headerHeight - 16;
          if (targetY < 0) targetY = 0;
        }

        window.scrollTo({
          top: targetY,
          behavior: 'smooth'
        });


        if (window.history && window.history.pushState) {
          window.history.pushState(null, '', href);
        } else {
          window.location.hash = href;
        }


        setActiveNavLink(href);


        const navMenu = document.querySelector('.nav-links');
        const toggleBtn = document.querySelector('.mobile-nav-toggle');
        if (navMenu && navMenu.classList.contains('open')) {
          navMenu.classList.remove('open');
          if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
          }
        }
      }
    });
  });


  const sectionIds = ['beranda', 'alur', 'menu-pilihan', 'fitur'];
  const sections = sectionIds
    .map((id) => ({ id: `#${id}`, el: document.getElementById(id) }))
    .filter((s) => s.el !== null);

  let isTicking = false;
  function handleScrollSpy() {
    const scrollY = window.pageYOffset;
    const headerHeight = getHeaderHeight();
    const isMobile = window.innerWidth <= 1024;


    if (window.innerHeight + scrollY >= document.documentElement.scrollHeight - 60) {
      setActiveNavLink('#fitur');
      return;
    }


    if (scrollY < 120) {
      setActiveNavLink('#beranda');
      return;
    }

    let activeId = '#beranda';
    for (let i = 0; i < sections.length; i++) {
      const section = sections[i];


      if (!isMobile && section.id === '#alur') {
        continue;
      }
      const topOffset = section.el.offsetTop - headerHeight - 80;
      if (scrollY >= topOffset) {
        activeId = section.id;
      }
    }

    setActiveNavLink(activeId);
  }

  window.addEventListener('scroll', () => {
    if (!isTicking) {
      window.requestAnimationFrame(() => {
        handleScrollSpy();
        isTicking = false;
      });
      isTicking = true;
    }
  }, { passive: true });


  if (window.location.hash) {
    const initialTarget = document.getElementById(window.location.hash.substring(1));
    if (initialTarget) {
      setTimeout(() => {
        const headerHeight = getHeaderHeight();
        const targetId = window.location.hash.substring(1);
        let targetY = 0;
        if (targetId === 'beranda') {
          targetY = 0;
        } else {
          const rect = initialTarget.getBoundingClientRect();
          targetY = window.pageYOffset + rect.top - headerHeight - 16;
          if (targetY < 0) targetY = 0;
        }
        window.scrollTo({
          top: targetY,
          behavior: 'smooth'
        });
        setActiveNavLink(window.location.hash);
      }, 100);
    }
  }
}

async function loadProfile() {
  const nameElement = document.getElementById("student-user-name") || document.querySelector(".student-user-name");
  const roleElement = document.getElementById("student-user-role") || document.querySelector(".student-user-role");
  const userChip = document.querySelector(".student-user-chip");

  if (!nameElement || !roleElement) return;

  function applyUserData(name, role) {
    if (!name) return;
    nameElement.textContent = name;
    const displayRole = (role || "siswa").toLowerCase();
    roleElement.textContent = displayRole === "student" || displayRole === "siswa"
      ? "Siswa"
      : displayRole === "sppg"
      ? "Petugas SPPG"
      : displayRole === "admin"
      ? "Administrator"
      : displayRole.toUpperCase();

    if (userChip) {
      let targetDashboard = "pages/siswa/dashboard.html";
      if (displayRole === "admin") {
        targetDashboard = "pages/admin/dashboard.html";
      } else if (displayRole === "sppg") {
        targetDashboard = "pages/sppg/dashboard.html";
      }
      userChip.setAttribute("href", targetDashboard);
      userChip.setAttribute("title", `Akun Masuk: ${name} (Buka Dashboard)`);
    }
  }

  // 1. Cek sesi lokal dari sessionStorage terlebih dahulu
  try {
    const rawAuth = sessionStorage.getItem("sim_mbg_auth");
    if (rawAuth) {
      const localUser = JSON.parse(rawAuth);
      if (localUser && (localUser.isLoggedIn || localUser.username)) {
        applyUserData(localUser.full_name || localUser.username, localUser.role);
        return;
      }
    }
  } catch (storageErr) {
    console.warn("Gagal membaca sessionStorage:", storageErr);
  }

  // 2. Jika tidak ada di sessionStorage, coba panggil API backend
  try {
    const response = await fetch("../backend/api/landing.php", {
      credentials: "include"
    });

    if (response.status === 401) {
      nameElement.textContent = "Masuk";
      roleElement.textContent = "Portal MBG";
      return;
    }

    if (!response.ok) {
      return;
    }

    const result = await response.json();
    if (result && result.success && result.data) {
      const user = result.data;
      const displayName = user.full_name || user.nama || user.username;
      applyUserData(displayName, user.role);
    }
  } catch (error) {
    console.debug("Info sesi backend tidak dapat diakses:", error);
  }
}
