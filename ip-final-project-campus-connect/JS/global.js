// Global JavaScript functions for Campus Connect
// Works with PHP backend sessions

var API_BASE = '/campus-connect/php--campus-connect-application-2/backendpart';

// Current user state
let currentUser = {
    isLoggedIn: false,
    id: null,
    name: '',
    email: '',
    role: ''
};

// Check authentication status from PHP backend
async function checkAuthStatus() {
    try {
        const response = await fetch(API_BASE + '/auth/check_auth.php');
        const data = await response.json();
        
        if (data.authenticated) {
            currentUser.isLoggedIn = true;
            currentUser.id = data.user.id;
            currentUser.name = data.user.fullname;
            currentUser.email = data.user.email;
            currentUser.role = data.user.role;
            
            updateNavbarForLoggedInUser();
        } else {
            currentUser.isLoggedIn = false;
            updateNavbarForLoggedOutUser();
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        updateNavbarForLoggedOutUser();
    }
}

// Update navbar when user is logged in
function updateNavbarForLoggedInUser() {
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const userDropdown = document.getElementById('userDropdown');
    
    if (loginLink) loginLink.style.display = 'none';
    if (registerLink) registerLink.style.display = 'none';
    if (userDropdown) userDropdown.style.display = 'block';
    
    // Update profile link with user name
    const profileLink = document.getElementById('profileLink');
    if (profileLink) {
        const link = profileLink.querySelector('a');
        if (link && currentUser.name) {
            link.innerHTML = `<i class="fas fa-user-circle"></i> ${currentUser.name}`;
        }
    }
    
    // Remove existing welcome message first to prevent duplicates
    removeWelcomeMessage();
    addWelcomeMessage(currentUser.name);
}

// Update navbar when user is logged out
function updateNavbarForLoggedOutUser() {
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const userDropdown = document.getElementById('userDropdown');
    
    if (loginLink) loginLink.style.display = 'block';
    if (registerLink) registerLink.style.display = 'block';
    if (userDropdown) userDropdown.style.display = 'none';
    
    removeWelcomeMessage();
}

// Add welcome message to navbar
function addWelcomeMessage(userName) {
    const navLinks = document.getElementById('navLinks');
    if (!navLinks) return;
    
    // Find the notification bell element
    const notificationLi = document.querySelector('.notification-li');
    
    // Create welcome message element
    const welcomeSpan = document.createElement('li');
    welcomeSpan.id = 'welcomeMessageSpan';
    welcomeSpan.style.color = '#38ef7d';
    welcomeSpan.style.marginLeft = '15px';
    welcomeSpan.style.fontSize = '14px';
    welcomeSpan.style.whiteSpace = 'nowrap';
    welcomeSpan.innerHTML = `<i class="fas fa-user-circle"></i> Welcome, ${userName}`;
    
    // Insert welcome message AFTER the notification bell
    if (notificationLi) {
        notificationLi.insertAdjacentElement('afterend', welcomeSpan);
    } else {
        navLinks.appendChild(welcomeSpan);
    }
}

// Remove welcome message
function removeWelcomeMessage() {
    const welcomeSpan = document.getElementById('welcomeMessageSpan');
    if (welcomeSpan) {
        welcomeSpan.remove();
    }
}

// ========== LOGOUT FUNCTION ==========
async function handleLogout() {
    try {
        const response = await fetch(API_BASE + '/auth/logout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        
        localStorage.clear();
        sessionStorage.clear();
        
        currentUser.isLoggedIn = false;
        currentUser.name = '';
        
        updateNavbarForLoggedOutUser();
        
        window.location.href = 'home.html';
        
    } catch (error) {
        console.error('Logout error:', error);
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = 'home.html';
    }
}

// Setup logout button
function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (logoutBtn) {
        const newBtn = logoutBtn.cloneNode(true);
        logoutBtn.parentNode.replaceChild(newBtn, logoutBtn);
        
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });
    }
}

// ========== BURGER MENU ==========
function initBurgerMenu() {
    const burgerMenu = document.getElementById('burgerMenu');
    const navLinks = document.getElementById('navLinks');
    const burgerIcon = document.getElementById('burgerIcon');
    
    if (burgerMenu && navLinks) {
        burgerMenu.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            if (burgerIcon) {
                if (navLinks.classList.contains('active')) {
                    burgerIcon.classList.remove('fa-bars');
                    burgerIcon.classList.add('fa-times');
                } else {
                    burgerIcon.classList.remove('fa-times');
                    burgerIcon.classList.add('fa-bars');
                }
            }
        });
        
        const navItems = navLinks.querySelectorAll('a');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                navLinks.classList.remove('active');
                if (burgerIcon) {
                    burgerIcon.classList.remove('fa-times');
                    burgerIcon.classList.add('fa-bars');
                }
            });
        });
        
        document.addEventListener('click', function(event) {
            if (!burgerMenu.contains(event.target) && !navLinks.contains(event.target) && window.innerWidth <= 768) {
                navLinks.classList.remove('active');
                if (burgerIcon) {
                    burgerIcon.classList.remove('fa-times');
                    burgerIcon.classList.add('fa-bars');
                }
            }
        });
    }
}

// ========== MOBILE DROPDOWNS ==========
function initMobileDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown > a');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('active');
            }
        });
    });
}

// ========== NOTIFICATIONS ==========
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

function initNotifications() {
    const bell = document.querySelector('.notification-bell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (bell && notificationDropdown) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotificationDropdown();
        });
        
        document.addEventListener('click', function() {
            notificationDropdown.classList.remove('show');
        });
        
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Sample notifications data
const sampleNotifications = [
    { title: "New study material added", time: "2 hours ago", read: false },
    { title: "Upcoming coding workshop", time: "Yesterday", read: false },
    { title: "Your course registration is confirmed", time: "2 days ago", read: true }
];

// ========== NOTIFICATION FUNCTIONS ==========

// Load notifications and display in dropdown
async function loadNotifications() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    
    if (!notificationDropdown) {
        console.log('Notification dropdown element not found');
        return;
    }
    
    try {
        const response = await fetch(API_BASE + '/notifications/get_notifications.php?limit=10');
        const data = await response.json();
        
        console.log('Notifications response:', data);
        
        if (data.success && data.notifications) {
            const unreadCount = data.notifications.filter(n => !n.is_read).length;
            
            // Update badge
            if (notificationBadge) {
                if (unreadCount > 0) {
                    notificationBadge.style.display = 'inline-block';
                    notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                } else {
                    notificationBadge.style.display = 'none';
                }
            }
            
            // Build dropdown HTML
            if (data.notifications.length === 0) {
                notificationDropdown.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #999;">
                        <i class="fas fa-bell-slash" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                        No notifications yet
                    </div>
                `;
            } else {
                let notificationsHtml = `
                    <div style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600; background: #f8f9fa;">
                        <i class="fas fa-bell"></i> Notifications
                        ${unreadCount > 0 ? `<span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 8px;">${unreadCount} new</span>` : ''}
                    </div>
                `;
                
                data.notifications.forEach(n => {
                    notificationsHtml += `
                        <div class="notification-item ${!n.is_read ? 'unread' : ''}" data-id="${n.id}" data-link="${n.link || '#'}" style="padding: 12px; border-bottom: 1px solid #eee; ${!n.is_read ? 'background: #f0f9f8;' : ''} cursor: pointer;">
                            <div style="font-weight: 600; font-size: 13px;">${escapeHtml(n.title)}</div>
                            <div style="font-size: 12px; color: #666; margin-top: 5px;">${escapeHtml(n.message)}</div>
                            <div style="font-size: 10px; color: #999; margin-top: 5px;">
                                <i class="far fa-clock"></i> ${escapeHtml(n.time_ago || 'Just now')}
                            </div>
                        </div>
                    `;
                });
                
                notificationsHtml += `
                    <div style="padding: 10px; text-align: center; border-top: 1px solid #eee;">
                        <a href="#" onclick="markAllNotificationsRead(); return false;" style="color: #11998e; text-decoration: none; font-size: 12px;">
                            Mark all as read
                        </a>
                    </div>
                `;
                
                notificationDropdown.innerHTML = notificationsHtml;
                
                // Add click handlers to notification items
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', async function(e) {
                        // Don't trigger if clicking on the "mark all" link
                        if (e.target.tagName === 'A') return;
                        
                        e.stopPropagation();
                        const id = this.getAttribute('data-id');
                        const link = this.getAttribute('data-link');
                        
                        // Mark as read
                        await fetch(API_BASE + '/notifications/mark_as_read.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: parseInt(id) })
                        });
                        
                        if (link && link !== '#') {
                            window.location.href = link;
                        }
                        
                        // Reload notifications and update badge
                        loadNotifications();
                        updateUnreadCount();
                    });
                });
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
        notificationDropdown.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #dc3545;">
                <i class="fas fa-exclamation-circle" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                Error loading notifications
            </div>
        `;
    }
}

// Update unread count badge
async function updateUnreadCount() {
    try {
        const response = await fetch(API_BASE + '/notifications/get_unread_count.php');
        const data = await response.json();
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (data.unread_count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating unread count:', error);
    }
}

// Mark all notifications as read
window.markAllNotificationsRead = async function() {
    try {
        const response = await fetch(API_BASE + '/notifications/mark_as_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: 0 })
        });
        const data = await response.json();
        if (data.success) {
            loadNotifications();
            updateUnreadCount();
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

// Toggle notification dropdown
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
            loadNotifications(); // Load notifications when opening
            updateUnreadCount();
        }
    }
}

// Initialize notifications
function initNotifications() {
    const bell = document.querySelector('.notification-bell');
    if (bell) {
        // Remove existing listener to avoid duplicates
        const newBell = bell.cloneNode(true);
        bell.parentNode.replaceChild(newBell, bell);
        
        newBell.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotificationDropdown();
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notificationDropdown');
        const bell = document.querySelector('.notification-bell');
        if (dropdown && bell) {
            if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        }
    });
    
    // Update unread count on page load
    updateUnreadCount();
    
    // Periodically check for new notifications (every 30 seconds)
    setInterval(() => {
        updateUnreadCount();
    }, 30000);
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Set active nav link
function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'home.html';
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}

// ========== INITIALIZE ==========
function initGlobal() {
    initBurgerMenu();
    initMobileDropdowns();
    initNotifications();
    checkAuthStatus();
    setupLogout();
    setActiveNavLink();
    loadNotifications();
}

// Run initialization when DOM is ready
document.addEventListener('DOMContentLoaded', initGlobal);