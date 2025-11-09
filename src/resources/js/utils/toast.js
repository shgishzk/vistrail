let activeToast = null;

const hideToast = () => {
  if (!activeToast) {
    return;
  }
  activeToast.classList.add('opacity-0', 'translate-y-2');
  setTimeout(() => {
    activeToast?.remove();
    activeToast = null;
  }, 200);
};

const showToast = (message, type = 'success') => {
  hideToast();

  const baseStyles = 'flex items-center gap-3 rounded-xl px-4 py-3 shadow-lg border';
  const typeStyles =
    type === 'success'
      ? 'bg-teal-500 text-white'
      : 'bg-red-200 text-white';

  const wrapper = document.createElement('div');
  wrapper.className = [
    'fixed bottom-6 right-4 z-[2000]',
    'transition transform duration-200 ease-out translate-y-4 opacity-0',
  ].join(' ');

  wrapper.innerHTML = `
    <div class="${baseStyles} ${typeStyles}">
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="${
            type === 'success'
              ? 'M5 12l5 5L20 7'
              : 'M6 18L18 6M6 6l12 12'
          }" />
      </svg>
      <span class="text-sm font-medium">${message}</span>
    </div>
  `;

  document.body.appendChild(wrapper);
  activeToast = wrapper;

  requestAnimationFrame(() => {
    activeToast.classList.remove('translate-y-4', 'opacity-0');
  });

  setTimeout(() => {
    hideToast();
  }, 2800);
};

export const toast = {
  success(message) {
    showToast(message, 'success');
  },
  error(message) {
    showToast(message, 'error');
  },
};
