document.addEventListener('DOMContentLoaded', function() {
    initPhotoGallery();
});

function initPhotoGallery() {
    const photoCards = document.querySelectorAll('.photo-card');
    const photoModal = document.getElementById('photoModal');
    const modalImage = document.getElementById('modalImage');
    const photographerLink = document.getElementById('photographerLink');
    const photographerName = document.getElementById('photographerName');

    if (!photoModal || !modalImage) return;

    const modal = new bootstrap.Modal(photoModal);

    photoCards.forEach(card => {
        card.addEventListener('click', function() {
            const imgUrl = this.dataset.fullImage;
            const photographer = this.dataset.photographer;
            const photographerUrl = this.dataset.photographerUrl;

            modalImage.src = imgUrl;
            photographerName.textContent = photographer;
            photographerLink.href = photographerUrl;
            
            modal.show();
        });
    });

    modalImage.addEventListener('load', function() {
        this.classList.remove('d-none');
        document.getElementById('modalSpinner')?.classList.add('d-none');
    });

    photoModal.addEventListener('show.bs.modal', function() {
        modalImage.classList.add('d-none');
        document.getElementById('modalSpinner')?.classList.remove('d-none');
    });
}
