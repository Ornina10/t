// API URLs for announcements
const ANNOUNCEMENTS_API = {
    getAll: '/campus-connect/php--campus-connect-application-2/backendpart/announcements/get_announcements.php',
    create: '/campus-connect/php--campus-connect-application-2/backendpart/announcements/create_announcement.php',
    update: '/campus-connect/php--campus-connect-application-2/backendpart/announcements/update_announcement.php',
    delete: '/campus-connect/php--campus-connect-application-2/backendpart/announcements/delete_announcement.php'
};

// Load and display announcements on home page
async function loadAnnouncements() {
    try {
        const response = await fetch(ANNOUNCEMENTS_API.getAll);
        const data = await response.json();
        
        if (data.status === 'success') {
            displayAnnouncements(data.data);
        }
    } catch (error) {
        console.error('Error loading announcements:', error);
    }
}

// Display announcements on the page
function displayAnnouncements(announcements) {
    const container = document.getElementById('announcements-container');
    if (!container) return;
    
    if (announcements.length === 0) {
        container.innerHTML = '<p class="no-announcements">No announcements yet.</p>';
        return;
    }
    
    container.innerHTML = '';
    
    announcements.forEach(announcement => {
        const card = `
            <div class="announcement-card" data-id="${announcement.id}">
                <div class="announcement-header">
                    <h3>${escapeHtml(announcement.title)}</h3>
                    ${isAdmin() ? `
                        <div class="announcement-actions">
                            <button onclick="editAnnouncement(${announcement.id})" class="edit-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteAnnouncement(${announcement.id})" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ` : ''}
                </div>
                <p class="announcement-message">${escapeHtml(announcement.message)}</p>
                <div class="announcement-footer">
                    <span class="announcement-author">
                        <i class="fas fa-user"></i> ${escapeHtml(announcement.created_by)}
                    </span>
                    <span class="announcement-date">
                        <i class="fas fa-calendar"></i> ${formatDate(announcement.created_at)}
                    </span>
                </div>
            </div>
        `;
        container.innerHTML += card;
    });
}

// Create new announcement (admin only)
async function createAnnouncement(title, message) {
    try {
        const response = await fetch(ANNOUNCEMENTS_API.create, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                title: title, 
                message: message,
                created_by: getCurrentUser() 
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Announcement created successfully!');
            loadAnnouncements(); // Refresh the list
            closeAnnouncementModal();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error creating announcement:', error);
    }
}

// Delete announcement (admin only)
async function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        try {
            const response = await fetch(ANNOUNCEMENTS_API.delete, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                alert('Announcement deleted successfully!');
                loadAnnouncements(); // Refresh the list
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error deleting announcement:', error);
        }
    }
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function isAdmin() {
    // Check if logged in user is admin
    // You can implement this based on your auth system
    return localStorage.getItem('user_role') === 'admin';
}

function getCurrentUser() {
    return localStorage.getItem('user_name') || 'Admin';
}

// Load announcements when page loads
document.addEventListener('DOMContentLoaded', loadAnnouncements);