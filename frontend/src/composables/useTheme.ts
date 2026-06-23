import { computed, ref } from "vue";

const THEME_KEY = "tracer-theme";
const isDark = ref(false);
const initialized = ref(false);

function getPreferredTheme() {
  if (typeof window === "undefined") return false;

  const stored = localStorage.getItem(THEME_KEY);

  if (stored === "dark") return true;
  if (stored === "light") return false;

  return window.matchMedia("(prefers-color-scheme: dark)").matches;
}

function applyTheme(value = isDark.value) {
  if (typeof document === "undefined") return;

  const html = document.documentElement;

  html.classList.toggle("dark", value);
  html.dataset.theme = value ? "dark" : "light";
  html.style.colorScheme = value ? "dark" : "light";
}

function initializeTheme() {
  if (initialized.value) return;

  isDark.value = getPreferredTheme();
  applyTheme();
  initialized.value = true;

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (event) => {
      const stored = localStorage.getItem(THEME_KEY);

      if (stored) return;

      isDark.value = event.matches;
      applyTheme();
    });
}

function setTheme(value: boolean) {
  isDark.value = value;
  localStorage.setItem(THEME_KEY, value ? "dark" : "light");
  applyTheme();
}

function toggleTheme() {
  setTheme(!isDark.value);
}

export function useTheme() {
  initializeTheme();

  return {
    isDark,
    theme: computed(() => (isDark.value ? "dark" : "light")),
    setTheme,
    toggleTheme,
  };
}
