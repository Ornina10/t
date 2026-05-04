// Upload form handling
const uploadForm = document.getElementById('uploadForm');
const messageDiv = document.getElementById('message');

if (uploadForm) {
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const title = document.getElementById('title').value.trim();
        const yearRadios = document.querySelectorAll('input[name="year"]');
        const file = document.getElementById('file').files[0];
        
        let selectedYear = '';
        for (const radio of yearRadios) {
            if (radio.checked) {
                selectedYear = radio.value;
                break;
            }
        }
        
        if (!title) {
            showMessage('Please enter course title', 'error');
            return;
        }
        
        if (!selectedYear) {
            showMessage('Please select a year', 'error');
            return;
        }
        
        if (!file) {
            showMessage('Please select a file', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('title', title);
        formData.append('full_name', document.getElementById('full_name').value || 'Anonymous');
        formData.append('year', selectedYear);
        formData.append('department', document.getElementById('department').value);
        formData.append('material_type', document.getElementById('material_type').value);
        formData.append('file', file);
        
        const submitBtn = document.querySelector('.btn-upload');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/campus-connect/php--campus-connect-application-2/backendpart/resources/upload_resource.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showMessage('✅ Upload successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = 'materials.html';
                }, 1500);
            } else {
                showMessage('❌ Error: ' + data.message, 'error');
                submitBtn.innerHTML = 'Upload Material';
                submitBtn.disabled = false;
            }
        } catch (error) {
            showMessage('❌ Upload failed. Please try again.', 'error');
            submitBtn.innerHTML = 'Upload Material';
            submitBtn.disabled = false;
        }
    });
}

function showMessage(msg, type) {
    messageDiv.textContent = msg;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 4000);
}