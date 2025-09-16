// JavaScript to calculate and set .fi-main position CSS variables
(function () {
  function docReadyState() {
    if (document.readyState === 'interactive') {
      updateFiMainPosition();
    }
    if (document.readyState === 'complete') {
      updateFiMainPosition();
    }
  }

  function updateFiMainPosition() {
    const fiMain = document.querySelector('.fi-main'); console.log(fiMain)
    if (fiMain) {
      const rect = fiMain.getBoundingClientRect();
      const root = document.documentElement;

      // Update CSS variables with fi-main position and dimensions
      root.style.setProperty('--fi-main-top', rect.top + 'px');
      root.style.setProperty('--fi-main-left', rect.left + 'px');
      root.style.setProperty('--fi-main-width', rect.width + 'px');
    }
  }

  document.addEventListener('readystatechange', docReadyState, false);
  window.addEventListener('resize', updateFiMainPosition);

  // Watch for DOM changes that might affect .fi-main position
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.type === 'childList') {
        // Check if relevant elements were added
        const addedNodes = Array.from(mutation.addedNodes);
        const hasRelevantChange = addedNodes.some(node =>
          node.nodeType === 1 &&
          (node.classList?.contains('fi-no-notification') ||
            node.querySelector?.('.fi-no-notification') ||
            node.classList?.contains('fi-main') ||
            node.querySelector?.('.fi-main'))
        );

        if (hasRelevantChange) {
          // Small delay to ensure DOM is fully updated
          setTimeout(updateFiMainPosition, 10);
        }
      }
    });
  });

  // Start observing
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
})();