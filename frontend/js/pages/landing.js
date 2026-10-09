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
  const captionEl = document.querySelector('#carouselCaption');
  const carouselContainer = document.querySelector('.carousel-stage-container');

  if (!cards || cards.length === 0) return;

  // Step 2 is active by default as shown in the design
  let currentStep = 2;
  const totalSteps = 3;

  const stepCaptions = {
    1: 'Daftar Akun Siswa Baru',
    2: 'Tekan Menu Makanan',
    3: 'Konfirmasi & Jadwal Pengambilan'
  };

  /**
   * Updates 3D card layout classes and timeline indicator
   */
  function renderCarousel(step) {
    currentStep = step;

    // Calculate left and right step indices for 3-step loop
    const leftStep = ((currentStep - 2 + totalSteps) % totalSteps) + 1;
    const rightStep = (currentStep % totalSteps) + 1;

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

    // Update timeline indicator states
    if (stepItems && stepItems.length > 0) {
      stepItems.forEach((item) => {
        const itemStep = parseInt(item.getAttribute('data-step'), 10);
        item.classList.remove('active', 'active-done');

        if (itemStep === currentStep) {
          item.classList.add('active');
        } else if (itemStep < currentStep) {
          item.classList.add('active-done');
        }
      });
    }

    // Update bottom caption text
    if (captionEl) {
      captionEl.textContent = stepCaptions[currentStep] || 'Tekan Menu Makanan';
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

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goNext();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goPrev();
    });
  }

  // Click on cards to bring them to center
  cards.forEach((card) => {
    card.addEventListener('click', () => {
      const cardStep = parseInt(card.getAttribute('data-step'), 10);
      if (cardStep && cardStep !== currentStep) {
        renderCarousel(cardStep);
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
        }
      });
    });
  }

  // Keyboard navigation support
  document.addEventListener('keydown', (e) => {
    const alurSection = document.querySelector('#alur');
    if (!alurSection) return;
    const rect = alurSection.getBoundingClientRect();
    const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
    if (isVisible) {
      if (e.key === 'ArrowLeft') goPrev();
      if (e.key === 'ArrowRight') goNext();
    }
  });

  // Auto-rotation timer every 6 seconds, pauses on hover
  let autoTimer = setInterval(goNext, 6000);

  if (carouselContainer) {
    carouselContainer.addEventListener('mouseenter', () => clearInterval(autoTimer));
    carouselContainer.addEventListener('mouseleave', () => {
      clearInterval(autoTimer);
      autoTimer = setInterval(goNext, 6000);
    });
  }

  // Initial render
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
