const materialsContainer = document.querySelector('.materials-container');
const searchInput = document.querySelector('.search-bar');
let allMaterials = JSON.parse(localStorage.getItem('materials') || '[]');
function displayMaterials(materials) {
    materialsContainer.innerHTML = '';

    if (materials.length === 0) {
        materialsContainer.innerHTML = '<p style="text-align:center; font-size:20px; margin-top:20px;">No file found</p>';
        return;
    }

    materials.forEach((mat, index) => {
        const card = document.createElement('div');
        card.classList.add('material-card');
        card.innerHTML = `
            <h3>${mat.title}</h3>
            <p><b>Year:</b> ${mat.year}</p>
            <p><b>Department:</b> ${mat.department}</p>
            <p><b>Uploaded by:</b> ${mat.fullName}</p>
            <p><b>Type:</b> ${mat.type}</p>
            <a href="${mat.fileData}" download="${mat.fileName}" class="download-btn">Download</a>
            <button class="delete" data-index="${index}">Delete</button>
        `;
        materialsContainer.appendChild(card);
    });

    const deleteButtons = document.querySelectorAll('.delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.index);
            allMaterials.splice(idx, 1);
            localStorage.setItem('materials', JSON.stringify(allMaterials));
            displayMaterials(allMaterials);
        });
    });
}
displayMaterials(allMaterials);
searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    const filtered = allMaterials.filter(mat => 
        mat.title.toLowerCase().includes(query) ||
        mat.fullName.toLowerCase().includes(query) ||
        mat.year.toLowerCase().includes(query) ||
        mat.department.toLowerCase().includes(query) ||
        mat.type.toLowerCase().includes(query)
    );
    displayMaterials(filtered);
});
