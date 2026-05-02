document.addEventListener("DOMContentLoaded", () => {

  const content = document.getElementById("content");

  const FADE = 180;

  function loadPage(page) {
    content.classList.add('rtr-fading');

    setTimeout(() => {
      content.innerHTML =
        '<div class="rtr-loader"><img src="partials/logo-icon.png" alt="Loading"></div>';
      content.classList.remove('rtr-fading');

      fetch(`pages/${page}.php`)
        .then(r => {
          if (r.status === 401) { window.location.href = "logout.php"; return; }
          return r.text();
        })
        .then(html => {
          if (!html) return;

          window.stopLockPolling?.();

          content.classList.add('rtr-fading');
          setTimeout(() => {
            content.innerHTML = html;
            content.classList.remove('rtr-fading');

            window[`init_${page.replace(/-/g, "_")}`]?.();
            window.init_logout_buttons?.();
            setActiveMenu(page);
          }, FADE);
        })
        .catch(err => console.error("Load error:", err));
    }, FADE);
  }

  function setActiveMenu(page) {
    document.querySelectorAll(".side-nav-item.active").forEach(li => {
      li.classList.remove("active");
    });

    const activeLink = document.querySelector(
      `.side-nav-link[data-page="${page}"]`
    );
    if (!activeLink) return;

    activeLink.closest(".side-nav-item")?.classList.add("active");

    const activeCollapse = activeLink.closest(".collapse");

    document.querySelectorAll(".collapse.show").forEach(collapse => {
      if (collapse !== activeCollapse) {
        bootstrap.Collapse.getOrCreateInstance(collapse).hide();
      }
    });

    if (activeCollapse) {
      bootstrap.Collapse.getOrCreateInstance(activeCollapse).show();
      activeCollapse.closest(".side-nav-item")?.classList.add("active");
    }
  }

  document.addEventListener("click", e => {
    const link = e.target.closest(".nav-link[data-page]");
    if (!link) return;

    e.preventDefault();
    loadPage(link.dataset.page);
  });

  loadPage("dashboard");

});
