// Import our custom CSS
import '../sass/admin.scss';

// Import all of CoreUI's JS
import * as coreui from '@coreui/coreui';

// Import CoreUI icons
import '@coreui/icons/css/all.min.css';

// Initialize CoreUI components
document.addEventListener('DOMContentLoaded', () => {
  // Initialize sidebar
  const sidebarElement = document.querySelector('#sidebar');
  if (sidebarElement) {
    new coreui.Sidebar(sidebarElement);
  }
});
