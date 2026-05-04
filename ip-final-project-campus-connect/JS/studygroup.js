// studygroup.js - Study Groups functionality
(function() {
    // API URLs - using API_BASE from global.js
    const GROUPS_API = {
        getAll: API_BASE + '/study_groups/get_groups.php',
        create: API_BASE + '/study_groups/create_group.php',
        join: API_BASE + '/study_groups/join_group.php',
        leave: API_BASE + '/study_groups/leave_group.php'
    };

    // Current user info
    let currentUser = {
        isLoggedIn: false,
        id: null,
        name: ''
    };

    // Store all groups
    let allGroups = [];

    // Check authentication status
    async function checkAuth() {
        try {
            const response = await fetch(API_BASE + '/auth/check_auth.php');
            const data = await response.json();
            
            if (data.authenticated) {
                currentUser.isLoggedIn = true;
                currentUser.id = data.user.id;
                currentUser.name = data.user.fullname;
                
                const organizerInput = document.getElementById('organizer');
                if (organizerInput && currentUser.name) {
                    organizerInput.value = currentUser.name;
                    organizerInput.readOnly = true;
                    organizerInput.style.backgroundColor = '#f5f5f5';
                }
            }
        } catch (error) {
            console.error('Auth check failed:', error);
        }
    }

    // Load groups from backend
    async function loadGroups() {
        try {
            showLoading();
            const response = await fetch(GROUPS_API.getAll);
            const data = await response.json();
            
            console.log('Groups API response:', data);
            
            if (data.status === 'success') {
                allGroups = data.data;
                renderGroups(allGroups);
            } else {
                showError('Failed to load groups');
            }
        } catch (error) {
            console.error('Error loading groups:', error);
            showError('Error connecting to server');
        }
    }

    // Show loading state
    function showLoading() {
        const container = document.getElementById('groups-container');
        if (container) {
            container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading groups...</div>';
        }
    }

    // Show error message
    function showError(message) {
        const container = document.getElementById('groups-container');
        if (container) {
            container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-circle"></i> ${message}</div>`;
        }
    }

    // Render groups to page
    function renderGroups(groups) {
        const container = document.getElementById('groups-container');
        const countLabel = document.getElementById('group-count');
        
        if (!container) return;
        
        if (!groups || groups.length === 0) {
            container.innerHTML = '<div class="no-groups"><i class="fas fa-users"></i> No study groups available. Create one!</div>';
            if (countLabel) countLabel.textContent = '0';
            return;
        }
        
        container.innerHTML = '';
        if (countLabel) countLabel.textContent = groups.length;
        
        groups.forEach(group => {
            const card = createGroupCard(group);
            container.appendChild(card);
        });
    }

    // Create group card HTML
    function createGroupCard(group) {
        const card = document.createElement('div');
        card.className = 'group-card';
        card.setAttribute('data-group-id', group.id);
        
        let daysArray = [];
        if (group.days) {
            try {
                daysArray = JSON.parse(group.days);
            } catch (e) {
                daysArray = group.days.split(',');
            }
        }
        
        const daysDisplay = daysArray.join(', ');
        const memberCount = group.member_count || 0;
        const maxMembers = group.max_members || 20;
        
        card.innerHTML = `
            <div class="group-info">
                <h4><i class="fas fa-book"></i> ${escapeHtml(group.subject || group.group_name)}</h4>
                <div class="group-meta">
                    <span><i class="fas fa-calendar"></i> ${escapeHtml(group.year || 'All Years')}</span>
                    <span><i class="fas fa-clock"></i> ${group.start_time || 'TBD'} - ${group.end_time || 'TBD'}</span>
                    <span><i class="fas fa-calendar-alt"></i> ${escapeHtml(daysDisplay) || 'Flexible'}</span>
                </div>
                <div class="group-members">
                    <i class="fas fa-users"></i> Members: ${memberCount}/${maxMembers}
                </div>
                <div class="group-organizer">
                    <i class="fas fa-user"></i> Organizer: ${escapeHtml(group.organizer || 'Admin')}
                </div>
                ${group.description ? `<div class="group-description"><i class="fas fa-info-circle"></i> ${escapeHtml(group.description)}</div>` : ''}
            </div>
            <button class="btn-join" onclick="window.joinGroup(${group.id}, this)">
                <i class="fas fa-sign-in-alt"></i> Join Group
            </button>
        `;
        
        return card;
    }

    // Join a group
    window.joinGroup = async function(groupId, buttonElement) {
        if (!currentUser.isLoggedIn) {
            if (confirm('Please login to join a study group. Click OK to login.')) {
                window.location.href = 'login.html';
            }
            return;
        }
        
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Joining...';
        
        try {
            const response = await fetch(GROUPS_API.join, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    group_id: groupId,
                    user_name: currentUser.name,
                    user_id: currentUser.id
                })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Joined!';
                buttonElement.style.background = '#28a745';
                alert('Successfully joined the group!');
                loadGroups();
            } else {
                buttonElement.innerHTML = '<i class="fas fa-sign-in-alt"></i> Join Group';
                buttonElement.disabled = false;
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error joining group:', error);
            buttonElement.innerHTML = '<i class="fas fa-sign-in-alt"></i> Join Group';
            buttonElement.disabled = false;
            alert('Failed to join group. Please try again.');
        }
    };

   // Create new group
async function createGroup(event) {
    event.preventDefault();
    
    console.log('=== CREATE GROUP DEBUG ===');
    console.log('Current user:', currentUser);
    
    if (!currentUser.isLoggedIn) {
        if (confirm('Please login to create a study group. Click OK to login.')) {
            window.location.href = 'login.html';
        }
        return;
    }
    
    const selectedDays = Array.from(document.querySelectorAll('.day-btn.selected'))
        .map(btn => btn.dataset.day);
    
    console.log('Selected days:', selectedDays);
    
    if (selectedDays.length === 0) {
        alert('Please select at least one day!');
        return;
    }
    
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;
    const subjectValue = document.getElementById('subject').value;
    const yearValue = document.getElementById('year').value;
    
    console.log('Subject:', subjectValue);
    console.log('Year:', yearValue);
    console.log('Start time:', startTime);
    console.log('End time:', endTime);
    
    if (!subjectValue) {
        alert('Please select a subject!');
        return;
    }
    
    if (!startTime || !endTime) {
        alert('Please select start and end time!');
        return;
    }
    
    const groupData = {
        group_name: subjectValue,
        subject: subjectValue,
        year: yearValue,
        days: JSON.stringify(selectedDays),
        start_time: startTime,
        end_time: endTime,
        organizer: currentUser.name,
        created_by: currentUser.id,
        description: `Study group for ${subjectValue}`
    };
    
    console.log('Sending data:', groupData);
    
    const submitButton = document.querySelector('#create-group-form button');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    submitButton.disabled = true;
    
    try {
        const response = await fetch(GROUPS_API.create, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(groupData)
        });
        
        const textResponse = await response.text();
        console.log('Raw response:', textResponse);
        
        let data;
        try {
            data = JSON.parse(textResponse);
        } catch(e) {
            console.error('JSON parse error:', e);
            alert('Server error: ' + textResponse.substring(0, 200));
            return;
        }
        
        if (data.status === 'success') {
            alert('Study group created successfully!');
            document.getElementById('create-group-form').reset();
            document.querySelectorAll('.day-btn.selected').forEach(btn => btn.classList.remove('selected'));
            
            const organizerInput = document.getElementById('organizer');
            if (organizerInput && currentUser.name) {
                organizerInput.value = currentUser.name;
            }
            
            loadGroups();
        } else {
            alert('Error: ' + (data.message || 'Could not create group'));
        }
    } catch (error) {
        console.error('Error creating group:', error);
        alert('Failed to create group. Please try again.');
    } finally {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

    // Filter groups
    window.filterGroups = function() {
        const filterYear = document.getElementById('filter-year').value;
        const filterSubject = document.getElementById('filter-subject').value;
        
        let filtered = [...allGroups];
        
        if (filterYear !== 'all') {
            filtered = filtered.filter(group => group.year === filterYear);
        }
        
        if (filterSubject !== 'all') {
            filtered = filtered.filter(group => group.subject === filterSubject);
        }
        
        renderGroups(filtered);
    };

    // Day selection logic
    function setupDaySelection() {
        const dayBtns = document.querySelectorAll('.day-btn');
        dayBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('selected');
            });
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', async () => {
        await checkAuth();
        await loadGroups();
        setupDaySelection();
        
        const createForm = document.getElementById('create-group-form');
        if (createForm) {
            createForm.addEventListener('submit', createGroup);
        }
    });
})();