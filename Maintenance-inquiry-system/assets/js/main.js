function fetchRecords() {
    fetch('api/getRecords.php')
        .then(response => response.json())
        .then(data => {
            const recordsContainer = document.getElementById('records');
            recordsContainer.innerHTML = data.map(record => `<p>${record.description}</p>`).join('');
        })
        .catch(error => console.error('Error fetching records:', error));
}
