document.addEventListener("DOMContentLoaded", () => {

  const btn = document.getElementById("btn");
  const msg = document.getElementById("msg");
  const username = document.getElementById("username");
  const password = document.getElementById("password");

  // 🔒 GUARD WAJIB (INI KUNCI UTAMA)
  if (!btn || !username || !password) {
    console.warn("Login elements not found, script skipped");
    return;
  }

  let confirmCallback = null;

  /* ===============================
     LOGIN FLOW
  ================================ */
  function login() {
    const u = username.value.trim();
    const p = password.value.trim();

    if (!u || !p) {
      msg.textContent = "⚠️ Username dan password wajib diisi";
      return;
    }

    openConfirm(
      "Konfirmasi Login",
      "Apakah Anda yakin ingin masuk ke sistem?",
      function () {
        processLogin(u, p);
      }
    );
  }

function processLogin(u, p) {
  btn.disabled = true;
  btn.textContent = "Memproses...";
  msg.textContent = "";

  fetch("/login", {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json", // 🔑 WAJIB
      "X-CSRF-TOKEN": document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content")
    },
    body: JSON.stringify({
      username: u,
      password: p
    })
  })
    .then(async res => {
      const text = await res.text();

      let data;
      try {
        data = JSON.parse(text);
      } catch {
        console.error("❌ BUKAN JSON:", text);
        throw new Error("Server error, bukan JSON");
      }

      if (!res.ok || !data.success) {
        throw new Error(data.message || "Login gagal");
      }

      // ✅ LOGIN SUKSES
      window.location.href = "/dashboard";
    })
    .catch(err => {
      msg.textContent = "❌ " + err.message;
      btn.disabled = false;
      btn.textContent = "Masuk";
      password.value = "";
    });
}

  /* ===============================
     CONFIRM MODAL
  ================================ */
  function openConfirm(title, message, onOk) {
    confirmCallback = onOk;

    const titleEl = document.getElementById("confirmTitle");
    const msgEl   = document.getElementById("confirmMessage");
    const modal   = document.getElementById("confirmModal");

    if (!titleEl || !msgEl || !modal) return;

    titleEl.innerText = title;
    msgEl.innerText   = message;

    modal.style.opacity = "1";
    modal.style.pointerEvents = "auto";
  }

  function closeConfirm() {
    const modal = document.getElementById("confirmModal");
    if (!modal) return;

    modal.style.opacity = "0";
    modal.style.pointerEvents = "none";
    confirmCallback = null;
  }

  function handleConfirmOk() {
    if (confirmCallback) confirmCallback();
    closeConfirm();
  }

  /* ===============================
     EVENT
  ================================ */
  btn.addEventListener("click", login);

  password.addEventListener("keyup", e => {
    if (e.key === "Enter") login();
  });

  window.togglePassword = function () {
    const input = document.getElementById("password");
    if (!input) return;

    input.type = input.type === "password" ? "text" : "password";
  };

  // expose untuk modal
  window.closeConfirm = closeConfirm;
  window.handleConfirmOk = handleConfirmOk;

});
