// internship.js - Internships page functionality
(function() {
    // API URL - using API_BASE from global.js
    const API_URL = API_BASE + '/internships/get_internship.php';
    let allInternships = [];

    // Load internships from backend
    async function loadInternships() {
        const container = document.getElementById('internships-list');
        if (!container) return;
        
        container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading internships from database...</div>';
        
        try {
            const response = await fetch(API_URL);
            const data = await response.json();
            
            console.log('Internships API response:', data);
            
            if (data.status === 'success' && data.data && data.data.length > 0) {
                allInternships = data.data;
                renderInternships(allInternships);
            } else {
                container.innerHTML = '<div class="no-internships-message"><i class="fas fa-database"></i> No internships found in database. Please check back later.</div>';
            }
        } catch (error) {
            console.error('Error fetching internships:', error);
            container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Failed to load internships. Please try again later.</div>';
        }
    }

    function renderInternships(internships) {
        const container = document.getElementById('internships-list');
        if (!container) return;
        
        if (!internships || internships.length === 0) {
            container.innerHTML = '<div class="no-internships-message"><i class="fas fa-info-circle"></i> No internships match your selected filters.</div>';
            return;
        }
        
        container.innerHTML = '';
        
        internships.forEach(internship => {
            const card = createInternshipCard(internship);
            container.appendChild(card);
        });
        
        checkDeadlines();
    }

    function createInternshipCard(internship) {
        const card = document.createElement('div');
        card.className = 'internship-card';
        
        card.setAttribute('data-years', internship.year_requirement || '');
        card.setAttribute('data-stipend', internship.stipend_type || 'unpaid');
        card.setAttribute('data-worktype', internship.work_type || 'on-site');
        
        let deadlineFormatted = 'Not specified';
        if (internship.deadline) {
            try {
                const deadlineDate = new Date(internship.deadline);
                if (!isNaN(deadlineDate.getTime())) {
                    deadlineFormatted = deadlineDate.toLocaleDateString('en-GB');
                }
            } catch(e) {}
        }
        
        const stipendClass = internship.stipend_type === 'paid' ? 'paid' : 'unpaid';
        const stipendText = internship.stipend_type === 'paid' 
            ? `Paid: ${internship.stipend || 0} birr/month` 
            : 'Unpaid';
        
        const workTypeClass = internship.work_type ? internship.work_type.toLowerCase() : 'on-site';
        const workTypeDisplay = internship.work_type ? internship.work_type.charAt(0).toUpperCase() + internship.work_type.slice(1) : 'On-site';
        
        let locationHtml = `<div class="location"><i class="fas fa-map-marker-alt"></i> <b>Location:</b> ${escapeHtml(internship.location || 'Not specified')}`;
        if (internship.work_type !== 'remote' && internship.location && internship.location !== 'Not specified') {
            const mapsQuery = encodeURIComponent(internship.location);
            locationHtml += ` <a href="https://maps.google.com/?q=${mapsQuery}" target="_blank"><i class="fas fa-external-link-alt"></i> View Map</a>`;
        }
        locationHtml += `</div>`;
        
        let requirementsHtml = '<div class="requirements"><h3>Requirements:</h3><ul>';
        if (internship.requirements) {
            const requirements = internship.requirements.split(',').map(r => r.trim()).filter(r => r);
            requirements.forEach(req => {
                requirementsHtml += `<li>${escapeHtml(req)}</li>`;
            });
        } else {
            requirementsHtml += '<li>No specific requirements listed</li>';
        }
        requirementsHtml += '</ul></div>';
        
        let descriptionHtml = '';
        if (internship.description && internship.description.trim()) {
            descriptionHtml = `<div class="description"><h3>Description:</h3><p>${escapeHtml(internship.description)}</p></div>`;
        }
        
        card.innerHTML = `
            <h2>${escapeHtml(internship.title)}</h2>
            <div class="badges">
                <span class="badge ${stipendClass}">${stipendText}</span>
                <span class="badge ${workTypeClass}">${escapeHtml(workTypeDisplay)}</span>
            </div>
            
            <div class="basic-info">
                <div><i class="fas fa-building"></i> <b>Company:</b> ${escapeHtml(internship.company)}</div>
                <div><i class="fas fa-user-graduate"></i> <b>Year:</b> ${escapeHtml(internship.year_requirement || 'All Years')}</div>
                <div><i class="fas fa-calendar"></i> <b>Deadline:</b> ${deadlineFormatted}</div>
                <div><i class="fas fa-clock"></i> <b>Duration:</b> ${escapeHtml(internship.duration || 'Not specified')}</div>
            </div>
            
            ${locationHtml}
            ${requirementsHtml}
            ${descriptionHtml}
            
            <div class="apply-section">
                <a href="#" class="apply-btn" onclick="window.open('https://forms.gle/apply-${internship.id}', '_blank'); return false;">
                    <i class="fas fa-paper-plane"></i> Apply Now
                </a>
                <div class="how-to-apply">
                    <b>How to apply:</b> Click the Apply Now button above.
                </div>
            </div>
        `;
        
        return card;
    }

    function filterJobs() {
        const yearFilter = document.getElementById('year-filter')?.value || 'all';
        const stipendFilter = document.getElementById('stipend-filter')?.value || 'all';
        const worktypeFilter = document.getElementById('worktype-filter')?.value || 'all';
        
        const cards = document.querySelectorAll('.internship-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            let show = true;
            
            if (yearFilter !== 'all') {
                const cardYears = card.getAttribute('data-years');
                if (cardYears) {
                    const years = cardYears.split(',').map(y => y.trim());
                    if (!years.includes(yearFilter)) show = false;
                } else {
                    show = false;
                }
            }
            
            if (show && stipendFilter !== 'all') {
                const cardStipend = card.getAttribute('data-stipend');
                if (cardStipend !== stipendFilter) show = false;
            }
            
            if (show && worktypeFilter !== 'all') {
                const cardWorktype = card.getAttribute('data-worktype');
                if (cardWorktype !== worktypeFilter) show = false;
            }
            
            card.style.display = show ? 'block' : 'none';
            if (show) visibleCount++;
        });
        
        const container = document.getElementById('internships-list');
        if (container && visibleCount === 0 && cards.length > 0) {
            let noResultsMsg = container.querySelector('.no-results-message');
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-internships-message no-results-message';
                noResultsMsg.innerHTML = '<i class="fas fa-filter"></i> No internships match your selected filters. Try adjusting your criteria.';
                container.appendChild(noResultsMsg);
            }
        } else {
            const existingMsg = container?.querySelector('.no-results-message');
            if (existingMsg) existingMsg.remove();
        }
    }

    function checkDeadlines() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const cards = document.querySelectorAll('.internship-card');
        
        cards.forEach(card => {
            const deadlineSpan = card.querySelector('.basic-info div:nth-child(3)');
            if (deadlineSpan) {
                const deadlineText = deadlineSpan.innerText.replace('Deadline:', '').trim();
                const dateParts = deadlineText.split('/');
                
                if (dateParts.length === 3) {
                    const day = parseInt(dateParts[0], 10);
                    const month = parseInt(dateParts[1], 10) - 1;
                    const year = parseInt(dateParts[2], 10);
                    const deadlineDate = new Date(year, month, day);
                    
                    if (!isNaN(deadlineDate.getTime()) && today > deadlineDate) {
                        const btn = card.querySelector('.apply-btn');
                        const title = card.querySelector('h2');
                        
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-times"></i> Application Closed';
                            btn.style.background = '#aaa';
                            btn.style.cursor = 'not-allowed';
                            btn.style.pointerEvents = 'none';
                        }
                        if (title) {
                            title.style.textDecoration = 'line-through';
                            title.style.opacity = '0.6';
                        }
                    }
                }
            }
        });
    }

    function setupFilters() {
        const yearFilter = document.getElementById('year-filter');
        const stipendFilter = document.getElementById('stipend-filter');
        const worktypeFilter = document.getElementById('worktype-filter');
        
        if (yearFilter) yearFilter.addEventListener('change', filterJobs);
        if (stipendFilter) stipendFilter.addEventListener('change', filterJobs);
        if (worktypeFilter) worktypeFilter.addEventListener('change', filterJobs);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        setupFilters();
        await loadInternships();
    });
})();