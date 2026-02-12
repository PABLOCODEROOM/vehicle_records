// Toggle user dropdown menu
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInside = dropdown && dropdown.parentElement.contains(event.target);
        if (!isClickInside && dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });
}

// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}

// Close sidebar when clicking on a link (mobile)
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && window.innerWidth < 768) {
                sidebar.classList.remove('mobile-open');
            }
        });
    });
    
    // Update color display when color input changes
    const colorInput = document.getElementById('color');
    if (colorInput) {
        colorInput.addEventListener('change', function() {
            const colorText = document.querySelector('input[placeholder="Color name"]');
            if (colorText) {
                colorText.value = this.value;
            }
        });
    }
});

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-TZ', {
        style: 'currency',
        currency: 'TZS'
    }).format(amount);
}

// Show success message and auto-hide
function showMessage(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Confirm deletion
function confirmDelete(message = 'Are you sure?') {
    return confirm(message);
}