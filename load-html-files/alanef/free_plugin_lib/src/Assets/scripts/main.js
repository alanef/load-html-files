document.addEventListener('DOMContentLoaded', function () {
    function toggleDetails(event) {
        event.preventDefault();
        const detailsContent = document.getElementById('detailsContent');

        // Make sure the element's current display state is correctly used
        if (detailsContent.style.display === 'none' || detailsContent.style.display === '') {
            detailsContent.style.display = 'block';
        } else {
            detailsContent.style.display = 'none';
        }
    }

    const detailsLink = document.getElementById('detailsLink');
    detailsLink.addEventListener('click', toggleDetails);
});