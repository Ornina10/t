// mentors.js - Mentors page functionality
(function() {
    // API URLs for mentors - using API_BASE from global.js
    const MENTORS_API = {
        getAll: API_BASE + '/mentorship/get_mentors.php',
        request: API_BASE + '/mentorship/request_mentorship.php',
        becomeMentor: API_BASE + '/mentorship/become_mentor.php'
    };

    // Store current user info (from login)
    let currentUser = {
        isLoggedIn: false,
        name: '',
        email: '',
        department: '',
        year: '',
        role: ''
    };

    // Check authentication status
    async function checkAuthStatus() {
        try {
            const response = await fetch(API_BASE + '/auth/check_auth.php');
            const data = await response.json();
            
            if (data.authenticated) {
                currentUser.isLoggedIn = true;
                currentUser.name = data.user.fullname;
                currentUser.email = data.user.email;
                currentUser.department = data.user.department || '';
                currentUser.year = data.user.year || '';
                currentUser.role = data.user.role;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
        }
    }

    // Load mentors from backend API
    async function loadMentorsFromBackend() {
        const container = document.querySelector('.mentors-container');
        if (!container) return;
        
        container.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div><p>Loading mentors...</p></div>';
        
        try {
            const response = await fetch(MENTORS_API.getAll);
            const data = await response.json();
            
            console.log('Mentors API response:', data);
            
            if (data.status === 'success' && data.data && data.data.length > 0) {
                displayMentors(data.data);
            } else {
                container.innerHTML = `<div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>No mentors available at the moment. Please check back later.</p>
                </div>`;
            }
        } catch (error) {
            console.error('Error loading mentors:', error);
            container.innerHTML = `<div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error loading mentors. Please refresh the page.</p>
                <button onclick="location.reload()" style="margin-top: 10px; padding: 8px 20px; background: #11998e; color: white; border: none; border-radius: 5px; cursor: pointer;">Retry</button>
            </div>`;
        }
    }

    // Display mentors dynamically
    function displayMentors(mentors) {
        const container = document.querySelector('.mentors-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        mentors.forEach(mentor => {
            const card = createMentorCard(mentor);
            container.appendChild(card);
        });
    }

    // Create mentor card HTML
    function createMentorCard(mentor) {
        const article = document.createElement('article');
        article.className = 'mentor-card';
        
        // Get initials for avatar
        const initials = mentor.name.split(' ').map(n => n[0]).join('').toUpperCase();
        
        // Determine badge based on skills
        const skillBadge = getSkillBadge(mentor.skills || mentor.department);
        
        article.setAttribute('data-skill', (mentor.skills || '').toLowerCase());
        
        article.innerHTML = `
            <div class="card-top">
                <div class="avatar-circle">${escapeHtml(initials)}</div>
                <div class="mentor-info">
                    <h3>${escapeHtml(mentor.name)}</h3>
                    <span class="badge">${escapeHtml(skillBadge)}</span>
                </div>
            </div>
            <div class="card-details">
                <p><i class="fas fa-code"></i> <strong>Skills:</strong> ${escapeHtml(mentor.skills || 'Full Stack Development')}</p>
                <p><i class="fas fa-graduation-cap"></i> <strong>Year:</strong> ${escapeHtml(mentor.year || '5th Year')}</p>
                <p class="bio">"${escapeHtml(mentor.bio || 'Experienced mentor ready to help you succeed in your software engineering journey.')}"</p>
            </div>
            <button class="btn-outline" onclick="openMentorshipRequest('${escapeHtml(mentor.name)}', ${mentor.id})">
                Request Mentorship
            </button>
        `;
        
        return article;
    }

    // Get skill badge based on skills/department
    function getSkillBadge(skills) {
        if (!skills) return 'Full Stack';
        
        const skillsLower = skills.toLowerCase();
        if (skillsLower.includes('web') || skillsLower.includes('react') || skillsLower.includes('javascript')) {
            return 'Full Stack Web';
        } else if (skillsLower.includes('mobile') || skillsLower.includes('flutter')) {
            return 'Mobile App Dev';
        } else if (skillsLower.includes('backend') || skillsLower.includes('java') || skillsLower.includes('spring')) {
            return 'Backend Systems';
        } else if (skillsLower.includes('ai') || skillsLower.includes('machine') || skillsLower.includes('python')) {
            return 'AI & Data';
        } else if (skillsLower.includes('devops') || skillsLower.includes('cloud') || skillsLower.includes('docker')) {
            return 'DevOps';
        } else if (skillsLower.includes('algorithm') || skillsLower.includes('c++')) {
            return 'Algorithms';
        }
        return 'Software Engineering';
    }

    // Setup search functionality
    function setupSearch() {
        const keywordInput = document.getElementById('topic-keyword');
        if (keywordInput) {
            keywordInput.addEventListener('keyup', (e) => {
                const term = e.target.value.toLowerCase();
                const mentorCards = document.querySelectorAll('.mentor-card');
                
                mentorCards.forEach(card => {
                    const textContent = card.innerText.toLowerCase();
                    const skillTags = card.getAttribute('data-skill') || "";
                    
                    if (textContent.includes(term) || skillTags.includes(term)) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        }
    }

    // Setup form submissions
    function setupForms() {
        // Request Mentorship Form
        const requestForm = document.getElementById('request-form');
        if (requestForm) {
            requestForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!currentUser.isLoggedIn) {
                    alert('Please login to request mentorship.');
                    window.location.href = 'login.html';
                    return;
                }
                
                const topicInput = requestForm.querySelector('input[placeholder="Topic (e.g. Project Help)"]');
                const mentorName = document.getElementById('selected-mentor')?.value || 'General Mentor';
                
                const requestData = {
                    mentee_name: currentUser.name,
                    mentor_name: mentorName,
                    department: currentUser.department || 'Software Engineering',
                    message: topicInput?.value || 'General mentorship request'
                };
                
                try {
                    const response = await fetch(MENTORS_API.request, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(requestData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        alert(`Thank you ${requestData.mentee_name}! Your mentorship request has been sent.`);
                        requestForm.reset();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error submitting request:', error);
                    alert('Failed to submit request. Please try again.');
                }
            });
        }
        
        // Become Mentor Form
        const mentorRegForm = document.getElementById('mentor-register-form');
        if (mentorRegForm) {
            mentorRegForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!currentUser.isLoggedIn) {
                    alert('Please login to register as a mentor.');
                    window.location.href = 'login.html';
                    return;
                }
                
                const skillsInput = mentorRegForm.querySelector('input[placeholder="e.g. React, Python"]');
                
                const mentorData = {
                    name: currentUser.name,
                    email: currentUser.email,
                    skills: skillsInput?.value,
                    department: currentUser.department || 'Software Engineering',
                    year: currentUser.year || '4th Year'
                };
                
                try {
                    const response = await fetch(MENTORS_API.becomeMentor, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(mentorData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        alert("Welcome to the team! We will verify your academic status and contact you shortly.");
                        mentorRegForm.reset();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error registering as mentor:', error);
                    alert('Failed to register. Please try again.');
                }
            });
        }
    }

    // Open mentorship request modal/populate form (make it global for onclick)
    window.openMentorshipRequest = function(mentorName, mentorId) {
        if (!currentUser.isLoggedIn) {
            alert('Please login to request mentorship.');
            window.location.href = 'login.html';
            return;
        }
        
        // Store selected mentor name
        let selectedMentorInput = document.getElementById('selected-mentor');
        if (!selectedMentorInput) {
            selectedMentorInput = document.createElement('input');
            selectedMentorInput.type = 'hidden';
            selectedMentorInput.id = 'selected-mentor';
            document.getElementById('request-form')?.appendChild(selectedMentorInput);
        }
        selectedMentorInput.value = mentorName;
        
        // Populate the message field
        const messageField = document.querySelector('#request-mentorship input[placeholder="Topic (e.g. Project Help)"]');
        if (messageField) {
            messageField.value = `Help needed from ${mentorName}`;
            document.getElementById('request-mentorship').scrollIntoView({ behavior: 'smooth' });
            messageField.style.borderColor = '#11998e';
            setTimeout(() => messageField.style.borderColor = '#ddd', 2000);
        }
        
        // Also populate name field if user is logged in
        const nameField = document.querySelector('#request-mentorship input[placeholder="Your Name"]');
        if (nameField && currentUser.name) {
            nameField.value = currentUser.name;
            nameField.readOnly = true;
            nameField.style.backgroundColor = '#f5f5f5';
        }
    };

    // Keep the original contactMentor function for compatibility
    window.contactMentor = function(mentorName) {
        window.openMentorshipRequest(mentorName, null);
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize page
    document.addEventListener("DOMContentLoaded", async () => {
        await checkAuthStatus();
        await loadMentorsFromBackend();
        setupSearch();
        setupForms();
    });
})();