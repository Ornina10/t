// Department/Course Finder JavaScript
(function() {
    // API Configuration - using API_BASE from global.js
    const API_URL = API_BASE + '/departments/get_courses.php';

    // Fallback sample data
    const sampleCourses = [
        { course_code: "MATH101", course_name: "Mathematics for Engineers I", credit_hours: 3, prerequisites: "None", year: 1, semester: 1, field: "Freshman (Common Courses)" },
        { course_code: "PHYS101", course_name: "General Physics I", credit_hours: 3, prerequisites: "None", year: 1, semester: 1, field: "Freshman (Common Courses)" },
        { course_code: "CS101", course_name: "Introduction to Programming", credit_hours: 4, prerequisites: "None", year: 2, semester: 1, field: "Software Engineering" },
        { course_code: "SE101", course_name: "Software Engineering Fundamentals", credit_hours: 3, prerequisites: "CS101", year: 2, semester: 1, field: "Software Engineering" }
    ];

    let allCourses = [];
    let isLoading = false;

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function showError(message) {
        const resultsContainer = document.getElementById('results-container');
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${escapeHtml(message)}</p>
                    <button onclick="retryFetchCourses()" style="margin-top: 15px; padding: 8px 20px; background: #11998e; color: white; border: none; border-radius: 20px; cursor: pointer;">
                        <i class="fas fa-sync-alt"></i> Try Again
                    </button>
                </div>
            `;
        }
    }

    function showLoading() {
        const resultsContainer = document.getElementById('results-container');
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading courses from database...</p>
                </div>
            `;
        }
    }

    function toggleCourseContent(header) {
        const content = header.nextElementSibling;
        header.classList.toggle('collapsed');
        content.classList.toggle('collapsed');
    }

    function renderCourses(courses) {
        const resultsContainer = document.getElementById('results-container');
        const statsBar = document.getElementById('statsBar');
        const courseCountSpan = document.getElementById('courseCount');

        if (!resultsContainer) return;

        if (courses.length === 0) {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>No courses match your filters.</p>
                </div>
            `;
            if (statsBar) statsBar.style.display = 'none';
            return;
        }

        if (statsBar) statsBar.style.display = 'flex';
        if (courseCountSpan) {
            courseCountSpan.textContent = `${courses.length} course${courses.length !== 1 ? 's' : ''} found`;
        }

        const grouped = courses.reduce((acc, course) => {
            const key = `${course.field} - Year ${course.year} - Semester ${course.semester}`;
            if (!acc[key]) acc[key] = [];
            acc[key].push(course);
            return acc;
        }, {});

        resultsContainer.innerHTML = '';
        
        for (const [headerText, courseList] of Object.entries(grouped)) {
            const card = document.createElement('div');
            card.className = 'course-card';
            
            let rowsHtml = '';
            courseList.forEach(c => {
                rowsHtml += `
                    <div class="course-item-row">
                        <span class="code">${escapeHtml(c.course_code)}</span>
                        <span class="title">${escapeHtml(c.course_name)}</span>
                        <span class="credits">${c.credit_hours} Cr</span>
                        <span class="prereq">Prereq: ${escapeHtml(c.prerequisites || 'None')}</span>
                    </div>
                `;
            });
            
            card.innerHTML = `
                <div class="course-header" onclick="toggleCourseContent(this)">
                    <span>${escapeHtml(headerText)}</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="course-content">
                    ${rowsHtml}
                </div>
            `;
            resultsContainer.appendChild(card);
        }
    }

    function applyFilters() {
        const filterField = document.getElementById('filter-field');
        const filterYear = document.getElementById('filter-year');
        const filterSemester = document.getElementById('filter-semester');
        
        let filtered = [...allCourses];
        
        const fieldVal = filterField ? filterField.value : '';
        const yearVal = filterYear ? filterYear.value : '';
        const semVal = filterSemester ? filterSemester.value : '';

        if (fieldVal) {
            filtered = filtered.filter(c => c.field === fieldVal);
        }
        if (yearVal) {
            filtered = filtered.filter(c => c.year === parseInt(yearVal));
        }
        if (semVal) {
            filtered = filtered.filter(c => c.semester === parseInt(semVal));
        }

        renderCourses(filtered);
    }

    function resetFilters() {
        const filterField = document.getElementById('filter-field');
        const filterYear = document.getElementById('filter-year');
        const filterSemester = document.getElementById('filter-semester');
        
        if (filterField) filterField.value = '';
        if (filterYear) filterYear.value = '';
        if (filterSemester) filterSemester.value = '';
        applyFilters();
    }

    async function fetchCourses() {
        if (isLoading) return;
        isLoading = true;
        
        showLoading();
        
        try {
            const response = await fetch(API_URL);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                allCourses = data.data;
                console.log(`✅ Loaded ${allCourses.length} courses from database`);
            } else {
                console.warn('⚠️ No data from API, using sample data');
                allCourses = sampleCourses;
            }
            
            applyFilters();
            
        } catch (error) {
            console.error('❌ Error fetching courses:', error);
            allCourses = sampleCourses;
            applyFilters();
            showError('Database connection failed. Showing sample data.');
        } finally {
            isLoading = false;
        }
    }

    window.retryFetchCourses = function() {
        fetchCourses();
    };

    window.toggleCourseContent = toggleCourseContent;

    function initDepartmentPage() {
        const filterField = document.getElementById('filter-field');
        const filterYear = document.getElementById('filter-year');
        const filterSemester = document.getElementById('filter-semester');
        const resetBtn = document.getElementById('resetFilters');

        if (filterField) filterField.addEventListener('change', applyFilters);
        if (filterYear) filterYear.addEventListener('change', applyFilters);
        if (filterSemester) filterSemester.addEventListener('change', applyFilters);
        if (resetBtn) resetBtn.addEventListener('click', resetFilters);

        fetchCourses();
    }

    document.addEventListener('DOMContentLoaded', initDepartmentPage);
})();