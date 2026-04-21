let CSRF_TOKEN = "";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");

  // IMPORTANT: silently exit if not on login page
  if (!form) return;

  form.addEventListener("submit", e => {
    e.preventDefault();
    login();
  });
});

// --------------------
// LOGIN
// --------------------
function login() {
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("userPassword");

  if (!usernameInput || !passwordInput) return;

  fetch("login_handler.php", {
    method: "POST",
    body: new URLSearchParams({
      username: usernameInput.value,
      password: passwordInput.value
    })
  })
  .then(r => r.json())
  .then(res => {
    if (!res.success) {
      const err = document.getElementById("loginError");
      err.textContent = res.message;
      err.classList.remove("d-none");
      return;
    }
    location.href = "index.php";
  })
  .catch(err => console.error("Login failed:", err));
}


// --------------------
// LOGOUT
// --------------------

function logout() {
  fetch("api/auth/logout.php", { method: "POST" })
    .then(() => {
      window.location.href = "login.php";
    })
    .catch(err => console.error("Logout failed:", err));
}

function init_logout_buttons() {
  document.querySelectorAll(".logout-btn").forEach(btn => {
    if (btn.dataset.bound) return; // prevent double binding
    btn.dataset.bound = "1";

    btn.addEventListener("click", e => {
      e.preventDefault();
      logout();
    });
  });
}

