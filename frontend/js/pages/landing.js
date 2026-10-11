/**
 * ==========================================================================
 * LANDING JS - SIM-MBG
 * 3D Peek Coverflow Carousel, Timeline Stepper, and Menu Chip interactions
 * Vanilla JS adhering to AGENTS.md guidelines (no libraries)
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  init3DCoverflowCarousel();
  initMenuChips();
});

/**
 * 3D Coverflow Peek Carousel matching Figma frame 27:2
 */
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

  /**
   * Updates 3D card layout classes, timeline progress, and captions
   */
  function renderCarousel(step) {
    currentStep = step;

    // Calculate left and right step indices for 3-step loop
    const leftStep = ((currentStep - 2 + totalSteps) % totalSteps) + 1;
    const rightStep = (currentStep % totalSteps) + 1;

    // Update Coverflow cards
    cards.forEach((card) => {
      const cardStep = parseInt(card.getAttribute('data-step'), 10);

      // Clean existing state classes
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

    // Update timeline stepper progress bar line
    if (progressLine) {
      const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressLine.style.width = `${progressPercent}%`;
    }

    // Update timeline indicator states
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

    // Update bottom caption text with smooth transition
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

  // Navigation handlers
  function goNext() {
    const next = (currentStep % totalSteps) + 1;
    renderCarousel(next);
  }

  function goPrev() {
    const prev = ((currentStep - 2 + totalSteps) % totalSteps) + 1;
    renderCarousel(prev);
  }

  // Auto-rotation timer (8s), resets on user interaction
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

  // Click on cards to bring them to center
  cards.forEach((card) => {
    card.addEventListener('click', () => {
      const cardStep = parseInt(card.getAttribute('data-step'), 10);
      if (cardStep && cardStep !== currentStep) {
        renderCarousel(cardStep);
        resetAutoTimer();
      }
    });

    // Keyboard support for cards
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

  // Click on timeline step items
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

  // Touch swipe gesture support for mobile devices
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

  // Keyboard navigation support when carousel area is focused or hovered
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

  // Pause rotation on hover and keyboard focus
  if (carouselContainer) {
    carouselContainer.addEventListener('mouseenter', () => clearInterval(autoTimer));
    carouselContainer.addEventListener('mouseleave', resetAutoTimer);
    carouselContainer.addEventListener('focusin', () => clearInterval(autoTimer));
    carouselContainer.addEventListener('focusout', resetAutoTimer);
  }

  // Initial render (starts on Step 2 per Figma)
  renderCarousel(currentStep);
}

/**
 * Interactive marquee menu chips
 */
function initMenuChips() {
  const chips = document.querySelectorAll('.menu-chip');
  if (!chips || chips.length === 0) return;

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      // Visual click feedback
      chip.style.transform = 'scale(0.96)';
      setTimeout(() => {
        chip.style.transform = '';
      }, 150);

      // Smooth scroll up to hero carousel
      const alurSection = document.querySelector('#alur');
      if (alurSection) {
        alurSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  });
}
