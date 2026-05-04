// materials.js - Wrapped to avoid conflicts
(function() {
    // Use the existing API_BASE from global.js (don't declare it here)
    const GET_URL = API_BASE + '/resources/get_resources.php';
    const DELETE_URL = API_BASE + '/resources/delete_resource.php';
    
    let currentUser = { role: '' };
    let allMaterials = [];
    
    // Check authentication
    async function checkAuth() {
        try {
            const response = await fetch(API_BASE + '/auth/check_auth.php');
            const data = await response.json();
            if (data.authenticated) {
                currentUser.role = data.user.role || 'student';
            }
        } catch (error) {
            console.error('Auth error:', error);
        }
    }
    
    // Load materials
    async function loadMaterials() {
        const container = document.getElementById('materialsContainer');
        if (!container) return;
        
        container.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div><p>Loading materials...</p></div>';
        
        try {
            const response = await fetch(GET_URL);
            const data = await response.json();
            
            if (data.status === 'success' && data.data && data.data.length > 0) {
                allMaterials = data.data;
                displayMaterials(allMaterials);
            } else {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Materials Found</h3>
                        <p>Be the first to share your study materials!</p>
                        <a href="upload.html" class="upload-btn-large" style="display: inline-block; margin-top: 20px;">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Now
                        </a>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading materials:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h3>Error Loading Materials</h3><p>Please try again later.</p></div>';
        }
    }
    
    // Display materials
    function displayMaterials(materials) {
        const container = document.getElementById('materialsContainer');
        container.innerHTML = '';
        
        materials.forEach(mat => {
            const card = document.createElement('div');
            card.className = 'material-card';
            
            const fileName = mat.file_name || '';
            const extension = fileName.split('.').pop().toLowerCase();
            let fileIcon = 'fa-file-alt';
            if (extension === 'pdf') fileIcon = 'fa-file-pdf';
            else if (extension === 'jpg' || extension === 'jpeg' || extension === 'png') fileIcon = 'fa-file-image';
            else if (extension === 'mp4' || extension === 'mov') fileIcon = 'fa-file-video';
            else if (extension === 'doc' || extension === 'docx') fileIcon = 'fa-file-word';
            else if (extension === 'xls' || extension === 'xlsx') fileIcon = 'fa-file-excel';
            else if (extension === 'ppt' || extension === 'pptx') fileIcon = 'fa-file-powerpoint';
            
            card.innerHTML = `
                <div class="material-card-header">
                    <h3><i class="fas ${fileIcon}"></i> ${escapeHtml(mat.title)}</h3>
                </div>
                <div class="material-card-body">
                    <div class="material-info">
                        <i class="fas fa-calendar"></i> <span><strong>Year:</strong> ${escapeHtml(mat.year)}</span>
                    </div>
                    <div class="material-info">
                        <i class="fas fa-building"></i> <span><strong>Dept:</strong> ${escapeHtml(mat.department)}</span>
                    </div>
                    <div class="material-info">
                        <i class="fas fa-user"></i> <span><strong>By:</strong> ${escapeHtml(mat.full_name)}</span>
                    </div>
                    <div class="material-info">
                        <i class="fas fa-tag"></i> <span><strong>Type:</strong> ${escapeHtml(mat.material_type)}</span>
                    </div>
                    <div class="material-info">
                        <i class="fas fa-download"></i> <span><strong>Downloads:</strong> ${mat.downloads_count || 0}</span>
                    </div>
                    <div class="material-actions">
                        <a href="${API_BASE}/resources/download_resource.php?id=${mat.id}" class="btn-download" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                        ${currentUser.role === 'admin' ? `<button class="btn-delete" onclick="deleteMaterial(${mat.id})"><i class="fas fa-trash"></i> Delete</button>` : ''}
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }
    
    // Delete material
    window.deleteMaterial = async function(id) {
        if (!confirm('Are you sure you want to delete this material?')) return;
        
        try {
            const response = await fetch(DELETE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                alert('Material deleted successfully!');
                loadMaterials();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Failed to delete material');
        }
    };
    
    // Search materials
    function searchMaterials() {
        const term = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const filtered = allMaterials.filter(mat => 
            mat.title.toLowerCase().includes(term) ||
            (mat.full_name && mat.full_name.toLowerCase().includes(term)) ||
            mat.year.toLowerCase().includes(term) ||
            mat.material_type.toLowerCase().includes(term)
        );
        displayMaterials(filtered);
    }
    
    // Helper function
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', async () => {
        await checkAuth();
        await loadMaterials();
        
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', searchMaterials);
        }
    });
})();